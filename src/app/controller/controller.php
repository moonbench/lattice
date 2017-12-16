<?php
namespace app\controller;

/*
 * Defines common methods for all controllers
 */
abstract class controller {
  protected static $cache = [];

  protected static function get_single_from_data( $data ){
    if(count($data)<1) return null;
    return new static::$model_class($data[0]);
  }
  protected static function get_many_from_data( $data ){
    $objects = array();
    foreach($data as $object_data){
      $objects[] = new static::$model_class($object_data);
    }
    return $objects;
  }


  protected static function is_in_cache($key){
    return array_key_exists(static::$model_class, self::$cache) && array_key_exists($key, self::$cache[static::$model_class]);
  }
  protected static function insert_into_cache($key, $value){
    if(!array_key_exists(static::$model_class, self::$cache)) self::$cache[static::$model_class] = [];
    self::$cache[static::$model_class][$key] = $value;
    if(is_array($value)){
      foreach($value as $specific){
	self::$cache[static::$model_class][$specific->id] = $specific;
      }
    }
  }
  protected static function get_from_cache($key){
    return self::$cache[static::$model_class][$key];
  }


  protected static function find_one_by_col_and_val( $column, $value ){
    $data = \database_controller::find("SELECT * FROM `". static::$table ."` WHERE `" . $column ."` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 1",
				       [":v" => $value]);
    return self::get_single_from_data($data);
  }
  protected static function find_all_by_col_and_val( $column, $value ){
    $data = \database_controller::find("SELECT * FROM `". static::$table ."` WHERE `" . $column ."` = :v AND `deleted_at` IS NULL ORDER BY `created_at` DESC",
				       [":v" => $value]);
    return self::get_many_from_data($data);
  }


  public static function find_all(){
    if(self::is_in_cache("all")) return self::get_from_cache("all");

    $data = \database_controller::find("SELECT * FROM `". static::$table ."` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC");
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

  public static function find_all_by_id( $id ){
    return self::find_all_by_col_and_val("id", $id);
  }

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