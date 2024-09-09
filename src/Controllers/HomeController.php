<?php
namespace App\Controllers;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use App\Components\Navigation;
use App\Models\Page;
use App\Models\PageContent;
use App\Models\Content;
class HomeController extends Controller
{
    private $App;
    private $twig;
    protected $view;
    protected $content;
    protected $page;
    protected $page_content;

    public function __construct()
    {
      parent::__construct();   
      $this->App = Application::$app;
      $this->view = new View;
      $this->page_content = new PageContent;
      $this->page = new Page;
      $this->content = new Content;
      $this->twig = $this->view->getTwigEnv();
    }
    
    public function index()
    {
      $home_page = $this->page->getHomePage();
      $page_id = 0;
      if($home_page){
        $page_id = $home_page['id'];
      }
      $sections = $this->page_content->findByPageId($page_id);
      $this->App->renderPage($sections);
    }
    
    public function page($slug)
    {
      $home_page = $this->page->findPageBySlug($slug);
      $page_id = 0;
      if($home_page){
        $page_id = $home_page['id'];
      }
      $sections = $this->page_content->findByPageId($page_id);
      $this->App->renderPage($sections);
    }

}
