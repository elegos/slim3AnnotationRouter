Slim3 Annotation Router
---
**by [Giacomo Furlan](http://giacomofurlan.name "Giacomo Furlan's website")**

This library allows routes to be defined in Slim3 with annotations.

Supported syntax:

    /**
    * @Route("/class/route/prefix")
    */
    class MyController {
        /**
        * @Route("/path/{arg}/{arg2}", name="route.name" methods=["GET", "POST"])
        */
        public function myAction(ServerRequestInterface $request, ResponseInterface $response, $arg1, $arg2)
        { ... }
    }

Rules:
- Controller classes' names MUST end with the suffix "Controller", i.e. `MyTestController`.
- Controller constructors MAY ONLY have `App` or `ContainerInterface` dependencies.
- `@Route` annotation needs at least the route (first argument in quotes)
- Classes MAY have the `@Route` annotation specifying only the prefix for the whole class' actions
- Methods MAY specify the `@Route` annotation. Optional values are:
    - `name`: the name of the route (in order to be called somewhere else)
    - `methods`: to specify at which methods the action responds. Default: all
- Methods can specify any argument written in the route (as placeholders) and are mapped per-name, case sensitive.
This means that if there is a route placeholder `{myArgument}`, there MAY be a method's argument `$myArgument`.
 If there is a method's argument called `$myArgument` there MUST be a `{myArgument}` route placeholder. Exceptions
 are the `ServerRequestInterface`, `ResponseInterface` and `ContainerInterface` arguments, that MAY be called with any variable name
 and are always the standard `$request`, `$response` and `$app->getContainer()` Slim3 objects.

Setup
---

    <?php
    use giacomofurlan\slim3AnnotationRouter\Router;

    # include your autoload

    $app = new \Slim\App(...);

    # define variables
    new Router($app, $controllersDirectory, $cacheDir);

    $app->run();

`$controllersDirectory` can either be a string, or an array of strings. You can thus define multiple controller
directories, if needed. Subdirectories will be scanned automatically.

`$cacheDir` is optional, default `null`. If set, cache files will be generated, so that the second time
the app is loaded it won't have to scan the directories again avoiding reflection classes. Cache is written again every
time a controller file has been modified.
