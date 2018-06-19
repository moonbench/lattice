<?php
namespace app;

class template{
  public $filepath;
  public $template_instance_data = array();

  public function __construct( $filename ){
    $this->filepath = APP_ROOT . "/view/" . $filename . ".tpl.php";
  }

  public function __get( $name ){
    return isset($this->template_instance_data[$name]) ? $this->template_instance_data[$name] : null;
  }

  public function __set( $name, $value ){
    $this->template_instance_data[$name] = $value;
  }

  public static function render( $filename, $vars = array() ){
    $template = new self($filename);
    foreach($vars as $key => $val){
      $template->$key = $val;
    }
    $template->parse_template();
  }

  public static function render_to_string( $filename, $vars = array() ){
    ob_start();
    self::render($filename, $vars);
    return ob_get_clean();
  }


  protected function parse_template(){
    if( !file_exists($this->filepath) ){
      trigger_error($this->filepath . " not found");
      return false;
    }

    $data = $this;
    include $this->filepath;    
    echo("\n");
  }
}
?>
