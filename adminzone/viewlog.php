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
elseif(!hasNamedRight($_SESSION['userid'], 'viewlog', $connect))
{
	header("Location: ../adminzone.php");
	die();
}

$datefrom = is_date($_POST['datefrom']) ? $_POST['datefrom'] : date('Y-m-d\TH:i', mktime(0, 0, 0, date('m'), 1));
$dateto = is_date($_POST['dateto']) ? $_POST['dateto'] : date('Y-m-d\TH:i', time());
if(isset($_POST['filtervals']))
{
	$filtervals = $_POST['filtervals'];
	$type = $_POST['type'];
	$source = trim($_POST['source']);
	$objectid = is_numeric($_POST['objectid']) ?  $_POST['objectid'] : '';
	$user = trim($_POST['user']);
	$message = trim($_POST['message']);
}
else
	$filtervals = 0;
$sortfields = [1=>'creationDate', 2=>'login', 3=>'companyName', 4=>'type', 5=>'source', 6=>'objectid', 7=>'message'];
$sortby = (isset($_POST['sortby']) && array_key_exists($_POST['sortby'], $sortfields)) ? $_POST['sortby'] : 1;
$sortfield = $sortfields[$sortby];
$direction = (!isset($_POST['back']) || $_POST['back']==1) ? 'DESC' : 'ASC';
$rows = isset($_POST['rows']) && is_numeric($_POST['rows']) ? $_POST['rows'] : $_SESSION['rows'];
updateUserSettingRows($rows, $connect);
$page = isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 1;

$where = '';
$where .= " creationDate >= '$datefrom'";
$where .= ($where==''?'':' AND')." creationDate <= '$dateto'";
if(isset($_POST['filtervals']))
{
	if($_POST['type']!='0') $where .= ($where==''?'':' AND')." type = '".$_POST['type']."'";
	if($source!='') $where .= ($where==''?'':' AND')." source LIKE '%".$source."%'";
	if(is_numeric($_POST['objectid'])) $where .= ($where==''?'':' AND')." objectid = ".$_POST['objectid'];
	if($user!='') $where .= ($where==''?'':' AND')." (login LIKE '%".$user."%' OR companyName LIKE '%".$user."%')";
	if($message!='') $where .= ($where==''?'':' AND')." message LIKE '%".$message."%'";
}
if($where!='') $where = ' WHERE'.$where;
//получаем общее количество страниц, соответствующих условиям
if($rows>0)
{
	$querycount = "SELECT count(*) as totalrows FROM log LEFT JOIN users ON log.userid = users.id$where";
	$res = $connect->query($querycount);
	$totalpages = ceil($res->fetch_assoc()['totalrows'] / $rows);
}
else
	$totalpages = 1;
