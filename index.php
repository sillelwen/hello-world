<?php include_once ("inc/util.php");
if(!isset($_SESSION['userid']))
{
	header("Location: login.php");
	die();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('inc/metalinks.php');?>
		<title>Личный кабинет</title>
	</head>
	<body>
		<?php include_once ("inc/header.php");?>
		<h3>Личный кабинет</h3>
		<?php 
		if(hasNamedRight($_SESSION['userid'], 'admin', $connect))
		{
			echo '<a href="adminzone.php"><div class="logo admin">Раздел администрирования</div></a><hr/>';
		}
		echo '<div class="logos">';
		if(hasNamedRight($_SESSION['userid'], 'addregister', $connect))
		{
			echo '<a href="addregisterline.php"><div class="logo addregisterline">Добавить отправление</div></a>';
		}
		/*if(hasNamedRight($_SESSION['userid'], 'addregister', $connect))
		{
			echo '<a href="addregister.php"><div class="logo addregister">Добавить новый реестр</div></a>';
		}*/
		if(hasNamedRight($_SESSION['userid'], 'viewregisters', $connect))
		{
			echo '<a href="listregisters.php"><div class="logo listregisters">Зарегистрированные отправления</div></a>';
			echo '<a href="closedate.php"><div class="logo closedate">Закрыть день</div></a>';
		}
		/*echo '<a href="user_settings.php"><div class="logo user_settings">Персональные настройки</div></a>';*/
		echo '</div>';
		?>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("inc/footer.php");
?>