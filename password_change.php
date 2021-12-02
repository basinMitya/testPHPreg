<?php 
require "db.php";
echo '
<html>
<head>
  <title>Смена пароля</title>
  <meta charset="utf-8">
  <link type="text/css" href = "main.css" rel = "stylesheet">
 </head>
 <body>
';
if (isset($_GET['token'])){
	//юзер пришел из почты
	$email = $_GET['email'];
	$token = $_GET['token'];
	$save_password = false;
} else if (isset($_POST['password'])){
	//юзер нажал кнопку сохранить и пришла форма
	$email = $_POST['email'];
	$password = $_POST['password'];
	$save_password = true;
}

if ($save_password){
  if (trim(strlen($password)) < 8) {
    echo "<div class=\"error\">Пароль должен содержать не менее 8 символов</div><br>";
    show_form($email);// покажем форму еще раз
  } else {
  	$user = R::findOne('users', 'email = ?', array($email));
	if ($user){
		$user -> password = password_hash($password, PASSWORD_DEFAULT);
  		R::store($user);
  		echo "<div class=\"success\">Ваш пароль изменен. <a href=\"/test/index.php\">Главная</a></div>";
	} else {
		echo "<div class=\"error\">Не удалось обновить пароль. Пользователь не найден</div>";
	}
  	exit();
  }
} else {
	$user = R::findOne('users', 'email = ?', array($email));
	if ($user){
		if ($user -> token == $_GET['token']){
			show_form($email);
		} else {
			echo "<div class=\"error\">Ссылка недействительна.</div>";
		}
	} else {
		echo "<div class=\"error\">Пользователь не найден</div>";
	}
}

function show_form($s) {
    echo "<form action=\"/test/password_change.php\" method=\"post\">
    	<h2>Смена пароля</h2>
    	<p>Ваш новый пароль <input type=\"password\" name=\"password\" required></p>
    	<input type=\"hidden\" name=\"email\" value=\"".$s."\">
    	<button type=\"submit\" name=\"do_save_password\">Сохранить</button>
  		</form>";
}
echo "</body>
  </html>";
  ?>