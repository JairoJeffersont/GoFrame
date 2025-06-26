<?php

namespace GoFrame\Core;

class Router {
    private array $routes = [];
    private string $basePath = '';

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

    public function dispatch(): void {

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $path = rtrim(parse_url($uri, PHP_URL_PATH), '/');
        if ($this->basePath && str_starts_with($path, $this->basePath)) {
            $path = substr($path, strlen($this->basePath));
            $path = $path ?: '/';
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $this->prepareRequestData();
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

    private function prepareRequestData(): void {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents("php://input");
            $input = json_decode($rawInput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $output = new \GoFrame\Core\Helpers\Output();
                $output->buildOutput([
                    'status' => 'bad_request',
                    'status_code' => '400'
                ]);
                exit;
            }

            $_REQUEST = $input ?? [];
        } elseif (
            stripos($contentType, 'application/x-www-form-urlencoded') !== false ||
            stripos($contentType, 'multipart/form-data') !== false
        ) {
            $_REQUEST = array_merge($_GET, $_POST, $_FILES);
        } else {
            $output = new \GoFrame\Core\Helpers\Output();
            $output->buildOutput([
                'status' => 'unsupported_media_type',
                'status_code' => '415'
            ]);
            exit;
        }
    }

    public function indexRoute(): void {
        $output = new \GoFrame\Core\Helpers\Output();
        $output->buildOutput([
            'status' => 'success',
            'status_code' => '200'
        ]);
    }
}
