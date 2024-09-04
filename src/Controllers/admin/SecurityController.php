<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;

class SecurityController extends Controller
{
    public function __construct()
    {
        parent::__construct();   
        $userId = Application::$app->session->get('user');
        if($userId){
            Application::$app->response->redirect("/admin");
        }
    }
    
    public function login()
    {
        $this->render('admin/login/index');
    }
}
