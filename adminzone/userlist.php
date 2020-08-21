<?php include_once ("../inc/util.php");
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
$maysetrights = hasNamedRight($_SESSION['userid'], 'setuserrights', $connect);
$maysetnotificationsemail = hasNamedRight($_SESSION['userid'], 'setnotificationsemail', $connect);
$mayeditusersettings = hasNamedRight($_SESSION['userid'], 'editusersettings', $connect);
$search = isset($_GET['search']) ? prepStr($_GET['search']) : '';
if($maysetnotificationsemail && isset($_POST['updateNotificationEmail']))
{
	foreach($_POST['newNotificationEmail'] as $userid=>$newEmail)
	{
		if (!setUserSettingValueStr($connect, intval($userid), 'notificationsEmail', prepStr($newEmail)))
			$message.="<p class=\"message\">Ошибка при попытке сохранения пользовательской настройки</p>";
	}
}
if($maysetnotificationsemail && isset($_POST['updateContractType']))
{
	foreach($_POST['newContractType'] as $userid=>$newContractType)
	{
		if (!setUserSettingValue($connect, intval($userid), 'contractType', intval($newContractType)))
			$message.="<p class=\"message\">Ошибка при попытке сохранения пользовательской настройки</p>";
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Администрирование - Список пользователей</title>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Список пользователей</h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;Список пользователей</p>
		<?=$message?>
		<form method="GET" name="searchbox">
		<p><input name="search" type="search" onsearch="searchbox.submit();" style="width:250px" placeholder="Поиск пользователей" value="<?htmlspecialchars($search)?>"/><input type="submit" value="Найти" style="margin:0; min-height:0px; min-width:0px; border-radius: 0px; font-size: 13pt"/></p>
		</form>
		<table class="list">
			<thead>
				<tr>
					<th>ID</th>
					<th>Логин</th>
					<th>Название компании</th>
					<th>Email</th>
					<th>Тип договора</th>
					<th>Email<br/>для уведомлений</th>
					<th<?=$mayeditusersettings ? ' colspan="2"' :''?>>Действия</th>
				</tr>
			</thead>
<?php
$res = getUsersList($connect, $search);
while($row = $res->fetch_assoc())
{
	$userid = $row['id'];
	echo ("<tr valign=\"top\"><td>$userid</td><td>".htmlspecialchars($row['login'])."</td><td>".htmlspecialchars($row['companyName'])."</td><td>".htmlspecialchars($row['email'])."</td><td>".$row['contractType'].
			($maysetnotificationsemail ? "<br/><a href=\"#\" id=\"contractType[$userid]\" onclick=\"document.getElementById('contractTypeForm[$userid]').style.display='block';document.getElementById('contractType[$userid]').style.display='none';\">Изменить</a>".
										"<form id=\"contractTypeForm[$userid]\" method=\"POST\" action=\"/adminzone/userlist.php?search=$search\" style=\"display:none; white-space: nowrap;\"><select name=\"newContractType[$userid]\">".listOptions('contracttypes', $connect, $row['contractTypeID'])."</select><input type=\"submit\" value=\"&raquo;\" class=\"search\" title=\"Обновить\" name=\"updateContractType\"/></form>" : '').
			"</td><td>".$row['notificationsEmail'].
			($maysetnotificationsemail ? "<br/><a href=\"#\" id=\"notificationsEmail[$userid]\" onclick=\"document.getElementById('notificationsEmailForm[$userid]').style.display='block';document.getElementById('notificationsEmail[$userid]').style.display='none';\">Изменить</a>".
										"<form id=\"notificationsEmailForm[$userid]\" method=\"POST\" action=\"/adminzone/userlist.php?search=$search\" style=\"display:none; white-space: nowrap;\"><input type=\"text\" name=\"newNotificationEmail[$userid]\" class=\"medium\"/><input type=\"submit\" value=\"&raquo;\" class=\"search\" title=\"Обновить\" name=\"updateNotificationEmail\"/></form>" : '').
			"</td><td>".($maysetrights ? "<a href=\"userrights.php?userid=$userid\">Права</a><br/>" : '')."<a href=\"userregisters.php?userid=$userid\">Отправления</a></td>".
			($mayeditusersettings ? "<td><a href=\"usersettings.php?userid=".$row['id']."\" title=\"Настройки\"><img alt=\"Настройки\" src=\"../style/i/settings_001.png\" height=\"30px\"/></a></td>" : '').'</tr>');
}
?>
			<tr><td colspan="<?=$mayeditusersettings ? '8' :'7'?>"><a href="adduser.php">Добавить нового пользователя&nbsp;&raquo;</a></td></tr>
		</table>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>