<?php
namespace app\model;

abstract class model implements \JsonSerializable{
  public function __construct($data = array()){
    $this->set_values($data);

    if(property_exists($this, "created_at") && !isset($this->created_at))
      $this->created_at = date("Y-m-d H:i:s");
  }

  public function __get($property){
    if(!isset($this->$property)) $this->$property = $this->try_lazy_load($property);
    return $this->$property;
  }

  public function __set($property, $value){
    if(property_exists($this, $property)){ $this->$property = $value; }
  }

  public function jsonSerialize(){
    $json = array();
    foreach($this as $property => $value){ $json[$property] = $value; }
    return $json;
  }

  protected function set_values($data){
    foreach($data as $property => $value){
      if(preg_match('/^[0-9]/', $property)) continue;
      if(property_exists($this, $property)) $this->$property = $value;
    }
  }

  protected function try_lazy_load($property){
    $method = "lazy_load_" . $property;
    if(method_exists($this, $method)) return call_user_func(array($this, $method));
  }
}
?>
