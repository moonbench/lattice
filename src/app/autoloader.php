<?php
namespace app;

/* 
 * Automatically finds and includes classes.
 */

class autoloader{
  protected $base_path;

  # Magic methods to bind the autoloader to the environment
  public function __construct(){
    spl_autoload_register( array($this, 'notify') );
    $this->base_path = APP_ROOT ? APP_ROOT : "/";
  }
  public function __destruct(){
    spl_autoload_unregister( array($this, 'notify') );
  }


  # Called when seeing a class for the first time
  public function notify( $class ){

    // Matches "\foo_controller"
    if( preg_match('/^([a-zA-Z0-9]+)_controller$/', $class, $matches) ){
      return $this->load_controller( $matches[1] );

    // Matches "\foo" (shorthand for models)
    } else if( preg_match('/^([a-zA-Z0-9]+)$/', $class, $matches) ){
      return $this->load_model( $matches[1] );

    // Matches "\module\foo"
    } else {
      return $this->load_general( $class );
    }
  }


  # Loading the different file types
  protected function load_controller( $name ){
    $path = $this->base_path . "/controller/" . $name . ".php";
    if( self::load_file( $path )){
      class_alias("\\app\\controller\\" . $name, $name . "_controller");
    }
  }
  protected function load_model( $name ){
    $path = $this->base_path . "/model/" . $name . ".php";
    if( self::load_file( $path )){
      class_alias("\\app\\model\\" . $name, $name);
    }
  }
  protected function load_general( $name ){
    $path = $this->base_path . "/../" . str_replace('\\', '/', $name) . ".php";
    self::load_file( $path );
  }


  # Standardized way to include the files, or error out
  protected static function load_file( $file_path ){
    if( file_exists( $file_path )){
      require_once( $file_path );
      return true;
    }
    trigger_error("Unable to find file for auto-inclusion: " . $file_path);
    return false;
  }
}
?>