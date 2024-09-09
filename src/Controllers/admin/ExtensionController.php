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
        $this->checkUserAuthentication();
    }

	private function checkUserAuthentication()
    {
        $userId = $this->App->session->get('user_id');
        if (!$userId) {
            $this->App->response->redirect("/admin/login");
        }
    }

    public function index()
    {
        $features = $this->App->extension->getFeatureList();
        $this->render('admin/extension/index', ['features' => $features]);
    }

    public function view()
    {
        $name = $this->App->request->getBody('name');
        $type = $this->App->extension->getType($name);
        $id = $type === "single" ? 1 : null;

        $form = $this->App->extension->getForm($name, true, $id);
        $table = $this->App->extension->getTable($form['table']);

        $this->render('admin/extension/form', [
            'package' => $name,
            'extension' => $this->App->extension,
            'form' => $form,
            'table' => $table,
        ]);
    }

    public function remove_data()
    {
        $referrerUrl = $this->getReferrerUrl();
        $table = $this->App->request->getBody('table');
        $id = $this->App->request->getBody('id');

        $this->App->db->execute("DELETE FROM $table WHERE id = $id");
        $this->App->session->setFlash("notification_success", "Data successfully removed.");
        $this->App->response->redirect(htmlspecialchars($referrerUrl));
    }

    public function form_submit()
    {
        $referrerUrl = $this->getReferrerUrl();
        $table = $this->App->request->getBody('table');
        $type = $this->App->request->getBody('type');
        $forms = $this->App->request->getAll();

        // Handle file uploads
        foreach ($forms['files'] as $key => $file) {
            $forms['data'][$key] = $this->App->getBaseUrl() . "/" . $this->App->normalizePath($this->App->uploader->upload($file)['file_path']);
        }

        unset($forms['data']['type'], $forms['data']['table']);

        $id = $type === "single" ? 1 : $this->App->request->getBody('id') ?? null;
        $query = $this->generateInsertSQL($table, $forms['data']);

        // Handle updates
        if ($type === 'single') {
            $exists = $this->App->db->fetch("SELECT * FROM $table");
            if ($exists) {
                $query = $this->generateUpdateSQL($table, $forms['data'], $id);
            }
        } elseif ($id) {
            $query = $this->generateUpdateSQL($table, $forms['data'], $id);
        }

        $this->App->db->execute($query);
        $this->App->session->setFlash("notification_success", "Data successfully saved.");
        $this->App->response->redirect(htmlspecialchars($referrerUrl));
    }

    public function enable()
    {
        $this->handleExtension('enable');
    }

    public function disable()
    {
        $this->handleExtension('disable');
    }

    private function handleExtension($action)
    {
        $name = $this->App->request->getBody('name');
        $package = $this->App->extension->getFeature($name);
        $installPath = Application::$ROOT_DIR . "/vendor/" . $this->App->normalizePath($package['install-path']);

        if ($action === 'enable') {
            $this->copyDirectory($installPath . "/migrations/", Application::$ROOT_DIR . "/src/Migrations");

            if (isset($package['extra']['resources']['migration'])) {
                foreach ($package['extra']['resources']['migration'] as $migration) {
                    (new Connection())->migrateSpecific($migration);
                }
            }

            $this->updateEnvFile("EXTENSION_" . $package['name'], "true");
        } else {
            $migrations = $this->scanDirRecursive($installPath . "/migrations/", $installPath);
            foreach ($migrations as $migration) {
                if (file_exists($migration)) {
                    unlink($migration);
                }
            }

            $this->updateEnvFile("EXTENSION_" . $package['name'], "false");
        }

        $this->App->response->redirect("/admin/extension");
    }

    private function updateEnvFile($key, $value)
    {
        $path = Application::$ROOT_DIR . '/.env';
        $key = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $key));

        if (file_exists($path)) {
            $envContent = file_get_contents($path);
            $pattern = "/^$key=.*$/m";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "$key=$value", $envContent);
            } else {
                $envContent .= "\n$key=$value";
            }

            file_put_contents($path, $envContent);
        }
    }

    private function copyDirectory($src, $dst)
    {
        $dir = opendir($src);

        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        while (($file = readdir($dir)) !== false) {
            if ($file !== '.' && $file !== '..') {
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

    private function scanDirRecursive($dir, $installPath)
    {
        $packagePath = Application::$ROOT_DIR . "/vendor/" . $this->App->normalizePath($installPath);
        $result = [];

        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($filePath)) {
                        $result = array_merge($result, $this->scanDirRecursive($filePath, $installPath));
                    } else {
                        $result[] = str_replace($packagePath . "/migrations/", Application::$ROOT_DIR . "/src/Migrations", $filePath);
                    }
                }
            }
        }

        return $result;
    }

    private function getReferrerUrl()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $parsedUrl = parse_url($_SERVER['HTTP_REFERER']);
            $path = $parsedUrl['path'] ?? '';
            $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
            return $path . $query;
        }

        return "/admin/extension";
    }

    private function generateInsertSQL($tableName, $data)
    {
        $columns = array_keys($data);
        $columnsList = implode(', ', array_map(fn($col) => "`$col`", $columns));
        $valuesList = implode(', ', array_map(fn($val) => "'" . addslashes($val) . "'", array_values($data)));

        return "INSERT INTO `$tableName` ($columnsList) VALUES ($valuesList);";
    }

    private function generateUpdateSQL($tableName, $data, $id)
    {
        $setList = implode(', ', array_map(fn($key, $val) => "`$key` = '" . addslashes($val) . "'", array_keys($data), $data));

        return "UPDATE `$tableName` SET $setList WHERE id = $id;";
    }
}
