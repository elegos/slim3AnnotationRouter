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

class Route
{
    const ALL_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /** @var string $class */
    private $class;
    /** @var Argument[] $constructorArguments */
    private $constructorArguments;
    /** @var string $classMethod */
    private $classMethod;
    /** @var string $name */
    private $name;
    /** @var string $route */
    private $route;
    /** @var string[] $methods */
    private $methods;
    /** @var Argument[] $arguments */
    private $arguments;

    /**
     * Route constructor.
     * @param $class
     * @param Argument[] $constructorArguments
     * @param $method
     * @param Argument[] $arguments
     * @param string $name
     * @param string $route
     * @param \string[]|null $methods if set to null, all the methods are valid
     */
    public function __construct($class, array $constructorArguments, $method, array $arguments, $name, $route, array $methods = null)
    {
        $this->class = $class;
        $this->constructorArguments = $constructorArguments;
        $this->classMethod = $method;
        $this->arguments = $arguments;
        $this->name = $name;
        $this->route = $route;
        $this->methods = $methods;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return Argument[]
     */
    public function getConstructorArguments()
    {
        return $this->constructorArguments;
    }

    /**
     * @return string
     */
    public function getClassMethod()
    {
        return $this->classMethod;
    }

    /**
     * @return Argument[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return \string[]
     */
    public function getMethods()
    {
        return $this->methods;
    }
}
