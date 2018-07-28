<?php
namespace app\controller;

class image {
  public static function upload_for_current_user($file_index){
    $image = \image::from_upload($_FILES[$file_index]);
    if(!$image) return attempt::failure('Unable to upload file');

    $image->owner = \user::current();
    return attempt::success($image);
  }
}
?>
