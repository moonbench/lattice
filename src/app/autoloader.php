<?php
namespace app;

/* 
 * Automatically finds and includes classes.
 *
 * This removes the need to "require" most files
 *
 * It will match \foo_controller to autoload controllers (ex, named "foo")
 * from the app/controllers folder
 * 
 * It will match \foo to autoload models (ex, named "foo") from the
 * app/models folder
 *
 * It will match \app\foldername\modulename to autoload other classes
 * (ex, "modulename" class from the app/foldername directory)
 */

class autoloader{
  protected $base_path;

  /**
   * Bind the autoloader to the PHP environment
   */
  public function __construct(){
    spl_autoload_register( array($this, 'notify') );
    $this->base_path = APP_ROOT ? APP_ROOT : "/";
  }

  /**
   * Unbind the autoloader from the PHP environment
   */
  public function __destruct(){
    spl_autoload_unregister( array($this, 'notify') );
  }


  /**
   * Attempt to autoload a class by requiring the associated file
   *
   * This is automatically called when attempting to use a new class
   */
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


  /**
   * Attempt to require a controller class
   */
  protected function load_controller( $name ){
    $path = $this->base_path . "/controller/" . $name . ".php";
    if( self::load_file( $path )){
      class_alias("\\app\\controller\\" . $name, $name . "_controller");
    }
  }

  /**
   * Attempt to require a model class
   */
  protected function load_model( $name ){
    $path = $this->base_path . "/model/" . $name . ".php";
    if( self::load_file( $path )){
      class_alias("\\app\\model\\" . $name, $name);
    }
  }

  /**
   * Attempt to require a generic module
   */
  protected function load_general( $name ){
    $path = $this->base_path . "/../" . str_replace('\\', '/', $name) . ".php";
    self::load_file( $path );
  }


  /**
   * Attempt to require a file from the specified location
   */
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
