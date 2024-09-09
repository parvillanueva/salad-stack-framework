<?php
namespace App\Controllers\Admin;

use Salad\Core\Application;
use Salad\Core\Controller;
use Salad\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use App\Components\Navigation;
use App\Models\Page;
use App\Models\PageContent;
use App\Models\Content;

class PagesController extends Controller
{
    private $app;
    private $twig;
    protected $view;
    protected $page;
    protected $page_content;
    protected $content;

    public function __construct()
    {
      parent::__construct();   
      $this->app = Application::$app;
      $this->view = new View;
      $this->page = new Page;
      $this->page_content = new PageContent;
      $this->content = new Content;
      $this->twig = $this->view->getTwigEnv();
    }
    
    public function index()
    {
        $data = $this->page->fetchAll();
        $this->render('admin/pages/index', ['data' => $data]);
    }
    
    public function add()
    {
        $contents = $this->content->fetchAll();
        $sections = $this->app->extension->getSections();
        $this->render('admin/pages/add', ['contents' => $contents, 'sections' => $sections]);
    }
	
	 
    public function edit()
    {
        $id = $this->app->request->getBody('id');

        $page = $this->page->findById($id);
        $contents = $this->content->fetchAll();
        $sections = $this->app->extension->getSections();
		$page_sections = $this->page_content->findByPageId($page['id']);

        $this->render('admin/pages/edit', ['page' => $page, 'page_sections' => $page_sections, 'contents' => $contents, 'sections' => $sections]);
    }

    public function update()
    {
        $data = $this->app->request->getAll()['data'];
        $pageId = trim($data['id'] ?? '');
        $title = trim($data['title'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $sections = $data['section'] ?? [];

        if ($title === '' || $slug === '' || $pageId === '') {
            $this->app->session->setFlash("notification_warning", "Fill all the required fields.");
            $this->app->response->redirect("/admin/pages/add");
            return;
        }

        if ($this->page->findBySlug($slug)) {
            $this->app->session->setFlash("notification_warning", "Page already exists.");
            $this->app->response->redirect("/admin/pages/add");
            return;
        }


        $page = $this->page->findById($pageId);
		if(!$page){
            $this->app->session->setFlash("notification_warning", "Page not exists.");
            $this->app->response->redirect("/admin/pages/add");
		}

		//update page
		$this->page->updatePage($pageId, $title, $slug);

		//remove all section
		$this->page_content->removeByPageId($pageId);

        foreach ($sections as $section) {
            $package = explode("/", $section);
            $type = "extension";
            $renderer = $section;

            if ($package[0] === "salad-content") {
                $type = "content";
                $renderer = $package[1];
            }
			$this->page_content->insertSection($pageId, $renderer, $type);
        }

        $this->app->session->setFlash("notification_success", "Page successfully saved.");
        $this->app->response->redirect("/admin/pages");
    }

    public function create()
    {
        $data = $this->app->request->getAll()['data'];
        $title = trim($data['title'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $sections = $data['section'] ?? [];

        if ($title === '' || $slug === '') {
            $this->app->session->setFlash("notification_warning", "Fill all the required fields.");
            $this->app->response->redirect("/admin/pages/add");
            return;
        }

        if ($this->page->findBySlug($slug)) {
            $this->app->session->setFlash("notification_warning", "Page already exists.");
            $this->app->response->redirect("/admin/pages/add");
            return;
        }

        $pageId = $this->page->createPage($title, $slug);

        foreach ($sections as $section) {
            $package = explode("/", $section);
            $type = "extension";
            $renderer = $section;

            if ($package[0] === "salad-content") {
                $type = "content";
                $renderer = $package[1];
            }
			$this->page_content->insertSection($pageId, $renderer, $type);
        }

        $this->app->session->setFlash("notification_success", "Page successfully saved.");
        $this->app->response->redirect("/admin/pages");
    }

    public function remove()
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $parsedUrl = parse_url($referrer);
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $referrerWithoutBaseUrl = $path . $query;

        $id = $this->app->request->getBody('id');

        if ($id) {
            $this->page->removeById($id);
            $this->app->session->setFlash("notification_success", "Data successfully removed.");
        } else {
            $this->app->session->setFlash("notification_error", "Invalid ID provided.");
        }

        $this->app->response->redirect(htmlspecialchars($referrerWithoutBaseUrl));
    }

    public function set_home()
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $parsedUrl = parse_url($referrer);
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $referrerWithoutBaseUrl = $path . $query;

        $id = $this->app->request->getBody('id');

		$this->page->setHomepage($id);
		$this->app->session->setFlash("notification_success", "Page successfully set as home page.");
        $this->app->response->redirect(htmlspecialchars($referrerWithoutBaseUrl));
    }
    
}
