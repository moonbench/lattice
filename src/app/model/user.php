<?php
namespace app\model;
/**
 * Represents a user account
 */

class user extends model {
  protected static $table = "users";
  public $id;
  public $name;
  public $email;
  public $password_hash;
  public $created_at;
  public $deleted_at;
  protected $session;
  protected $is_logged_in;

  /**
   * Creates or updates the database row for our user account
   */
  public function save(){
    self::__save(["name", "email", "password_hash", "created_at", "deleted_at"],
		 [$this->name, $this->email, $this->password_hash, $this->created_at, $this->deleted_at]);
  }
}
?>
