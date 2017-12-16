<?php
namespace app\controller;

class session extends controller {
  protected static $table = "sessions";
  protected static $model_class = "session";

  public static $COOKIE_KEY = "set_a_key_here";
  public static $COOKIE_NAME = "rememberme";

  protected static $current_user_session;

  public static function find_by_user_id_and_token( $user_id, $token ){
    $data = \database_controller::find("SELECT * FROM `sessions` WHERE `user` = :uid AND `token` = :t  AND `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 1",
                                       [":uid" => $user_id, ":t" => $token]);
    return parent::get_single_from_data($data);
  }
  public static function find_by_anonymous_and_token( $token ){
    $data = \database_controller::find("SELECT * FROM `sessions` WHERE `user` IS NULL AND `token` = :t  AND `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 1",
                                       [":t" => $token]);
    return parent::get_single_from_data($data);
  }

  public static function create_for_anonymous(){
    return self::create_new_session_for_user(null);
  }

  public static function get_cookie_data(){
    if( !array_key_exists(self::$COOKIE_NAME, $_COOKIE) ) return false;
    $cookie = $_COOKIE[self::$COOKIE_NAME];
    return explode(":", $cookie);
  }

  public static function get_from_current_cookie(){
    $cookie_data = self::get_cookie_data();
    if($cookie_data == false) return false;
    list( $hex_user_id, $token, $cookie_key ) = $cookie_data;

    if( !hash_equals(hash_hmac('sha256', $hex_user_id .":". $token, self::$COOKIE_KEY), $cookie_key)) return false;

    $user;
    if(hexdec( $hex_user_id )==0) $user =self::find_by_anonymous_and_token( $token );
    else $user =self::find_by_user_id_and_token( hexdec( $hex_user_id ), $token );
    return $user;
  }

  public static function current(){
    if(isset(self::$current_user_session)) return self::$current_user_session;

    $session = self::get_from_current_cookie();
    if(!$session) $session = new \session();
    self::$current_user_session = $session;
    return $session;
  }

  protected static function set_cookie_for_session( $session ){
    $cookie = dechex( $session->user->id ) .":". $session->token;

    // Add a hash to ensure the cookie was built with this server's secret
    $cookie .= ":" . hash_hmac("sha256", $cookie, self::$COOKIE_KEY);
    setcookie(self::$COOKIE_NAME, $cookie, strtotime($session->expires_at), "/");
  }

  public static function create_new_session_for_user( $user ){
    // Create a random token
    $token = openssl_random_pseudo_bytes( 24 );
    $token = bin2hex( $token );

    // Expire automatically in 2 years
    $expire_time = time() + 60*60*24*30*12*2;

    // Create a session
    $session = new \session(["user" => $user,
			     "token" => $token,
			     "ip" => $_SERVER["REMOTE_ADDR"],
			     "user_agent" => $_SERVER["HTTP_USER_AGENT"],
			     "expires_at" => date("Y-m-d H:i:s", $expire_time)]);
    $session->save();

    self::set_cookie_for_session($session);

    return $session;
  }
}
?>