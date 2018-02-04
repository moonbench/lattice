<?php
namespace app\model;
/**
 * Represents a user account
 */

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



  /**
   * Find a user account based on their name
   */
  public static function find_by_name( $name ){
    return self::find_one_by_col_and_val("name", $name);
  }

  /**
   * Find a user account based on their email address
   */
  public static function find_by_email( $email ){
    return self::find_one_by_col_and_val("email", $email);
  }

  /**
   * Find a user account based on the session from the current cookie
   */
  public static function current(){
    if(isset(self::$current_user)) return self::$current_user;

    $session = \session::get_from_current_cookie();
    if( !$session || !$session->id || !$session->user ) return new self();

    self::$current_user = $session->user;
    return self::$current_user;
  }



  /**
   * Creates or updates the database row for our user account
   */
  public function save(){
    self::__save(["name", "email", "password_hash", "created_at", "deleted_at"],
		 [$this->name, $this->email, $this->password_hash, $this->created_at, $this->deleted_at]);
  }



  /**
   * Attempt to authenticate a user
   *
   * This takes in a username and a password
   * It then attempts to validate the password for any matching user account
   * If successful, an authenticated session is created
   * Otherwise we return null
   */
  public static function login( $name, $password ){
    $user = self::find_by_name($name);
    if(!($user instanceof \user)) return null;

    // Time-safe password validation
    if(!password_verify($password, $user->password_hash)) return null;

    // Create a new session
    $session = \session::create_for_user($user);
    self::$current_user = $session->user;
    self::$current_user->session = $session;

    return self::$current_user;
  }



  /**
   * Generate a password hash for a provided password string
   */
  public static function hash_password( $password ){
    return password_hash( $password, PASSWORD_BCRYPT, ["cost" => 14] );
  }
}
?>
