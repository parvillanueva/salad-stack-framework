<?php
namespace App\Controllers;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use App\Components\Navigation;

class HomeController extends Controller
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
      $package = $this->App->extension->getFeature("salad-dressing/image-banner");
      $package_path = Application::$ROOT_DIR ."/vendor/" . $this->normalizePath($package['install-path']);

      $this->view->addViewPath($package_path .'/src/Views');

      $style = file_get_contents($package_path . "/" . $package['extra']['section']['style']);

      $this->view->addTwixExtension(new $package['extra']['section']['extension']);

      $this->render("template/site/chunk/header");
      $this->render("template/site/chunk/styles", ["styles" => $style]);
      $this->render("template/site/chunk/navigation");

      $this->render($package['extra']['section']['render']);

      $this->render("template/site/chunk/footer");

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
