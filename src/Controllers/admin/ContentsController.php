<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use App\Components\Navigation;

class ContentsController extends Controller
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
      $data = $this->App->db->fetchAll("SELECT * FROM site_contents");
      $this->render('admin/content/index', [ "data" => $data]);
    }
    
    public function add()
    {
      $this->render('admin/content/add');
    }
    
    
    public function edit()
    {
      $id = $this->App->request->getBody('id');
      if(!$id){
        $this->App->session->setFlash("notification_warning", "Content not found.");
        $this->App->response->redirect("/admin/content");
      }
      $data = $this->App->db->fetch("SELECT * FROM site_contents WHERE id = $id");

      if(!$data){
        $this->App->session->setFlash("notification_warning", "Content not found.");
        $this->App->response->redirect("/admin/content");
      }
      $this->render('admin/content/edit', ["data" => $data]);
    }
    
    public function update()
    {
       
      $id = $this->App->request->getBody('id');
      $title = $this->App->request->getBody('title');
      $content = $this->App->request->getBody('content');

      if(
        trim($title) == "" |
        trim($content) == ""
      ){
        $this->App->session->setFlash("notification_warning", "Fill all the required fields.");
        $this->App->response->redirect("/admin/content/add");
      }
      $data = $this->App->db->fetch("SELECT * FROM site_contents WHERE id = $id");

      if(!$data){
        $this->App->session->setFlash("notification_warning", "Content not found.");
        $this->App->response->redirect("/admin/content");
      }


      $this->App->db->execute("UPDATE site_contents SET title=:title, content=:content WHERE id=$id", [":title" => $title, ":content" => htmlspecialchars_decode($content)]);


      $this->App->session->setFlash("notification_success", "Content successfully saved.");
      $this->App->response->redirect("/admin/content");

    }
    
    public function create()
    {
       
      $title = $this->App->request->getBody('title');
      $content = $this->App->request->getBody('content');

      if(
        trim($title) == "" |
        trim($content) == ""
      ){
        $this->App->session->setFlash("notification_warning", "Fill all the required fields.");
        $this->App->response->redirect("/admin/content/add");
      }
      $this->App->db->execute("INSERT INTO site_contents (title, content) VALUES (:title, :content);", [":title" => $title, ":content" => htmlspecialchars_decode($content)]);

      $this->App->session->setFlash("notification_success", "Content successfully saved.");
      $this->App->response->redirect("/admin/content");

    }
    
    public function upload()
    {
      $forms = $this->App->request->getAll();
      echo json_encode( [
        "url" => $this->App->getBaseUrl() . "/".  $this->normalizePath($this->App->uploader->upload($forms['files']['upload'])['file_path'])
      ]);
    }


    public function remove()
    {  
      if (isset($_SERVER['HTTP_REFERER'])) {
        $referrer = $_SERVER['HTTP_REFERER'];
        $parsedUrl = parse_url($referrer);
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $referrerWithoutBaseUrl = $path . $query;

        $id = $this->App->request->getBody('id');

        $this->App->db->execute("DELETE FROM site_contents WHERE id = $id");

        $this->App->session->setFlash("notification_success", "Data successfully removed.");
        $this->App->response->redirect(htmlspecialchars($referrerWithoutBaseUrl));
      }
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
