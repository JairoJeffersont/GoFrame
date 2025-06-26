<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GoFrame\Core\Router;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$router = new Router();

$basePath = $_ENV['APP_BASE_PATH'] ?? '';
$router->setBasePath($basePath);

$routeDefinitions = require __DIR__ . '/../src/routes/web.php';
$routeDefinitions($router);

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
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


$router->dispatch($method, $uri);
