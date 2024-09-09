<?php

namespace App\Models;

use Salad\Core\Application;

class Content
{
  protected $App;
  
  public function __construct()
  {
    $this->App = Application::$app;
  }

  public function fetchAll()
  {
    return $this->App->db->fetchAll("SELECT * FROM site_contents");
  }

  public function findById($id)
  {
    return $this->App->db->fetch("SELECT * FROM site_contents WHERE id = :id", [':id' => $id]);
  }

  public function removeById($id)
  {
    return $this->App->db->fetch("DELETE FROM site_contents WHERE id = :id", [':id' => $id]);
  }

  public function updateContent($id, $title, $content)
  {
    return $this->App->db->execute("UPDATE site_contents SET title=:title, content=:content WHERE id=:id", [":id" => $id, ":title" => $title, ":content" => $content]);
  }

  public function createContent($title, $content)
  {
    return $this->App->db->execute("INSERT INTO site_contents (title, content) VALUES (:title, :content);", [":title" => $title, ":content" => $content]);

  }
}
