<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\View;
use App\Models\Content;

class ContentsController extends Controller
{
    protected $app;
    protected $view;
    protected $content;
    protected $twig;

    public function __construct()
    {
        parent::__construct();   
        $this->app = Application::$app;
        $this->view = new View;
        $this->content = new Content;
        $this->twig = $this->view->getTwigEnv();
        $this->checkUserAuthentication();
    }
    
    private function checkUserAuthentication()
    {
        $userId = $this->app->session->get('user_id');
        if (!$userId) {
            $this->app->response->redirect("/admin/login");
        }
    }

    public function index()
    {
        $data = $this->content->fetchAll();
        $this->render('admin/content/index', ["data" => $data]);
    }
    
    public function add()
    {
        $this->render('admin/content/add');
    }
    
    public function edit()
    {
        $id = $this->app->request->getBody('id');
        
        if (!$id || !$data = $this->content->findById($id)) {
            $this->handleNotFound("/admin/content", "Content not found.");
        }

        $this->render('admin/content/edit', ["data" => $data]);
    }
    
    public function update()
    {
        $id = $this->app->request->getBody('id');
        $title = trim($this->app->request->getBody('title'));
        $content = trim($this->app->request->getBody('content'));

        if (empty($title) || empty($content)) {
            $this->handleValidationError("/admin/content/add", "Fill all the required fields.");
        }

        if (!$this->content->findById($id)) {
            $this->handleNotFound("/admin/content", "Content not found.");
        }

        $this->content->updateContent($id, $title, htmlspecialchars_decode($content));

        $this->handleSuccess("/admin/content", "Content successfully saved.");
    }
    
    public function create()
    {
        $title = trim($this->app->request->getBody('title'));
        $content = trim($this->app->request->getBody('content'));

        if (empty($title) || empty($content)) {
            $this->handleValidationError("/admin/content/add", "Fill all the required fields.");
        }

        $this->content->createContent($title, htmlspecialchars_decode($content));

        $this->handleSuccess("/admin/content", "Content successfully saved.");
    }
    
    public function upload()
    {
        $filePath = $this->app->uploader->upload($this->app->request->getAll()['files']['upload'])['file_path'];
        echo json_encode(["url" => $this->app->getBaseUrl() . "/" . $this->app->normalizePath($filePath)]);
    }

    public function remove()
    {  
        $id = $this->app->request->getBody('id');
        $this->content->removeById($id);

        $referrerUrl = $this->getReferrerUrl();
        $this->handleSuccess($referrerUrl, "Data successfully removed.");
    }

    private function handleNotFound($redirectUrl, $message)
    {
        $this->app->session->setFlash("notification_warning", $message);
        $this->app->response->redirect($redirectUrl);
    }

    private function handleValidationError($redirectUrl, $message)
    {
        $this->app->session->setFlash("notification_warning", $message);
        $this->app->response->redirect($redirectUrl);
    }

    private function handleSuccess($redirectUrl, $message)
    {
        $this->app->session->setFlash("notification_success", $message);
        $this->app->response->redirect($redirectUrl);
    }

    private function getReferrerUrl()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $parsedUrl = parse_url($_SERVER['HTTP_REFERER']);
            $path = $parsedUrl['path'] ?? '';
            $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
            return htmlspecialchars($path . $query);
        }
        return "/admin/content";
    }
}
