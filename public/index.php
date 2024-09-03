<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Salad\Core\Application;
use Salad\Core\Router;
use Salad\Controllers\HomeController;
use Symfony\Component\Yaml\Yaml;

$application = new Application(dirname(__DIR__));

$routesArray = Yaml::parseFile(__DIR__ . '/../routes.yml');
$routes = $routesArray['routes'];

// Initialize the router with routes and the base namespace
$router = new Router($routes, '');


$router->dispatch();