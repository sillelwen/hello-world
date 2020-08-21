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
elseif(!hasNamedRight($_SESSION['userid'], 'editusersettings', $connect))
{
	header("Location: userlist.php");
	die();
}
elseif(empty($_GET['userid']))
{
	header("Location: userlist.php");
	die();
}

$userid = $_GET['userid'];
$res = getUserByID($connect, $userid);
if($res->num_rows==0)
{
	header("Location: userlist.php");
	die();
}
$row = $res->fetch_assoc();
$login = $row['login'];
$companyName = $row['companyName'];
$email = $row['email'];
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Личный кабинет - Настройки печатной формы</title>
	</head>
	<body onload="toggle('intervals', 'intervals2')">
		<?php include_once ("../inc/header.php");?>
		<h3>Настройки печатной формы для <?=$login?></h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;<a href="userlist.php">Пользователи</a>&nbsp;&gt;&nbsp;<a href="usersettings.php?userid=<?=$userid?>">Персональные настройки</a>&nbsp;&gt;&nbsp;Печатная форма</p>
		<?php
		$message = printregister_settings($connect, null, $userid);
		?>
<?php
if(!empty($message)) echo("<div class=\"message\" onclick=\"this.style.display='none';\">$message</div>");
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>