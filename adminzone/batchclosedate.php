<?php include_once ("../inc/util.php");
DEFINE ('EVENTSOURCE', 'batchclosedate');
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
elseif(!hasNamedRight($_SESSION['userid'], 'viewregisters', $connect))
{
	header("Location: ../index.php");
	die();
}
$dateClosed = false;
if(isset($_POST['closeDate']) && is_date($_POST['receptionDate']))
{
	$receptionDate = $_POST['receptionDate'];
	if(BatchCloseDate($receptionDate))
	{
		$message .= "<p class=\"message\">День закрыт: ".dateForPrint($receptionDate).".<br/>Пользователи, добавившие отправления на эту дату, больше не могут их добавлять и редактировать.</p>";
		$dateClosed=true;
	}
	else
		$message .= "<p class=\"message\">Произошла ошибка при закрытии дня: ".dateForPrint($receptionDate)."</p>";
}
else
	$receptionDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Личный кабинет - Администрирование - Закрытие дня</title>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Закрытие дня для всех</h3>
		<?=$message?>
<?php
if($dateClosed)
{
	echo "<p>ВСЁ =)</p>";
}
else
{
?>
		<p>Вы перешли на страницу закрытия дня для всех отправлений с заданной датой забора ДЛЯ ВСЕХ ПОЛЬЗОВАТЕЛЕЙ.</p>
		<p>После того, как вы нажмёте на кнопку "Закрыть день", изменить созданные отправления или добавить новые с той же датой забора будет нельзя.</p>
		<p>Менеджерам курьерской службы станут видны все данные о заказах, предназначенных к забору на выбранную дату.</p>
		<form method="POST" name="frmCloseDate" class="gridform filterform">
			<div class="valueslist">
				<h4>Дата забора:</h4>
				<input type="date" name="receptionDate" value="<?=$receptionDate?>" class="closedate span2"/>
				<!--input type="checkbox" class="closedate" name="chkPrintWaybills" value="1" id="chkPrintWaybills"/><label for="chkPrintWaybills">Печать накладных</label>
				<input type="checkbox" class="closedate" name="chkPrintRegister" value="1" id="chkPrintRegister"/><label for="chkPrintRegister">Печать реестра</label-->
			<input type="submit" name="closeDate" value="Закрыть день"/>		
			</div>
		</form>
<?php
}
?>
		<p><a href="commonregister.php">Перейти к списку отправлений &rarr;</a></p>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>