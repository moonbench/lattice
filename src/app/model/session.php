<?php
namespace app\model;

/*
 * A specific authentication for a user
 */

class session extends model {
  public static $table = "sessions";

  public $id;
  protected $user;
  public $token;
  public $ip;
  public $user_agent;
  public $created_at;
  public $expires_at;
  public $deleted_at;

  public function __get($property){
    if($property == "user" && !($this->user instanceof \user)) $this->user = $this->lazy_load_user();
    return parent::__get($property);
  }

  public function save(){
    self::__get("user");

    $user = $this->user ? $this->user->id : null;
    parent::__save(["user", "token", "ip", "user_agent", "created_at", "expires_at", "deleted_at"],
		   [$user, $this->token, $this->ip, $this->user_agent, $this->created_at, $this->expires_at, $this->deleted_at]
		   );
  }

  protected function lazy_load_user(){
    if(!$this->user) return new \user();
    $user = \user_controller::find_by_id( $this->user );
    $user->session = $this;
    return $user;
  }
}
?>
