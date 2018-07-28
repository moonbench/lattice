<?php
namespace app\model;

class login_attempt extends model {
  use traits\saveable;

  protected static $table = "login_attempts";

  public $id;
  protected $user;
  public $successful = false;
  public $ip;
  public $user_agent;
  public $created_at;
  public $deleted_at;

  public static function create_for_user($user){
    return new \login_attempt([
      "user" => $user,
      "ip" => $_SERVER["REMOTE_ADDR"],
      "user_agent" => $_SERVER["HTTP_USER_AGENT"]
    ]);
  }

  public static function allowed_for_current_ip(){
    $ip = $_SERVER["REMOTE_ADDR"];
    $since = sql_date(time()-\app\config::site('login_lockout_seconds'));
    $count = count(sql_find("
        SELECT * FROM `".self::table_name()."`
        WHERE `ip` = :ip
          AND `successful` = false
          AND `deleted_at` IS NULL
          AND `created_at` > :since
        ORDER BY `created_at` DESC",
        [":ip" => $ip, ":since" => $since]
      ))
    ;
    return $count < \app\config::site('max_login_attempts');
  }

  public function succeed(){
    $this->successful = true;
    $this->save();
  }

  public function __get($property){
    if($property == "user" && !($this->user instanceof \user)) $this->user = $this->lazy_load_user();
    return parent::__get($property);
  }

  public function save(){
    $user = $this->__get('user');
    $user = $user ? $user->id : null;

    self::__save(['user', 'successful',
                  'ip', 'user_agent',
                  'created_at', 'deleted_at'],
                 [$user, $this->successful,
                  $this->ip, $this->user_agent,
                  $this->created_at, $this->deleted_at]);
  }

  protected function lazy_load_user(){
    return !!$this->user ? \user::find_by_id($this->user) : null;
  }
}
?>
