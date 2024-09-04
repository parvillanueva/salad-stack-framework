<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Salad\Core\Application;
use Salad\Core\Router;
use Symfony\Component\Yaml\Yaml;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
if ($_ENV['IS_SET']!== 'true') {
  // Redirect to the setup page
  header('Location: /setup.php');
  exit;
}

function mergeMultipleYamlFiles(array $files) {
  $mergedData = [];

  foreach ($files as $file) {
    try {
      $data = Yaml::parseFile($file);
      $mergedData = array_merge_recursive($mergedData, $data);
    } catch (ParseException $e) {
      echo "Unable to parse the YAML file '$file': ", $e->getMessage(), "\n";
    }
  }
  return $mergedData;
}

function scanDirRecursive($dir) {
  $result = [];
  if (!is_dir($dir)) {
      return $result;
  }
  $files = scandir($dir);
  foreach ($files as $file) {
      if ($file === '.' || $file === '..') {
          continue;
      }
      $filePath = $dir . DIRECTORY_SEPARATOR . $file;
      if (is_dir($filePath)) {
        $result = array_merge($result, scanDirRecursive($filePath));
      } else {
        $result[] = $filePath;
      }
  }

  return $result;
}

$application = new Application(dirname(__DIR__));

$routers = scanDirRecursive('../routes/');
$routes = mergeMultipleYamlFiles($routers);
$routes = $routes['routes'];

// Initialize the router with routes and the base namespace
$router = new Router($routes, '');


$router->dispatch();

