<?php include_once ("inc/util.php");
if(!isset($_SESSION['userid']))
{
	header("Location: login.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'admin', $connect))
{
	header("Location: login.php");
	die();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('inc/metalinks.php');?>
		<title>Администрирование</title>
	</head>
	<body>
		<?php include_once ("inc/header.php");?>
		<h3>Администрирование</h3>
		<div class="logos">
			<a href="adminzone/userlist.php">
			<div class="users logo">
				Пользователи
			</div>
			</a>
			<a href="adminzone/commonregister.php">
			<div class="logo listregisters2">
				Реестр отправлений
			</div>
			</a>
		</div>
		<div class="logos">
			<?php
			if(hasNamedRight($_SESSION['userid'], 'changesystemsettings', $connect))
			{
			?>
			<a href="adminzone/settingslist.php">
			<div class="settings logo">
				Системные настройки
			</div>
			</a>
			<a href="adminzone/directories.php">
			<div class="directories logo">
				Справочники
			</div>
			<?php
			}
			if(hasNamedRight($_SESSION['userid'], 'viewlog', $connect))
			{
			?>
			<a href="adminzone/viewlog.php">
			<div class="log logo">
				Журнал событий
			</div>
			</a>
			<?php
			}
			?>
			</a>			
		</div>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("inc/footer.php");
?>