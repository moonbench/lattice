<?php
/*
 * Common functions for handling images
 */
require_once APP_ROOT . "/common/files.php";


/**
 * Generate a new width and height by scaling an image down to be
 * no larger than the provided max width and max height
 */
function thumbnail_dimensions_from( $width, $height, $max_width, $max_height ){
  $aspect_ratio = $width / $height;
  $thumb_ratio = $max_width / $max_height;

  $thumbnail_width = 0;
  $thumbnail_height = 0;

  if( $width <= $max_width && $height <= $max_height ){
    $thumbnail_width = $width;
    $thumbnail_height = $height;
  } else if( $thumb_ratio > $aspect_ratio ){
    $thumbnail_width = (int) ($max_width * $aspect_ratio);
    $thumbnail_height = $max_height;
  } else {
    $thumbnail_width = $max_height;
    $thumbnail_height = (int) ($max_width / $aspect_ratio);
  }

  return array( $thumbnail_width, $thumbnail_height );
}

/**
 * Create a new image by scaling down an exiting image
 */
function create_thumbnail( $url, $thumb_url, $max_width, $max_height ){
  $url = APP_ROOT . "/../" . $url;

  list($width, $height, $filetype) = getimagesize($url);
  $source_image_data = get_local_source_from_url_for_type($url, $filetype);

  make_directory_if_needed( $thumb_url );
  if( !\app\error::is_empty()) return;

  list( $thumbnail_width, $thumbnail_height ) = thumbnail_dimensions_from( $width, $height, $max_width, $max_height );
  $thumbnail_gd_image = imagecreatetruecolor( $thumbnail_width, $thumbnail_height );
  $copied_success = imagecopyresampled( $thumbnail_gd_image, $source_image_data, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $width, $height );
  imagepng( $thumbnail_gd_image, APP_ROOT . "/../" . $thumb_url, 4);
  imagedestroy( $thumbnail_gd_image );

  if( !$copied_success ){
    trigger_error("Failed to create thumbnail image. Using resolution: $thumbnail_width x $thumbnail_height");
    return false;
  }
  return true;
}


/**
 * Obtain an image object from a path
 */
function get_local_source_from_url_for_type( $url, $filetype ){
  switch( $filetype ){
  case IMAGETYPE_GIF:
    return imagecreatefromgif( $url );
    break;
  case IMAGETYPE_JPEG:
    return imagecreatefromjpeg( $url );
    break;
  case IMAGETYPE_PNG:
    return imagecreatefrompng( $url );
    break;
  }
  if( !isset( $source_image_data )){
    trigger_error("Unable to reade image data");
    return false;
  }
}
?>
