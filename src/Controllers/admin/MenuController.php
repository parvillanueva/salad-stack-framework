<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use App\Components\Navigation;
use App\Models\Page;
use App\Models\Menu;

class MenuController extends Controller
{
    private $app;
    private $twig;
    private $page;
    private $menu;
    protected $view;

    public function __construct()
    {
      parent::__construct();   
      $this->app = Application::$app;
      $this->view = new View;
      $this->page = new Page;
      $this->menu = new Menu;
      $this->twig = $this->view->getTwigEnv();
    }
    
    public function index()
    {
      
      $pages = $this->page->fetchAll();
      $menus = $this->menu->fetchAll();
      $data = [];
      foreach ($menus as $key => $value) {
        $sub_menus = $this->menu->fetchSubMenu($value['id']);
        $data[] = [
          "index" => $key,
          "menu"  => $value['menu'],
          "page_id"  => $sub_menus ? null : $value['page_id'],
          "submenu"   => $sub_menus
        ];
      }
      $this->render('admin/menu/index', ['pages' => $pages, 'menus' => $data]);

    }
    
    public function create()
    {
      $data = $this->app->request->getBody();

      if (count($_POST['items']) == 0) {
        $this->app->session->setFlash("notification_warning", "Fill all the required fields.");
        $this->app->response->redirect("/admin/menu/add");
        return;
      }

      $this->menu->truncateMenu();

      foreach ($_POST['items'] as $key => $item) {
        if(trim($item['menu']) !== "" || trim($item['value']) !== ""){
          $main_menu_id = $this->menu->createMenu($item['menu'], $item['value'], $parent_id = null); 
          if(isset($item['sub_items']) && count($item['sub_items']) > 0){
            foreach ($item['sub_items'] as $key => $sub_item) {
              if(trim($sub_item['menu']) !== "" || trim($sub_item['value']) !== ""){
                $this->menu->createMenu($sub_item['menu'], $sub_item['value'], $main_menu_id); 
              }
            }
          }
        }
      }

      $this->app->session->setFlash("notification_success", "Page successfully saved.");
      $this->app->response->redirect("/admin/menu");

    }
}