if($page > $totalpages) $page = max($totalpages, 1);
//получаем содержимое страницы
$limit = $rows>0 ? ' LIMIT '.$rows.($page>1 ? ' OFFSET '.($page-1)*$rows : '') : '';
$sorting = " ORDER BY $sortfield $direction";
$query = "SELECT creationDate, login, companyName, type, source, objectid, message FROM log LEFT JOIN users ON log.userid = users.id$where$sorting$limit";
//echo $query;
?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('../inc/metalinks.php');?>
		<title>Администрирование - Журнал событий</title>
		<script type="text/javascript">
		function toggle(elName) {
		  var x = document.getElementById(elName);
		  x.classList.toggle('hidden');
		}
		
		function sort(sortby, back)
		{
			var x = document.forms.filterform;
			x.reset();
			x.elements.sortby.value = sortby;
			x.elements.back.value = back;
			x.submit();
		}
		
		function setrows(rows)
		{
			document.forms.filterform.reset();
			document.getElementById('rows').value = rows;
			document.forms.filterform.submit();
		}
		
		function setpage(page)
		{
			document.forms.filterform.reset();
			document.getElementById('page').value = page;
			document.forms.filterform.submit();
		}
		
		function clearfilter()
		{
			var x = document.forms.filterform;
			x.elements.filtervals.value = 0;
			x.elements.sortby.value = 1;
			x.elements.back.value = 1;
			x.elements.datefrom.value = '';
			x.elements.dateto.value = '';
			x.elements.type.value = 0;
			x.elements.source.value = '';
			x.elements.objectid.value = '';
			x.elements.user.value = '';
			x.elements.message.value = '';
			x.submit();
		}
		</script>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Журнал событий</h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;Журнал событий</p>
		<a href="#" onclick="toggle('filterform');"><h4>Фильтр &#8659;</h4></a>
		<form class="filterform" id="filterform" method="POST">
			<input type="hidden" name="filtervals" id="filtervals" value="<?php echo $filtervals; ?>"/>
			<input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby;?>"/>
			<input type="hidden" name="back" id="back" value="<?php echo isset($_POST['back']) ? $_POST['back'] : 1;?>"/>
			<input type="hidden" name="rows" id="rows" value="<?php echo $rows;?>"/>
			<input type="hidden" name="page" id="page" value="<?php echo $page;?>"/>
			<fieldset>
				<legend>Дата события</legend>
				<div class="fieldset">
					<label for="datefrom">С&nbsp;</label><input type="datetime-local" name="datefrom" value="<?php echo $datefrom ?>"/>
					<label for="dateto">по&nbsp;</label><input type="datetime-local" name="dateto" value="<?php echo $dateto ?>"/>
				</div>
			</fieldset>
			<fieldset>
				<legend>Событие</legend>
				<div class="fieldset">
					<label for="type">Тип&nbsp;события&nbsp;</label><select name="type"><option value="0">любой</option><?php echo listOptionsEnum('log', 'type', $connect, $type); ?></select>
					<label for="source">Источник&nbsp;</label><input type="text" name="source" value="<?php echo $source ?>"/>
					<label for="objectid">Объект&nbsp;</label><input type="number" name="objectid" value="<?php echo $objectid ?>"/>
				</div>
			</fieldset>
			<fieldset>
				<legend>Пользователь</legend>
				<label for="user">Логин или компания </label><input type="text" name="user" value="<?php echo $user ?>"/>
			</fieldset>
			<fieldset>
				<legend>Сообщение</legend>
				<textarea name="message" wrap="soft" rows="3" cols="45" style="width:100%"><?php echo $message ?></textarea>
			</fieldset>
			<div class="fieldset">
				<input type="button" onclick="clearfilter();" value="Сброс"/>
				<input type="submit" value="Применить" onclick="document.getElementById('filtervals').value=1;"/>
			</div>
		</form>
		<div class="paginating">
			<select class="rowscount" name="rowscount" onchange="setrows(this.value);" name="rowscount">
				<option value="0"<?php if($rows==0) echo ' selected';?>>Все</option>
				<option value="5"<?php if($rows==5) echo ' selected';?>>5</option>
				<option value="10"<?php if($rows==10) echo ' selected';?>>10</option>
				<option value="20"<?php if($rows==20) echo ' selected';?>>20</option>
				<option value="50"<?php if($rows==50) echo ' selected';?>>50</option>
				<option value="100"<?php if($rows==100) echo ' selected';?>>100</option>
			</select>
			<div class="pages"><?=pages($page, $totalpages)?></div>
		</div>
		<table class="register list" width="100%">
			<thead>
				<tr>
					<th width="60px">Дата и время&nbsp;<?=arrowsPOST(1, $sortby, $direction)?></th>
					<th width="60px">Логин пользователя&nbsp;<?=arrowsPOST(2, $sortby, $direction)?></th>
					<th width="60px">Название компании&nbsp;<?=arrowsPOST(3, $sortby, $direction)?></th>
					<th width="60px">Тип события&nbsp;<?=arrowsPOST(4, $sortby, $direction)?></th>
					<th width="60px">Источник&nbsp;<?=arrowsPOST(5, $sortby, $direction)?></th>
					<th width="40px">Объект&nbsp;<?=arrowsPOST(6, $sortby, $direction)?></th>
					<th>Сообщение&nbsp;<?php echo arrowsPOST(7, $sortby, $direction);?></th>
				</tr>
			</thead>
<?php

$res = $connect->query($query);
while($row = $res->fetch_assoc())
	echo ("<tr valign=\"top\"><td>".$row['creationDate']."</td><td>".$row['login']."</td><td>".$row['companyName']."</td><td>".$row['type']."</td><td>".$row['source']."</td><td>".$row['objectid']."</td><td>".$row['message']."</td></tr>");
?>
		</table>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
?>