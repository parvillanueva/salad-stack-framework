<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;

class HomeController extends Controller
{
    private Application $App;

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
        $this->render('admin/home/index');
    }
}
