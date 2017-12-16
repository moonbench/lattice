<?php
namespace app;

/*
 * Handles errors in framework or parsing
 */

class error{
  const OUTPUT_ENABLED = true;
  public static $errors_seen = array();

  public static function is_empty(){
    return count(self::$errors_seen) == 0;
  }
  public static function as_strings(){
    $strings = [];
    foreach(self::$errors_seen as $error){
      $strings[] = $error["string"];
    }
    return $strings;
  }


  # New default error handler (as configured in main.php bootloader)
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


  # Manipulate the errors
  protected static function unpack_mysqli_error( $string ){
    return [$string[0], $string[2]];
  }


  # Consume the errors
  protected static function log_error( $number, $string, $file, $line ){
    self::$errors_seen[] = array(
      "number" => $number,
      "string" => $string,
      "file" => $file,
      "line" => $line
    );
  }
  protected static function print_error( $error_data ){
    $error_string = "[Error: #" . $error_data["number"] . "] ";
    $error_string .= "<br/>". $error_data["string"] . " ";
    $error_string .= "<br/>". $error_data["file"];
    $error_string .= ":" . $error_data["line"];
    echo( "<pre>".$error_string."</pre>" );
  }
}

?>
