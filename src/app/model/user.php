<?php
namespace app\model;

class user extends model {
  protected static $table = "users";
  private static $current_user;

  public $id;
  public $name;
  public $email;
  public $password_hash;
  public $created_at;
  public $deleted_at;
  protected $session;
  protected $is_logged_in;

  public static function find_by_name($name){
    return self::select_one("name", $name);
  }

  public static function find_by_email($email){
    return self::select_one("email", $email);
  }

  public static function current(){
    if(isset(self::$current_user)) return self::$current_user;

    $session = \session::get_from_current_cookie();
    if(!$session || !$session->id || !$session->user) return new self();

    self::$current_user = $session->user;
    return self::$current_user;
  }

  public function save(){
    self::__save(["name", "email",
                  "password_hash",
                  "created_at", "deleted_at"],
                 [$this->name, $this->email,
                  $this->password_hash,
                  $this->created_at, $this->deleted_at]);
  }

  public static function login($name, $password){
    $user = self::find_by_name($name);
    if(!($user instanceof \user)) return null;

    if(!password_verify($password, $user->password_hash)) return null; // Timesafe

    $session = \session::create_for_user($user);
    self::$current_user = $session->user;
    self::$current_user->session = $session;

    return self::$current_user;
  }

  public static function hash_password($password){
    return password_hash($password, PASSWORD_BCRYPT, ["cost" => 14]);
  }
}
?>
