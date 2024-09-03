<?php
namespace App\Controllers;

use Salad\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $data = ['message' => 'Welcome to My MVC Framework!'];
        $this->render('home/index', $data);
    }


    public function about()
    {
      $data = ['message' => 'Welcome About Page of My MVC Framework!'];
      $this->render('home/index', $data);
    }


    public function userProfile($id)
    {
      $data = ['message' => "Welcome Profile Page with ID #$id of My MVC Framework!"];
      $this->render('home/index', $data);
    }


}
