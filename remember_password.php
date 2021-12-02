<?php require "db.php";
require "random.php";
echo '
<html>
<head>
  <title>Восстановление пароля</title>
  <meta charset="utf-8">
  <link type="text/css" href = "main.css" rel = "stylesheet">
 </head>
 <body>
';
$data = $_POST;

if (isset($data['do_remember'])){
  if (trim($data['email']) == '') {
    echo "<div class=\"error\">Вы не указали свой email</div> <br>";
  } else {
    $user = R::findOne('users', 'email = ?', array($data['email']));
    if ($user){
      $token = randomString(20);// генерим новый токен
      $user -> token = $token;// обновляем его
      R::store($user);// сохраняем
      $fr="From: info@s17.hostia.name\r\n";
      $fr.="Content-type: text/html; charset=utf-8\r\n";

      $subject = "Восстановление пароля";
      $message = "
      <html>
        <head>
          <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
          <title>Восстановление пароля</title>
        </head>
        <body>
        <table width=\"50%\" align=\"center\" style=\"background-color:lightskyblue; -webkit-border-radius:15px;
         -moz-border-radius:15px; border-radius:15px; box-shadow: 0 0 5px black; box-shadow: 0 0 10px rgba(0,0,0,0.5);
          -moz-box-shadow: 0 0 10px rgba(0,0,0,0.5); -webkit-box-shadow: 0 0 10px rgba(0,0,0,0.5); padding:20px; font-size:1em\">
          <tr><td>Мы получили сообщение о том, что вы забыли свой пароль. Если это были вы, у вас есть возможность сбросить пароль прямо сейчас.</td></tr>
          <tr><td>Для сброса пароля перейдите по <a href=\"http://redbeam.xyz/test/password_change.php?email="
          .$data['email']."&token=".$token."\">ccылке</a></td></tr>
          <tr><td>Если вы не запрашивали ссылку для сброса пароля, проигнорируйте это сообщение</td></tr>
        </table>
        </body>
      </html>";

    $to = $data['email'];
    if(mail($to, $subject, $message, $fr)){
        echo "<div class=\"success\">Ссылка для сброса пароля отправлена на почту</div> <br>";
      }
    } else {
      echo "<div class=\"error\">Пользователь с таким адресом почты не найден</div> <br>";
    }
  }
}

echo "<form action=\"/test/remember_password.php\" method=\"post\">
    <h2>Восстановление пароля</h2>
    <p>Введите Email который указывали при регистрации</p>
    <p><input type=\"email\" name=\"email\" placeholder=\"email\"></p>
    <button type=\"submit\" name=\"do_remember\">Далее</button>
  </form>";

 echo "</body>
  </html>";
  ?>