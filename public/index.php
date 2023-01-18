<?php

use FastRoute\RouteCollector;
use Pimple\Container;
use Rrmode\Pihach\Controller\ThreadController;
use Rrmode\Pihach\RequestInterface;
use function FastRoute\simpleDispatcher;

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor/autoload.php';



$requestFactory = fn (array $routeParams) => new class implements RequestInterface {
    public function getAll(): mixed
    {
        return 0;
    }

    public function getRouteParams(): array
    {
        return $routeParams;
    }
};

$container = new Container();

$container[ThreadController::class] = new ThreadController();



$getHandler = function (array $controllerAction) use ($container): callable
{
    [$controllerClass, $actionName] = $controllerAction;

    $controller = $container[$controllerClass];

    return $controller->$actionName(...);
};

$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/', [ThreadController::class, 'indexAction']);
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos); 
} 

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

$resolveArgs = function (callable $f) use ($container): array
{
    $ref = new ReflectionFunction($f);
    $params = $ref->getParameters();

    return array_map(
        function (ReflectionParameter $r) use ($container) {
            return $container[$r->getClass()];
        },
        $params
    );
};

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        break;
    case FastRoute\Dispatcher::FOUND:
        // $container->add(RequestInterface::class, $requestFactory)->addArgument($routeInfo[2]);

        $handler = $getHandler($routeInfo[1]);

        $args = $resolveArgs($handler);

        echo $handler($args);
}










