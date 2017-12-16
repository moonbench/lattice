<?php
namespace app;

/* 
 * Renders template files to buffers or output stream
 */

class template{
  public $filepath;
  public $template_instance_data = array();


  # Magic getter and setter to make adding and selecting values more convenient
  public function __get( $name ){
    return isset($this->template_instance_data[$name]) ? $this->template_instance_data[$name] : null;
  }
  public function __set( $name, $value ){
    $this->template_instance_data[$name] = $value;
  }


  # Instantiate a new template
  public function __construct( $filename ){
    $this->filepath = APP_ROOT . "/view/" . $filename . ".tpl.php";
  }

  # Instantiate a new template with values, and render it to the output stream
  public static function render( $filename, $vars = array() ){
    $template = new self($filename);
    foreach($vars as $key => $val){
      $template->$key = $val;
    }
    $template->parse_template();
  }

  # Instantiate a new template with values, and return it as a string
  public static function render_to_string( $filename, $vars = array() ){
    ob_start();
    self::render($filename, $vars);
    return ob_get_clean();
  }


  # Parse the template with this instance's data
  protected function parse_template(){
    if( !file_exists($this->filepath) ){
      trigger_error($this->filepath . " not found");
      return false;
    }

    $data = $this;
    include $this->filepath;    
    echo("\n");
  }


  

  public function to_string(){
    ob_start();
    $this->render();
    return ob_get_clean();
  }

  


  public function clean(){
    $this->data = array();
  }


}
?>