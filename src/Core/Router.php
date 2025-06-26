<?php

namespace GoFrame\Core;

/**
 * Class Router
 * 
 * A simple HTTP router for registering and dispatching routes to controller methods.
 * Supports GET, POST, PUT, PATCH, and DELETE HTTP methods.
 * 
 * Routes can contain parameters using `{param}` syntax, which will be passed as method arguments.
 * For POST, PUT, and PATCH requests, the request data ($_REQUEST) is appended as the last argument.
 * 
 * It also supports setting a base path if the application is hosted in a subdirectory.
 * 
 * If no route matches the request, a 404 JSON response is returned.
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
     * Array of registered routes by HTTP method.
     * 
     * @var array<string, array<string, array>>
     */
    private array $routes = [];

    /**
     * Base path of the application (useful if it's hosted in a subdirectory).
     * 
     * @var string
     */
    private string $basePath = '';

    /**
     * Sets the base path for the application.
     * 
     * @param string $basePath Example: '/my-app'
     * @return void
     */
    public function setBasePath(string $basePath): void {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Registers a GET route.
     * 
     * @param string $route
     * @param array $handler
     * @return void
     */
    public function get(string $route, array $handler): void {
        $this->routes['GET'][$route] = $handler;
    }

    /**
     * Registers a POST route.
     * 
     * @param string $route
     * @param array $handler
     * @return void
     */
    public function post(string $route, array $handler): void {
        $this->routes['POST'][$route] = $handler;
    }

    /**
     * Registers a PUT route.
     * 
     * @param string $route
     * @param array $handler
     * @return void
     */
    public function put(string $route, array $handler): void {
        $this->routes['PUT'][$route] = $handler;
    }

    /**
     * Registers a PATCH route.
     * 
     * @param string $route
     * @param array $handler
     * @return void
     */
    public function patch(string $route, array $handler): void {
        $this->routes['PATCH'][$route] = $handler;
    }

    /**
     * Registers a DELETE route.
     * 
     * @param string $route
     * @param array $handler
     * @return void
     */
    public function delete(string $route, array $handler): void {
        $this->routes['DELETE'][$route] = $handler;
    }

    /**
     * Dispatches the incoming request to the appropriate route handler.
     * 
     * Matches the URI against registered routes and invokes the controller method.
     * If the method is POST, PUT, or PATCH, $_REQUEST is passed as the last parameter.
     * 
     * Returns a 404 JSON response if no route matches.
     * 
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string $uri Request URI
     * @return void
     */
    public function dispatch(string $method, string $uri): void {
        $path = rtrim(parse_url($uri, PHP_URL_PATH), '/');

        if ($this->basePath && str_starts_with($path, $this->basePath)) {
            $path = substr($path, strlen($this->basePath));
            $path = $path ?: '/';
        }

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('#\{[^}]+\}#', '([^/]+)', $route);
            $pattern = "#^" . rtrim($pattern, '/') . "$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                [$controllerClass, $methodName] = $handler;
                $controller = new $controllerClass();

                if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
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
     * Default index route (e.g., when accessing '/').
     * 
     * Returns a 200 success JSON response.
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
