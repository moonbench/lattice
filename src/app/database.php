<?php
namespace app;

class database{
  protected static $connection;
  protected static $current_transaction = null;

  public static function find($query, $params = array()){
    $result = self::execute($query, $params)->fetchAll();
    return is_array($result) ? $result : [];
  }

  public static function set($query, $params){
    self::execute($query, $params);
  }

  protected static function execute($query, $params){
    self::setup();

    $stmt = self::$connection->prepare($query);
    foreach($params as $placeholder => $value){
      $stmt->bindValue($placeholder, $value);
    }

    $stmt->execute();
    self::handle_errors_if_any($stmt, $query, $params);

    return $stmt;
  }

  public static function beginTransaction($key){
    if(self::$current_transaction != null) return;

    self::setup();
    self::$current_transaction = $key;
    self::$connection->beginTransaction();
  }

  public static function commit($key){
    if(self::$current_transaction != $key) return;

    self::$current_transaction = null;
    self::$connection->commit();
  }

  public static function rollBack($key){
    if(self::$current_transaction != $key) return;

    self::$current_transaction = null;
    self::$connection->rollBack();
  }

  public static function last_id(){
    return self::$connection->lastInsertId();
  }

  protected static function setup(){
    if(isset(self::$connection)) return;

    $config = parse_ini_file(APP_ROOT."config/database.default.ini");
    if(file_exists(APP_ROOT."config/database.ini"))
      $config = parse_ini_file(APP_ROOT."config/database.ini");

    self::$connection = self::connect($config["username"], $config["password"], $config["hostname"], $config["database"]);
  }

  protected static function connect($username, $password, $hostname, $database){
    mysqli_connect($hostname, $username, $password);

    $database_selection = "mysql:";
    $database_selection .= "dbname=${database};";
    $database_selection .= "host=${hostname};";
    return new \PDO($database_selection, $username, $password);
  }

  protected static function handle_errors_if_any($stmt, $query, $params){
    if($stmt->errorInfo() && $stmt->errorInfo()[0] != "0000"){
      \app\error::php_error(-1, $stmt->errorInfo(), $query, $params);
    }
  }
}
?>
