<?php
namespace app\model;
/**
 * Represents an image file
 */

class image extends model {
  protected static $table = "images";

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

  /**
   * Create or update the database row for this image
   */
  public function save(){
    parent::__save(["filename", "url", "thumb_small_url", "thumb_medium_url", "width", "height", "thumb_medium_width", "thumb_medium_height", "size", "hash", "created_at", "deleted_at"],
		   [$this->filename, $this->url, $this->thumb_small_url, $this->thumb_medium_url, $this->width, $this->height, $this->thumb_medium_url, $this->thumb_medium_height, $this->size, $this->hash, $this->created_at, $this->deleted_at]);
  }
}
?>
