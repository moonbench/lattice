<?php
namespace app\model;

class file extends model {
  public $upload;
  public $original_filename;
  public $url;
  public $size;
  public $filename;
  protected $human_size;
  protected $hash;
  protected $owner;

  public static function from_upload($upload, $to_directory=''){
    $file = new self(['upload' => $upload]);

    $file->original_filename = $file->upload['name'];
    $file->filename = $file->make_filename();
    $file->url = $file->make_url($to_directory);

    if(!$file->copy_upload_to_server($to_directory)) return false;

    $file->size = filesize(SITE_PATH.$file->url);
    $file->hash = hash_file('sha256', SITE_PATH.$file->url);

    return $file;
  }

  public function is_acceptable_to_upload(){
    if(!file_exists($this->upload['tmp_name'])) trigger_error('No such file');

    $type = $this->upload['type'];
    if(!in_array($type, explode(', ', \app\config::site('allowed_file_types')))) trigger_error('Invalid file type');

    if($this->upload['size'] > \app\config::site('max_upload_bytes')){
      $size = self::human_size($this->upload['size']);
      $max = self::human_size(\app\config::site('max_upload_bytes'));
      trigger_error("File is too big (Size: $size, Max: $max)");
    }

    if(\app\error::is_empty()) return true;
  }

  public static function human_size($bytes, $precision = 2){
    $base = log($bytes, 1000);
    $suffixes = array('', 'kb', 'Mb', 'Gb', 'Tb');
    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
  }

  public function __get($property){
    if($property == "owner" && !($this->owner instanceof \user)) $this->owner = $this->lazy_load_owner();
    return parent::__get($property);
  }

  protected function copy_upload_to_server(){
    if(!isset($this->upload['name']) || !file_exists($this->upload['tmp_name']))
      trigger_error('File not found on the server');

    $filepath = SITE_PATH.$this->url;

    if(!$this->is_acceptable_to_upload()) trigger_error('Invalid file');
    if(file_exists($filepath)) trigger_error('Duplicate file');

    if(!\app\error::is_empty()) return false;
    self::make_directory_if_needed($filepath);
    return move_uploaded_file($this->upload['tmp_name'], $filepath);
  }

  protected static function make_directory_if_needed($filepath){
    $file_components = explode('/', $filepath);
    array_pop($file_components);

    $path = '/';
    foreach($file_components as $directory){
      $path .= $directory . '/';

      if(!file_exists($path) || !is_dir($path)){
        mkdir($path);

        if(!file_exists($path) || !is_dir($path)){
          trigger_error("Unable to find or create directory, $path");
          return;
        }
      }
    }
  }

  protected function make_url($directory=""){
    $directory = strlen($directory) > 0 ? "$directory/" : $directory;
    return \app\config::site('upload_directory')."/$directory".date('Y').'/'.date('m').'/'.$this->filename;
  }

  protected function make_filename(){
    $file_type = explode('.', $this->upload['name']);
    $name = time() . substr(microtime(),2, 2);
    $name .= '.' . strtolower(end($file_type));
    return $name;
  }

  protected function lazy_load_human_size(){
    return self::human_size($this->size);
  }

  protected function lazy_load_owner(){
    return \user::find_by_id($this->owner);
  }
}
?>
