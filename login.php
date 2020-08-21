<?php include_once ("inc/util.php");
DEFINE ('EVENTSOURCE', 'login');
if(isset($_SESSION['userid']))
{
	header("Location: index.php");
	die();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$l = $_POST['login'];
	$res = getUserByLogin($connect, $l);
	if($res->num_rows==0)
		$message = "<p class=\"message\">Пользователь с логином ".htmlspecialchars($l)." не найден в базе данных.</p>";
	else
	{
		$row = $res->fetch_assoc();
		$p = $_POST['password'];
		if(!password_verify($p, $row['password_hash']))
		{
			$message = "<p class=\"message\">Пользователь с таким сочетанием логина и пароля не найден в базе данных.</p>";
		}
		elseif(!hasNamedRight($row['id'], 'login', $connect))
		{
			$message = "<p class=\"message\">Пользователь не имеет права входа в систему (вероятно, был заблокирован).</p>";
		}
		else
		{
			setsessionloginparams($row);
			sessionreadsettings($connect);
			logevent($connect, EVENT_TYPE_AUDIT, EVENTSOURCE, 'Successfully logged in');
			header("Location: index.php");
			die();
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('inc/metalinks.php');?>
		<title>Личный кабинет - Вход</title>
	</head>
	<body>
		<?php include_once ("inc/header.php");?>
			<h3>Личный кабинет</h3>
			<p>Если у вас есть учётная запись в нашем личном кабинете, вы можете использовать её, чтобы регистрировать свои отправления.</p>
			<p>Чтобы получить учётную запись, свяжитесь с нами <a href="mailto:logist@dedal-express.ru">по электронной почте</a>.</p>
			<form method="POST">
				<?php echo($message);?>
				<table>
					<tr><td>Логин:</td><td><input type="text" name="login" required autofocus /></td></tr>
					<tr><td>Пароль:</td><td><input type="password" name="password" required /></td></tr>
					<tr><td></td><td><input type="submit" value="Войти"/></td></tr>
					<tr><td></td><td><a href="reset_password.php">Забыли пароль?</a></td></tr>
				</table>
			</form>
<?php
//phpinfo(32);
//закрываем соединение с БД
$connect->close();
?>
		</div>
	</body>
</html>