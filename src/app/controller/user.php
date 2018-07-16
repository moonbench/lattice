<?php
namespace app\controller;

class user {
  public static function register($username, $email, $password, $confirm_password){
    if(strlen($username)<1) trigger_error('Username is required');
    if(strlen($password)<1) trigger_error('A password is required');

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) trigger_error('Email is invalid');
    if($password != $confirm_password) trigger_error('Passwords do not match');

    if(!!\user::find_by_name($username)) trigger_error('User with that name already exists');
    if(!!\user::find_by_email($email)) trigger_error('User with that email already exists');

    if(!\app\error::is_empty()) return false;

    $user = new \user(["name" => $username, "email" => $email]);
    $user->password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 14]);
    $user->save();
    self::login($username, $password);
    return $user;
  }

  public static function login($username, $password){
    $user = \user::find_by_name($username);
    if(!($user instanceof \user)){
      trigger_error('No such user');
      return null;
    }

    if(!password_verify($password, $user->password_hash)){
      trigger_error('Bad password');
      return null;
    }

    $session = \session::create_for_user($user);
    $user->session = $session;
    \user::$current_user = $user;

    return $user;
  }

  public static function logout($user){
    $user->session->delete();
    \user::$current_user = null;
    setcookie(\app\config::site('cookie_name'), '', time()-3600, "/");
  }
}
?>
