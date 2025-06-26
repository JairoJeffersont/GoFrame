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


$router->dispatch();
