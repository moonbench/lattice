<?php
namespace app\model;
require_once APP_ROOT . "common/files.php";
require_once APP_ROOT . "common/images.php";

class image extends model {
  protected static $table = "images";
  const MEDIA_DIRECTORY = "images";
  const THUMBNAIL_DIRECTORY = "images/t";

  public $id;
  public $filename;
  public $url;
  public $thumb_small_url;
  public $thumb_medium_url;
  public $width;
  public $height;
  public $thumb_medium_width;
  public $thumb_medium_height;
  public $size;
  public $hash;
  public $created_at;
  public $deleted_at;

  public function save(){
    parent::__save(["filename", "url",
                    "thumb_small_url", "thumb_medium_url",
                    "width", "height", "thumb_medium_width", "thumb_medium_height",
                    "size", "hash",
                    "created_at", "deleted_at"],
                   [$this->filename, $this->url,
                    $this->thumb_small_url, $this->thumb_medium_url,
                    $this->width, $this->height, $this->thumb_medium_url, $this->thumb_medium_height,
                    $this->size, $this->hash,
                    $this->created_at, $this->deleted_at]);
  }

  public static function hash_for_image($upload){
    return hash_for_file($upload);
  }

  public static function save_upload_to_server_for_image($upload, $image){
    if(!isset($upload["name"]) || !file_exists($upload['tmp_name'])){
      trigger_error("Uploaded file did not make it to server");
      return;
    }

    $image->filename = $upload['name'];
    $image->url = generate_url_for_file($upload, self::MEDIA_DIRECTORY);

    copy_uploaded_file_to_directory( $upload, $image->url);
    if(!\app\error::is_empty()) return;

    list($width, $height) = getimagesize(APP_ROOT . "../" . $image->url);
    $image->width = $width;
    $image->height = $height;

    $image->size = filesize(APP_ROOT . "../" . $image->url);

    return $image;
  }
}
?>
