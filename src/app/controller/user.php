<?php
namespace app\controller;
/*
 * Functions for finding and manipulating user models
 */

class user extends controller {
  protected static $table = "users";
  protected static $model_class = "user";
  private static $current_user;

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

    $session = \session_controller::get_from_current_cookie();
    if( !$session || !$session->id || !$session->user ) return new \user();

    self::$current_user = $session->user;
    return self::$current_user;
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
    if( !password_verify($password, $user->password_hash) ) return null;

    // Create a new session
    $session = \session_controller::create_new_session_for_user($user);
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