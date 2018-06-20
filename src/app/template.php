<?php
namespace app;

class template{
  public $filepath;
  public $data = array();

  public function __construct($filename){
    $this->filepath = APP_ROOT."view/${filename}.tpl.php";

    if(!file_exists($this->filepath))
      trigger_error("Template file '".$this->filepath."' not found");
  }

  public function __get($name){
    return isset($this->data[$name]) ? $this->data[$name] : null;
  }

  public function __set($name, $value){
    $this->data[$name] = $value;
  }

  public static function render($filename, $vars = array()){
    $template = new self($filename);
    foreach($vars as $key => $val){ $template->$key = $val; }
    $template->parse_template();
  }

  public static function render_to_string($filename, $vars = array()){
    ob_start();
    self::render($filename, $vars);
    return ob_get_clean();
  }


  protected function parse_template(){
    $data = $this;
    require $this->filepath;
    echo("\n");
  }
}
?>
