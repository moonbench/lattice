<?php
namespace app;
/*
 * Automatic error handler
 *
 * Provides a consistent mechanism for logging and printing errors
 */

class error{
  const OUTPUT_ENABLED = true;
  public static $errors_seen = array();

  /**
   * Returns a boolean representing if we have encountered any errors
   */
  public static function is_empty(){
    return count(self::$errors_seen) == 0;
  }

  /**
   * Returns a string representation of all errors encountered
   */
  public static function as_strings(){
    $strings = [];
    foreach(self::$errors_seen as $error){
      $strings[] = $error["string"];
    }
    return $strings;
  }


  /**
   * Default error handler
   *
   * This is bound to the PHP environment and automatically called whenever an error occurs
   */
  public static function php_error( $number, $string, $file, $line ){
    if($number == -1 && is_array($string)){
      // Massage any SQL errors into the desired format
      list($number, $string) = self::unpack_mysqli_error($string);
      $line = " (# params ". count($line) . ")";
    }

    // Log
    self::log_error($number, $string, $file, $line);
    if( self::OUTPUT_ENABLED ){ self::print_error( end(self::$errors_seen) ); }
  }


  /**
   * Convert mysqli errors to a format matching general PHP errors
   */
  protected static function unpack_mysqli_error( $string ){
    return [$string[0], $string[2]];
  }


  /**
   * Keep a record of this error
   */
  protected static function log_error( $number, $string, $file, $line ){
    self::$errors_seen[] = array(
      "number" => $number,
      "string" => $string,
      "file" => $file,
      "line" => $line
    );
  }

  /**
   * Print the error to the output buffer
   */
  protected static function print_error( $error_data ){
    $error_string = "[Error: #" . $error_data["number"] . "] ";
    $error_string .= "<br/>". $error_data["string"] . " ";
    $error_string .= "<br/>". $error_data["file"];
    $error_string .= ":" . $error_data["line"];
    echo( "<pre>".$error_string."</pre>" );
  }
}

?>
