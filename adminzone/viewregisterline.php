<?php include_once ("../inc/util.php");
DEFINE ('EVENTSOURCE', 'adminzone/viewregisterline');

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

$maychangestatus = hasNamedRight($_SESSION['userid'], 'setstatus', $connect);

if (isset($_GET['lineid']))
{
	$message = '';
    $lineid = $_GET['lineid'];
}
else
{
	header("Location: userlist.php");
	die();
}

if($res = getRegisterRowByID($connect, $_GET['lineid']))
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
	$sum = number_format($row['sum'],2,',',' ');
	$note = str_replace("\n", "<br/>", htmlspecialchars($row['note']));
	$creationDate = dateTimeForPrint($row['creationDate']);
	$status = htmlspecialchars($row['status']);
	if(isset($_POST['status']))
	{
		if(!$maychangestatus)
			$message = '<p class="message">У вас нет права изменять статус.</p>';
		elseif($_POST['status'] == $_POST['currentstatus'])
		{
			$message = '<p class="message">Статус не изменён.</p>';
		}
		elseif(updateRegisterRowStatus($connect, $lineid, $_POST['status']))
		{
			$message = '<p class="message">Статус обновлён</p>';
		}
		else
			$message = '<p class="message">Ошибка при обновлении статуса!</p>';
	}
	$statusid = $row['statusID'];
	$userid = $row['userid'];
	$login = htmlspecialchars($row['login']);
	$companyName = htmlspecialchars($row['companyName']);
	$receptionRegion = htmlspecialchars($row['receptionRegion']);
	$deliveryRegion = htmlspecialchars($row['deliveryRegion']);
	$dimension1 = $row['dimension1'];
	$dimension2 = $row['dimension2'];
	$dimension3 = $row['dimension3'];
	$receptionCity = htmlspecialchars($row['receptionCity']);
	$deliveryCity = htmlspecialchars($row['deliveryCity']);
	$receptionAddress = htmlspecialchars($row['receptionAddress']);
	$receptionContactFIO = htmlspecialchars($row['receptionContactFIO']);
	$deleted = $row['deleted'];
}
else
{
	$message .= "<p class=\"message\">Произошла ошибка при попытке получения данных отправления.</p>";
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Администрирование - Просмотр отправления</title>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Просмотр отправления</h3>
		<p><a href="../adminzone.php">Администрирование</a> &gt;&nbsp;<a href="userlist.php">Пользователи</a> &gt;&nbsp;<a href="userregisters.php?userid=<?php echo $userid ?>"><?php echo $login;?></a> &gt;&nbsp;Просмотр отправления</p>
		<?php echo($message);?>
		<table>
			<thead>
			<?php
				if($deleted)
				{
					echo '<tr><th>Это отправление удалено!</th>';
					echo '<td></td></tr>';
				}
			?>
				<tr>
					<th>Отправление зарегистрировано</th>
					<td class="valuefield"><?php echo $creationDate; ?></td>
				</tr>
				<tr>
					<th>Статус</th>
					<?php
					if($maychangestatus)
						echo "<td class=\"valuefield\"><span id=\"statusval\" onclick=\"document.getElementById('statusform').style.display='block';document.getElementById('statusval').style.display='none';\">$status</span><form method=\"POST\" action=\"/adminzone/viewregisterline.php?lineid=$lineid\" id=\"statusform\"><input type=\"hidden\" name=\"currentstatus\" value=\"$statusid\"/><select name=\"status\">".listOptions('orderstatuses', $connect, $statusid)."</select><input type=\"submit\" value=\"&raquo;\" class=\"search\" title=\"Обновить статус\"/></form></td>\r\n";
					else
						echo "<td class=\"valuefield\">$status</td>\r\n";
					?>
				</tr>
			</thead>
		</table>
		<button class="edit" type="button" onclick="document.location.href='editregisterline.php?lineid=<?=$lineid?>';">Редактировать</button>
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
						<td class="valuefield"><?php echo $waybill; ?></td>
					</tr>
					<tr>
						<td>Регион заказа</td>
						<td class="valuefield"><?=$receptionRegion?></td>
					</tr>
					<tr>
						<td>Дата забора</td>
						<td class="valuefield"><?php echo $receptionDate; ?></td>
					</tr>
					<tr>
						<td>Кол-во мест</td>
						<td class="valuefield"><?php echo $places; ?></td>
					</tr>
					<tr>
						<td>Вес (кг)</td>
						<td class="valuefield"><?php echo $weight; ?></td>
					</tr>
					<tr>
						<td>Габариты (см)</td>
						<td class="valuefield"><?=$dimension1.' &#215; '.$dimension2.' &#215; '.$dimension3?></td>
					</tr>
					<?php
					if(!empty($deliveryDate))
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
						<td>Время</td>
						<td class="valuefield"><?php echo "С $timeSpanFrom до $timeSpanTo"; ?></td>
					</tr>
					<tr>
						<td>Тип оплаты</td>
						<td class="valuefield"><?php echo $paymentType; ?></td>
					</tr>
					<tr>
						<td>Сумма с получателя</td>
						<td class="valuefield"><?php echo $sum; ?></td>
					</tr>
				</tbody>
			</table>
			<table>
				<thead>
					<tr>
						<th colspan="2">Информация о <strong>получателе</strong></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Компания получатель</td>
						<td class="valuefield"><?php echo $addressee; ?></td>
					</tr>
					<tr>
						<td>Регион доставки</td>
						<td class="valuefield"><?=$deliveryRegion?></td>
					</tr>
					<tr>
						<td>Город доставки</td>
						<td class="valuefield"><?=$deliveryCity?></td>
					</tr>
					<tr>
						<td>Адрес получателя</td>
						<td class="valuefield"><?php echo $address; ?></td>
					</tr>
					<tr>
						<td>Контактное лицо</td>
						<td class="valuefield"><?php echo $contactFIO; ?></td>
					</tr>
					<tr>
						<td>Контактный телефон</td>
						<td class="valuefield"><?php echo $contactPhone1; ?></td>
					</tr>
					<tr>
						<td>Дополнительный телефон</td>
						<td class="valuefield"><?php echo $contactPhone2; ?></td>
					</tr>
				</tbody>
			</table>
			<table>
				<thead>
					<tr>
						<th colspan="2">Информация об <strong>отправителе</strong></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Регион отправителя</td>
						<td class="valuefield"><?=$receptionRegion?></td>
					</tr>
					<tr>
						<td>Город отправителя</td>
						<td class="valuefield"><?=$receptionCity?></td>
					</tr>
					<tr>
						<td>Адрес отправителя</td>
						<td class="valuefield"><?=$receptionAddress?></td>
					</tr>
					<tr>
						<td>ФИО представителя отправителя</td>
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
						<td class="valuefield"><?php echo $note; ?></td>
					</tr>
				</tbody>
			</table>
		
		</div>
<?php
include('../orderhistory.php');
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>