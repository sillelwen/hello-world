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
elseif(!hasNamedRight($_SESSION['userid'], 'setuserrights', $connect))
{
	header("Location: ../adminzone.php");
	die();
}
if (isset($_GET['userid']))
{
    $userid = $_GET['userid'];
	$res = getUserByID($connect, $userid);
	if($res->num_rows==0)
	{
		header("Location: userlist.php");
		die();
	}
	$row = $res->fetch_assoc();
	$login = $row['login'];
	$res = getUserRights($connect, $userid);
	while($row = $res->fetch_assoc())
	{
		$rightsforuser[$row['id']] = ['name'=>$row['name'], 'description'=>$row['description'], 'hasright'=>$row['hasright']];
	}
}
else
{
	header("Location: userlist.php");
	die();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if(isset($_POST['rightsforuser']) && $_POST['rightsforuser']==$userid)
	{
		$message=null;
		foreach ($rightsforuser as $rightid => $rightdata)
		{
			$chk = 'chkHasRight'.$rightid;
			if($rightdata['hasright']==0 AND isset($_POST[$chk]))
			{
				if(addUserRight($connect, $userid, $rightid))
				{
					$rightsforuser[$rightid]['hasright']=1;
				}
				else
					$message = $message."<p class=\"message\">Ошибка при попытке добавления разрешения ".$rightsforuser[$rightid]['name'].".</p>\r\n";
			}
			elseif($rightdata['hasright']==1 AND !isset($_POST[$chk]))
			{
				if(deleteUserRight($connect, $userid, $rightid))
				{
					$rightsforuser[$rightid]['hasright']=0;
				}
				else
					$message = $message."<p class=\"message\">Ошибка при попытке удаления разрешения ".$rightsforuser[$rightid]['name'].".</p>\r\n";
			}
		}
		if($message==null)
			$message="<p class=\"message\">Разрешения успешно обновлены!</p>";
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Администрирование - Права пользователя</title>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Права пользователя <?php echo($login)?></h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;<a href="userlist.php">Пользователи</a>&nbsp;&gt;&nbsp;Права пользователя</p>
		<form method="POST">
		<input type="hidden" name="rightsforuser" value="<?php echo($userid)?>"/>
		<?php echo($message);?>
		<table class="list">
		<?php
			foreach ($rightsforuser as $rightid => $rightdata)
			{
				echo ("<tr><td>".$rightdata['name']."</td><td>-</td><td>".$rightdata['description']."</td><td><input type=\"checkbox\" name=\"chkHasRight$rightid\" value=\"1\"".($rightdata['hasright']==1 ? ' checked' : '')."/></td></tr>");
			}
		?>
		</table>
		<input type="submit" value="Сохранить"/>
		</form>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
 ?>