<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();   
        $userId = Application::$app->session->get('user');
        if(!$userId){
            Application::$app->response->redirect("/admin/login");
        }
    }
    
    public function index()
    {
        $this->render('admin/home/index');
    }
}
