<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\Database;
use Salad\Core\Request;

use App\Models\User;

class SecurityController extends Controller
{
    protected $App;
    protected $user;
    public function __construct()
    {
        parent::__construct();   
        $this->App = Application::$app;
        $this->user = new User;
    }
    
    public function login()
    {

        $userId = $this->App->session->get('user_id');
        $userName = $this->App->session->get('user_name');
        if($userId){
            $this->App->response->redirect("/admin");
        }

        $this->render('admin/login/index');
    }
    
    public function logout()
    {

        $userId = $this->App->session->get('user_id');
        $userName = $this->App->session->get('user_name');
        if(!$userId){
            $this->App->response->redirect("/admin");
        }

        $this->App->session->remove('user_id');
        $this->App->session->remove('user_name');
        $this->App->response->redirect("/admin/login");
    }

    public function change_password()
    {
       echo "change password";
    }
    
    public function login_submit()
    {
        
        $email = $this->App->request->getBody('email');
        $password = $this->App->request->getBody('password');
        
        if(
            trim($email) == "" || 
            trim($password) == ""
        ){
            $this->App->session->setFlash("notification_message", "Fill all the required fields.");
            $this->App->response->redirect("/admin/login");
        }

        $stmt = $this->user->findByEmail($email);
        if(!$stmt){
            $this->App->session->setFlash("notification_message", "Authentication failed.");
            $this->App->response->redirect("/admin/login");
        }

        if (!password_verify($password, $stmt['password'])) {
            $this->App->session->setFlash("notification_message", "Authentication failed.");
            $this->App->response->redirect("/admin/login");
            return;
        } 

        $this->App->session->set('user_id', $stmt['id']);
        $this->App->session->set('user_name', $stmt['username']);
        $this->App->response->redirect("/admin/");
    }
}
