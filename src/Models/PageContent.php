<?php

namespace App\Models;

use Salad\Core\Application;

class PageContent
{
  protected $App;
  
  public function __construct()
  {
    $this->App = Application::$app;
  }

  public function fetchAll()
  {
    return $this->App->db->fetchAll("SELECT * FROM site_page_content");
  }

  public function findByPageId($page_id)
  {
    return $this->App->db->fetchAll("SELECT * FROM site_page_content WHERE page_id = :id", [':id' => $page_id]);
  }

  public function insertSection($pageId, $renderer, $type){
    $this->App->db->execute(
      "INSERT INTO site_page_content (page_id, section, type) VALUES (:page_id, :section, :type);",
      [":page_id" => $pageId, ":section" => $renderer, ":type" => $type]
    );
  }

  public function removeByPageId($page_id)
  {
    return $this->App->db->fetch("DELETE FROM site_page_content WHERE page_id = :id", [':id' => $page_id]);
  }

}
