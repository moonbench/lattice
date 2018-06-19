<?php
namespace app\model;

abstract class model implements \JsonSerializable{
  static protected $current_transaction = null;
  protected static $cache = [];


  public function __construct( $data = array() ){
    if( count($data)>0 ) $this->set_properties_from_data_array( $data );

    if( property_exists($this, "created_at") && !isset($this->created_at) ){
      $this->created_at = date("Y-m-d H:i:s");
    }
  }

  public function __get( $property ){
    if(!isset($this->$property)) $this->$property = $this->try_lazy_load( $property );
    return $this->$property;
  }

  public function __set( $property, $value ){
    if( property_exists($this, $property) ){ $this->$property = $value; }
  }



  protected function set_properties_from_data_array( $data ){
    foreach( $data as $property => $value ){
      if( preg_match('/^[0-9]/', $property)) continue;
      if( property_exists($this, $property)) $this->$property = $value;
    }
  }

  protected function try_lazy_load( $property ){
    $method_name = "lazy_load_" . $property;
    if( !method_exists($this, $method_name)) return null;
    return call_user_func(array($this, $method_name));
  }


  public function jsonSerialize(){
    $json = array();
    foreach( $this as $property => $value ){
      $json[$property] = $value;
    }
    return $json;
  }



  protected static function get_single_from_data( $data ){
    if(count($data)<1) return null;
    $class = get_called_class();
    return new $class($data[0]);
  }

  protected static function get_many_from_data( $data ){
    $objects = array();
    $class = get_called_class();
    foreach($data as $object_data){
      $objects[] = new $class($object_data);
    }
    return $objects;
  }

  protected static function is_in_cache($key){
    $class = get_called_class();
    return array_key_exists($class, self::$cache) && array_key_exists($key, self::$cache[$class]);
  }

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

  protected static function get_from_cache($key){
    return self::$cache[static::$model_class][$key];
  }

  protected static function find_one_by_col_and_val( $column, $value ){
    $data = sql_find("SELECT * FROM `". static::$table ."` WHERE `" . $column ."` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 1", [":v" => $value]);
    return self::get_single_from_data($data);
  }

  protected static function find_all_by_col_and_val( $column, $value ){
    $data = sql_find("SELECT * FROM `". static::$table ."` WHERE `" . $column ."` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC", [":v" => $value]);
    return self::get_many_from_data($data);
  }

  public static function find_all(){
    if(self::is_in_cache("all")) return self::get_from_cache("all");

    $data = sql_find("SELECT * FROM `". static::$table ."` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC");
    $objects = self::get_many_from_data($data);

    self::insert_into_cache("all", $objects);
    return $objects;
  }

  public static function find_by_id( $id ){
    if(self::is_in_cache($id)) return self::get_from_cache($id);

    $object = self::find_one_by_col_and_val("id", $id);

    self::insert_into_cache($id, $object);
    return $object;
  }

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

  protected function create_or_update( $columns, $values ){
    if( isset($this->id) ){
      self::update( $this->id, $columns, $values );
    } else {
      self::create( $columns, $values );
      $this->id = \app\database::last_id();
    }
  }

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

  protected function delete(){
    $this->deleted_at = date("Y-m-d H:i:s");
    $this->save();
  }


  protected static function start_transaction(){
    if( isset( self::$current_transaction) && self::$current_transaction !== null ) return false;
    self::$current_transaction = get_called_class();
    \app\database::beginTransaction(self::$current_transaction);
  }

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
