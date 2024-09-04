<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Salad\Core\Application;
use Salad\Core\Router;
use Salad\Controllers\HomeController;
use Symfony\Component\Yaml\Yaml;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
if ($_ENV['IS_SET']!== 'true') {
  // Redirect to the setup page
  header('Location: /setup.php');
  exit;
}

$application = new Application(dirname(__DIR__));

$routesArray = Yaml::parseFile(__DIR__ . '/../routes.yml');
$routes = $routesArray['routes'];

// Initialize the router with routes and the base namespace
$router = new Router($routes, '');


$router->dispatch();