<?php

namespace GoFrame\Core;

/**
 * Class Router
 * 
 * Simple HTTP router for registering routes and dispatching requests to controllers.
 * Supports GET, POST, PUT, and DELETE HTTP methods.
 * 
 * Routes can include parameters using `{param}` syntax, which will be passed as arguments
 * to the controller method.
 * 
 * For POST and PUT requests, the request data ($_REQUEST) is appended as the last argument.
 * 
 * If no matching route is found, returns a 404 response with JSON output.
 * 
 * Example usage:
 * ```php
 * $router = new Router();
 * $router->get('/users/{id}', [UserController::class, 'show']);
 * $router->post('/users', [UserController::class, 'create']);
 * $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
 * ```
 * 
 * @package GoFrame\Core
 */
class Router {
    /**
     * Array of registered routes by HTTP method
     * 
     * @var array<string, array<string, array>> 
     */
    private array $routes = [];

    /**
     * Registers a GET route.
     * 
     * @param string $route URI pattern, supports parameters in `{param}` format.
     * @param array $handler Controller class and method to handle the route, e.g. [ControllerClass::class, 'method'].
     * @return void
     */
    public function get(string $route, array $handler): void {
        $this->routes['GET'][$route] = $handler;
    }

    /**
     * Registers a POST route.
     * 
     * @param string $route URI pattern, supports parameters in `{param}` format.
     * @param array $handler Controller class and method to handle the route.
     * @return void
     */
    public function post(string $route, array $handler): void {
        $this->routes['POST'][$route] = $handler;
    }

    /**
     * Registers a PUT route.
     * 
     * @param string $route URI pattern, supports parameters in `{param}` format.
     * @param array $handler Controller class and method to handle the route.
     * @return void
     */
    public function put(string $route, array $handler): void {
        $this->routes['PUT'][$route] = $handler;
    }

    /**
     * Registers a DELETE route.
     * 
     * @param string $route URI pattern, supports parameters in `{param}` format.
     * @param array $handler Controller class and method to handle the route.
     * @return void
     */
    public function delete(string $route, array $handler): void {
        $this->routes['DELETE'][$route] = $handler;
    }

    /**
     * Dispatches the HTTP request to the matched route handler.
     * 
     * Matches the given HTTP method and URI against registered routes.
     * If a match is found, instantiates the controller and calls the method with route parameters.
     * For POST and PUT methods, appends $_REQUEST data as the last argument.
     * 
     * If no route matches, outputs a 404 JSON response.
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE).
     * @param string $uri Request URI.
     * @return void
     */
    public function dispatch(string $method, string $uri): void {
        $uri = rtrim(parse_url($uri, PHP_URL_PATH), '/');

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('#\{[^}]+\}#', '([^/]+)', $route);
            $pattern = "#^" . rtrim($pattern, '/') . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controllerClass, $methodName] = $handler;
                $controller = new $controllerClass();

                if (in_array($method, ['POST', 'PUT'])) {
                    $matches[] = $_REQUEST;
                }

                call_user_func_array([$controller, $methodName], $matches);
                return;
            }
        }

        $output = new \GoFrame\Core\Helpers\Output();
        $output->buildOutput([
            'status' => 'route_not_found',
            'status_code' => '404'
        ]);
    }

    /**
     * Default route handler (index route).
     * Outputs a 200 success JSON response.
     * 
     * @return void
     */
    public function indexRoute(): void {
        $output = new \GoFrame\Core\Helpers\Output();
        $output->buildOutput([
            'status' => 'success',
            'status_code' => '200'
        ]);
    }
}
