<?php

namespace App\Models;

use Salad\Core\Application;

class Menu
{
  protected $App;
  
  public function __construct()
  {
    $this->App = Application::$app;
  }

  public function fetchAll()
  {
    return $this->App->db->fetchAll("SELECT * FROM site_menu WHERE parent_id IS NULL");
  }

  public function fetchSubMenu($id)
  {
    return $this->App->db->fetchAll("SELECT * FROM site_menu WHERE parent_id = :parent_id", [":parent_id" => $id]);
  }

  public function createMenu($menu, $page_id = null, $parent_id = null)
  {
    $this->App->db->execute("INSERT INTO site_menu (menu, page_id, parent_id) VALUES (:menu, :page_id, :parent_id);", [":menu" => $menu, ":page_id" => $page_id, ":parent_id" => $parent_id]);
    return $this->App->db->lastInsertId();
  }

  public function truncateMenu()
  {
    return $this->App->db->execute("TRUNCATE TABLE site_menu;");
  }
}
