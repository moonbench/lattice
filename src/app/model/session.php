<?php
namespace app\model;

class session extends model {
  use traits\saveable;

  public static $table = "sessions";
  protected static $current;

  public $id;
  protected $user;
  public $token;
  public $ip;
  public $user_agent;
  public $created_at;
  public $expires_at;
  public $deleted_at;

  public static function find_by_user_id_and_token($user_id, $token){
    $table = self::$table;
    $data = sql_find("SELECT * FROM `$table` WHERE `user` = :uid AND `token` = :t  AND `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 1", [":uid" => $user_id, ":t" => $token]);
    return parent::get_single_from_data($data);
  }

  public static function find_by_anonymous_and_token($token){
    $table = self::$table;
    $data = sql_find("SELECT * FROM `$table` WHERE `user` IS NULL AND `token` = :t  AND `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 1", [":t" => $token]);
    return parent::get_single_from_data($data);
  }

  public static function get_from_current_cookie(){
    $cookie_data = self::get_cookie_data();
    if($cookie_data == false) return false;
    list($hex_user_id, $token, $cookie_key) = $cookie_data;

    if(!hash_equals(hash_hmac('sha256', $hex_user_id .":". $token, SITE_CONFIG["cookie_key"]), $cookie_key)) return false;

    if(hexdec($hex_user_id)==0) return self::find_by_anonymous_and_token($token);
    else return self::find_by_user_id_and_token(hexdec($hex_user_id), $token);
  }

  public static function current(){
    if(isset(self::$current)) return self::$current;

    self::$current = self::get_from_current_cookie();
    if(!self::$current) self::$current = new \session();

    return self::$current;
  }

  public function __get($property){
    if($property == "user" && !($this->user instanceof \user)) $this->user = $this->lazy_load_user();
    return parent::__get($property);
  }

  public function save(){
    self::__get("user");
    $user = $this->user ? $this->user->id : null;

    parent::__save(["user",
                    "token",
                    "ip", "user_agent",
                    "created_at", "expires_at", "deleted_at"],
                   [$user,
                    $this->token,
                    $this->ip, $this->user_agent,
                    $this->created_at, $this->expires_at, $this->deleted_at]);
  }


  protected function lazy_load_user(){
    return !!$this->user ? \user::find_by_id($this->user) : new \user();
  }

  public static function create_for_user($user){
    $token = openssl_random_pseudo_bytes(24);
    $token = bin2hex($token);

    $expire_time = time() + 60*60*24*30*12*2; // 2 years

    $session = new \session(["user" => $user,
           "token" => $token,
           "ip" => $_SERVER["REMOTE_ADDR"],
           "user_agent" => $_SERVER["HTTP_USER_AGENT"],
           "expires_at" => date("Y-m-d H:i:s", $expire_time)]);
    $session->save();
    self::set_cookie_for_session($session);

    return $session;
  }

  public static function create_for_anonymous(){
    return self::create_new_session_for_user(null);
  }

  public static function get_cookie_data(){
    if(!array_key_exists(SITE_CONFIG["cookie_name"], $_COOKIE)) return false;
    $cookie = $_COOKIE[SITE_CONFIG["cookie_name"]];
    return explode(":", $cookie);
  }

  protected static function set_cookie_for_session($session){
    $cookie = dechex($session->user->id) .":". $session->token;
    $cookie .= ":" . hash_hmac("sha256", $cookie, SITE_CONFIG["cookie_key"]);
    setcookie(SITE_CONFIG["cookie_name"], $cookie, strtotime($session->expires_at), "/");
  }
}
?>
