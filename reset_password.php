<?php include_once ("inc/util.php");?>
<?php
$showform = TRUE;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$l = $_POST['login'];
	$e = $_POST['email'];
	$res = getUserByLoginAndEmail($connect, $l, $e);
	if($res->num_rows==0)
		$message = "<p class=\"message\">Пользователь с логином ".htmlspecialchars($l)." и адресом электронной почты ".htmlspecialchars($e)." не найден в базе данных.</p>";
	else
	{
		$row=$res->fetch_assoc();
		$token = md5($e.time());
		deletePasswordResetRows($connect, $userid);
		if($res = addPasswordResetRow($connect, $row['id'], getUserIpAddr(), $token))
		{
			$baseurl = getSetting('baseURL', $connect);
			if(sendMailAttachment($e, getSetting('emailFrom', $connect), getSetting('nameFrom', $connect), "Сброс пароля в личном кабинете ".htmlspecialchars($baseurl), "Здравствуйте!<br/>Вы запросили ссылку для сброса пароля пользователя ".htmlspecialchars($l)." в личном кабинете ".htmlspecialchars($baseurl).".<br/><br/>Для сброса пароля перейдите по следующей ссылке: $baseurl/reset_password.php?token=$token.<br/>Ссылка действительна сутки.<br/><br/>Если вы не запрашивали сброс пароля, игнорируйте это письмо."))
			{
				$showform = FALSE;
				$message = '<p class="message">Ссылка для сброса пароля отправлена на ваш электронный адрес.</p>';
			}
			else
				$message = '<p class="message">Произошла ошибка при отправке письма со ссылкой для сброса пароля.</p>';
		}
		else
			$message = '<p class="message">Произошла ошибка при попытке создания ссылки для сброса пароля.</p>';
	}
}
if (isset($_GET['token']))
{
	$res = getPasswordResetData($connect, $_GET['token']);
	if ($res->num_rows==0)
		$message='<p class="message">Ошибка сброса пароля: неизвестный токен или токен устарел.</p>';
	else
	{
		$row=$res->fetch_assoc();
		$baseurl = getSetting('baseURL', $connect);
		$userid = $row['userid']; $password = generatePassword(); $login=$row['login'];
		if(updateUserPassword($connect, $userid, $password))
		{
			deletePasswordResetRows($connect, $userid);
			if(sendMailAttachment($row['email'], getSetting('emailFrom', $connect), getSetting('nameFrom', $connect),
				"Новый пароль в личном кабинете ".htmlspecialchars($baseurl), "Здравствуйте!<br/>Вы сбросили пароль в личном кабинете ".htmlspecialchars($baseurl).".<br/><br/>Для входа используйте следующие данные:<br/>Логин: ".htmlspecialchars($login)."<br/>Пароль: $password"))
			{
				$message = '<p class="message">Пароль успешно сброшен, новый пароль отправлен вам на электронную почту.</p>';
				$showform = FALSE;
			}
			else
				$message = '<p class="message">Пароль был сброшен, но, к сожалению, произошла ошибка при попытке отправить его на электронную почту. Попробуйте запросить сброс пароля заново. Если ошибка сохраняется, сообщите нам об этом <a href="mailto:logist@dedal-express.ru">по электронной почте</a>.</p>';
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('inc/metalinks.php');?>
		<title>Личный кабинет - Сброс пароля</title>
	</head>
	<body>
		<?php include_once ("inc/header.php");?>
		<h3>Сброс пароля</h3>
		<?php
		if($showform)
		{
		?>
		<p>Если у вас есть учётная запись в нашем личном кабинете, но вы <strong>забыли пароль</strong>, заполните форму ниже, чтобы получить письмо со ссылкой для сброса пароля.</p>
		<p>Чтобы получить новую учётную запись, свяжитесь с нами <a href="mailto:logist@dedal-express.ru">по электронной почте</a>.</p>
		<form method="POST">
		<?php
		}
		echo($message);
		if($showform)
		{
		?>
			<table>
				<tr><td>Логин:</td><td><input type="text" name="login" required autofocus /></td></tr>
				<tr><td>E-mail:</td><td><input type="email" name="email" required /></td></tr>
				<tr><td></td><td><input type="submit" value="Сбросить пароль"/></td></tr>
			</table>
		</form>
		<?php
		}
		?>
<?php
//phpinfo(32);
//закрываем соединение с БД
$connect->close();
?>
	</body>
</html>