<?php include_once ("inc/util.php");
DEFINE ('EVENTSOURCE', 'addregisterline');
if(!isset($_SESSION['userid']))
{
	header("Location: login.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'addregister', $connect))
{
	header("Location: index.php");
	die();
}

$senderTitle = "Информация об <strong>отправителе</strong>";
$receiverTitle = "Информация о <strong>получателе</strong>";

//чтение пользовательских настроек
$deliveryterms = getUserSetting('deliveryterms', $connect, DELIVERYTERMS_CHOOSE_DATE);
$intervals = getUserSetting('intervals', $connect, INTERVALS_PRESET);

$res = getUserAddresses($connect, $_SESSION['userid']);
$readonlyAddress = ($res->num_rows > 0 && !hasNamedRight($_SESSION['userid'], 'editaddress', $connect)) ? ' readonly' : '';
$receptionAddresses=array();
if($row=$res->fetch_assoc())
{
	$receptionRegionDef = $row['region'];
	$receptionRegionNameDef = $row['regionName'];
	$receptionCityDef = $row['cityName'];
	$receptionAddressDef = $row['address'];
	do
	{
		$receptionAddresses[$row['id']] = ['region'=>$row['region'], 'regionName'=>$row['regionName'], 'cityName'=>$row['cityName'], 'address'=>$row['address']] ;
	}
	while($row=$res->fetch_assoc());
}
else
{
	$receptionRegionDef = 1;
}
mysqli_free_result($res);


if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$direction = in_array($_POST['direction'], array(DIRECTION_DELIVERY, DIRECTION_RECEPTION, DIRECTION_BOTH)) ? $_POST['direction'] : DIRECTION_DELIVERY;
	
	$waybill = $_POST['waybill'];
	$receptionDate =  $_POST['receptionDate'];
	$addressee = $_POST['addressee'];
	$address = isset($_POST['address']) ? $_POST['address'] : $receptionAddressDef;
	$receptionAddress = $_POST['receptionAddress'];
	$contactFIO = $_POST['contactFIO'];
	$receptionContactFIO = $_POST['receptionContactFIO'];
	$contactPhone1 = $_POST['contactPhone1'];
	$contactPhone2 = $_POST['contactPhone2'];
	$places = intval($_POST['places']);
	$weight = floatval($_POST['weight']);
	$dimension1 = intval($_POST['dimension1']);
	$dimension2 = intval($_POST['dimension2']);
	$dimension3 = intval($_POST['dimension3']);
	$deliveryDay = isset($_POST['deliveryDay']) ? intval($_POST['deliveryDay']) : 0;
	$deliveryDate = $_POST['deliveryDate'];
	$timeSpanFrom = isset($_POST['timeSpanFrom']) ? intval($_POST['timeSpanFrom']) : 0;
	$timeSpanTo = isset($_POST['timeSpanTo']) ? intval($_POST['timeSpanTo']) : 0;
	$receptionRegion = isset($_POST['receptionRegion']) ? intval($_POST['receptionRegion']) : $receptionRegionDef;
	$receptionRegionName = getValueName('regions', $connect, $receptionRegion);
	$deliveryRegion = isset($_POST['deliveryRegion']) ? intval($_POST['deliveryRegion']) : 1;
	$receptionCity = isset($_POST['receptionCity']) ? $_POST['receptionCity'] : $receptionCityDef;
	$deliveryCity = $_POST['deliveryCity'];
	if(isset($_POST['timeSpan']))
	{
		$res = getUsersTimeSpanByID($connect, $_SESSION['userid'], $_POST['timeSpan']);
		if($row=$res->fetch_assoc())
		{
			$timeSpanFrom = $row['timeSpanFrom'];
			$timeSpanTo = $row['timeSpanTo'];
		}
		mysqli_free_result($res);
	}
	$paymentTypeID = intval($_POST['paymentType']);
	$sum = $_POST['sum'];
	$note = $_POST['note'];
	$takePapers = boolval($_POST['takePapers']);
	$needNotification = boolval($_POST['needNotification']);

	if(isDateClosed($connect, $receptionDate))
	{
		$message = '<p class="message">Нельзя установить дату забора '.dateForPrint($receptionDate).', поскольку для этой даты уже выполнено закрытие дня. Выберите другую дату забора.</p>';
	}
	elseif($lineid = AddRegisterRow($connect, $_SESSION['userid'], $waybill, $receptionDate, $addressee, $address, $contactFIO, $contactPhone1, $contactPhone2,
										$places, $weight, $dimension1, $dimension2, $dimension3, $deliveryDay, $deliveryDate, $timeSpanFrom, $timeSpanTo,
										$paymentTypeID, $sum, $note, $receptionRegion, $receptionCity, $receptionAddress, $receptionContactFIO, $deliveryRegion, $deliveryCity,
										ADDED_SEPARATE_FORM, $direction, $takePapers, $needNotification))
	{
		logevent($connect, EVENT_TYPE_INFO, 'addregisterline', 'Added a new register line', $lineid);
		header("Location: listregisters.php");
	}
	else
		$message = '<p class="message">Произошла ошибка при сохранении данных. Проверьте введённые данные и попробуйте ещё раз. Если ошибка сохраняется, свяжитесь с нами.</p>';//."<p>$query</p>";
}
else
{
	$receptionRegion = $receptionRegionDef;
	$receptionCity = $receptionCityDef;
	$receptionAddress = $receptionAddressDef;
	$receptionRegionName = $receptionRegionNameDef;
	$direction = DIRECTION_DELIVERY;
}

$date = new DateTime(); $date->add(new DateInterval('P7D'));
$deldate = new DateTime(); $deldate->add(new DateInterval('P1D'));
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('inc/metalinks.php');?>
		<title>Личный кабинет - Добавление отправления</title>
		<script type="text/javascript">

		function isValidDate(str)
		{
			if (!/^\d{4}-\d{2}-\d{2}$/.test(str)) return false;
			var t = str.split ('-'), p = function(x) {return parseInt(x)}
			t[0] = p(t[0]), t[1] = p (t[1] ) - 1, t[2] = p(t[2]);	
			with (new Date (t[0], t[1], t[2])) if (t.join('-') !=
			[getFullYear(), getMonth(), getDate()].join('-'))
			return false; return true;
		}
		
		function checkAndSubmit()
		{
			
			if(!isValidDate(frmRegisterLine.receptionDate.value))
			{
				alert("Пожалуйста, введите дату забора в верном формате: ГГГГ-ММ-ДД!");
				frmRegisterLine.receptionDate.focus();
				return;
			}
			if(frmRegisterLine.receptionDate.value < '<?=date('Y-m-d')?>')
			{
				alert("Дата забора не может быть раньше сегодняшней.");
				frmRegisterLine.receptionDate.focus();
				return;
			}
			if(frmRegisterLine.receptionDate.value > '<?=$date->format('Y-m-d')?>')
			{
				alert("Дата забора не может быть позже <?=$date->format('Y-m-d')?>.");
				frmRegisterLine.receptionDate.focus();
				return;
			}
				
<?php
if($intervals==INTERVALS_FROM_TO)
{
?>
			if(frmRegisterLine.timeSpanFrom.value >= frmRegisterLine.timeSpanTo.value)
			{
				alert("Значение времени 'до' должно быть больше значения 'с'!");
				frmRegisterLine.timeSpanTo.focus();
				return;
			}
			
<?php
}
if($deliveryterms==DELIVERYTERMS_CHOOSE_DATE)
{
?>
			
			if(!isValidDate(frmRegisterLine.deliveryDate.value))
			{
				alert("Пожалуйста, введите дату доставки в верном формате: ГГГГ-ММ-ДД!");
				frmRegisterLine.deliveryDate.focus();
				return;
			}

			if(frmRegisterLine.receptionDate.value > frmRegisterLine.deliveryDate.value)
			{
				alert("Дата забора не может быть позже даты доставки!");
				frmRegisterLine.receptionDate.focus();
				return;
			}
			
			if(frmRegisterLine.receptionDate.value == frmRegisterLine.deliveryDate.value)
			{
				alert("Пожалуйста, установите дату доставки позже даты забора!");
				frmRegisterLine.deliveryDate.focus();
				return;
			}
<?php
}
?>
			if(frmRegisterLine.paymentType.value==0 && frmRegisterLine.sum.value!=0)
			{
				alert("При данном типе оплаты сумма должна быть равна 0!");
				frmRegisterLine.sum.focus();
				return;
			}
			else if(frmRegisterLine.paymentType.value!=0 && frmRegisterLine.sum.value==0)
			{
				alert("При данном типе оплаты сумма не должна быть равна 0!");
				frmRegisterLine.sum.focus();
				return;
			}
			frmRegisterLine.submit();			
		}
<?php
if(count($receptionAddresses) > 1)
{
?>	
		function setAddress(value)
		{
			var regionId = 0; var regionName = '';
			var cityName = ''; var address = '';
			switch(value) {
<?php
			foreach($receptionAddresses as $id=>$vals)
				echo "case '$id':\n
				regionId = ".htmlspecialchars($vals['region'], ENT_QUOTES).";\n
				regionName = '".htmlspecialchars($vals['regionName'], ENT_QUOTES)."';\n
				cityName = '".htmlspecialchars($vals['cityName'], ENT_QUOTES)."';\n
				address = '".htmlspecialchars($vals['address'], ENT_QUOTES)."';\n
				break;\n";
?>
			};
			frmRegisterLine.receptionRegion.value = regionId;
			<?php
			if(!empty($readonlyAddress))
				echo 'frmRegisterLine.receptionRegionName.value=regionName;';
			?>
			frmRegisterLine.receptionCity.value = cityName;
			frmRegisterLine.receptionAddress.value = address;
		}
<?php
}
?>

		function swapDirection()
		{
			var sSenderTitle = "<?=$senderTitle?>";
			var sReceiverTitle = "<?=$receiverTitle?>";
			
			var receptionDateMin = "<?=date('Y-m-d'); ?>";
			var receptionDateMax="<?=$date->format('Y-m-d')?>";
			
			if(document.getElementById("direction1").checked || document.getElementById("direction3").checked)
			{
				document.getElementById("clientinfo").innerHTML = sSenderTitle;
				
				document.getElementById("targetinfo").innerHTML = sReceiverTitle;
				
				document.getElementById("timeSpanlabel").innerText = "Интервал доставки";
				document.getElementById("clientDatelabel").innerHTML = "Дата забора <star>*</star>";
				document.getElementById("clientDate").name = "receptionDate";
				document.getElementById("clientinfo").min = receptionDateMin;
				document.getElementById("clientinfo").max = receptionDateMax;
			<?php
			if($deliveryterms==DELIVERYTERMS_CHOOSE_DATE)
			{
			?>
				document.getElementById("targetDatelabel").innerHTML = "Дата доставки <star>*</star>";
				document.getElementById("targetDate").name = "deliveryDate";
			<?php
			}
			?>
				document.getElementById("receptionRegionlabel").innerText = "Регион отправителя";
				document.getElementById("receptionCitylabel").innerText = "Город отправителя";
				document.getElementById("receptionAddresslabel").innerText = "Адрес отправителя";
				document.getElementById("receptionContactFIOlabel").innerText = "ФИО представителя отправителя";
				
				document.getElementById("contactFIOlabel").innerText = "ФИО получателя";
				document.getElementById("addresseelabel").innerText = "Компания получатель";
				document.getElementById("deliveryRegionlabel").innerText = "Регион доставки";
				document.getElementById("deliveryCitylabel").innerText = "Город доставки";
				document.getElementById("addresslabel").innerText = "Адрес получателя";
			}
			else if(document.getElementById("direction2").checked)
			{
				document.getElementById("clientinfo").innerHTML = sReceiverTitle;
				document.getElementById("targetinfo").innerHTML = sSenderTitle;
				
				document.getElementById("timeSpanlabel").innerText = "Интервал забора";
				document.getElementById("clientDatelabel").innerHTML = "Дата доставки <star>*</star>";
				document.getElementById("clientDate").name = "deliveryDate";
			<?php
			if($deliveryterms==DELIVERYTERMS_CHOOSE_DATE)
			{
			?>
				document.getElementById("targetDatelabel").innerHTML = "Дата забора <star>*</star>";
				document.getElementById("targetDate").name = "receptionDate";
				document.getElementById("targetDate").min = receptionDateMin;
				document.getElementById("targetDate").max = receptionDateMax;
			<?php
			}
			?>
			
				document.getElementById("receptionRegionlabel").innerText = "Регион получателя";
				document.getElementById("receptionCitylabel").innerText = "Город получателя";
				document.getElementById("receptionAddresslabel").innerText = "Адрес получателя";
				document.getElementById("receptionContactFIOlabel").innerText = "ФИО представителя получателя";
				
				document.getElementById("contactFIOlabel").innerText = "ФИО отправителя";
				document.getElementById("addresseelabel").innerText = "Компания отправитель";
				document.getElementById("deliveryRegionlabel").innerText = "Регион забора";
				document.getElementById("deliveryCitylabel").innerText = "Город забора";
				document.getElementById("addresslabel").innerText = "Адрес отправителя";
			}
		}
		</script>
	</head>
	<body onload="swapDirection()">
		<?php include_once ("inc/header.php");?>
		<h3>Добавление отправления</h3>
		<p><a href="listregisters.php">Отправления</a>&nbsp;&gt;&nbsp;Добавление отправления</p>
		<?php echo($message);?>
		<p>Для регистрации отправления заполните следующие поля<br/>(обязательные поля отмечены звёздочкой):</p>
		<form method="POST" onsubmit="checkAndSubmit();return false;" name="frmRegisterLine">
			<div class="tableformpart" id="info">
				<table>
					<thead>
						<tr>
							<th colspan="2">Информация об <strong>отправлении</strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Номер заказа <star>*</star></td>
							<td><input name="waybill" type="text" maxlength="30" required <?php if(isset($waybill)) echo 'value="'.htmlspecialchars($waybill).'"'; ?> autofocus /></td>
						</tr>
						<tr>
							<td id="clientDatelabel">Дата забора <star>*</star></td>
							<td>
							
							<input id="clientDate" name="receptionDate" type="date" placeholder="ГГГГ-ММ-ДД" min="<?=date('Y-m-d'); ?>" max="<?=$date->format('Y-m-d')?>" <?php if(isset($receptionDate)) echo "value=\"$receptionDate\""; ?> required />
								
							</td>
						</tr>
						<tr>
							<td>Кол-во мест <star>*</star></td>
							<td><input name="places" type="number" min="1" max="50" <?php if(isset($places)) echo "value=\"$places\""; ?> required /></td>
						</tr>
						<tr>
							<td>Вес (кг) <star>*</star></td>
							<td><input name="weight" type="number" min="0" max="200" step="0.1" required <?php if(isset($weight)) echo "value=\"$weight\""; ?>/></td>
						</tr>
						<tr>
							<td>Габариты (см)</td>
							<td><input type="number" min="0" max="250" name="dimension1" class="short"/>&#215;<input type="number" min="0" max="250" name="dimension2" class="short"/>&#215;<input type="number" min="0" max="250" name="dimension3" class="short"/></td>
						</tr>
						<tr>
<?php
	if($deliveryterms==DELIVERYTERMS_STANDART)
	{
?>
							<td>Срочность <star>*</star></td>
							<td>
								<select name="deliveryDay" required>
		<?php
									echo isset($deliveryDay) ? listOptions("deliveryterms", $connect, $deliveryDay) :  listOptions("deliveryterms", $connect);
		?>
								</select>
							</td>
<?php
	}
	elseif($deliveryterms==DELIVERYTERMS_CHOOSE_DATE)
	{
?>
							<td id="targetDatelabel">Дата доставки <star>*</star></td>
							<td>
								<input id="targetDate" type="date" name="deliveryDate" placeholder="ГГГГ-ММ-ДД" <?php echo 'min="'.$deldate->format('Y-m-d').'" '; if(isset($deliveryDate)) echo "value=\"$deliveryDate\""; ?> required />
<?php
	}
?>
						</tr>
						<tr>
							<td id="timeSpanlabel">Интервал доставки</td>
							<td>
		<?php
							if($intervals==INTERVALS_PRESET)
							{
								$res = getDeliveryIntervalsForUser($connect, $_SESSION['userid']);
								if($res->num_rows==0)
									$intervals = INTERVALS_FROM_TO;
								else
								{
									echo '<p><select name="timeSpan">';
									while($row = $res->fetch_assoc())
									{
										$from = $row['timeSpanFrom']; $to = $row['timeSpanTo'];
										echo '<option value="'.$row[id].'">С '.intdiv($from,2).':'.str_pad((($from%2)*30),2,0).' по '.intdiv($to,2).':'.str_pad((($to%2)*30),2,0).'</option>';
									}
									echo '</select></p>';
								}
							}
							if($intervals==INTERVALS_FROM_TO)
								echo '<p>C '.buildTimeList("timeSpanFrom", $timeSpanFrom, 'from').' до '.buildTimeList("timeSpanTo", $timeSpanTo, 'to').'</p>';
		?>
							</td>
						</tr>
						<tr>
							<td>Тип оплаты <star>*</star></td>
							<td>
								<select name="paymentType" required onchange="if(this.value==0 && frmRegisterLine.sum.value=='') frmRegisterLine.sum.value = 0;" >
		<?php								
									echo isset($paymentTypeID) ? listPaymentTypes($connect, $paymentTypeID) :  listPaymentTypes($connect, 1);
		?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Сумма с получателя <star>*</star></td>
							<td><input name="sum" type="number" min="0" max="10000000" step="0.01" value="<?=$sum?>" required /></td>
						</tr>
						<tr>
							<td>Направление</td>
							<td>
								<input type="radio" name="direction" id="direction1" onchange="swapDirection();" value="<?=DIRECTION_DELIVERY?>"<?=$direction==DIRECTION_DELIVERY ? " checked" : ""?>/><label for="direction1"> <?=getDirectionName(DIRECTION_DELIVERY)?></label><br/>
								<input type="radio" name="direction" id="direction2" onchange="swapDirection();" value="<?=DIRECTION_RECEPTION?>"<?=$direction==DIRECTION_RECEPTION ? " checked" : ""?>/><label for="direction2"> <?=getDirectionName(DIRECTION_RECEPTION)?></label><br/>
								<input type="radio" name="direction" id="direction3" onchange="swapDirection();" value="<?=DIRECTION_BOTH?>"<?=$direction==DIRECTION_BOTH ? " checked" :""?>/><label for="direction3"> <?=getDirectionName(DIRECTION_BOTH)?></label>
							</td>
						</tr>
					</tbody>
				</table>
				<table>
					<thead>
						<tr>
							<th colspan="2" id="targetinfo">Информация о <strong>получателе</strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td id="contactFIOlabel">ФИО получателя</td>
							<td><textarea name="contactFIO" rows="2" maxlength="50"><?php if(isset($contactFIO)) echo htmlspecialchars($contactFIO); ?></textarea></td>
						</tr>
						<tr>
							<td id="addresseelabel">Компания получатель</td>
							<td><input name="addressee" type="text" maxlength="30" <?php if(isset($addressee)) echo 'value="'.htmlspecialchars($addressee).'"'; ?>/></td>
						</tr>
						<tr>
							<td id="deliveryRegionlabel">Регион доставки</td>
							<td><select name="deliveryRegion"><?=listRegions($connect, false, true, $deliveryRegion)?></select></td>
						</tr>
						<tr>
							<td id="deliveryCitylabel">Город доставки</td>
							<td><input name="deliveryCity" type="text" maxlength="30" <?php if(isset($deliveryCity)) echo 'value="'.htmlspecialchars($deliveryCity).'"'; ?>/></td>
						</tr>
						<tr>
							<td id="addresslabel">Адрес получателя <star>*</star></td>
							<td><textarea name="address" rows="3" maxlength="150" required><?php if(isset($address)) echo htmlspecialchars($address); ?></textarea></td>
						</tr>
						<tr>
							<td>Контактный телефон <star>*</star></td>
							<td><input name="contactPhone1" type="tel" maxlength="30" required <?php if(isset($contactPhone1)) echo 'value="'.htmlspecialchars($contactPhone1).'"'; ?>/></td>
						</tr>
						<tr>
							<td>Дополнительный телефон</td>
							<td><input name="contactPhone2" type="tel" maxlength="30" <?php if(isset($contactPhone2)) echo 'value="'.htmlspecialchars($contactPhone2).'"'; ?>/></td>
						</tr>
					</tbody>
				</table>
				<table>
					<thead>
						<tr>
							<th colspan="2" id="clientinfo">Информация об <strong>отправителе</strong></th>
						</tr>
					</thead>
					<tbody>
					<?php
						if(count($receptionAddresses)>1)
						{
							echo '<tr><td colspan="2" class="nocenter">Адрес <select onchange="setAddress(this.value)" style="max-width: 350px;">';
							foreach($receptionAddresses as $id=>$vals)
								echo "<option value=\"$id\">".htmlspecialchars($vals['regionName']).', '.htmlspecialchars($vals['cityName']).', '.htmlspecialchars($vals['address']).'</option>';
							echo '</select></td></tr>';
						}
					?>
						<tr>
							<td id="receptionRegionlabel">Регион отправителя</td>
							<td>
							<?php
							if(empty($readonlyAddress))
								echo '<select name="receptionRegion">'.listRegions($connect, true, false, $receptionRegion).'</select>';
							else
							{
								echo '<input type="hidden" name="receptionRegion" value="'.$receptionRegion.'"/>';
								echo '<input type="text" name="receptionRegionName" value="'.htmlspecialchars($receptionRegionName).'" readonly />';
							}
							?>
							</td>
						</tr>
						<tr>
							<td id="receptionCitylabel">Город отправителя</td>
							<td><input type="text" name="receptionCity" value="<?=htmlspecialchars($receptionCity)?>"<?=$readonlyAddress?>/></td>
						</tr>
						<tr>
							<td id="receptionAddresslabel">Адрес отправителя</td>
							<td><textarea name="receptionAddress" rows="3" maxlength="150"<?=$readonlyAddress?>><?=htmlspecialchars($receptionAddress)?></textarea></td>
						</tr>
						<tr>
							<td id="receptionContactFIOlabel">ФИО представителя отправителя</td>
							<td><textarea name="receptionContactFIO" rows="2" maxlength="50"><?=htmlspecialchars($receptionContactFIO)?></textarea></td>
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
							<td><textarea name="note" rows="5" maxlength="150"><?=htmlspecialchars($note)?></textarea></td>
						</tr>
						<tr>
							<td><input type="checkbox" name="takePapers" id="takePapers" value="1"<?=$takePapers?" checked":""?>/><label for="takePapers"> Забрать документы</label></td>
						</tr>
						<tr>
							<td><input type="checkbox" name="needNotification" id="needNotification" value="1"<?=$needNotification?" checked":""?>/><label for="needNotification"> Требуется уведомление</label></td>
						</tr>
					</tbody>
				</table>
			</div>
			<p><input type="submit" value="Отправить"/></p>
		</form>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("inc/footer.php");
?>