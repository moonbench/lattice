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
    self::login($username, $password);
    return attempt::success($user);
  }

  public static function login($username, $password){
    $user = \user::find_by_name($username);

    if(!($user instanceof \user)) return attempt::failure('No such user');
    if(!password_verify($password, $user->password_hash)) return attempt::failure('Bad password');

    $session = \session::create_for_user($user);
    $user->session = $session;
    \user::$current_user = $user;
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
