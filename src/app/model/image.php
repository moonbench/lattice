<?php
namespace app\model;

class image extends file {
  use traits\uuid_saveable;

  protected static $table = 'images';

  public $id;
  public $thumb_small_url;
  public $thumb_medium_url;
  public $width;
  public $height;
  public $created_at;
  public $deleted_at;

  public static function from_upload($upload, $to_directory=''){
    $image = new self(parent::from_upload($upload, \app\config::site('image_directory', $to_directory)));

    list($width, $height) = getimagesize(SITE_PATH.$image->url);
    $image->width = $width;
    $image->height = $height;

    return $image;
  }

  public function save(){
    $owner = $this->__get('owner');
    $owner = !!$owner ? $owner->id : null;

    self::__save(["owner",
                  "filename", "original_filename",
                  "url",
                  "thumb_small_url", "thumb_medium_url",
                  "width", "height",
                  "size", "hash",
                  "created_at", "deleted_at"],
                 [$owner,
                  $this->filename, $this->original_filename,
                  $this->url,
                  $this->thumb_small_url, $this->thumb_medium_url,
                  $this->width, $this->height,
                  $this->size, $this->hash,
                  $this->created_at, $this->deleted_at]);
}

  public function thumbnail_url($size='t'){
    return  \app\config::site('upload_directory').'/'.
            \app\config::site('thumbnail_directory').'/'.
            date('Y').'/'.date('m').'/'.
            "$size".$this->filename;
  }

  public function generate_thumbnail($thumbnail_url, $max_width, $max_height){
    $url = SITE_PATH.$this->url;
    $thumbnail_url = SITE_PATH.$thumbnail_url;

    list($width, $height, $filetype) = getimagesize($url);
    $imagedata = $this->get_imagedata_by_type($filetype);

    self::make_directory_if_needed($thumbnail_url);
    if(!\app\error::is_empty()) return;

    list($thumbnail_width, $thumbnail_height) = self::thumbnail_dimensions_for($width, $height, $max_width, $max_height);

    $image = \imagecreatetruecolor($thumbnail_width, $thumbnail_height);
    $success = \imagecopyresampled(
      $image,
      $imagedata,
      0, 0, 0, 0,
      $thumbnail_width,
      $thumbnail_height,
      $width,
      $height
    );
    \imagepng($image, $thumbnail_url, 4);
    \imagedestroy($image);

    if(!$success) trigger_error('Unable to create thumbnail.');
  }

  public function thumbnail_dimensions_for($width, $height, $max_width, $max_height){
    $aspect_ratio = $width / $height;
    $thumb_ratio = $max_width / $max_height;

    $thumbnail_width = 0;
    $thumbnail_height = 0;

    if($width <= $max_width && $height <= $max_height){
      $thumbnail_width = $width;
      $thumbnail_height = $height;
    } else if($thumb_ratio > $aspect_ratio){
      $thumbnail_width = (int) ($max_width * $aspect_ratio);
      $thumbnail_height = $max_height;
    } else {
      $thumbnail_width = $max_height;
      $thumbnail_height = (int) ($max_width / $aspect_ratio);
    }

    return [$thumbnail_width, $thumbnail_height];
  }

  protected function get_imagedata_by_type($filetype){
    switch($filetype){
    case IMAGETYPE_GIF:
      return \imagecreatefromgif(SITE_PATH.$this->url);
      break;
    case IMAGETYPE_JPEG:
      return \imagecreatefromjpeg(SITE_PATH.$this->url);
      break;
    case IMAGETYPE_PNG:
      return \imagecreatefrompng(SITE_PATH.$this->url);
      break;
    }
    trigger_error('Unable to read image data');
  }
}
?>
