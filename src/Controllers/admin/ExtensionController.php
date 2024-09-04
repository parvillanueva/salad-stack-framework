<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\Connection;

class ExtensionController extends Controller
{
    protected $App;
    public function __construct()
    {
        parent::__construct();   
        $this->App = Application::$app;
        $userId = $this->App->session->get('user_id');
        if(!$userId){
            $this->App->response->redirect("/admin/login");
        }
    }
    
    public function index()
    {
      $name = $this->App->request->getBody('name');
      $package = $this->App->extension->getFeature($name);
      $this->render('admin/extension/index', [
        "Feature"=> $package['extra']['salad-extension']['title'] ?? "",
        "Description"=> $package['description'] ?? "",
        "extension_name"=> $package['name']?? "",
      ]);
    }
    
    public function enable()
    {
      $name = $this->App->request->getBody('name');
      $package = $this->App->extension->getFeature($name);

      // var_dump($package);
      $install_path = $package['install-path'];
      $package_path = Application::$ROOT_DIR ."/vendor/" . $this->normalizePath($install_path);

      // copy resources 
      $files = $this->copyDirectory($package_path . "/resources/", Application::$ROOT_DIR . "/src/");

      // run migration
      if(isset($package['extra']['resources']['migration'])){
        foreach ($package['extra']['resources']['migration'] as $key => $migration) {
          (new Connection())->migrateSpecific($migration);
        }
      };

      //update .env file
      $this->updateEnvFile("EXTENSION_" . $package['name'], "true");
      $this->App->response->redirect("/admin");
    
    }
    
    public function disable()
    {
      $name = $this->App->request->getBody('name');
      $package = $this->App->extension->getFeature($name);

      // run rollback
      if(isset($package['extra']['resources']['migration'])){
        foreach ($package['extra']['resources']['migration'] as $key => $migration) {
          (new Connection())->rollbackSpecific($migration);
        }
      };

      // var_dump($package);
      $install_path = $package['install-path'];
      $package_path = Application::$ROOT_DIR ."/vendor/" . $this->normalizePath($install_path);

      // get resources 
      $files = $this->scanDirRecursive($package_path . "/resources/", $install_path);
      foreach ($files as $key => $file) {
        if (file_exists($file)) {
          unlink($file);
        }
      }

      // //update .env file
      $this->updateEnvFile("EXTENSION_" . $package['name'], "false");
      $this->App->response->redirect("/admin");
    
    }


    function updateEnvFile($key, $value) {
      $path = Application::$ROOT_DIR . '/.env';
      $key = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $key));
  
      if (file_exists($path)) {
          $envContent = file_get_contents($path);
          if (strpos($envContent, "$key=") !== false) {
              $envContent = preg_replace("/^$key=.*$/m", "$key=$value", $envContent);
          } else {
              $envContent .= "\n$key=$value";
          }
  
          file_put_contents($path, $envContent);
      }
  }

    function copyDirectory($src, $dst) {
      $dir = opendir($src);
      if (!is_dir($dst)) {
          mkdir($dst, 0755, true);
      }
      while (false !== ($file = readdir($dir))) {
          if ($file != '.' && $file != '..') {
              $srcPath = $src . DIRECTORY_SEPARATOR . $file;
              $dstPath = $dst . DIRECTORY_SEPARATOR . $file;
              if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $dstPath);
              } else {
                copy($srcPath, $dstPath);
              }
          }
        }
      closedir($dir);
  }

  function scanDirRecursive($dir, $install_path) {
    $package_path = Application::$ROOT_DIR ."/vendor/" . $this->normalizePath($install_path);
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
          $result = array_merge($result, $this->scanDirRecursive($filePath, $install_path));
        } else {
          $result[] = str_replace($package_path . "/resources/", Application::$ROOT_DIR . "/src",  $filePath );
        }
    }

    return $result;
}

  function normalizePath($path) {
    $parts = explode('/', $path);
    $stack = [];

    foreach ($parts as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }
        
        if ($part === '..') {
            if (!empty($stack)) {
                array_pop($stack);
            }
        } else {
            $stack[] = $part;
        }
    }
    return implode('/', $stack);
  }
}
