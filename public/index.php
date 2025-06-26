<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GoFrame\Core\Router;

$router = new Router();


$routeDefinitions = require __DIR__ . '/../src/routes/web.php';
$routeDefinitions($router);


$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];


if (in_array($method, ['POST', 'PUT'])) {
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
}

$router->dispatch($method, $uri);
