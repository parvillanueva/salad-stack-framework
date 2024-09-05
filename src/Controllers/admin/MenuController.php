<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use App\Components\Navigation;

class MenuController extends Controller
{
    private $App;
    private $twig;
    protected $view;

    public function __construct()
    {
      parent::__construct();   
      $this->App = Application::$app;
      $this->view = new View;
      $this->twig = $this->view->getTwigEnv();
    }
    
    public function index()
    {
      
      $this->render('admin/menu/index');

    }



}
