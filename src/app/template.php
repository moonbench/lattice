<?php
namespace app;
/* 
 * Simple templating engine
 *
 * Takes in a filename relative to the views folder, with optional variables
 * and renders the views to the output buffer or to a string
 */

class template{
  public $filepath;
  public $template_instance_data = array();

  /**
   * Create a new template from a filename with no variables
   */
  public function __construct( $filename ){
    $this->filepath = APP_ROOT . "/view/" . $filename . ".tpl.php";
  }


  /**
   * Get a variable which can be used within a template
   */
  public function __get( $name ){
    return isset($this->template_instance_data[$name]) ? $this->template_instance_data[$name] : null;
  }

  /**
   * Set a variable which can be used within a template
   */
  public function __set( $name, $value ){
    $this->template_instance_data[$name] = $value;
  }

  /**
   * Remove all stored variables
   */
  public function clean(){
    $this->data = array();
  }


  /**
   * Parse a new template from a filename and render it to the current output buffer
   */
  public static function render( $filename, $vars = array() ){
    $template = new self($filename);
    foreach($vars as $key => $val){
      $template->$key = $val;
    }
    $template->parse_template();
  }

  /**
   * Parse a new template from a file and return it as a string
   */
  public static function render_to_string( $filename, $vars = array() ){
    ob_start();
    self::render($filename, $vars);
    return ob_get_clean();
  }


  /**
   * Convert the view for this template into a parsed output
   */
  protected function parse_template(){
    if( !file_exists($this->filepath) ){
      trigger_error($this->filepath . " not found");
      return false;
    }

    $data = $this;
    include $this->filepath;    
    echo("\n");
  }


  /**
   * Parse this template and capture the output into a string
   */
  public function to_string(){
    ob_start();
    $this->render();
    return ob_get_clean();
  }
}
?>
