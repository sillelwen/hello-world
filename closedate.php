<?php include_once ("inc/util.php");
DEFINE ('EVENTSOURCE', 'closedate');
if(!isset($_SESSION['userid']))
{
	header("Location: login.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'viewregisters', $connect))
{
	header("Location: index.php");
	die();
}
$dateClosed = false;
if(isset($_POST['closeDate']) && is_date($_POST['receptionDate']))
{
	$receptionDate = $_POST['receptionDate'];
	if(isDateClosed($connect, $receptionDate))
	{
		$message .= "<p class=\"message\">Невозможно закрыть день ".dateForPrint($receptionDate).".<br/>День уже был закрыт ранее.</p>";
		$dateClosed=true;
	}
	elseif(closeDate($connect, $receptionDate))
	{
		$message .= "<p class=\"message\">День закрыт: ".dateForPrint($receptionDate).".<br/>Вы больше не можете редактировать и добавлять отправления за эту дату.</p>";
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
		<?php include_once('inc/metalinks.php');?>
		<title>Личный кабинет - Закрытие дня</title>
		<script type="text/javascript">
		function groupPrint(option)
		{
			switch(option)
			{
				case 1:
					printform.action = "print/printregister.php";
					break;
				case 2:
					printform.action = "print/waybill.php";
					break;
			}
			printform.submit();
		}
		</script>
	</head>
	<body>
		<?php include_once ("inc/header.php");?>
		<h3>Закрытие дня</h3>
		<?=$message?>
<?php
if($dateClosed)
{
	$IDs = getUserOrderIDsByReceptionDate($connect, $receptionDate);
	if(count($IDs)>0)
	{
?>
		<form action="print/printregister.php" method="POST" target="_blank" name="printform">
			<button onclick="groupPrint(2);">Печать накладных</button>
			<button onclick="groupPrint(1);">Печать реестра</button>
			<input type="hidden" name="receptionDate" value="<?=$receptionDate?>"/>
			<?php
			foreach($IDs as $id)
				echo '<input type="hidden" name="chkRow['.$id.']" value="1"/>';
			?>
		</form>
<?php
	}
	else
	{
		echo '<p>Печать реестра и накладных недоступна, поскольку на эту дату не зарегистрировано ни одного отправления.</p>';
	}
	echo '<p><a href="closedate.php">Закрыть другой день</a></p>';
}
else
{
?>
		<p>Вы перешли на страницу закрытия дня для всех отправлений с заданной датой забора.</p>
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
		<p><a href="listregisters.php">Перейти к списку отправлений &rarr;</a></p>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("inc/footer.php");
?>