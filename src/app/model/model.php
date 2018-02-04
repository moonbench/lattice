<?Php
namespace app\model;

/*
 * Abstract class which defines common methods for all models
 */

abstract class model implements \JsonSerializable{
  static protected $current_transaction = null;
  protected static $cache = [];


  #
  # Magic methods
  #
  /**
   * Create a new model
   *
   * This will create a new model by assigning the provided data to the
   * model's properties, and will initalize the created_at property if it
   * isn't provided
   */
  public function __construct( $data = array() ){
    if( count($data)>0 ) $this->set_properties_from_data_array( $data );

    if( property_exists($this, "created_at") && !isset($this->created_at) ){
      $this->created_at = date("Y-m-d H:i:s");
    }
  }

  /**
   * Magic getter
   *
   * This will return the property if it exists, or it will attempt to call
   * a lazy load function to construct the property
   */
  public function __get( $property ){
    if(!isset($this->$property)) $this->$property = $this->try_lazy_load( $property );
    return $this->$property;
  }

  /**
   * Magic setter
   */
  public function __set( $property, $value ){
    if( property_exists($this, $property) ){ $this->$property = $value; }
  }



  #
  # Functions for creating a new model
  #
  /**
   * Take in an array of key-values and assign them to the model
   */
  protected function set_properties_from_data_array( $data ){
    foreach( $data as $property => $value ){
      if( preg_match('/^[0-9]/', $property)) continue;
      if( property_exists($this, $property)) $this->$property = $value;
    }
  }

  /**
   * Attempt to lazy load data if a method is defined
   *
   * This will attempt to call lazy_load_$property for the provided
   * property name, if such a function exists
   */
  protected function try_lazy_load( $property ){
    $method_name = "lazy_load_" . $property;
    if( !method_exists($this, $method_name)) return null;
    return call_user_func(array($this, $method_name));
  }



  #
  # JSON serialization
  #
  /**
   * Return an array of all of this model's properties and values
   */
  public function jsonSerialize(){
    $json = array();
    foreach( $this as $property => $value ){
      $json[$property] = $value;
    }
    return $json;
  }



  #
  # Finding models from the database
  #
  /**
   * Get a single instance of this model from a data array
   */
  protected static function get_single_from_data( $data ){
    if(count($data)<1) return null;
    $class = get_called_class();
    return new $class($data[0]);
  }

  /**
   * Get all instances of this model from a data array
   */
  protected static function get_many_from_data( $data ){
    $objects = array();
    $class = get_called_class();
    foreach($data as $object_data){
      $objects[] = new $class($object_data);
    }
    return $objects;
  }

  /**
   * Check if we've seen this object
   */
  protected static function is_in_cache($key){
    $class = get_called_class();
    return array_key_exists($class, self::$cache) && array_key_exists($key, self::$cache[$class]);
  }

  /**
   * Store a copy of this object for the lifetime of this class
   */
  protected static function insert_into_cache($key, $value){
    $class = get_called_class();
    if(!array_key_exists($class, self::$cache)) self::$cache[$class] = [];
    self::$cache[$class][$key] = $value;
    if(is_array($value)){
      foreach($value as $specific){
        self::$cache[$class][$specific->id] = $specific;
      }
    }
  }

  /**
   * Retrieve a stored copy of an object
   */
  protected static function get_from_cache($key){
    return self::$cache[static::$model_class][$key];
  }


  /**
   * Get a single object from the database for the provided column and value
   */
  protected static function find_one_by_col_and_val( $column, $value ){
    $data = sql_find("SELECT * FROM `". static::$table ."` WHERE `" . $column ."` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 1", [":v" => $value]);
    return self::get_single_from_data($data);
  }

  /**
   * Get all possible objects from the database for the provided column and value
   */
  protected static function find_all_by_col_and_val( $column, $value ){
    $data = sql_find("SELECT * FROM `". static::$table ."` WHERE `" . $column ."` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC", [":v" => $value]);
    return self::get_many_from_data($data);
  }


  /**
   * Get all objects from the database for this model
   */
  public static function find_all(){
    if(self::is_in_cache("all")) return self::get_from_cache("all");

    $data = sql_find("SELECT * FROM `". static::$table ."` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC");
    $objects = self::get_many_from_data($data);

    self::insert_into_cache("all", $objects);
    return $objects;
  }

