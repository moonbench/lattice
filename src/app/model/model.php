<?Php
namespace app\model;

/*
 * Abstract class which defines common methods for all models
 */

abstract class model implements \JsonSerializable{
  static protected $current_transaction = null;


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
      $this->id = \database_controller::last_id();
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
    \database_controller::set( $stmt, $values);
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
    \database_controller::set( $stmt, $values );
  }

  /**
   * Marks the "deleted_at" property for this model, and saves that change
   */
  protected function delete(){
    $this->deleted_at = date("Y-m-d H:i:s");
    $this->save();
  }


  /**
   * Start a database transaction
   *
   * This creates a new transaction, and stores the calling class as the initiator of the action
   */
  protected static function start_transaction(){
    if( isset( self::$current_transaction) && self::$current_transaction !== null ) return false;
    self::$current_transaction = get_called_class();
    \database_controller::beginTransaction(self::$current_transaction);
  }

  /**
   * Will commit the change to the database, only if the calling class initaiated the transaction
   */
  protected static function commit_transaction(){
    if(get_called_class() != self::$current_transaction) return false;

    if( !\app\error::is_empty() ){
      trigger_error("Unable to save in " . get_called_class());
      \database_controller::rollBack(self::$current_transaction);
      return false;
    }
    \database_controller::commit(self::$current_transaction);

    self::$current_transaction = null;
    return true;
  }
}
?>
