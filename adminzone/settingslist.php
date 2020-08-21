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
elseif(!hasNamedRight($_SESSION['userid'], 'changesystemsettings', $connect))
{
	header("Location: ../adminzone.php");
	die();
}
$res = getSettings($connect);
while($row = $res->fetch_assoc())
{
	$settings[$row['name']] = ['name'=>$row['name'], 'description'=>$row['description'], 'value'=>$row['value']];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$message=null;
	foreach ($settings as $settingname => $settingdata)
	{
		$postsettingvalue = '';
		if(substr($settingname, 0, 6)=='enable')
		{
			if(empty($_POST[$settingname]))
				$postsettingvalue = 0;
			else
				$postsettingvalue = 1;			
		}
		else
			$postsettingvalue = $_POST[$settingname];
		
		if($settingdata['value'] <> $postsettingvalue)
		{
			if(setSetting($connect, $settingname, $postsettingvalue))
			{
				$settings[$settingname]['value']=$postsettingvalue;
			}
			else
				$message = $message."<p class=\"message\">Ошибка при попытке обновления значения настройки ".$settingname.".</p>\r\n";
		}
	}
	if($message==null)
		$message="<p class=\"message\">Настройки успешно обновлены!</p>";
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Администрирование - Системные настройки</title>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Системные настройки</h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;Системные настройки</p>
		<form method="POST">
		<?php echo($message);?>
		<table class="list">
			<tbody>
		<?php
			foreach ($settings as $settingname => $settingdata)
			{
				if(substr($settingname, 0, 6)=='enable')
					echo "<tr><td><b>".htmlspecialchars($settingdata['name'])."</b><br/></td><td><input type=\"checkbox\" name=\"$settingname\" value=\"1\"".($settingdata['value'] ? ' checked' : '')." class=\"settingvalue\"/></td></tr><tr><td colspan=\"2\">".htmlspecialchars($settingdata['description'])."</td></tr>";
				else
					echo "<tr><td><b>".htmlspecialchars($settingdata['name'])."</b><br/></td><td><input type=\"text\" name=\"$settingname\" value=\"".htmlspecialchars($settingdata['value'])."\" class=\"settingvalue\"/></td></tr><tr><td colspan=\"2\">".htmlspecialchars($settingdata['description'])."</td></tr>";
			}
		?>
			</tbody>
		</table>
		<input type="submit" value="Сохранить"/>
		</form>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
 ?>