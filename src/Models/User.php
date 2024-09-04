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

  public function findById($id)
  {
    $stmt = $this->App->db->fetch("SELECT * FROM users WHERE id = :id", [':id' => $id]);
    return $stmt;
  }

  public function updatePassword($id, $password)
  {
    $new_password = password_hash($password, PASSWORD_BCRYPT);
    return $this->App->db->execute("UPDATE users SET password = :pass WHERE id = :id", [':id' => $id, ':pass' => $new_password]);
  }
}
