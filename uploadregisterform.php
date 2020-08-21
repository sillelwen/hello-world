<?php include_once ("inc/util.php");
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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['registerFile']))
{
	$path = "regfiles/";
	$filename = "Register_".$_SESSION['userid']."_".generatePassword().".xml";
	if(!move_uploaded_file($_FILES['registerFile']['tmp_name'], $path.$filename))
		$message = "<p class=\"message\"Ошибка при загрузке файла.</p>";
	else
	{
		addFileToUser($_SESSION['userid'], $filename, $connect);
		sendMailAttachment(getSetting('emailForFiles', $connect), getSetting('emailFrom', $connect), getSetting('nameFrom', $connect), 'Новый реестр от компании '.$_POST['companyName'],
			'Пользователь '.$_SESSION['login'].' загрузил новый реестр.', $path.$filename);
		$message = "<p class=\"message\">Файл успешно загружен.</p>";
	}
	
	
	/*//делаем преобразование в таблицу HTML - чисто посмотреть
	$xml = new DOMDocument(); $xml->load($path.$filename);
	$xsl = new DOMDocument(); $xsl->load("inc/excelDecomp.xsl");
	$proc = new XSLTProcessor(); $proc->importStyleSheet($xsl); $table = $proc->transformToXML($xml);
	
	if (!$table)
	{
		$message = "<p>Не удалось разобрать загруженный файл.</p>";
	}
	else
	{
		print $table;
		die();
	}*/
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('inc/metalinks.php');?>
		<title>Добавление реестра</title>
	</head>
	<body>
		<?php include_once ("inc/header.php");?>
		<h3>Добавление реестра</h3>
		<p><a href="addregister.php">Добавление реестра</a>&nbsp;&gt;&nbsp;Загрузка готового файла</p>
		<form enctype="multipart/form-data" method="POST">
			<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
			<?php echo($message);?>
			<?php echo($table);?>
			<div class="excel logo">
			</div>
			<br/>
			<input type="file" name="registerFile" accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/xml"/><br/>
			<input type="submit" value="Загрузить"/>
		</form>
		<hr/>
		<p>Здесь вы можете <a href="regfiles/regDraft.xml" download>скачать образец реестра для заполнения</a>.</p>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("inc/footer.php");
?>