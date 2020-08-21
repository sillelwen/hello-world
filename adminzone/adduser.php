<?php include_once ("../inc/util.php");
if(!isset($_SESSION['userid']))
{
	header("Location: ../login.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'admin', $connect))
{
	header("Location: ../login.php");
	die();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$l = $_POST['login'];
	$e = $_POST['email'];
	$cn = $_POST['companyName'];
	$ct = $_POST['contractType'];
	$res = countUserByLogin($connect, $l);
	$row = $res->fetch_assoc();
	if($row['count']>0)
		$message = '<p class="message">Логин '.htmlspecialchars($l).' занят.</p>';
	elseif(!filter_var($e, FILTER_VALIDATE_EMAIL))
		$message = '<p class="message">'.htmlspecialchars($e).' не является допустимым адресом электронной почты.</p>';
	else
	{
		$p = generatePassword();
		$baseurl = getSetting('baseURL', $connect);
		
		if(!addNewUser($l, $e, $cn, $ct, $p, $connect))
		{
			$message = "<p class=\"message\">Ошибка при добавлении пользователя в базу данных.</p>";
		}
		elseif(!sendMailAttachment($e, getSetting('emailFrom', $connect), getSetting('nameFrom', $connect),
						"Вы зарегистрированы в личном кабинете ".htmlspecialchars($baseurl),
						"Здравствуйте!<br/>Вы (".htmlspecialchars($cn).") были зарегистрированы в личном кабинете ".htmlspecialchars($baseurl)
						.".<br/><br/>Для входа используйте следующие данные:<br/>Логин: ".htmlspecialchars($l)."<br/>Пароль: ".htmlspecialchars($p)))
		{
			$message = "<p class=\"message\">Пользователь добавлен в базу данных, но письмо не отправлено.</p>";			
		}
		else
		{
			$res = getUserByLogin($connect, $l);
			$row = $res->fetch_assoc();
			header("Location: usersettings.php?userid=".$row['id']);
			die();
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Администрирование - Добавление пользователя</title>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Добавление пользователя</h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;<a href="userlist.php">Пользователи</a>&nbsp;&gt;&nbsp;Добавление пользователя</p>
		<form method="POST">
		<?php echo($message);?>
		<table>
			<tr><td>Логин</td><td><input type="text" name="login" value="<?=htmlspecialchars($l)?>" maxlength="30" required /></td></tr>
			<tr><td>E-mail</td><td><input type="text" name="email" value="<?=htmlspecialchars($e)?>" maxlength="30" required /></td></tr>
			<tr><td>Название компании</td><td><input type="text" name="companyName" maxlength="30" value="<?=htmlspecialchars($cn)?>" required /></td></tr>
			<tr><td>Тип договора</td><td><select name="contractType" required><?=listOptions('contracttypes', $connect, $ct)?></select></td></tr>
			<tr><td colspan="2"><input type="submit" value="Добавить"/></tr>
		</table>
		</form>
		<p>Пароль генерируется автоматически и высылается на указанный e-mail.</p>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>