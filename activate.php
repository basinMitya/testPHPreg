<?php require "db.php";
echo '
<html>
<head>
  <title>Подтверждение аккаунта</title>
  <meta charset="utf-8">
  <link type="text/css" href = "main.css" rel = "stylesheet">
 </head>
 <body>
';
$data = $_GET;
$user = R::findOne('users', 'login = ?', array($data['login']));
if ($user){
  $user -> activate = 1;// подтверждаем аккаунт
  R::store($user);// сохраняем
  echo "<div class=\"success\">Ваш аккаунт успешно подтвержден. Можете выполнить вход. <a href=\"/test/index.php\">Главная</a></div>";
} else {
  echo "<div class=\"error\">Пользователь не найден.</div>";
 }
echo "</body>
  </html>";
  ?>
