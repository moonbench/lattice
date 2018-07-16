<?php
namespace app;

class config {
  protected static $site_config;
  protected static $database_config;

  public static function db($attribute, $default=null){
    if(!isset(self::$database_config)) self::load_database_config();
    return get_or_else(self::$database_config, $attribute, $default);
  }

  public static function site($attribute, $default=null){
    if(!isset(self::$site_config)) self::load_site_config();
    return get_or_else(self::$site_config, $attribute, $default);
  }

  public static function clear_db_auths(){
    unset(self::$database_config['username']);
    unset(self::$database_config['password']);
    unset(self::$database_config['hostname']);
    unset(self::$database_config['database']);
  }

  public static function load_database_config(){
    self::$database_config = parse_ini_file(APP_ROOT.'config/database.default.ini');
    if(file_exists(APP_ROOT.'config/database.ini')) self::$database_config = parse_ini_file(APP_ROOT.'config/database.ini');
  }

  public static function load_site_config(){
    self::$site_config = parse_ini_file(APP_ROOT.'config/site.default.ini');
    if(file_exists(APP_ROOT.'config/site.ini')) self::$site_config = parse_ini_file(APP_ROOT.'config/site.ini');

    define('SITE_ROOT', self::site('site_root'));
    define('SITE_PATH', APP_ROOT.self::site('app_to_site'));
  }
}
?>
