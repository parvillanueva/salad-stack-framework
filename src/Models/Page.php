<?php

namespace App\Models;

use Salad\Core\Application;

class Page
{
  protected $App;
  
  public function __construct()
  {
    $this->App = Application::$app;
  }

  public function fetchAll()
  {
    return $this->App->db->fetchAll("SELECT * FROM site_pages");
  }

  public function findById($id)
  {
    return $this->App->db->fetch("SELECT * FROM site_pages WHERE id = :id", [':id' => $id]);
  }

  public function findBySlug($slug)
  {
    return $this->App->db->fetchAll("SELECT * FROM site_pages WHERE slug = :slug", [':slug' => $slug]);
  }

  public function findPageBySlug($slug)
  {
    return $this->App->db->fetch("SELECT * FROM site_pages WHERE slug = :slug", [':slug' => $slug]);
  }

  public function removeById($id)
  {
    return $this->App->db->fetch("DELETE FROM site_pages WHERE id = :id", [':id' => $id]);
  }

  public function updatePage($id, $title, $slug)
  {
    return $this->App->db->execute("UPDATE site_pages SET title=:title, slug=:slug WHERE id=:id", [":id" => $id, ":title" => $title, ":slug" => $slug]);
  }

  public function createPage($title, $slug)
  {
    $this->App->db->execute("INSERT INTO site_pages (title, slug) VALUES (:title, :slug);", [":title" => $title, ":slug" => $slug]);
    return $this->App->db->lastInsertId();
  }


  public function setHomepage($id)
  {
    $this->App->db->execute("UPDATE site_pages SET is_home=:is_home", [":is_home" => 0]);
    return $this->App->db->fetch("UPDATE site_pages SET is_home=:is_home WHERE id = :id", [':id' => $id, ":is_home" => 1]);
  }


  public function getHomePage()
  {
    return $this->App->db->fetch("SELECT * FROM site_pages WHERE is_home = :is_home", [':is_home' => 1]);
  }
}
