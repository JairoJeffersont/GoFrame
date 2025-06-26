<?php
// index.php

require_once __DIR__ . '/../vendor/autoload.php';

use GoFrame\Controllers\UserController;

// Criar instância do controller
$userController = new UserController();

$data = ['id' => '5', 'name' => 2, 'email' => 'teswest@asdasdtest.com'];

print_r($userController->getAll());
