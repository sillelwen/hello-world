<?php include_once ("../inc/util.php");
if(!isset($_SESSION['userid']))
{
	header("Location: login.php");
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

$res = getAvailableValues($connect, 'deliveryterms');
while($row = $res->fetch_assoc())
{
	$result .= $row['value']."\r\n";
}
$checked = ' checked';


if(isset($_POST['deliveryterms_choice']))
{
	$res = setUserSettingValue($connect, $userid, 'deliveryterms', $_POST['deliveryterms']);
	$deliveryterms = $_POST['deliveryterms'];
	logevent($connect, EVENT_TYPE_INFO, 'user_settings', 'User setting: deliveryterms='.$deliveryterms, $userid);
	$message = '<p class="message">Сроки доставки успешно обновлены.</p>';
}
else
	$deliveryterms = getOtherUserSetting('deliveryterms', $connect, $userid, DELIVERYTERMS_CHOOSE_DATE);

if(isset($_POST['intervals_choice']))
{
	$res = setUserSettingValue($connect, $userid, 'intervals', $_POST['intervals']);
	$intervals = $_POST['intervals'];
	logevent($connect, EVENT_TYPE_INFO, 'user_settings', 'User setting: intervals='.$intervals, $userid);
	$message = '<p class="message">Интервалы доставки успешно обновлены.</p>';
}
else
	$intervals = getOtherUserSetting('intervals', $connect, $userid, INTERVALS_PRESET);

if(isset($_POST['new_interval']))
{
	if($_POST['new_value_from']>=$_POST['new_value_to'])
		$message = '<p class="message">Начало интервала должно быть раньше его окончания</p>';
	elseif($res = addUserDeliveryInterval($connect, $userid, $_POST['new_value_from'], $_POST['new_value_to']))
	{
		$message = '<p class="message">Интервал добавлен</p>';
		logevent($connect, EVENT_TYPE_INFO, 'user_settings', 'User setting: new interval: '.$_POST['new_value_from'].' - '.$_POST['new_value_to'], $userid);
		$_POST['new_value_from'] = ''; $_POST['new_value_to'] = '';
	}
	$intervals = INTERVALS_PRESET;
}

if(isset($_POST['delete_interval']))
{
	foreach($_POST['delete_interval'] as $intervalid=>$val)
	if($res = deleteUserDeliveryInterval($connect, $userid, $intervalid))
	{
		$message = '<p class="message">Интервал удалён</p>';
		logevent($connect, EVENT_TYPE_INFO, 'user_settings', 'User setting: interval deleted', $intervalid, $userid);
	}
	$intervals = INTERVALS_PRESET;
}


if(isset($_POST['new_address']))
{
	if(empty($_POST['receptionCity']) || empty($_POST['receptionAddress']))
		$message = '<p class="message">Необходимо заполнить все поля адреса</p>';
	elseif($res = addUserAddress($connect, $userid, $_POST['receptionRegion'], $_POST['receptionCity'], $_POST['receptionAddress']))
	{
		$message = '<p class="message">Адрес добавлен</p>';
		logevent($connect, EVENT_TYPE_INFO, 'user_settings', 'User setting: new address: '.$_POST['receptionRegion'].', '.$_POST['receptionCity'].', '.$_POST['receptionAddress'], $userid);
		$_POST['receptionRegion'] = null; $_POST['receptionCity'] = ''; $_POST['receptionAddress'] = '';
	}
}

if(isset($_POST['delete_address']))
{
	foreach($_POST['delete_address'] as $addressid=>$val)
	if($res = deleteUserAddress($connect, $userid, $addressid))
	{
		$message = '<p class="message">Адрес удалён</p>';
		logevent($connect, EVENT_TYPE_INFO, 'user_settings', 'User setting: address deleted', $addressid, $userid);
	}
}

if(isset($_POST['profileUpdate']))
{
	if(UpdateOtherUserProfile($connect, $_POST['companyName'], $_POST['email'], $userid))
	{
		logevent($connect, EVENT_TYPE_INFO, 'user_profile', 'User profile data updated: '.$_POST['companyName'].', '.$_POST['email'], $userid);
		$companyName = $_POST['companyName'];
		$email = $_POST['email'];
		$message = '<p class="message">Данные профиля успешно обновлены.</p>';
	}
	else
		$message = '<p class="message">Ошибка при обновлении данных профиля.</p>';
}

?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Администрирование - Персональные настройки</title>
		<script type="text/javascript">
		function toggle(div_id, checked_id)
		{
			document.getElementById(div_id).style.display = document.getElementById(checked_id).checked ? 'grid' : 'none';
		}
		</script>
	</head>
	<body onload="toggle('intervals', 'intervals2')">
		<?php include_once ("../inc/header.php");?>
		<h3>Настройки пользователя <?=$login?></h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;<a href="userlist.php">Пользователи</a>&nbsp;&gt;&nbsp;Персональные настройки</p>
		<div class="tableformpart">
			<form method="POST" class="gridform">
				<h4>Данные профиля</h4>
				<label for="companyName">Название компании</label><input type="text" name="companyName" value="<?=htmlspecialchars($companyName)?>" required />
				<label for="email">Электронная почта</label><input type="email" name="email" value="<?=htmlspecialchars($email)?>" required />
				<input type="submit" value="Сохранить" name="profileUpdate"/>
			</form>
			<form method="POST" class="gridform">
				<h4>Адреса отправителя</h4>
				<?php
				$res = getUserAddresses($connect, $userid);
				if($res->num_rows>0)
				{
					echo '<div class="valueslist span2" id="addresses">';
					while($row=$res->fetch_assoc())
					{
						echo '<span><strong>'.htmlspecialchars($row['regionName']).'</strong>, <em>'.htmlspecialchars($row['cityName']).'</em>,<br/>'.htmlspecialchars($row['address']).'</span>';
						echo '<span><input type="submit" name="delete_address['.$row['id'].']" value="&#10799;" class="search"/></span>';
					}
					echo '</div>';
				}
				?>
				<label for="receptionRegion">Регион</label><select name="receptionRegion"><?=listRegions($connect, true, false, $_POST['receptionRegion'])?></select>
				<label for="receptionCity">Город</label><input type="text" name="receptionCity" value="<?=htmlspecialchars($_POST['receptionCity'])?>" maxlength="30"/>
				<label for="receptionAddress">Адрес</label><input type="text" name="receptionAddress" value="<?=htmlspecialchars($_POST['receptionAddress'])?>" maxlength="150"/>
				<input type="submit" value="Добавить" name="new_address"/>
			</form>
			<!--form method="POST" class="gridform">
				<h4>Сроки доставки</h4>
				<input type="radio" name="deliveryterms" id="deliveryterms1" value="<?php echo DELIVERYTERMS_STANDART;?>"<?php if($deliveryterms==DELIVERYTERMS_STANDART) echo $checked ?>/><label for="deliveryterms1" title="<?php echo $result; ?>">Выбор из стандартных вариантов</label>
				<input type="radio" name="deliveryterms" id="deliveryterms2" value="<?php echo DELIVERYTERMS_CHOOSE_DATE;?>"<?php if($deliveryterms==DELIVERYTERMS_CHOOSE_DATE) echo $checked ?>/><label for="deliveryterms2">Указание точной даты</label>
				<input type="submit" value="Сохранить" name="deliveryterms_choice"/>
			</form-->
			<form method="POST" class="gridform">
				<h4>Интервалы доставки</h4>
				<input type="radio" name="intervals" id="intervals1" value="<?php echo INTERVALS_FROM_TO;?>" onchange="toggle('intervals', 'intervals2');"<?php if($intervals==INTERVALS_FROM_TO) echo $checked ?>/><label for="intervals1">Выбирать время начала и конца</label>
				<input type="radio" name="intervals" id="intervals2" value="<?php echo INTERVALS_PRESET;?>" onchange="toggle('intervals', 'intervals2');"<?php if($intervals==INTERVALS_PRESET) echo $checked ?>/><label for="intervals2">Заданные интервалы</label>
				<div class="valueslist" id="intervals">
				<?php
					$res = getUsersTimesSpanByUserID($connect, $userid);
					while($row = $res->fetch_assoc())
					{
						$from = $row['timeSpanFrom']; $to = $row['timeSpanTo'];
						echo '<div>С '.intdiv($from,2).':'.str_pad((($from%2)*30),2,0).' по '.intdiv($to,2).':'.str_pad((($to%2)*30),2,0).'</div><span><input type="submit" name="delete_interval['.$row['id'].']" value="&#10799;" class="search"/></span>';
					}
					echo '<div>';
					echo 'С '.buildTimeList("new_value_from", $_POST['new_value_from'], 'from');
					echo ' по '.buildTimeList("new_value_to", $_POST['new_value_to'], 'to');
					echo '</div><span><input type="submit" value="+" class="search" name="new_interval"/></span>';
				?>
				</div>
				<input type="submit" value="Сохранить" name="intervals_choice"/>
			</form>
		</div>
		<h4>Другие настройки</h4>
		<ul>
			<li><a href="printregister_settings.php?userid=<?=$userid?>">Настройки печатной формы</a></li>
		</ul>
<?php
if(!empty($message)) echo("<div class=\"message\" onclick=\"this.style.display='none';\">$message</div>");
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>