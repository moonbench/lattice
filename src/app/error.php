<?php
namespace app;

class error{
  const OUTPUT_ENABLED = true;
  public static $errors_seen = array();

  /**
   * This is automatically called when an error occurs anywhere
   */
  public static function php_error($number, $string, $file, $line){
    if($number == -1 && is_array($string))
      list($number, $string) = self::unpack_mysqli_error($string);

    self::log_error($number, $string, $file, $line);
    if(self::OUTPUT_ENABLED){ self::print_error(end(self::$errors_seen)); }
  }

  public static function is_empty(){
    return count(self::$errors_seen) == 0;
  }

  public static function as_strings(){
    return array_map(
      function($error){ return $error["string"]; },
      self::$errors_seen);
  }


  protected static function unpack_mysqli_error($string){
    return [$string[0], $string[2]];
  }

  protected static function log_error($number, $string, $file, $line){
    self::$errors_seen[] = array(
      "number" => $number,
      "string" => $string,
      "file" => $file,
      "line" => $line
    );
  }

  protected static function print_error($error_data){
    $error_string = "[Error: #" . $error_data["number"] . "] ";
    $error_string .= "<br/>". $error_data["string"] . " ";
    $error_string .= "<br/>". $error_data["file"];
    $error_string .= ":" . $error_data["line"];
    echo("<pre>".$error_string."</pre>");
  }
}

?>
