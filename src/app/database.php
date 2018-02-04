<?php
namespace app;

/*
 * Interacts with the database
 */
class database{
  const CONFIG_FILE = "/config/database.ini";
  protected static $username;
  protected static $password;
  protected static $hostname;
  protected static $database;
  protected static $connection;
  protected static $current_transaction = null;


  // Queries
  /**
   * Execute a query and return the result
   */
  public static function find( $query, $params = array() ){
    $result = self::execute( $query, $params )->fetchAll();
    if( is_array( $result )) return $result;
    return array();
  }

  /**
   * Execute a query and don't return the result
   */
  public static function set( $query, $params ){
    $stmt = self::execute( $query, $params );
  }

  /**
   * Bind values and execute a prepared statement
   */
  protected static function execute( $query, $params ){
    if( !isset(self::$dbh) ) self::setup();

    $stmt = self::$connection->prepare( $query );
    foreach( $params as $placeholder => $value ){
      $stmt->bindValue( $placeholder, $value );
    }
    $stmt->execute();
    self::handle_errors_if_any( $stmt, $query, $params );
    return $stmt;
  }


  // Meta
  /**
   * Begin a new database transaction
   */
  public static function beginTransaction( $key ){
    if(self::$current_transaction != null) return;
    self::$current_transaction = $key;
    self::setup();
    self::$connection->beginTransaction();
  }

  /**
   * Commit and end a transaction
   */
  public static function commit( $key ){
    if(self::$current_transaction != $key) return;
    self::$current_transaction = null;
    self::$connection->commit();
  }

  /**
   * Roll back and end a transaction
   */
  public static function rollBack( $key ){
    if(self::$current_transaction != $key) return;
    self::$current_transaction = null;
    self::$connection->rollBack();
  }

  /**
   * Get the id of the last inserted row
   */
  public static function last_id(){
    return self::$connection->lastInsertId();
  }


  // Connections
  /**
   * Setup the database access based on the config data
   */
  protected static function setup(){
    if( isset(self::$connection)) return;

    $config_data = parse_ini_file( APP_ROOT . self::CONFIG_FILE );
    self::$username = $config_data["username"];
    self::$password = $config_data["password"];
    self::$hostname = $config_data["hostname"];
    self::$database = $config_data["database"];

    self::$connection = self::get_new_connection();
  }

  /**
   * Attempt to connect to the database
   */
  protected static function get_new_connection(){
    mysqli_connect( self::$hostname, self::$username, self::$password );

    $database_selection = "mysql:";
    $database_selection .= "dbname=" . self::$database . ";";
    $database_selection .= "host=" . self::$hostname . ";";
    return new \PDO( $database_selection, self::$username, self::$password );
  }



  // Support functions
  /**
   * Print any errors that exist in a query
   */
  protected static function handle_errors_if_any( $stmt, $query, $params ){
    if( $stmt->errorInfo() && $stmt->errorInfo()[0] != "0000"){
      \app\error::php_error( -1, $stmt->errorInfo(), $query, $params );

      if( 1==0 ){
        ob_start();
        echo("<pre>");
        var_dump($stmt);
        echo("</pre>");
        $params = ob_get_clean();
        var_dump( $stmt->errorInfo() );
      }
    }
  }
}
?>
