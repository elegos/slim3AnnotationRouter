<?php

/**
 * This file is part of slim3AnnotationRouter.
 *
 * slim3AnnotationRouter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * slim3AnnotationRouter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with slim3AnnotationRouter.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace giacomofurlan\slim3AnnotationRouter;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use Slim\App;

class Router
{
    /** @var App $app */
    private $app;
    /** @var string[] $controllersDirectory */
    private $controllersDirectory;
    /** @var string $cacheDirectory */
    private $cacheDirectory;

    /**
     * Router constructor.
     * @param App $app
     * @param string|string[] $controllersDirectory
     * @param string|null $cacheDirectory if null, cache is disabled
     */
    public function __construct(App $app, $controllersDirectory, $cacheDirectory = null)
    {
        $this->app = $app;
        $this->controllersDirectory = is_array($controllersDirectory) ? $controllersDirectory : [$controllersDirectory];
        $this->cacheDirectory = $cacheDirectory;

        $routes = $this->getRoutes();
        $this->registerRoutes($routes);
    }

    private function getRoutes()
    {
        $time = 0;
        $files = [];

        foreach($this->controllersDirectory as $dir) {
            $directory = new \RecursiveDirectoryIterator($dir);
            $iterator = new \RecursiveIteratorIterator($directory);
            $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

            foreach ($regex as $item) {
                $item = $item[0];

                $mTime = filemtime($item);
                $files[] = $item;

                if ($mTime > $time) {
                    $time = $mTime;
                }
            }
        }

        # No files found
        if ($time === 0) {
            return [];
        }

        $cacheFile = null;
        if ($this->cacheDirectory) {
            $cacheFile = $this->cacheDirectory . DIRECTORY_SEPARATOR . $time . '.php';

            if (!is_dir($this->cacheDirectory)) {
                mkdir($this->cacheDirectory, 0777, true);
            }

            # Cache already updated
            if (file_exists($cacheFile)) {
                return include $cacheFile;
            }
        }

        # Cache disabled, or cache doesn't exist
        foreach($files as $file) {
            require_once $file;
        }

        $classes = array_filter(get_declared_classes(), function ($className) {
            return preg_match('/Controller$/', $className);
        });

        $routes = $this->getRoutesFromClassNames($classes);

        $this->writeCache($routes, $cacheFile);

        return $routes;
    }

    private function getParametersFromReflectionMethod(ReflectionMethod $method)
    {
        $parameters = [];
        
        foreach($method->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass();
            $parameters[$parameter->getPosition()] = 
                new Argument($parameter->getName(), $parameterClass ? $parameterClass->getName() : null);
        }
        
        return $parameters;
    }

    /**
     * @param string[] $classes
     * @return Route[]
     */
    private function getRoutesFromClassNames($classes)
    {
        $routes = [];

        foreach($classes as $class) {
            $reflector = new \ReflectionClass($class);

            $classPrefix = "";
            if (preg_match('/@Route\("([^"]+)"\)/', $reflector->getDocComment(), $matches)) {
                $classPrefix = $matches[1];
            }
            
            $constructor = $reflector->getConstructor();
            $constructorArgs = $constructor
                ? $this->getParametersFromReflectionMethod($constructor)
                : [];

            foreach($reflector->getMethods() as $method) {
                if (preg_match('/@Route\(([^)]+)\)/', $method->getDocComment(), $matches)) {
                    $match = $matches[1];
                    preg_match('/^"([^"]+)/', $match, $routeMatches);
                    $route = $classPrefix . $routeMatches[1];

                    $arguments = $this->getParametersFromReflectionMethod($method);

                    if (preg_match('/methods\s{0,}=\s{0,}\[([^\]]+)\]/', $match, $methodsMatches)) {
                        $methods = array_map(function ($element) {
                            return trim(str_replace('"', '', $element));
                        }, explode(',', $methodsMatches[1]));
                    } else {
                        $methods = Route::ALL_METHODS;
                    }

                    $name = null;
                    if (preg_match('/name\s{0,}=\s{0,}"([^"]+)"/', $match, $nameMatches)) {
                        $name = $nameMatches[1];
                    }

                    $routes[] = new Route($class, $constructorArgs, $method->getName(), $arguments, $name, $route, $methods);
                }
            }
        }

        return $routes;
    }

    /**
     * Writes the cache file, if cache directory is set
     * @param Route[] $routes
     * @param $cacheFile
     */
    private function writeCache($routes, $cacheFile)
    {
        if ($this->cacheDirectory) {
            $write = [
                '<?php',
                '# THIS FILE IS AUTO-GENERATED: DO NOT EDIT',
                'use giacomofurlan\slim3AnnotationRouter\Argument;',
                'use giacomofurlan\slim3AnnotationRouter\Route;',
                '$routes = [];'
            ];
            foreach ($routes as $route) {
                $constructorArguments = array_map(function ($element) {
                    /** @var Argument $element */
                    return sprintf(
                        'new Argument("%s", %s)',
                        $element->getName(),
                        $element->getClass() ? sprintf('"%s"', str_replace('\\', '\\\\', $element->getClass())) : 'null'
                    );
                }, $route->getConstructorArguments());
                $constructorArguments = implode(',', $constructorArguments);

                $methods = array_map(function ($element) {
                    return sprintf('"%s"', $element);
                }, $route->getMethods());
                $methods = implode(',', $methods);

                $arguments = array_map(function ($element) {
                    /** @var Argument $element */
                    return sprintf(
                        'new Argument("%s", %s)',
                        $element->getName(),
                        $element->getClass() ? sprintf('"%s"', str_replace('\\', '\\\\', $element->getClass())) : 'null'
                    );
                }, $route->getArguments());
                $arguments = implode(',', $arguments);

                $write[] = sprintf(
                    '$routes[] = new Route("%s", [%s], "%s", [%s], "%s", "%s", [%s]);',
                    str_replace('\\', '\\\\', $route->getClass()),
                    $constructorArguments,
                    $route->getClassMethod(),
                    $arguments,
                    $route->getName(),
                    $route->getRoute(),
                    $methods
                );
            }

            $write[] = 'return $routes;';

            $write = implode("\n", $write);

            # Delete old cache
            $cacheDir = dirname($cacheFile);
            array_map('unlink', glob("$cacheDir/*.php"));

            # Write the new file
            file_put_contents($cacheFile, $write);
        }
    }

    /**
     * @param Argument[] $arguments
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @param array $routeArguments
     * @return array
     */
    private function getMethodArgumentsByArguments(array $arguments, ServerRequestInterface $request = null,
                                                  ResponseInterface $response = null, array $routeArguments = [])
    {
        $args = [];

        foreach($arguments as $argument) {
            $argClass = $argument->getClass();

            # Primitive type
            if (!$argClass) {
                $name = $argument->getName();
                if (isset($routeArguments[$name])) {
                    $args[] = $routeArguments[$name];

                    continue;
                }

                # Not found, set null
                $args[] = null;

                continue;
            }

            # Standard injections

            $injections = [
                App::class => $this->app,
                ContainerInterface::class => $this->app->getContainer(),
                ServerRequestInterface::class => $request,
                ResponseInterface::class => $response,
            ];

            foreach($injections as $class => $injection) {
                if($argClass === $class || in_array($class, class_implements($argClass))) {
                    $args[] = $injection;

                    continue 2;
                }
            }

            # Not found, probable null pointer exception
            $args[] = null;
        }

        return $args;
    }

    /**
     * Register the found routes to Slim3
     * @param Route[] $routes
     * @todo move argument class-specific code in decorators (\DateTime => specific function etc)
     */
    private function registerRoutes($routes)
    {
        foreach($routes as $route) {
            $methods = $route->getMethods();
            $router = $this;
            
            $this->app->map($methods, $route->getRoute(),
                function (ServerRequestInterface $request, ResponseInterface $response, $arguments) use ($route, $router) {
                    $constructorArgs = $router->getMethodArgumentsByArguments($route->getConstructorArguments());
                    $methodArgs =
                        $router->getMethodArgumentsByArguments($route->getArguments(), $request, $response, $arguments);

                    $class = $route->getClass();
                    $class = new $class(...$constructorArgs);
                    $class->{$route->getClassMethod()}(...$methodArgs);
                }
            );
        }
    }
}
