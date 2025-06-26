<?php

use GoFrame\Core\Router;
use GoFrame\Controllers\UserController;

return function (Router $router) {
    
    //User´s routes
    $router->get('/users', [UserController::class, 'getAll']);
    $router->get('/users/{id}', [UserController::class, 'findOne']);
    $router->post('/users', [UserController::class, 'create']);
    $router->post('/users/{id}', [UserController::class, 'update']);
    $router->delete('/users/{id}', [UserController::class, 'delete']);
};
