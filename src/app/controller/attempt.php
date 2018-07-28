<?php
namespace app\controller;

class attempt {
  public $successful = false;
  public $failed = false;
  public $errors = [];
  public $output;

  public function succeed($object=null){
    if($this->failed) return $this;

    $this->successful = true;
    $this->output = $object;
    return $this;
  }

  public static function success($object=null){
    return (new self())->succeed($object);
  }

  public function fail($error){
    $this->successful = false;
    $this->failed = true;
    $this->errors[] = $error;
    return $this;
  }

  public static function failure($errors){
    return (new self())->fail($errors);
  }
}
?>
