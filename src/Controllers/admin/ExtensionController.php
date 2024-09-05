<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\Connection;
use Salad\Core\FileUploader;

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
      $packages = $this->App->extension->getFeatureList();
      $this->render('admin/extension/index', [
        "features"=> $packages
      ]);
    }
    
    public function view()
    {
      $name = $this->App->request->getBody('name');
      $form = $this->App->extension->getForm($name, true, 1);
      $table = $this->App->extension->getTable($form['table']);

      $this->render('admin/extension/form', [
        "form" => $form,
        "table" => $table,
      ]);
    }
    public function form_submit()
    {  
      if (isset($_SERVER['HTTP_REFERER'])) {
        $referrer = $_SERVER['HTTP_REFERER'];
        $parsedUrl = parse_url($referrer);
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $referrerWithoutBaseUrl = $path . $query;


        $table = $this->App->request->getBody('table');
        $type = $this->App->request->getBody('type');
        $forms = $this->App->request->getAll();

        foreach ($forms['files'] as $key => $file) {
          $forms['data'][$key] = $this->App->getBaseUrl() . "/".  $this->normalizePath($this->App->uploader->upload($file)['file_path']);
        }

        unset($forms['data']['type']);
        unset($forms['data']['table']);

        $id = $type == "single"? 1 : null;
        $query = $this->generateInsertSQL($table, $forms['data']);
        if($type == 'single'){
          $stmt = $this->App->db->fetch("SELECT * FROM $table");
          if($stmt){
            $query = $this->generateUpdateSQL($table, $forms['data'], $id);
          }
        }
        $this->App->db->execute($query); 


        $this->App->session->setFlash("notification_success", "Data successfully saved.");
        $this->App->response->redirect(htmlspecialchars($referrerWithoutBaseUrl));
      }
      
    }

    function generateInsertSQL($tableName, $data) {
      $columns = array_keys($data);
      $columnsList = implode(', ', array_map(fn($col) => "`$col`", $columns));
      $values = array_map(fn($value) => "'" . addslashes($value) . "'", array_values($data));
      $valuesList = "(" . implode(', ', $values) . ")";
      
      return "INSERT INTO `$tableName` ($columnsList) VALUES $valuesList;";
    }

    function generateUpdateSQL($tableName, $data, $id) {
      $setParts = [];
      foreach ($data as $field => $value) {
          $setParts[] = "`$field` = '" . addslashes($value) . "'";
      }
      $setSQL = implode(', ', $setParts);
      return "UPDATE `$tableName` SET $setSQL WHERE id=$id;";
  }
  
    
    public function enable()
    {
      $name = $this->App->request->getBody('name');
      $package = $this->App->extension->getFeature($name);

      // var_dump($package);
      $install_path = $package['install-path'];
      $package_path = Application::$ROOT_DIR ."/vendor/" . $this->normalizePath($install_path);

      // copy migration 
      $files = $this->copyDirectory($package_path . "/migrations/", Application::$ROOT_DIR . "/src/Migrations");

      // run migration
      if(isset($package['extra']['resources']['migration'])){
        foreach ($package['extra']['resources']['migration'] as $key => $migration) {
          (new Connection())->migrateSpecific($migration);
        }
      };

      //update .env file
      $this->updateEnvFile("EXTENSION_" . $package['name'], "true");
      $this->App->response->redirect("/admin/extension");
    
    }
    
    public function disable()
    {
      $name = $this->App->request->getBody('name');
      $package = $this->App->extension->getFeature($name);

      $install_path = $package['install-path'];
      $package_path = Application::$ROOT_DIR ."/vendor/" . $this->normalizePath($install_path);


      $migrations = $this->scanDirRecursive($package_path . "/migrations/", $install_path);
      foreach ($migrations as $key => $migration) {
        if (file_exists($migration)) {
          unlink($migration);
        }
      }

      //update .env file
      $this->updateEnvFile("EXTENSION_" . $package['name'], "false");
      $this->App->response->redirect("/admin/extension");
    
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
          $result[] = str_replace($package_path . "/migrations/", Application::$ROOT_DIR . "/src/Migrations",  $filePath );
        }
    }

    return $result;
  }

  function scanDirRoutes($dir, $install_path) {
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
          $result[] = str_replace($package_path . "/routes/", Application::$ROOT_DIR . "/routes",  $filePath );
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
