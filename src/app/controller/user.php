<?php
namespace app\controller;

class user {
  public static function register($username, $email, $password, $confirm_password){
    $attempt = new attempt();

    if(strlen($username)<1) $attempt->fail('Username is required');
    if(strlen($password)<1) $attempt->fail('A password is required');
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $attempt->fail('Email is invalid');
    if($password != $confirm_password) $attempt->fail('Passwords do not match');
    if($attempt->failed) return $attempt;

    if(!!\user::find_by_name($username)) return $attempt->fail('User with that name already exists');
    if(!!\user::find_by_email($email)) return $attempt->fail('User with that email already exists');

    $user = self::create_user($username, $email, $password);
    $login_attempt = self::login($username, $password);
    if($login_attempt->failed){
      trigger_error('Unable to log in newly created user');
      inspect($login_attempt);
    }
    return attempt::success($user);
  }

  public static function login($username, $password){
    if(!\login_attempt::allowed_for_current_ip())
      return attempt::failure('Too many recent failed login attempts');

    $user = \user::find_by_name($username);
    $login_attempt = \login_attempt::create_for_user($user);
    $login_attempt->save();

    if(!($user instanceof \user))return attempt::failure('No such user');
    if(!password_verify($password, $user->password_hash)) return attempt::failure('Bad password');

    $session = \session::create_for_user($user);
    $user->session = $session;
    \user::$current_user = $user;
    $login_attempt->succeed();

    return attempt::success($user);
  }

  public static function logout($user){
    $user->session->delete();
    \user::$current_user = null;
    setcookie(\app\config::site('cookie_name'), '', time()-3600, "/");
    return attempt::success();
  }

  protected static function create_user($username, $email, $password){
    $user = new \user(["name" => $username, "email" => $email]);
    $user->password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 14]);
    $user->save();
    return $user;
  }
}
?>
