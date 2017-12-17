<?php
namespace app\controller;
/**
 * Provides methods for accessing and manipulating image models
 */
require_once APP_ROOT . "/common/files.php";
require_once APP_ROOT . "/common/images.php";

class image extends controller {
  protected static $table = "images";
  protected static $model_class = "image";

  const MEDIA_DIRECTORY = "/images";
  const THUMBNAIL_DIRECTORY = "/images/t";

  /**
   * Generate a hash of the image's contents
   */
  public static function hash_for_image( $upload ){
    return hash_for_file($upload);
  }

  /**
   * Copy an uploaded file to the server and store it in a model
   */
  public static function save_upload_to_server_for_image( $upload, $image ){
    if( !isset( $upload["name"] ) || !file_exists( $upload['tmp_name']) ){
      trigger_error("Uploaded file did not make it to server");
      return;
    }

    $image->filename = $upload['name'];
    $image->url = generate_url_for_file( $upload, self::MEDIA_DIRECTORY );

    copy_uploaded_file_to_directory( $upload, $image->url );
    if( !\app\error::is_empty()) return;

    list( $width, $height ) = getimagesize( APP_ROOT . "/../" . $image->url );
    $image->width = $width;
    $image->height = $height;

    $image->size = filesize( APP_ROOT . "/../" . $image->url );

    return $image;
  }

  /**
   * Generate small and medium sized thumbnails
   *
   * This will generate thumbnails if the source image is larger than a threshold
   */
  public static function create_thumbnails_for_image( $upload, $image ){
    if( $image->width > 499 || $image->height > 499 ){
      $image->thumb_small_url = generate_url_for_file( $upload, self::THUMBNAIL_DIRECTORY, "_at350" );
      create_thumbnail( $image->url, $image->thumb_small_url, 350, 350 );
    } else {
      $image->thumb_small_url = $image->url;
    }

    if( $image->width > 1300 || $image->height > 1300){
      $image->thumb_medium_url = generate_url_for_file( $upload, self::THUMBNAIL_DIRECTORY,  "_at1200" );
      create_thumbnail( $image->url, $image->thumb_medium_url, 1200, 1200 );
    } else {
      $image->thumb_medium_url = $image->url;
    }

    return $image;
  }
}
?>
