<?php
namespace app\controller;

/*
 * An abstract class which provides methods for all controllers
 */
abstract class controller {
  protected static $cache = [];

  /**
   * Get a single instance of this controller's model from a data array
   */
  protected static function get_single_from_data( $data ){
    if(count($data)<1) return null;
    return new static::$model_class($data[0]);
  }

  /**
   * Get all instances of this controller's model from a data array
   */
  protected static function get_many_from_data( $data ){
    $objects = array();
    foreach($data as $object_data){
      $objects[] = new static::$model_class($object_data);
    }
    return $objects;
  }


  /**
   * Check if we've seen this object during this class' lifetime
   */
  protected static function is_in_cache($key){
    return array_key_exists(static::$model_class, self::$cache) && array_key_exists($key, self::$cache[static::$model_class]);
  }

  /**
   * Store a copy of this object for the lifetime of this class
   */
  protected static function insert_into_cache($key, $value){
    if(!array_key_exists(static::$model_class, self::$cache)) self::$cache[static::$model_class] = [];
    self::$cache[static::$model_class][$key] = $value;
    if(is_array($value)){
      foreach($value as $specific){
	self::$cache[static::$model_class][$specific->id] = $specific;
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
    $data = \database_controller::find("SELECT * FROM `". static::$table ."` WHERE `" . $column ."` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 1",
				       [":v" => $value]);
    return self::get_single_from_data($data);
  }

  /**
   * Get all possible objects from the database for the provided column and value
   */
  protected static function find_all_by_col_and_val( $column, $value ){
    $data = \database_controller::find("SELECT * FROM `". static::$table ."` WHERE `" . $column ."` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC",
				       [":v" => $value]);
    return self::get_many_from_data($data);
  }


  /**
   * Get all objects from the database for this controller's table
   */
  public static function find_all(){
    if(self::is_in_cache("all")) return self::get_from_cache("all");

    $data = \database_controller::find("SELECT * FROM `". static::$table ."` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC");
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
   * Find all objects that have the given id
   */
  public static function find_all_by_id( $id ){
    return self::find_all_by_col_and_val("id", $id);
  }


  /**
   * Find the last-inserted object
   */
  public static function find_last( $count ){
    if(self::is_in_cache("last".$count)) return self::get_from_cache("last".$count);
    if(self::is_in_cache("all")){
      $set = array_slice(self::get_from_cache("all"), 1-$count);
      self::insert_into_cache("last".$count, $set);
      return $set;
    }

    $count = intval($count);
    $data = \database_controller::find("SELECT * FROM `". static::$table . "` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT " . $count . ";");
    $objects = self::get_many_from_data($data);
    self::insert_into_cache("last".$count, $objects);
    return $objects;
  }
}
?>