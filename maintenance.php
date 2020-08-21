<?php 
if(!file_exists('maintenance'))
{
	session_start();
	$refferer  = !empty($_SESSION['last_refferer']) ? $_SESSION['last_refferer'] : "index.php";
	header("Location: $refferer");
	die();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('inc/metalinks.php');?>
		<title>Личный кабинет - Технические работы</title>
	</head>
	<body>
		<?php include_once ("inc/header.php");?>
		<h3>Технические работы</h3>
		<p><img src="style/i/under_construction.png" alt="Технические работы"/></p>
		<p><strong>Уважаемые пользователи!</strong></p>
		<p>В данный момент на сайте производятся работы по техническому обслуживанию.</p>
		<p>Приносим свои извинения за временные неудобства.</p>
		<p>Мы вернёмся ещё удобнее и надёжнее, чем раньше!</p>
<?=$refferer?>
<?php
//phpinfo(32);
 include_once ("inc/footer.php");
?>