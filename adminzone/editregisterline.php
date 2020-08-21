<?php include_once ("../inc/util.php");
if(!isset($_SESSION['userid']))
{
	header("Location: ../login.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'editregister', $connect))
{
	header("Location: ../adminzone.php");
	die();
}
elseif(!isset($_GET['lineid']))
{
	header("Location: ../adminzone.php");
	die();
}

$rowid = $_GET['lineid'];
if($res = getRegisterRowByID($connect, $rowid))
{
	$row=$res->fetch_assoc();
	$userid = $row['userid'];
	$login = $row['login'];
	$status = $row['statusID'];
}
else
{
	header("Location: ../adminzone.php");
	die();
}
$editable = getEditableFields($connect, $status);

if(isset($_POST['updateFields']))
{
	//формируем набор полей для обновления и запись лога
	$set = array();
	$changes = array();
	if(in_array('waybill',$editable) && !empty($_POST['waybill']))
		if($_POST['waybill']!=$row['waybill'])
		{
			$set[] = "waybill='".prepStr($_POST['waybill'])."'";
			$changes['waybill'] = ['name'=>'Номер счёта', 'newvalue'=>$_POST['waybill'], 'oldvalue'=>$row['waybill']];
			$row['waybill'] = $_POST['waybill'];
		}
		
	if(in_array('receptionDate',$editable) && !empty($_POST['receptionDate']))
		if($_POST['receptionDate']!=$row['receptionDate'])
		{
			$set[] = "receptionDate='".prepDate($_POST['receptionDate'])."'";
			$changes['receptionDate'] = ['name'=>'Дата забора', 'newvalue'=>dateForPrint($_POST['receptionDate']), 'oldvalue'=>dateForPrint($row['receptionDate'])];
			$row['receptionDate'] = $_POST['receptionDate'];
		}
		
	if(in_array('places',$editable) && !empty($_POST['places']))
		if($_POST['places']!=$row['places'])
		{
			$set[] = "places=".intval($_POST['places']);
			$changes['places'] = ['name'=>'Кол-во мест', 'newvalue'=>$_POST['places'], 'oldvalue'=>$row['places']];
			$row['places'] = $_POST['places'];
		}
		
	if(in_array('weight',$editable) && !empty($_POST['weight']))
		if($_POST['weight']!=$row['weight'])
		{
			$set[] = "weight=".prepNum($_POST['weight']);
			$changes['weight'] = ['name'=>'Вес', 'newvalue'=>$_POST['weight'], 'oldvalue'=>$row['weight']];
			$row['weight'] = $_POST['weight'];
		}
		
	if(in_array('dimensions',$editable) && isset($_POST['dimension1']))
		if($_POST['dimension1']!=$row['dimension1'])
		{
			$set[] = "dimension1=".intval($_POST['dimension1']);
			$changes['dimension1'] = ['name'=>'Габарит 1', 'newvalue'=>$_POST['dimension1'], 'oldvalue'=>$row['dimension1']];
			$row['dimension1'] = $_POST['dimension1'];
		}
	if(in_array('dimensions',$editable) && isset($_POST['dimension2']))
		if($_POST['dimension2']!=$row['dimension2'])
		{
			$set[] = "dimension2=".intval($_POST['dimension2']);
			$changes['dimension2'] = ['name'=>'Габарит 2', 'newvalue'=>$_POST['dimension2'], 'oldvalue'=>$row['dimension2']];
			$row['dimension2'] = $_POST['dimension2'];
		}
	if(in_array('dimensions',$editable) && isset($_POST['dimension3']))
		if($_POST['dimension3']!=$row['dimension3'])
		{
			$set[] = "dimension3=".intval($_POST['dimension3']);
			$changes['dimension3'] = ['name'=>'Габарит 3', 'newvalue'=>$_POST['dimension3'], 'oldvalue'=>$row['dimension3']];
			$row['dimension3'] = $_POST['dimension3'];
		}
		
	if(in_array('deliveryDate',$editable) && !empty($_POST['deliveryDay']))
		if($_POST['deliveryDay']!=$row['deliveryDayID'])
		{
			$set[] = "deliveryDay=".intval($_POST['deliveryDay']);
			$valueName = getValueName('deliveryterms', $connect, $_POST['deliveryDay']);
			$changes['deliveryDay'] = ['name'=>'Срочность', 'newvalue'=>$valueName, 'oldvalue'=>$row['deliveryDay']];
			$row['deliveryDayID'] = $_POST['deliveryDay'];
			$row['deliveryDay'] = $valueName;
		}
	if(in_array('deliveryDate',$editable) && !empty($_POST['deliveryDate']))
		if($_POST['deliveryDate']!=$row['deliveryDate'])
		{
			$set[] = "deliveryDate='".prepDate($_POST['deliveryDate'])."'";
			$changes['deliveryDate'] = ['name'=>'Дата доставки', 'newvalue'=>dateForPrint($_POST['deliveryDate']), 'oldvalue'=>dateForPrint($row['deliveryDate'])];
			$row['deliveryDate'] = $_POST['deliveryDate'];
		}
		
	if(in_array('timeSpan',$editable) && isset($_POST['timeSpanFrom']))
		if($_POST['timeSpanFrom']!=$row['timeSpanFrom'])
		{
			$set[] = "timeSpanFrom=".intval($_POST['timeSpanFrom']);
			$changes['timeSpanFrom'] = ['name'=>'Начало интервала доставки', 'newvalue'=>formatTimeVal($_POST['timeSpanFrom']), 'oldvalue'=>formatTimeVal($row['timeSpanFrom'])];
			$row['timeSpanFrom'] = $_POST['timeSpanFrom'];
		}
	if(in_array('timeSpan',$editable) && isset($_POST['timeSpanTo']))
		if($_POST['timeSpanTo']!=$row['timeSpanTo'])
		{
			$set[] = "timeSpanTo=".intval($_POST['timeSpanTo']);
			$changes['timeSpanTo'] = ['name'=>'Конец интервала доставки', 'newvalue'=>formatTimeVal($_POST['timeSpanTo']), 'oldvalue'=>formatTimeVal($row['timeSpanTo'])];
			$row['timeSpanTo'] = $_POST['timeSpanTo'];
		}
	
	if(in_array('paymentTypeID',$editable) && isset($_POST['paymentType']))
		if($_POST['paymentType']!=$row['paymentTypeID'])
		{
			$set[] = "paymentTypeID=".intval($_POST['paymentType']);
			$changes['paymentType'] = ['name'=>'Тип оплаты', 'newvalue'=>getValueName('paymenttypes', $connect, $_POST['paymentType']), 'oldvalue'=>$row['paymentType']];
			$row['paymentTypeID'] = $_POST['paymentType'];
		}
		
	if(in_array('sum',$editable) && isset($_POST['sum']))
		if($_POST['sum']!=$row['sum'])
		{
			$set[] = "sum=".prepNum($_POST['sum']);
			$changes['sum'] = ['name'=>'Сумма', 'newvalue'=>$_POST['sum'], 'oldvalue'=>$row['sum']];
			$row['sum'] = $_POST['sum'];
		}
	
	if(in_array('contactFIO',$editable) && isset($_POST['contactFIO']))
		if($_POST['contactFIO']!=$row['contactFIO'])
		{
			$set[] = "contactFIO='".prepStr($_POST['contactFIO'])."'";
			$changes['contactFIO'] = ['name'=>'ФИО получателя', 'newvalue'=>$_POST['contactFIO'], 'oldvalue'=>$row['contactFIO']];
			$row['contactFIO'] = $_POST['contactFIO'];
		}

	if(in_array('addressee',$editable) && isset($_POST['addressee']))
		if($_POST['addressee']!=$row['addressee'])
		{
			$set[] = "addressee='".prepStr($_POST['addressee'])."'";
			$changes['addressee'] = ['name'=>'Компания получатель', 'newvalue'=>$_POST['addressee'], 'oldvalue'=>$row['addressee']];
			$row['addressee'] = $_POST['addressee'];
		}
		
	if(in_array('deliveryRegion',$editable) && isset($_POST['deliveryRegion']))
		if($_POST['deliveryRegion']!=$row['deliveryRegionID'])
		{
			$set[] = "deliveryRegion=".intval($_POST['deliveryRegion']);
			$valueName = getValueName('regions', $connect, $_POST['deliveryRegion']);
			$changes['deliveryRegion'] = ['name'=>'Регион доставки', 'newvalue'=>$valueName, 'oldvalue'=>$row['deliveryRegion']];
			$row['deliveryRegionID'] = $_POST['deliveryRegion'];
			$row['deliveryRegion'] = $valueName;
		}
		
	if(in_array('deliveryCity',$editable) && isset($_POST['deliveryCity']))
		if($_POST['deliveryCity']!=$row['deliveryCity'])
		{
			$set[] = "deliveryCity='".prepStr($_POST['deliveryCity'])."'";
			$changes['deliveryCity'] = ['name'=>'Город доставки', 'newvalue'=>$_POST['deliveryCity'], 'oldvalue'=>$row['deliveryCity']];
			$row['deliveryCity'] = $_POST['deliveryCity'];
		}

	if(in_array('address',$editable) && !empty($_POST['address']))
		if($_POST['address']!=$row['address'])
		{
			$set[] = "address='".prepStr($_POST['address'])."'";
			$changes['address'] = ['name'=>'Адрес доставки', 'newvalue'=>$_POST['address'], 'oldvalue'=>$row['address']];
			$row['address'] = $_POST['address'];
		}

	if(in_array('contactPhones',$editable) && !empty($_POST['contactPhone1']))
		if($_POST['contactPhone1']!=$row['contactPhone1'])
		{
			$set[] = "contactPhone1='".prepStr($_POST['contactPhone1'])."'";
			$changes['contactPhone1'] = ['name'=>'Контактный телефон', 'newvalue'=>$_POST['contactPhone1'], 'oldvalue'=>$row['contactPhone1']];
			$row['contactPhone1'] = $_POST['contactPhone1'];
		}

	if(in_array('contactPhones',$editable) && isset($_POST['contactPhone2']))
		if($_POST['contactPhone2']!=$row['contactPhone2'])
		{
			$set[] = "contactPhone2='".prepStr($_POST['contactPhone2'])."'";
			$changes['contactPhone2'] = ['name'=>'Дополнительный телефон', 'newvalue'=>$_POST['contactPhone2'], 'oldvalue'=>$row['contactPhone2']];
			$row['contactPhone2'] = $_POST['contactPhone2'];
		}
		
	if(in_array('receptionRegion',$editable) && isset($_POST['receptionRegion']))
		if($_POST['receptionRegion']!=$row['receptionRegionID'])
		{
			$set[] = "receptionRegion=".intval($_POST['receptionRegion']);
			$valueName =getValueName('regions', $connect, $_POST['receptionRegion']);
			$changes['receptionRegion'] = ['name'=>'Регион отправителя', 'newvalue'=>$valueName, 'oldvalue'=>$row['receptionRegion']];
			$row['receptionRegionID'] = $_POST['receptionRegion'];
			$row['receptionRegion'] = $valueName;
		}
		
	if(in_array('receptionCity',$editable) && isset($_POST['receptionCity']))
		if($_POST['receptionCity']!=$row['receptionCity'])
		{
			$set[] = "receptionCity='".prepStr($_POST['receptionCity'])."'";
			$changes['receptionCity'] = ['name'=>'Город отправителя', 'newvalue'=>$_POST['receptionCity'], 'oldvalue'=>$row['receptionCity']];
			$row['receptionCity'] = $_POST['receptionCity'];
		}

	if(in_array('receptionAddress',$editable) && isset($_POST['receptionAddress']))
		if($_POST['receptionAddress']!=$row['receptionAddress'])
		{
			$set[] = "receptionAddress='".prepStr($_POST['receptionAddress'])."'";
			$changes['receptionAddress'] = ['name'=>'Адрес отправителя', 'newvalue'=>$_POST['receptionAddress'], 'oldvalue'=>$row['receptionAddress']];
			$row['receptionAddress'] = $_POST['receptionAddress'];
		}

	if(in_array('receptionContactFIO',$editable) && isset($_POST['receptionContactFIO']))
		if($_POST['receptionContactFIO']!=$row['receptionContactFIO'])
		{
			$set[] = "receptionContactFIO='".prepStr($_POST['receptionContactFIO'])."'";
			$changes['receptionContactFIO'] = ['name'=>'ФИО представителя отправителя', 'newvalue'=>$_POST['receptionContactFIO'], 'oldvalue'=>$row['receptionContactFIO']];
			$row['receptionContactFIO'] = $_POST['receptionContactFIO'];
		}
		
	if(in_array('note',$editable) && isset($_POST['note']))
		if($_POST['note']!=$row['note'])
		{
			$set[] = "note='".prepStr($_POST['note'])."'";
			$changes['note'] = ['name'=>'Примечание', 'newvalue'=>$_POST['note'], 'oldvalue'=>$row['note']];
			$row['note'] = $_POST['note'];
		}
	if(count($set)>0)
	{
		$query = "UPDATE register SET ".implode(', ', $set)." WHERE id=".$rowid;
		if($connect->query($query))
		{
			$changes['columns'] = ['name'=>'Поле', 'newvalue'=>'Новое значение', 'oldvalue'=>'Старое значение'];
			$details[] = $changes;
			//die(prepStr(serialize($details)));
			logevent($connect, EVENT_TYPE_ORDERDATA, 'adminzone/editregisterline', 'Изменены данные отправления', $rowid, serialize($details));
			//$message = '<p class="message">Данные отправления успешно обновлены.</p>';
			header("Location: viewregisterline.php?lineid=$rowid");
			die();
		}
		else
			$message = '<p class="message">Ошибка при обновлении данных отправления.</p>';
	}
}
	
//чтение пользовательских настроек
$deliveryterms = getOtherUserSetting('deliveryterms', $connect, $userid, DELIVERYTERMS_CHOOSE_DATE);
$intervals = getOtherUserSetting('intervals', $connect, $userid, INTERVALS_PRESET);

$res = getUserAddresses($connect, $userid);
//$readonlyAddress = ($res->num_rows > 0 && !hasNamedRight($_SESSION['userid'], 'editaddress', $connect)) ? ' readonly' : '';
$receptionAddresses=array();
if($rowAddr=$res->fetch_assoc())
{
	$receptionRegionDef = $rowAddr['region'];
	$receptionRegionNameDef = $rowAddr['regionName'];
	$receptionCityDef = $rowAddr['cityName'];
	$receptionAddressDef = $rowAddr['address'];
	do
	{
		$receptionAddresses[$rowAddr['id']] = ['region'=>$rowAddr['region'], 'regionName'=>$rowAddr['regionName'], 'cityName'=>$rowAddr['cityName'], 'address'=>$rowAddr['address']] ;
	}
	while($rowAddr=$res->fetch_assoc());
}
else
{
	$receptionRegionDef = 1;
}


$waybill = $row['waybill'];
$receptionDate =  $row['receptionDate'];
$addressee = $row['addressee'];
$address = $row['address'];
$receptionAddress = $row['receptionAddress'];
$contactFIO = $row['contactFIO'];
$receptionContactFIO = $row['receptionContactFIO'];
$contactPhone1 = $row['contactPhone1'];
$contactPhone2 = $row['contactPhone2'];
$places = $row['places'];
$weight = $row['weight'];
$dimension1 = $row['dimension1'];
$dimension2 = $row['dimension2'];
$dimension3 = $row['dimension3'];
$deliveryDay = $row['deliveryDay'];
$deliveryDayID = $row['deliveryDayID'];
$deliveryDate = $row['deliveryDate'];
$timeSpanFrom = $row['timeSpanFrom'];
$timeSpanTo = $row['timeSpanTo'];
$receptionRegion = $row['receptionRegionID'];
$receptionRegionName = $row['receptionRegion'];
$deliveryRegion = $row['deliveryRegionID'];
$receptionCity = $row['receptionCity'];
$deliveryCity = $row['deliveryCity'];
$paymentTypeID = $row['paymentTypeID'];
$paymentType = $row['paymentType'];
$sum = $row['sum'];
$note = $row['note'];

?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Личный кабинет - Администрирование - Редактирование отправления</title>
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
			if(frmRegisterLine.timeSpanFrom.value >= frmRegisterLine.timeSpanTo.value)
			{
				alert("Значение времени 'до' должно быть больше значения 'с'!");
				frmRegisterLine.timeSpanTo.focus();
				return;
			}
			
<?php
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
		</script>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Редактирование отправления</h3>
		<?php echo($message);?>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;<a href="userlist.php">Пользователи</a>&nbsp;&gt;&nbsp;<a href="userregisters.php?userid=<?=$userid?>"><?=$login?></a>&nbsp;&gt;&nbsp;Редактирование отправления</p>
		<p>Вы можете изменить значения отдельных полей отправления<br/>(обязательные поля отмечены звёздочкой):</p>
		<p><a href="viewregisterline.php?lineid=<?=$rowid?>">&larr;&nbsp;Вернуться к просмотру отправления</a></p>
		<form method="POST" onsubmit="checkAndSubmit();return false;" name="frmRegisterLine">
			<input type="hidden" name="updateFields" value="1"/>
			<div class="tableformpart">
				<table>
					<thead>
						<tr>
							<th colspan="2">Информация об <strong>отправлении</strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Номер заказа <star>*</star></td>
							<td><input name="waybill" type="text" maxlength="30" required <?php if(isset($waybill)) echo 'value="'.htmlspecialchars($waybill).'"'; if(!in_array('waybill',$editable)) echo ' readonly';?> autofocus /></td>
						</tr>
						<tr>
							<td>Дата забора <star>*</star></td>
							<td><input name="receptionDate" type="date" placeholder="ГГГГ-ММ-ДД" <?php if(isset($receptionDate)) echo "value=\"$receptionDate\""; if(!in_array('receptionDate',$editable)) echo ' readonly';?> required /></td>
						</tr>
						<tr>
							<td>Кол-во мест <star>*</star></td>
							<td><input name="places" type="number" min="1" max="50" <?php if(isset($places)) echo "value=\"$places\""; if(!in_array('places',$editable)) echo ' readonly';?> required /></td>
						</tr>
						<tr>
							<td>Вес (кг) <star>*</star></td>
							<td><input name="weight" type="number" min="0" max="200" step="0.1" required <?php if(isset($weight)) echo "value=\"$weight\""; if(!in_array('weight',$editable)) echo ' readonly';?>/></td>
						</tr>
						<tr>
							<td>Габариты (см)</td>
							<td><input type="number" min="0" max="250" name="dimension1" value="<?=$dimension1?>" class="short"<?php if(!in_array('dimensions',$editable)) echo ' readonly';?>/>&#215;<input type="number" min="0" max="250" name="dimension2" value="<?=$dimension2?>" class="short"<?php if(!in_array('dimensions',$editable)) echo ' readonly';?>/>&#215;<input type="number" min="0" max="250" name="dimension3" value="<?=$dimension3?>" class="short"<?php if(!in_array('dimensions',$editable)) echo ' readonly';?>/></td>
						</tr>
						<tr>
<?php
	if(!empty($deliveryDay) || $deliveryterms==DELIVERYTERMS_STANDART)
	{
?>
							<td>Срочность <star>*</star></td>
							<td>
	<?php if(in_array('deliveryDate',$editable))
	{
	?>
								<select name="deliveryDay" required>
		<?php
									echo isset($deliveryDayID) ? listOptions("deliveryterms", $connect, $deliveryDayID) :  listOptions("deliveryterms", $connect);
		?>
								</select>
	<?php
	}
	else
		echo '<input type="text" readonly value="'.$deliveryDay.'"/>';
	?>
							</td>
<?php
	}
	elseif(!empty($deliveryDate) || $deliveryterms==DELIVERYTERMS_CHOOSE_DATE)
	{
?>
							<td>Дата доставки <star>*</star></td>
							<td>
								<input type="date" name="deliveryDate" placeholder="ГГГГ-ММ-ДД" <?php if(isset($deliveryDate)) echo "value=\"$deliveryDate\""; if(!in_array('deliveryDate',$editable)) echo ' readonly';?> required />
<?php
	}
?>
						</tr>
						<tr>
							<td>Интервал доставки</td>
							<td>
		<?php
							if(in_array('timeSpan',$editable))
							{
								echo '<p>C '.buildTimeList("timeSpanFrom", $timeSpanFrom, 'from').' до '.buildTimeList("timeSpanTo", $timeSpanTo, 'to').'</p>';
							}
							else
								echo '<p>С <input type="text" readonly value="'.formatTimeVal($timeSpanFrom).'" class="short"/> до <input type="text" readonly value="'.formatTimeVal($timeSpanTo).'" class="short"/></p>';
		?>
							</td>
						</tr>
						<tr>
							<td>Тип оплаты <star>*</star></td>
							<td>
<?php if(in_array('paymentTypeID',$editable))
{
?>
								<select name="paymentType" required onchange="if(this.value==0 && frmRegisterLine.sum.value=='') frmRegisterLine.sum.value = 0;" >
		<?php
								
									echo isset($paymentTypeID) ? listOptions("paymenttypes", $connect, $paymentTypeID) :  listOptions("paymenttypes", $connect, 1);
		?>
								</select>
<?php
}
else
	echo '<input type="text" readonly value="'.$paymentType.'"/>';
?>
							</td>
						</tr>
						<tr>
							<td>Сумма с получателя <star>*</star></td>
							<td><input name="sum" type="number" min="0" max="10000000" step="0.01" value="<?=$sum?>"<?php if(!in_array('deliveryDate',$editable)) echo ' readonly';?> required /></td>
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
							<td>ФИО получателя</td>
							<td><textarea name="contactFIO" rows="2" maxlength="50"<?php if(!in_array('contactFIO',$editable)) echo ' readonly';?>><?php if(isset($contactFIO)) echo htmlspecialchars($contactFIO); ?></textarea></td>
						</tr>
						<tr>
							<td>Компания получатель</td>
							<td><input name="addressee" type="text" maxlength="30" <?php if(isset($addressee)) echo 'value="'.htmlspecialchars($addressee).'"';  if(!in_array('addressee',$editable)) echo ' readonly';?>/></td>
						</tr>
						<tr>
							<td>Регион доставки</td>
							<td>
							<?php
							if(in_array('deliveryRegion',$editable))
								echo '<select name="deliveryRegion">'.listRegions($connect, true, false, $deliveryRegion).'</select>';
							else
							{
								echo '<input type="hidden" name="deliveryRegion" value="'.$deliveryRegion.'"/>';
								echo '<input type="text" name="deliveryRegionName" value="'.htmlspecialchars($deliveryRegionName).'" readonly />';
							}
							?>
							</td>
						</tr>
						<tr>
							<td>Город доставки</td>
							<td><input name="deliveryCity" type="text" maxlength="30" <?php if(isset($deliveryCity)) echo 'value="'.htmlspecialchars($deliveryCity).'"';  if(!in_array('deliveryCity',$editable)) echo ' readonly';?>/></td>
						</tr>
						<tr>
							<td>Адрес получателя <star>*</star></td>
							<td><textarea name="address" rows="3" maxlength="150"<?php if(!in_array('address',$editable)) echo ' readonly';?> required><?php if(isset($address)) echo htmlspecialchars($address); ?></textarea></td>
						</tr>
						<tr>
							<td>Контактный телефон <star>*</star></td>
							<td><input name="contactPhone1" type="tel" maxlength="30" required <?php if(isset($contactPhone1)) echo 'value="'.htmlspecialchars($contactPhone1).'"'; if(!in_array('contactPhones',$editable)) echo ' readonly';?>/></td>
						</tr>
						<tr>
							<td>Дополнительный телефон</td>
							<td><input name="contactPhone2" type="tel" maxlength="30" <?php if(isset($contactPhone2)) echo 'value="'.htmlspecialchars($contactPhone2).'"'; if(!in_array('contactPhones',$editable)) echo ' readonly';?>/></td>
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
					<?php
						if(count($receptionAddresses)>1 && in_array('receptionAddress',$editable))
						{
							echo '<tr><td colspan="2" class="nocenter">Адрес <select onchange="setAddress(this.value)" style="max-width: 350px;">';
							foreach($receptionAddresses as $id=>$vals)
								echo "<option value=\"$id\">".htmlspecialchars($vals['regionName']).', '.htmlspecialchars($vals['cityName']).', '.htmlspecialchars($vals['address']).'</option>';
							echo '</select></td></tr>';
						}
					?>
						<tr>
							<td>Регион отправителя</td>
							<td>
							<?php
							if(in_array('receptionRegion',$editable))
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
							<td>Город отправителя</td>
							<td><input type="text" name="receptionCity" value="<?=htmlspecialchars($receptionCity)?>"<?php if(!in_array('receptionCity',$editable)) echo ' readonly';?>/></td>
						</tr>
						<tr>
							<td>Адрес отправителя</td>
							<td><textarea name="receptionAddress" rows="3" maxlength="150"<?php if(!in_array('receptionAddress',$editable)) echo ' readonly';?>><?=htmlspecialchars($receptionAddress)?></textarea></td>
						</tr>
						<tr>
							<td>ФИО представителя отправителя</td>
							<td><textarea name="receptionContactFIO" rows="2" maxlength="50"<?php if(!in_array('receptionContactFIO',$editable)) echo ' readonly';?>><?=htmlspecialchars($receptionContactFIO)?></textarea></td>
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
							<td><textarea name="note" rows="5" maxlength="150"<?php if(!in_array('note',$editable)) echo ' readonly';?>><?=htmlspecialchars($note)?></textarea></td>
						</tr>
					</tbody>
				</table>
			</div>
			<p><input type="submit" value="Сохранить"/></p>
		</form>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>