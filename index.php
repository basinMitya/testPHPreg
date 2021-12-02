<?php require "db.php";
require "random.php";
echo '
<html>
<head>
  <title>Главная</title>
  <meta charset="utf-8">
  <link type="text/css" href = "main.css" rel = "stylesheet">
 </head>
 <body>
';
$data = $_POST;

if (isset($data['do_post'])){//нажали кнопку опубликовать
	$message_errors = array();

	if (trim($data['message_theme']) == ''){
		$message_errors[] = 'Введите тему новой записи';
	}

	if (trim($data['message_text']) == ''){
		$message_errors[] = 'Введите текст записи';
	}

	if (empty($message_errors)){
	$message = R::dispense('messages');
		$message -> theme = $data['message_theme'];
		$message -> text = $data['message_text'];
		R::store($message);// сохраняем
	} else {
		echo "<div class=\"error\">".array_shift($message_errors)."</div> <br>";//выводим первую ошибку из массива ошибок
		$message_theme_value = $data['message_theme'];//подставим в поле тема ранее введенный текст для удобства
		$message_text_value = $data['message_text'];//подставим в поле текст так же предыдуший текст
	}
}

if (isset($data['do_login'])){//пользователь пытается войти
	$login_errors = array();

	$user = R::findOne('users', 'login = ?', array($data['login']));//проверяем есть ли такой логин в бд
	if ($user){
		//пользователь есть
	  if (password_verify($data['password'], $user -> password)){//пароль совпал
			if ($user -> activate == 1){//и страница подтверждена
				$_SESSION['logged_user'] = $user;
			} else $login_errors[] = 'Ваш аккаунт не подтвержден';
		} else {
			$login_errors[] = 'Неверно введен пароль';
			$login_value = $data['login'];
		}
	} else {
		$login_errors[] = 'Пользователь с таким логином не найден';
	}

	if (! empty($login_errors)){
		echo "<div class=\"error\">".array_shift($login_errors)."</div> <br>";
	}
}

if (isset($data['do_signup'])){//нажали кнопку регистрация
	$errors = array();// массив для хранения ошибок

	if (trim($data['login']) == ''){
		// юзер не заполнил поле логин
		$errors[] = 'Введите логин';
	}

	if (trim($data['email']) == ''){
		// юзер не заполнил поле почты
		$errors[] = 'Введите email';
	}

	if (R::count('users', "login = ?", array($data['login'])) > 0){
		$errors[] = 'Этот логин уже занят';
	}

	if (empty($errors)){
		// ошибок нет, шлем на мыло пароль и ссылку активации

		$password = randomString(8);
		$token = randomString(20);

		$user = R::dispense('users');
		$user -> login = $data['login'];
		$user -> email = $data['email'];
		$user -> password = password_hash($password, PASSWORD_DEFAULT);
		$user -> token = $token;
		$user -> activate = 0;// страница пока не подтверждена
		R::store($user);// сохраняем

		$fr="From: info@s17.hostia.name\r\n";
		$fr.="Content-type: text/html; charset=utf-8\r\n";

		$subject = "Регистрация нового пользователя";
		$message = "
			<html>
    		<head>
   				<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
					<title>Регистрация нового пользователя</title>
    		</head>
    		<body>
				<table width=\"50%\" align=\"center\" style=\"background-color:lightskyblue; -webkit-border-radius:15px;
				 -moz-border-radius:15px; border-radius:15px; box-shadow: 0 0 5px black; box-shadow: 0 0 10px rgba(0,0,0,0.5);
				  -moz-box-shadow: 0 0 10px rgba(0,0,0,0.5); -webkit-box-shadow: 0 0 10px rgba(0,0,0,0.5); padding:20px; font-size:1em\">
					<tr><td>Этот адрес электронной почты был указан при регистрации.</td></tr>
					<tr><td>Ваш логин: ".$data['login']."</td></tr>
					<tr><td>Ваш пароль: ".$password."</td></tr>
					<tr><td>Для подтверждения регистрации перейдите по <a href=\"http://redbeam.xyz/test/activate.php?login="
					.$data['login']."&token=".$token."\">ccылке</a></td></tr>
				</table>
    		</body>
			</html>";

		$to = $data['email'];
		if(mail($to, $subject, $message, $fr)){
				echo "<div class=\"success\">Отправили письмо с паролем и ссылкой активации</div> <br>";
			}

	} else {
		echo "<div class=\"error\">".array_shift($errors)."</div> <br>";//выводим первую ошибку из массива
		$signup_login_value = $data['login'];//подставим в поле логин ранее введенный текст для удобства
		$signup_email_value = $data['email'];//подставим в поле адрес почты так же
	}
}

if (isset($_SESSION['logged_user'])){
	//пользователь авторизован
	echo "<div class=\"success\">Добро пожаловать. <a href=\"/test/logout.php\">Выйти</a></div>
<br>
	<form action=\"/test/index.php\" method=\"post\">
		<h2>Новая запись</h2>
		<p><input type=\"text\" name=\"message_theme\" value=\"$message_theme_value\" placeholder=\"Тема сообщения\"></p>

		<p><textarea rows=\"5\" cols=\"25\"  name=\"message_text\" 
	 placeholder=\"Текст сообщения\">$message_text_value</textarea></p>

		<button type=\"submit\" name=\"do_post\">Опубликовать</button>
	</form>";

	$messages = R::findAll('messages');
	foreach (array_reverse($messages) as $message) {
		$text = $message -> text;
		$theme = $message -> theme;
		echo "<br>
		<div class=\"msg\">
		<p> <b>$theme</b> </p>
         <p> $text </p>
         </div> <br>";
     }
} else {
	echo "
	<form action=\"/test/index.php\" method=\"post\">
		<h2>Зарегистрироваться</h2>
		<p>Логин <br> <input type=\"text\" name=\"login\" value=\"$signup_login_value\"></p>
		<p>Email <br> <input type=\"email\" name=\"email\" value=\"$signup_email_value\"></p>
		<button type=\"submit\" name=\"do_signup\">Продолжить регистрацию</button>
	</form>
	<br>
	<form action=\"/test/index.php\" method=\"post\">
		<h2>Войти</h2>
		<p>Логин <br> <input type=\"text\" name=\"login\" value=\"$login_value\"></p>
		<p>Пароль <br> <input type=\"password\" name=\"password\"></p>
		<p><a href=\"/test/remember_password.php\">Забыл пароль</a></p>
		<button type=\"submit\" name=\"do_login\">Войти</button>
	</form>
	";
}

echo "</body>
	</html>";
	?>
