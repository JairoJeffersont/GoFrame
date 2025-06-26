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
 * $router->setBasePath('/minha-app');
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
     * Caminho base da aplicação (caso esteja em um subdiretório)
     * 
     * @var string
     */
    private string $basePath = '';

    /**
     * Define o caminho base da aplicação.
     * 
     * @param string $basePath Ex: '/minha-app'
     * @return void
     */
    public function setBasePath(string $basePath): void {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $route, array $handler): void {
        $this->routes['GET'][$route] = $handler;
    }

    public function post(string $route, array $handler): void {
        $this->routes['POST'][$route] = $handler;
    }

    public function put(string $route, array $handler): void {
        $this->routes['PUT'][$route] = $handler;
    }

    public function patch(string $route, array $handler): void {
        $this->routes['PATCH'][$route] = $handler;
    }

    public function delete(string $route, array $handler): void {
        $this->routes['DELETE'][$route] = $handler;
    }

    /**
     * Despacha a requisição HTTP para a rota correspondente.
     * 
     * @param string $method Método HTTP (GET, POST, PUT, DELETE)
     * @param string $uri URI da requisição
     * @return void
     */
    public function dispatch(string $method, string $uri): void {
        $path = rtrim(parse_url($uri, PHP_URL_PATH), '/');

        // Remove o basePath, se definido
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
     * Rota padrão (root /).
     * Retorna sucesso em JSON.
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
