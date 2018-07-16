<?php
namespace app;

class autoloader{
  protected $base_path;

  public function __construct(){
    spl_autoload_register(array($this, 'notify'));
    $this->base_path = APP_ROOT ? APP_ROOT : "/";
  }

  public function __destruct(){
    spl_autoload_unregister(array($this, 'notify'));
  }

  public function notify($class){
    if(preg_match('/^([a-zA-Z0-9]+)$/', $class, $matches)){
      return $this->load_model($matches[1]);
    } else {
      return $this->load_general($class);
    }
  }

  protected function load_model($name){
    $path = $this->base_path . '/model/' . $name . '.php';
    if(self::load_file($path)) class_alias("\\app\\model\\" . $name, $name);
  }

  protected function load_general($name){
    self::load_file($this->base_path.'../'.str_replace('\\', '/', $name).'.php');
  }

  protected static function load_file($file_path){
    if(file_exists($file_path)){
      require_once($file_path);
      return true;
    }

    trigger_error('Unable to find file for auto-inclusion: '.$file_path);
    return false;
  }
}
?>
