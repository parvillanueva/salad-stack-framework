<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;

class AppearanceController extends Controller
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
        $this->render('admin/appearance/index');
    }
    
    public function save()
    {
        $email = $this->App->request->getBody('email');
        $password = $this->App->request->getBody('password');
    }
}
