<?php include_once ("inc/util.php");
DEFINE ('EVENTSOURCE', 'viewregisterline');

if(!isset($_SESSION['userid']))
{
	header("Location: login.php");
	die();
}
elseif(!isset($_GET['lineid']))
{
	header("Location: listregisters.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'viewregisters', $connect))
{
	header("Location: index.php");
	die();
}
$lineid = $_GET['lineid'];
if(isset($_POST['restore']))
	restoreRow($lineid);
if(isset($_POST['delete']))
	deleteRow($lineid);

$showstatus = hasNamedRight($_SESSION['userid'], 'viewstatus', $connect);

$senderTitle = "Информация об <strong>отправителе</strong>";
$receiverTitle = "Информация о <strong>получателе</strong>";

if(($res = getUserRegisterRowByID($connect, $_SESSION['userid'], $lineid)) && $res->num_rows>0)
{
	$row = $res->fetch_assoc();
	$waybill = htmlspecialchars($row['waybill']);
	$receptionDate = dateForPrint($row['receptionDate']);
	$addressee = htmlspecialchars($row['addressee']);
	$address = str_replace("\n", "<br/>", htmlspecialchars($row['address']));
	$contactFIO = str_replace("\n", "<br/>", htmlspecialchars($row['contactFIO']));
	$contactPhone1 = htmlspecialchars($row['contactPhone1']);
	$contactPhone2 = htmlspecialchars($row['contactPhone2']);
	$places = $row['places'];
	$weight = number_format($row['weight'],1,',',' ');
	$deliveryDay = htmlspecialchars($row['deliveryDay']);
	$deliveryDate = dateForPrint($row['deliveryDate']);
	$timeSpanFrom = formatTimeVal($row['timeSpanFrom']);
	$timeSpanTo = formatTimeVal($row['timeSpanTo']);
	$paymentType = htmlspecialchars($row['paymentType']);
	$sum = $row['sum'];
	$note = str_replace("\n", "<br/>", htmlspecialchars($row['note']));
	$creationDate = dateTimeForPrint($row['creationDate']);
	$status = htmlspecialchars($row['status']);
	$statusID = $row['statusID'];
	$receptionRegion = htmlspecialchars($row['receptionRegion']);
	$deliveryRegion = htmlspecialchars($row['deliveryRegion']);
	$dimension1 = $row['dimension1'];
	$dimension2 = $row['dimension2'];
	$dimension3 = $row['dimension3'];
	$receptionCity = htmlspecialchars($row['receptionCity']);
	$deliveryCity = htmlspecialchars($row['deliveryCity']);
	$receptionAddress = htmlspecialchars($row['receptionAddress']);
	$receptionContactFIO = htmlspecialchars($row['receptionContactFIO']);
	$login = htmlspecialchars($_SESSION['login']);
	$companyName = htmlspecialchars($_SESSION['companyName']);
	$deleted = $row['deleted'];
	$dateClosed = $row['dateClosed'];
	$direction = $row['direction'];
	$takePapers = $row['takePapers'];
	$needNotification = $row['needNotification'];
	
}
else
{
	header("Location: listregisters.php");
	die();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('inc/metalinks.php');?>
		<title>Личный кабинет - Просмотр отправления</title>
	</head>
	<body>
		<?php include_once ("inc/header.php");?>
		<h3>Просмотр отправления</h3>
		<p><a href="listregisters.php">Список отправлений</a>&nbsp;&gt;&nbsp;Просмотр отправления</p>
		<?php echo($message);?>
		<form action="print/waybill.php" name="printform" target="_blank" action="GET">
			<input type="hidden" name="lineid" value="<?=$lineid?>"/>
		</form>
		<table>
			<thead>
			<?php
				if($deleted)
				{
					echo '<tr><th>Это отправление удалено!</th>';
					echo '<td>';
					if(!$dateClosed && $row['receptionDate']>=date("Y-m-d") && $statusID==0)
						echo "<form method=\"POST\" action=\"viewregisterline.php?lineid=$lineid\"><button type=\"submit\" name=\"restore\">Восстановить</button></form>";
					echo '</td></tr>';
				}
				elseif($statusID==0)
					echo "<form method=\"POST\" name=\"frmDelete\" action=\"viewregisterline.php?lineid=$lineid\"><input type=\"hidden\" name=\"delete\"/></form>";
			?>
				<tr>
					<th>Отправление зарегистрировано</th>
					<td class="valuefield"><?php echo $creationDate; ?></td>
				</tr>
				<?php
				if($showstatus)
					echo "<tr><th>Статус</th><td class=\"valuefield\">$status</td></tr>\r\n";
				?>
				</tr>
			</thead>
		</table>
		
		<div class="buttongroup">
			<button onclick="printform.submit();" class="print">Печать</button>
			<?php
			if($statusID==0)
			{
				echo "<button class=\"edit\" type=\"button\" onclick=\"document.location.href='editregisterline.php?lineid=$lineid';\">Редактировать</button>";
				if(!$deleted)
					echo "<button class=\"delete\" type=\"button\" onclick=\"frmDelete.submit();\">Удалить</button>";
			}
			?>
		</div>
		<div class="tableformpart">
			<table>
				<thead>
					<tr>
						<th colspan="2">Информация об <strong>отправлении</strong></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Номер заказа</td>
						<td class="valuefield"><?=$waybill?></td>
					</tr>
					<tr>
						<td>Регион заказа</td>
						<td class="valuefield"><?=$receptionRegion?></td>
					</tr>
					<tr>
						<td>Дата забора</td>
						<td class="valuefield"><?=$receptionDate?></td>
					</tr>
					<tr>
						<td>Кол-во мест</td>
						<td class="valuefield"><?=$places?></td>
					</tr>
					<tr>
						<td>Вес (кг)</td>
						<td class="valuefield"><?=$weight?></td>
					</tr>
					<tr>
						<td>Габариты (см)</td>
						<td class="valuefield"><?=$dimension1.' &#215; '.$dimension2.' &#215; '.$dimension3?></td>
					</tr>
					<?php
					if($deliveryDate!==null)
					{
						echo "
					<tr>
						<td>Дата доставки</td>
						<td class=\"valuefield\">$deliveryDate</td>
					</tr>";
					}
					if(!empty($deliveryDay))
					{
						echo "
					<tr>
						<td>Срочность</td>
						<td class=\"valuefield\">$deliveryDay</td>
					</tr>";
					}
					?>
					<tr>
						<td>Интервал доставки</td>
						<td class="valuefield"><?php echo "С $timeSpanFrom до $timeSpanTo"; ?></td>
					</tr>
					<tr>
						<td>Тип оплаты</td>
						<td class="valuefield"><?=$paymentType?></td>
					</tr>
					<tr>
						<td>Сумма с получателя</td>
						<td class="valuefield"><?=$sum?></td>
					</tr>
					<tr>
						<td>Направление</td>
						<td class="valuefield"><?=getDirectionName($direction)?></td>
					</tr>
				</tbody>
			</table>
			<table>
				<thead>
					<tr>
						<th colspan="2"><?=($direction==DIRECTION_RECEPTION) ? $senderTitle : $receiverTitle?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?=($direction==DIRECTION_RECEPTION) ? 'Компания отправитель' : 'Компания получатель'?></td>
						<td class="valuefield"><?=$addressee?></td>
					</tr>
					<tr>
						<td><?=($direction==DIRECTION_RECEPTION) ? 'Регион забора' : 'Регион доставки'?></td>
						<td class="valuefield"><?=$deliveryRegion?></td>
					</tr>
					<tr>
						<td><?=($direction==DIRECTION_RECEPTION) ? 'Город забора' : 'Город доставки'?></td>
						<td class="valuefield"><?=$deliveryCity?></td>
					</tr>
					<tr>
						<td><?=($direction==DIRECTION_RECEPTION) ? 'Адрес отправителя' : 'Адрес получателя'?></td>
						<td class="valuefield"><?=$address?></td>
					</tr>
					<tr>
						<td>Контактное лицо</td>
						<td class="valuefield"><?=$contactFIO?></td>
					</tr>
					<tr>
						<td>Контактный телефон</td>
						<td class="valuefield"><?=$contactPhone1?></td>
					</tr>
					<tr>
						<td>Дополнительный телефон</td>
						<td class="valuefield"><?=$contactPhone2?></td>
					</tr>
				</tbody>
			</table>
			<table>
				<thead>
					<tr>
						<th colspan="2"><?=($direction==DIRECTION_DELIVERY || $direction==DIRECTION_BOTH) ? $senderTitle : $receiverTitle?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?=($direction==DIRECTION_RECEPTION) ? 'Регион получателя' : 'Регион отправителя'?></td>
						<td class="valuefield"><?=$receptionRegion?></td>
					</tr>
					<tr>
						<td><?=($direction==DIRECTION_RECEPTION) ? 'Город получателя' : 'Город отправителя'?></td>
						<td class="valuefield"><?=$receptionCity?></td>
					</tr>
					<tr>
						<td><?=($direction==DIRECTION_RECEPTION) ? 'Адрес получателя' : 'Адрес отправителя'?></td>
						<td class="valuefield"><?=$receptionAddress?></td>
					</tr>
					<tr>
						<td>ФИО представителя <?=($direction==DIRECTION_RECEPTION) ? 'получателя' : 'отправителя'?></td>
						<td class="valuefield"><?=$receptionContactFIO?></td>
					</tr>
				</tbody>
			</table>
			<table width="100%">
				<thead>
					<tr>
						<th>Примечание</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="valuefield"><?=$note?></td>
					</tr>
					<?php
					if($takePapers || $needNotification)
					{
					?>
					<tr>
						<td class="valuefield">&nbsp;</td>
					</tr>
					
					<?php
					}
					if($takePapers)
					{
					?>
					<tr>
						<td class="valuefield">&#10004;&nbsp;Забрать документы</td>
					</tr>
					<?php
					}
					if($needNotification)
					{
					?>
					<tr>
						<td class="valuefield">&#10004;&nbsp;Требуется уведомление</td>
					</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		</div>
<?php
include('orderhistory.php');
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("inc/footer.php");
?>