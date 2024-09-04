<?php
namespace App\Controllers;

use Salad\Core\Application;
use Salad\Core\Connection;
use Dotenv\Dotenv;


class SetupController
{
    private $db;
    private $dotenv;

    public function __construct()
    {
        $this->db = (new Connection())->getConnection();
        $this->dotenv = Dotenv::createImmutable(Application::$ROOT_DIR);
        $this->dotenv->load();
    }

    public function index()
    {
        if($_ENV['IS_CONFIG']!== 'true'){
            (new Connection())->migrateAll();
            $site_id = 1;

            // add admin password
            $admin_email = $_ENV['ADMIN_USER'];
            $admin_user = explode('@', $admin_email)[0];
            $admin_password = $_ENV['ADMIN_PASS'];
            $this->db->query("INSERT INTO users (id, username, email, password) VALUES ($site_id, '$admin_user', '$admin_email', '$admin_password'); ");

            // add site basic settings
            $site_title = $_ENV['SITE_TITLE'];
            $site_description = $_ENV['SITE_DESCRIPTION'];
            $this->db->query("INSERT INTO site_basic_settings (id, title, description) VALUES ($site_id, '$site_title', '$site_description'); ");
            $this->db->query("INSERT INTO site_custom_settings (id) VALUES ($site_id); ");

            //update env file
            $this->updateEnvFile("IS_CONFIG", "true");

            //redirect to homepage
            header('Location: /');
        }

    }

    function updateEnvFile($key, $value) {
        $path = Application::$ROOT_DIR . '/.env';
    
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
}