  /**
   * Find a single object with the given id
   */
  public static function find_by_id( $id ){
    if(self::is_in_cache($id)) return self::get_from_cache($id);

    $object = self::find_one_by_col_and_val("id", $id);

    self::insert_into_cache($id, $object);
    return $object;
  }


  /**
   * Find the last-inserted object
   */
  public static function find_last( $count=1 ){
    if(self::is_in_cache("last".$count)) return self::get_from_cache("last".$count);
    if(self::is_in_cache("all")){
      $set = array_slice(self::get_from_cache("all"), 1-$count);
      self::insert_into_cache("last".$count, $set);
      return $set;
    }

    $count = intval($count);
    $data = sql_find("SELECT * FROM `". static::$table . "` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT " . $count . ";");
    $objects = self::get_many_from_data($data);
    self::insert_into_cache("last".$count, $objects);
    return $objects;
  }

  #
  # Saving models to the database
  #
  /**
   * A save method which can be called by models to write their data to the database
   *
   * Takes in an array of column names, and a matching array of values for those columns
   * The "id" column and value should never be included
   */
  protected function __save( $columns, $values ){
    self::start_transaction();

    $col_to_placeholder = [];
    $placeholder_to_val = [];

    for($i = 0; $i < count($columns); $i++){
      $col_to_placeholder[$columns[$i]] = ":val".$i;
      $placeholder_to_val[":val".$i] = $values[$i];
    }

    $this->create_or_update( $col_to_placeholder, $placeholder_to_val );
    self::commit_transaction();    
  }


  /*
   * Create a database row if this model is new, otherwise update the existing row
   *
   * If the "id" property exists, we'll attempt to update. Otherwise we create a new row
   */
  protected function create_or_update( $columns, $values ){
    if( isset($this->id) ){
      self::update( $this->id, $columns, $values );
    } else {
      self::create( $columns, $values );
      $this->id = \app\database::last_id();
    }
  }

  /**
   * Creates a new row in the database for this model
   */
  protected function create( $variables, $values ){
    $db_table = static::$table;

    $stmt_columns = array();
    $stmt_value_placeholders = array();
    foreach( $variables as $column_name => $value_placeholder ){
      $stmt_columns[] = "`$column_name`";
      $stmt_value_placeholders[] = $value_placeholder;
    }

    $stmt_columns = implode(", ", $stmt_columns);
    $stmt_value_placeholders = implode(", ", $stmt_value_placeholders);

    $stmt = "INSERT INTO `$db_table` ($stmt_columns) VALUES ($stmt_value_placeholders)";
    sql_set( $stmt, $values);
  }

  /**
   * Updates an existing row in the database for this model
   */
  protected function update( $id, $variables, $values ){
    $db_table = static::$table;
    $values[":stmtUpdateId"] = $id;

    $stmt_column_to_placeholder_pairings = array();
    foreach( $variables as $column_name => $value_placeholder ){
      $stmt_column_to_placeholder_pairings[] = "`$column_name` = $value_placeholder";
    }
    $stmt_column_to_placeholder_pairings = implode(", ", $stmt_column_to_placeholder_pairings);

    $stmt = "UPDATE `$db_table` SET $stmt_column_to_placeholder_pairings WHERE `$db_table`.`id` = :stmtUpdateId LIMIT 1;";
    sql_set( $stmt, $values );
  }



  #
  # Deleting models from the database
  #

  /**
   * Marks the "deleted_at" property for this model, and saves that change
   */
  protected function delete(){
    $this->deleted_at = date("Y-m-d H:i:s");
    $this->save();
  }



  #
  # Database transaction utilities
  #

  /**
   * Start a database transaction
   *
   * This creates a new transaction, and stores the calling class as the initiator of the action
   */
  protected static function start_transaction(){
    if( isset( self::$current_transaction) && self::$current_transaction !== null ) return false;
    self::$current_transaction = get_called_class();
    \app\database::beginTransaction(self::$current_transaction);
  }

  /**
   * Will commit the change to the database, only if the calling class initaiated the transaction
   */
  protected static function commit_transaction(){
    if(get_called_class() != self::$current_transaction) return false;

    if( !\app\error::is_empty() ){
      trigger_error("Unable to save in " . get_called_class());
      \app\database::rollBack(self::$current_transaction);
      return false;
    }
    \app\database::commit(self::$current_transaction);

    self::$current_transaction = null;
    return true;
  }
}
?>
