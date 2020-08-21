<?php include_once ("inc/util.php");
DEFINE ('EVENTSOURCE', 'listregisters');
if(!isset($_SESSION['userid']))
{
	header("Location: login.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'viewregisters', $connect))
{
	header("Location: index.php");
	die();
}

if(isset($_POST['deleteRow']) && $_POST['deleteRow']!='')
{
	deleteRow(intval($_POST['deleteRow']));
}
if(isset($_POST['restoreRow']) && $_POST['restoreRow']!='')
{
	restoreRow(intval($_POST['restoreRow']));
}

$showstatus = hasNamedRight($_SESSION['userid'], 'viewstatus', $connect);
$link = '/listregisters.php?';
$search = '';
if(isset($_GET['search']))
{
	$search = prepStr($_GET['search']);
	$link .= 'search='.urlencode($search).'&';
}
//фильтрация дат
$filterDates = array();

if(isset($_POST['filterDates']))
{
	if(is_array($_POST['filterDates']))
		foreach($_POST['filterDates'] as $key=>$value)
		{
			if(!isset($_POST['removeDate'][$key]) && is_date($_POST['filterDates'][$key]))
			{
				$filterDates[$key]=$_POST['filterDates'][$key];
			}
			if(isset($_POST['closeDate'][$key]) && is_date($_POST['filterDates'][$key]))
			{
				if(closeDate($connect, $value))
					$message .= "<p class=\"message\">День закрыт: ".dateForPrint($value).".<br/>Вы больше не можете редактировать и добавлять отправления за эту дату.</p>";
				else
					$message .= "<p class=\"message\">Произошла ошибка при закрытии дня: ".dateForPrint($value)."</p>";
			}
		}
	elseif(!empty($_POST['filterDates']))
		$filterDates = explode_dates(';', $_POST['filterDates']);
}
if(isset($_POST['addDate']) && !empty($_POST['receptionDate']) && is_date($_POST['receptionDate']))
{
	if(!in_array($_POST['receptionDate'], $filterDates))
		$filterDates[] = $_POST['receptionDate'];
}

if(empty($_POST['filterDates']) && empty($_POST['receptionDate']) && isset($_GET['filterDates']))
	$filterDates = explode_dates(',', $_GET['filterDates']);

sort($filterDates);

if(!empty($filterDates))
	$link .= 'filterDates='.urlencode(implode(',', $filterDates)).'&';

$datefilter = count($filterDates)>0 ? " AND receptionDate IN ('".implode("', '", $filterDates)."')" : '';

$sortfields = [1=>'waybill', 2=>'receptionDate', 3=>'deliveryDate', 4=>'weight', 5=>'places', 6=>'dimensions', 7=>'address', 8=>'deliveryInterval', 9=>'paymentType', 10=>'sum', 11=>'status'];
$sortfieldnames = [1=>'Номер заказа', 2=>'Дата забора', 3=>'Дата доставки', 4=>'Вес (кг)', 5=>'Кол-во мест', 6=>'Габариты (см)', 7=>'Адрес', 8=>'Время доставки', 9=>'Тип оплаты', 10=>'Сумма с клиента', 11=>'Статус'];
$formatfields = [1=>'', 2=>'date', 3=>'date', 4=>1, 5=>0, 6=>'pre', 7=>'', 8=>'pre', 9=>'', 10=>2, 11=>''];
$sortby = (isset($_GET['sortby']) && array_key_exists($_GET['sortby'], $sortfields)) ? $_GET['sortby'] : 2;
$sortfield = $sortfields[$sortby];
$direction = (isset($_GET['back']) || !isset($_GET['sortby'])) ? 'DESC' : 'ASC';
$rows = isset($_GET['rows']) && is_numeric($_GET['rows']) ? $_GET['rows'] : $_SESSION['rows'];
updateUserSettingRows($rows, $connect);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

if(isset($_POST['setColumns']))
{
	$columns = isset($_POST['chkColumns']) ? array_keys(array_filter($_POST['chkColumns'], function($v) {return $v == 1;})) : array();
	if(count($columns)==0)
	{
		$message .= "<p class=\"message\">Нельзя отключить отображение всех столбцов.</p>";
		$columns = explode(';', getUserSettingStr('columns', $connect, COLUMNS_DEFAULT));
	}
	else
		setUserSettingValueStr($connect, $_SESSION['userid'], 'columns', implode(';',$columns));
}
else
	$columns = explode(';', getUserSettingStr('columns', $connect, COLUMNS_DEFAULT));

$and = $datefilter.(($search=='') ? '' : " AND (waybill LIKE '%".$search."%' OR address LIKE '%".$search."%' OR addressee LIKE '%".$search."%' OR contactFIO LIKE '%".$search."%')");
$sorting = " ORDER BY $sortfield $direction";

//получаем общее количество страниц, соответствующих условиям
if($rows>0)
{
	$querycount = "SELECT count(*) as totalrows FROM register WHERE userid=".$_SESSION['userid'].$and;
	$res = $connect->query($querycount);
	$totalpages = ceil($res->fetch_assoc()['totalrows'] / $rows);
}
else
	$totalpages = 1;

if($page > $totalpages) $page = $totalpages;
$limit = $rows>0 ? ' LIMIT '.$rows.($page>1 ? ' OFFSET '.($page-1)*$rows : '') : '';

?>
<!DOCTYPE html>
<html>
	<head>
		<?php include_once('inc/metalinks.php');?>
		<title>Личный кабинет - Зарегистрированные отправления</title>
		<script type="text/javascript">
		function setcheck(cell, rowid)
		{
			var fldname = 'chkRow[' + rowid + ']';
			//alert(fldname);
			var fld = document.getElementById(fldname);
			fld.value = Math.abs(fld.value-1);
			if (fld.value == 1)
			{
				cell.parentElement.classList.toggle('checked', true);
			}
			else
			{
				cell.parentElement.classList.toggle('checked', false);
			}
		}
		function checkall()
		{
			var q = Array.prototype.slice.call(document.getElementsByClassName("chkRow"));
			
			var res = 0;
			for (var i=0; i<q.length; i++)
				res += parseInt(q[i].value);

			var val; var className;
			val = (res < q.length) ? 1 : 0;
			
			for (var i=0; i<q.length; i++){
				q[i].value = val;
				q[i].parentElement.parentElement.classList.toggle('checked', val==1);
			}
		}
		function toggleFilter()
		{
			document.getElementsByName('btnFilter')[0].classList.toggle('grey');
			frmFilter.classList.toggle('hidden');
		}
		function toggleDialog(dialogID)
		{
			document.getElementById(dialogID).classList.toggle('show');
			document.getElementById(dialogID).parentElement.classList.toggle('show');
		}
		function groupPrint(option)
		{
			switch(option)
			{
				case 1:
					printform.action = "print/printregister.php";
					break;
				case 2:
					printform.action = "print/waybill.php";
					break;
			}
			printform.submit();
		}
		function deleteRow(id)
		{
			frmFilter.reset();
			frmFilter.deleteRow.value = id;
			frmFilter.submit();
		}
		function restoreRow(id)
		{
			frmFilter.reset();
			frmFilter.restoreRow.value = id;
			frmFilter.submit();
		}
		</script>
	</head>
	<body>
		<?php include_once ("inc/header.php");?>
		<h3>Зарегистрированные отправления</h3>
		<?=$message?>
		<form method="GET" name="searchbox">
			<input type="search" name="search" onsearch="searchbox.submit();" style="width:250px" placeholder="Поиск отправления" title="Поиск по номеру заказа, названию или ФИО получателя, адресу доставки" value="<?=htmlspecialchars($search)?>"/><input type="submit" value="Найти" class="search"/>
		</form>
		<form method="POST" name="frmFilter" class="gridform filterform<?=count($filterDates)>0 ? '' :' hidden'?>">
			<div class="valueslist">
				<h4>Дата забора:</h4>
		<?php
			foreach($filterDates as $key=>$value)
			{
				$closed = isDateClosed($connect, $value);
				echo "<input type=\"hidden\" name=\"filterDates[$key]\" value=\"$value\"/>".dateForPrint($value)."<div class=\"buttongroup\"><button type=\"submit\" name=\"removeDate[$key]\">&#10799;</button><button type=\"submit\" name=\"closeDate[$key]\" title=\"".($closed ? 'День закрыт' :'Закрыть день').'"'.($closed ?' disabled':'').">&#10003;</button></div>";
			}
		?>
				<input type="hidden" name="deleteRow" value=""/>
				<input type="hidden" name="restoreRow" value=""/>
				<input type="date" name="receptionDate" value="<?=date('Y-m-d')?>"/><button type="submit" name="addDate">+</button>
			</div>
		</form>
		<div class="dialogContainer">
		<?php
		if(count($filterDates)>0)
		{
		?>
			<div class="dialog" id="printDialog">
				<h4>Групповая печать <button class="closebutton" title="Закрыть диалог" onclick="toggleDialog('printDialog');"/></h4>
				<p>Выберите вариант печати:</p>
				<button onclick="groupPrint(2);">Накладные</button>
				<button onclick="groupPrint(1);">Реестр</button>
			</div>
		<?php
		}
		?>
			<form method="POST" name="frmColumns" class="dialog" id="frmColumns">
				<h4>Отображаемые столбцы <button class="closebutton" title="Закрыть диалог" onclick="toggleDialog('frmColumns');"/></h4>
			<?php
				echo "<input type=\"hidden\" name=\"filterDates\" value=\"".implode(';', $filterDates)."\"/>\n";
				for($i=1;$i<=count($sortfieldnames);$i++)
				{
					if($sortfields[$i]!='status' || $showstatus)
						echo '<input type="checkbox" name="chkColumns['.$i.']" id="chkColumns['.$i.']" value="1"'.(in_array($i,$columns) ? ' checked' : '').'/><label for="chkColumns['.$i.']">'.$sortfieldnames[$i].'</label>';
				}
			?>
				<input type="submit" name="setColumns" value="Применить"/>
			</form>
		</div>
		<form action="print/printregister.php" method="POST" target="_blank" name="printform">
		<?php
			echo "<input type=\"hidden\" name=\"receptionDate\" value=\"".implode_datesForPrint(', ', $filterDates)."\"/>\n";
		?>
			<div class="paginating print">
				<div class="buttongroup">
					<button onclick="<?=count($filterDates)>0 ? "toggleDialog('printDialog');" : 'groupPrint(2);'?>" class="print" type="button">Печать</button>
					<button onclick="document.location.href='addregisterline.php';" class="addnew" type="button">Добавить</button>
					<button onclick="toggleFilter();" class="filter<?=count($filterDates)>0 ? ' grey' :''?>" name="btnFilter" type="button">Фильтр</button>
					<button onclick="toggleDialog('frmColumns');" class="columns" name="btnColumns" type="button">Столбцы</button>
				</div>
				<label for="rowscount">Показывать по: </label>
				<select name="rowscount" class="rowscount" onchange="document.location.href='<?php echo $link."&sortby=$sortby".($direction=='DESC'?'&back':'')."&page=$page&rows="; ?>' + this.value;">
					<option value="0"<?php if($rows==0) echo ' selected';?>>Все</option>
					<option value="5"<?php if($rows==5) echo ' selected';?>>5</option>
					<option value="10"<?php if($rows==10) echo ' selected';?>>10</option>
					<option value="20"<?php if($rows==20) echo ' selected';?>>20</option>
					<option value="50"<?php if($rows==50) echo ' selected';?>>50</option>
					<option value="100"<?php if($rows==100) echo ' selected';?>>100</option>
				</select>
				<?php if ($totalpages>1) {?>
					<div class="pages"><?php echo pageslink($page, $totalpages, $link, $rows, $sortby, $direction); ?></div>
				<?php } ?>
			</div>
			<table class="register list" width="100%">
				<thead>
					<tr>
						<th class="checkrow" onclick="checkall();"></th>
						<?php
							for($i=1;$i<=count($sortfieldnames);$i++)
							{
								if(in_array($i,$columns) && ($sortfields[$i]!='status' || $showstatus))
									echo '<th'.($sortfields[$i]=='address' ? ' width="25%"' :'').'><div class="sortH">'.$sortfieldnames[$i].'&nbsp;'.arrows($link."rows=$rows&page=$page&", $i, $sortby, $direction).'</div></th>';
							}
						?>
						<th></th>
					</tr>
				</thead>
				<tbody>
<?php

	$query = "SELECT register.id, waybill, receptionDate, IFNULL(deliveryDate, concat(DATE(receptionDate), ' - ', deliveryterms.value)) as deliveryDate,
					weight, places, CONCAT(dimension1, ' &#215; ', dimension2, ' &#215; ', dimension3) as dimensions,
					CONCAT(regions.value, ', ', register.deliveryCity, CASE WHEN register.deliveryCity <> '' THEN ', ' ELSE '' END, register.address) as address,
					concat(LPAD(timeSpanFrom DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanFrom, 2)*30, 2, '0'), ' - ', LPAD(timeSpanTo DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanTo, 2)*30, 2, '0')) as deliveryInterval,
					sum, orderstatuses.value as status, status as statusid, paymenttypes.value as paymentType, deleted, IFNULL(receptiondates.date, 0) as dateClosed
				FROM register
				LEFT JOIN orderstatuses ON register.status = orderstatuses.id
				LEFT JOIN deliveryterms ON register.deliveryDay = deliveryterms.id
				LEFT JOIN paymenttypes ON register.paymentTypeID = paymenttypes.id
				LEFT JOIN regions ON deliveryRegion = regions.id
				LEFT JOIN receptiondates ON receptiondates.userid = register.userid AND receptiondates.date = register.receptionDate
				WHERE register.userid=".$_SESSION['userid'].$and.$sorting.$limit;
			//die($query);
	if($res = $connect->query($query))
	{
		while($row = $res->fetch_assoc())
		{
			if ($row['deleted'])
				echo "<tr onclick=\"document.location.href = 'viewregisterline.php?lineid=".$row['id']."';\" class=\"deleted\"><td></td>";
			else
				echo "<tr onclick=\"document.location.href = 'viewregisterline.php?lineid=".$row['id']."';\"><td class=\"checkrow\" id=\"checkrow_".$row['id']."\" onclick=\"event.stopPropagation(); setcheck(this, ".$row['id'].');"><input type="hidden" name="chkRow['.$row['id'].']" id="chkRow['.$row['id'].']" value="0" class="chkRow"></td>';
			for($i=1;$i<=count($sortfields);$i++)
			{
				if(in_array($i,$columns) && ($sortfields[$i]!='status' || $showstatus))
				{
					if(is_numeric($formatfields[$i]))
						$val = number_format($row[$sortfields[$i]], $formatfields[$i], ',', ' ');
					elseif($formatfields[$i] == 'date')
						$val = dateForPrint($row[$sortfields[$i]]);
					elseif($formatfields[$i] == 'pre')
						$val = $row[$sortfields[$i]];
					else
						$val = htmlspecialchars($row[$sortfields[$i]]);
						
					echo '<td>'.$val.'</td>';
				}
			}
			echo '<td>';
			if($row['statusid']==0)
			{
				//заменить: эти кнопки живут внутри формы печати и не работают как надо. использовать скрытую форму, скрипт подстановки значения и сабмитить её
				if($row['deleted'] && !$row['dateClosed'] && ($row['receptionDate']>=date('Y-m-d')))
					echo '<button class="restore" type="button" name="restoreRow['.$row['id'].']" onclick="event.stopPropagation(); restoreRow('.$row['id'].');" notext title="Восстановить"/>';
				elseif(!$row['deleted'])
					echo '<button class="delete" type="button" name="deleteRow['.$row['id'].']" onclick="event.stopPropagation(); deleteRow('.$row['id'].');" notext title="Удалить (отменить)"/>';
			}
			else
				echo "<button type=\"button\" class=\"print\" onclick=\"event.stopPropagation(); window.open('print/waybill.php?lineid=".$row['id']."');\" notext title=\"Печать накладной\"/>";
			echo '</td>';
			echo '</tr>';
		}
	}
?>
			</tbody>
			</table>
		</form>
<!--		<hr/>
		<h3>Список ранее загруженных реестров</h3>
		<table class="list">
			<thead>
				<tr>
					<th>Дата загрузки</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
<--?php
	$res = $connect->query("SELECT filename, creationDate FROM userfiles WHERE userid=".$_SESSION['userid']." ORDER BY creationDate DESC");
	$path = "regfiles/";
	while ($row = $res->fetch_assoc())
		echo("<tr><td>".$row['creationDate']."</td><td><a href=\"$path".$row['filename']."\" download>Скачать</a></td></tr>\r\n");
?>
			</tbody>
		</table>
-->
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("inc/footer.php");
?>