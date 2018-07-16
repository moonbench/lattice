<?php
namespace app\model\traits;

trait uuid_saveable {
  use saveable;

  protected function __insert($columns, $placeholders, $values){
    sql_set("INSERT INTO `".self::table_name()."` (`id`, $columns) VALUES (UUID_SHORT(), $placeholders)", $values);
  }
}
?>
