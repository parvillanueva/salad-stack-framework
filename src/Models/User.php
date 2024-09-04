<?php

namespace App\Models;

use Salad\Core\Application;

class User
{
  protected $App;
  
  public function __construct()
  {
    $this->App = Application::$app;
  }

  public function findByEmail($email)
  {
    $stmt = $this->App->db->fetch("SELECT * FROM users WHERE email = :email", [':email' => $email]);
    return $stmt;
  }
}
