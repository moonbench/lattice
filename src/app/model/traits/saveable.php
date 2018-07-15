<?php
namespace app\model\traits;

trait saveable {
  static protected $current_transaction = null;
  protected static $cache = [];

  public static function find_all(){
    if(self::is_in_cache("all")) return self::get_from_cache("all");

    $data = sql_find("SELECT * FROM `".self::table_name()."` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC");
    self::insert_into_cache("all", self::get_many($data));
    return self::get_from_cache("all");
  }

  public static function find_by_id($id){
    if(self::is_in_cache($id)) return self::get_from_cache($id);

    self::insert_into_cache($id, self::select_one("id", $id));
    return self::get_from_cache($id);
  }

  public static function find_last($count=1){
    $count = intval($count);

    if(self::is_in_cache("last".$count)) return self::get_from_cache("last".$count);
    if(self::is_in_cache("all")){
      self::insert_into_cache("last".$count, array_slice(self::get_from_cache("all"), 1-$count));
    } else {
      $data = sql_find("SELECT * FROM `".self::table_name()."` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT ".$count.";");
      self::insert_into_cache("last".$count, self::get_many($data));
    }
    return self::get_from_cache("last".$count);;
  }

  protected static function table_name(){
    return \app\database::$config['prefix'] . static::$table;
  }

  protected static function get_one($data){
    return self::get_many($data)[0];
  }

  protected static function get_many($data){
    $class = get_called_class();
    return array_map(
      function($instance_data) use (&$class){
        return new $class($instance_data);
      }, $data);
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
      foreach($value as $instance){
        self::$cache[$class][$instance->id] = $instance;
      }
    }
  }

  protected static function get_from_cache($key){
    return self::$cache[get_called_class()][$key];
  }

  protected static function select_one($column, $value){
    return self::get_one(sql_find("SELECT * FROM `".self::table_name()."` WHERE `${column}` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 1", [":v" => $value]));
  }

  protected static function select_many($column, $value){
    return self::get_many(sql_find("SELECT * FROM `".self::table_name()."` WHERE `${column}` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC", [":v" => $value]));
  }

  protected function __save($columns, $values){
    self::start_transaction();

    $col_to_placeholder = [];
    $placeholder_to_val = [];

    for($i = 0; $i < count($columns); $i++){
      $col_to_placeholder[$columns[$i]] = ":val".$i;
      $placeholder_to_val[":val".$i] = $values[$i];
    }

    $this->create_or_update($col_to_placeholder, $placeholder_to_val);
    self::commit_transaction();    
  }

  protected function create_or_update($columns, $values){
    if(isset($this->id)){
      self::update($this->id, $columns, $values);
    } else {
      self::create($columns, $values);
      $this->id = \app\database::last_id();
    }
  }

  protected function create($variables, $values){
    $stmt_columns = array();
    $stmt_value_placeholders = array();

    foreach($variables as $column_name => $value_placeholder){
      $stmt_columns[] = "`$column_name`";
      $stmt_value_placeholders[] = $value_placeholder;
    }

    $stmt_columns = implode(", ", $stmt_columns);
    $stmt_value_placeholders = implode(", ", $stmt_value_placeholders);
    $stmt = "INSERT INTO `".self::table_name()."` ($stmt_columns) VALUES ($stmt_value_placeholders)";

    sql_set($stmt, $values);
  }

  protected function update($id, $variables, $values){
    $stmt_column_to_placeholder_pairings = array();
    $values[":stmtUpdateId"] = $id;

    foreach($variables as $column_name => $value_placeholder){
      $stmt_column_to_placeholder_pairings[] = "`$column_name` = $value_placeholder";
    }

    $stmt_column_to_placeholder_pairings = implode(", ", $stmt_column_to_placeholder_pairings);
    $stmt = "UPDATE `".self::table_name()."` SET $stmt_column_to_placeholder_pairings WHERE `$db_table`.`id` = :stmtUpdateId LIMIT 1;";

    sql_set($stmt, $values);
  }

  protected function delete(){
    $this->deleted_at = date("Y-m-d H:i:s");
    $this->save();
  }

  protected static function start_transaction(){
    if(isset(self::$current_transaction) && self::$current_transaction !== null) return false;

    self::$current_transaction = get_called_class();
    \app\database::beginTransaction(self::$current_transaction);
  }

  protected static function commit_transaction(){
    if(get_called_class() != self::$current_transaction) return false;

    if(!\app\error::is_empty()){
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
