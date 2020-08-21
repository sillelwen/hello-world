<?php include_once ("../inc/util.php");
 include_once ("../inc/quickrun.php");
DEFINE ('EVENTSOURCE', 'commonregister');
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
if(isset($_POST['filtervals']))
{
	$filter == '';
	
	if(!empty($_POST['company']))
		$filter .= " AND (users.companyName LIKE '%".prepStr($_POST['company'])."%' OR users.login LIKE '%".prepStr($_POST['company'])."%')";

	$condition1='';
	if(!empty($_POST['minDeliveryDate']) && !empty($_POST['maxDeliveryDate']) && is_date($_POST['minDeliveryDate']) && is_date($_POST['maxDeliveryDate']))
		$condition1 = "deliveryDate BETWEEN '".$_POST['minDeliveryDate']."' AND '".$_POST['maxDeliveryDate']."'";
	elseif(!empty($_POST['minDeliveryDate']) && is_date($_POST['minDeliveryDate']))
		$condition1 = "deliveryDate >= '".$_POST['minDeliveryDate']."'";
	elseif(!empty($_POST['maxDeliveryDate']) && is_date($_POST['maxDeliveryDate']))
		$condition1 = "deliveryDate <= '".$_POST['maxDeliveryDate']."'";
	
	$condition2 = '';
	if(!empty($_POST['deliveryDays']) && count($_POST['deliveryDays'])>0)
	{
		$condition2 = 'deliveryDay IN ('.implode_numbers(', ', $_POST['deliveryDays']).')';
	}
	
	if(!empty($condition1) && !empty($condition2))
		$filter .= ' AND('.$condition1.' OR '.$condition2.')';
	elseif(!empty($condition1))
		$filter .= ' AND '.$condition1;
	elseif(!empty($condition2))
		$filter .= ' AND '.$condition2;
	
	if(!empty($_POST['orderStatuses']) && count($_POST['orderStatuses'])>0)
		$filter .= ' AND status IN ('.implode_numbers(', ', $_POST['orderStatuses']).')';
	
	if(!empty($_POST['minReceptionDate']) && !empty($_POST['maxReceptionDate']) && is_date($_POST['minReceptionDate']) && is_date($_POST['maxReceptionDate']))
		$filter .= " AND receptionDate BETWEEN '".$_POST['minReceptionDate']."' AND '".$_POST['maxReceptionDate']."'";
	elseif(!empty($_POST['minReceptionDate']) && is_date($_POST['minReceptionDate']))
		$filter .= " AND receptionDate >= '".$_POST['minReceptionDate']."'";
	elseif(!empty($_POST['maxReceptionDate']) && is_date($_POST['maxReceptionDate']))
		$filter .= " AND receptionDate <= '".$_POST['maxReceptionDate']."'";
}
else
	$filter .= ' AND status = 1';
$link = "/adminzone/commonregister.php?";

//$sortfields = [1=>'waybill', 2=>'creationDate', 3=>'weight', 4=>'address', 5=>'status'];
$sortfields = [1=>'status', 2=>'companyName', 3=>'address', 4=>'waybill', 5=>'places', 6=>'weight', 7=>'sum', 8=>'deliveryDate', 9=>'deliveryInterval', 10=>'deliveryRegion', 11=>'receptionDate', 12=>'rowid', 13=>'quickrun_status'];
$sortfieldnames = [1=>'Статус', 2=>'Отправитель', 3=>'Адрес', 4=>'Номер заказ', 5=>'Кол-во мест', 6=>'Вес', 7=>'Сумма', 8=>'Дата доставки', 9=>'Интервал доставки', 10=>'Регион доставки', 11=>'Дата забора', 12=>'ID печатной формы', 13=>'Статус в Бегунке'];
$formatfields = [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>1, 7=>2, 8=>'date', 9=>'pre', 10=>'', 11=>'date', 12=>'pre', 13=>''];
$sortby = (isset($_POST['sortby']) && array_key_exists($_POST['sortby'], $sortfields)) ? $_POST['sortby'] : 1;
$sortfield = $sortfields[$sortby];
$direction = (!isset($_POST['back']) || $_POST['back']==1) ? 'DESC' : 'ASC';
$rows = isset($_POST['rows']) && is_numeric($_POST['rows']) ? $_POST['rows'] : $_SESSION['rows'];
updateUserSettingRows($rows, $connect);
$page = isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 1;

if(isset($_POST['setColumns']) && $_POST['setColumns']==1)
{
	$columns = isset($_POST['chkColumns']) ? array_keys(array_filter($_POST['chkColumns'], function($v) {return $v == 1;})) : array();
	if(count($columns)==0)
	{
		$message = "<p class=\"message\">Нельзя отключить отображение всех столбцов.</p>";
		$columns = explode(';', getUserSettingStr('columns_commonreg', $connect, COLUMNS_COMMONREG_DEFAULT));
	}
	else
		setUserSettingValueStr($connect, $_SESSION['userid'], 'columns_commonreg', implode_numbers(';',$columns));
}
else
	$columns = explode(';', getUserSettingStr('columns_commonreg', $connect, COLUMNS_COMMONREG_DEFAULT));

if(!empty($_POST['getQuickRunStatuses']))
{
	$idsForQuickrun = explode_numbers(';', $_POST['getQuickRunStatuses']);
	if($res=getOrdersForQuickrunByIDs($idsForQuickrun))
	{
		while($row = $res->fetch_assoc())
		{
			if(is_date($row['deliveryDate']) && $quickrunStatusData = getQuickrunDeliveryStatus($row['id'], $row['receptionRegionCode'], $row['deliveryRegionCode'], $row['deliveryDate']))
				storeQuickRunStatus($row['id'], $quickrunStatusData['quickrun_id'], $quickrunStatusData['quickrun_status'], $quickrunStatusData['quickrun_status_id']);			
		}
	}
}
if(!empty($_POST['updateStatuses']) && isset($_POST['updateStatusesId']) && is_numeric($_POST['updateStatusesId']))
{
	$idsForStatusUpdate = explode_numbers(';', $_POST['updateStatuses']);
	updateStatuses($idsForStatusUpdate, intval($_POST['updateStatusesId']));
}

$and .= $filter;
$sorting = " ORDER BY $sortfield $direction";

//получаем общее количество страниц, соответствующих условиям
if($rows>0)
{
	$querycount = "SELECT count(*) as totalrows FROM register INNER JOIN users ON users.id = register.userid WHERE 1".$and;
	
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
		<?php include_once('../inc/metalinks.php');?>
		<title>Администрирование - Реестр отправлений</title>
		<script>
		
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
		
		function getchecks()
		{
			var q = Array.prototype.slice.call(document.getElementsByClassName("chkRow"));
			var checks = [];
			for (var i=0; i<q.length; i++){
				if(q[i].value==1){
					checks.push(q[i].getAttribute('data-id'));
				}
			}
			return checks;
		}
		
		function getQuickRunStatuses()
		{
			var checks = getchecks();
			if(checks.length==0)
			{
				alert("Не выбраны отправления!");
				return false;
			}
			else
			{
				document.forms.filterform.reset();
				document.forms.filterform.getQuickRunStatuses.value = checks.join(';');
				document.forms.filterform.submit();
				return true;
			}
		}
		
		function updateStatuses()
		{
			var checks = getchecks();
			if(checks.length==0)
			{
				alert("Не выбраны отправления!");
				return false;
			}
			else
			{
				document.forms.filterform.reset();
				document.forms.filterform.updateStatuses.value = checks.join(';');
				document.forms.filterform.updateStatusesId.value = document.getElementById('newStatus').value;
				document.forms.filterform.submit();
				return true;
			}
		}
		
		function toggleFilter()
		{
			document.getElementsByName('btnFilter')[0].classList.toggle('grey');
			filterform.classList.toggle('hidden');
		}
		function toggleDialog(dialogID)
		{
			document.getElementById(dialogID).classList.toggle('show');
			document.getElementById(dialogID).parentElement.classList.toggle('show');
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
		
		function setcolumns()
		{
			document.forms.filterform.reset();
<?php			
				for($i=1;$i<=count($sortfieldnames);$i++)
				{
					echo "			elem = document.getElementById('chkColumns[$i]');";
					echo "			document.forms.filterform.querySelector('input[name=\"' + elem.name + '\"]').value = elem.checked ? 1 : 0;\n";
				}
?>

			document.forms.filterform.setColumns.value = '1';
			document.forms.filterform.submit();
		}
		
		function clearfilter()
		{
			var x = document.forms.filterform;
			x.elements.filtervals.value = 0;
			x.elements.sortby.value = 1;
			x.elements.back.value = 1;
			x.elements.company = '';
			//здесь очистить поля
			[].forEach.call(x.elements.orderStatuses.options,
				function (currentValue)
				{currentValue.selected = (currentValue.value == '1');}
				);
			x.minDeliveryDate.value = '';
			x.maxDeliveryDate.value = '';
			[].forEach.call(x.elements.deliveryDays.options,
				function (currentValue)
				{currentValue.selected = false;}
				);
			[].forEach.call(x.elements.deliveryRegions.options,
				function (currentValue)
				{currentValue.selected = false;}
				);
			x.minReceptionDate.value = '';
			x.maxReceptionDate.value = '';
			x.submit();
		}
		</script>
	</head>
	<body>
		<?php include_once ("../inc/header.php");?>
		<h3>Реестр отправлений</h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;Реестр отправлений</p>
		<form name="filterform" method="POST" class="filterform">
			<input type="hidden" name="filtervals" id="filtervals" value="1"/>
			<input type="hidden" name="sortby" id="sortby" value="<?=$sortby?>"/>
			<input type="hidden" name="back" id="back" value="<?=isset($_POST['back']) ? $_POST['back'] : 1?>"/>
			<input type="hidden" name="rows" id="rows" value="<?=$rows?>"/>
			<input type="hidden" name="page" id="page" value="<?=$page?>"/>
			<div class="tableformpart">
				<h4>Фильтр</h4>
				<div class="gridform">
					<h4>Статус</h4>
					<select id="orderStatuses" name="orderStatuses[]" multiple class="span2" title="Используйте зажатую клавишу Ctrl для выбора нескольких значений или снятия выбора"><?=listOptions("orderstatuses", $connect, (isset($_POST['filtervals'])? $_POST['orderStatuses'] : 1))?></select>
				</div>
				<div class="gridform">
					<h4>Отправитель</h4>
					<input type="text" name="company" value="<?=htmlspecialchars($_POST['company'])?>" class="span2"/>
				</div>
				<div class="gridform">
					<h4>Дата доставки</h4>
					С<input type="date" name="minDeliveryDate" value="<?=htmlspecialchars($_POST['minDeliveryDate'])?>"/>
					по<input type="date" name="maxDeliveryDate" value="<?=htmlspecialchars($_POST['maxDeliveryDate'])?>"/>
					<!--или <select id="deliveryDays"  name="deliveryDays[]" multiple title="Используйте зажатую клавишу Ctrl для выбора нескольких значений или снятия выбора"><?=listOptions("deliveryterms", $connect, $_POST['deliveryDays'])?></select>-->
				</div>
				<div class="gridform">
					<h4>Дата забора</h4>
					С<input type="date" name="minReceptionDate" value="<?=htmlspecialchars($_POST['minReceptionDate'])?>"/>
					по<input type="date" name="maxReceptionDate" value="<?=htmlspecialchars($_POST['maxReceptionDate'])?>"/>
				</div>
			</div>
			<input type="hidden" name="setColumns" value="0"/>
			<?php
				for($i=1;$i<=count($sortfieldnames);$i++)
				{
					echo '<input type="hidden" name="chkColumns['.$i.']" value="'.(in_array($i,$columns) ? '1' : '0').'"/>';
				}
			?>
			<input type="hidden" name="getQuickRunStatuses" value=""/>
			<input type="hidden" name="updateStatuses" value=""/>
			<input type="hidden" name="updateStatusesId" value=""/>
			<input type="button" onclick="clearfilter();" value="Сброс"/>
			<input type="submit" value="Применить"/>
		</form>
		<div class="dialogContainer">
			<form method="POST" name="frmColumns" class="dialog" id="frmColumns">
				<h4>Отображаемые столбцы <button class="closebutton" title="Закрыть диалог" onclick="toggleDialog('frmColumns');"/></h4>
			<?php
				for($i=1;$i<=count($sortfieldnames);$i++)
				{
					echo '<input type="checkbox" name="chkColumns['.$i.']" id="chkColumns['.$i.']" value="1"'.(in_array($i,$columns) ? ' checked' : '').'/><label for="chkColumns['.$i.']">'.$sortfieldnames[$i].'</label>';
				}
			?>
				<input type="button" name="setColumns" value="Применить" onclick="setcolumns();" />
			</form>
			<div class="dialog" id="statusesDialog">
				<h4>Статусы <button class="closebutton" title="Закрыть диалог" onclick="toggleDialog('statusesDialog');"/></h4>
				<p>Выберите действие:</p>
				<button onclick="toggleDialog('statusesDialog');toggleDialog('statusesChangeDialog');">Изменить статусы</button><br/>
				<button onclick="getQuickRunStatuses();">Получить статусы &laquo;Бегунка&raquo;</button>
			</div>
			<div class="dialog" id="statusesChangeDialog">
				<h4>Статусы <button class="closebutton" title="Закрыть диалог" onclick="toggleDialog('statusesChangeDialog');"/></h4>
				<p>Выберите новый статус:</p>
				<select id="newStatus">
				<?=listOptions('orderstatuses', $connect, 1)?>
				</select><br/>
				<button onclick="updateStatuses();">Изменить статусы</button>
			</div>
		</div>
		<div class="paginating print">
			<div class="buttongroup">
				<button onclick="exportForm.submit()" class="export">Экспорт</button>
				<button onclick="toggleFilter();" class="filter" name="btnFilter" type="button">Фильтр</button>
				<button onclick="toggleDialog('frmColumns');" class="columns" name="btnColumns" type="button">Столбцы</button>
				<button onclick="toggleDialog('statusesDialog');" class="statuses" name="btnStatuses" type="button">Статусы</button>
			</div>
			<label for="rowscount">Показывать по: </label>
			<select class="rowscount" onchange="setrows(this.value)">
				<option value="0"<?php if($rows==0) echo ' selected';?>>Все</option>
				<option value="5"<?php if($rows==5) echo ' selected';?>>5</option>
				<option value="10"<?php if($rows==10) echo ' selected';?>>10</option>
				<option value="20"<?php if($rows==20) echo ' selected';?>>20</option>
				<option value="50"<?php if($rows==50) echo ' selected';?>>50</option>
				<option value="100"<?php if($rows==100) echo ' selected';?>>100</option>
			</select>
			<?php
			if ($totalpages>1)
				echo '<div class="pages">'.pages($page, $totalpages).'</div>';
			?>
		</div>
		<table class="register list" width="100%">
			<thead>
				<tr>
					<th class="checkrow" onclick="checkall();"></th>
					<?php
						for($i=1;$i<=count($sortfieldnames);$i++)
						{
							if(in_array($i,$columns))
								echo '<th'.($sortfields[$i]=='address' ? ' width="25%"' :'').'><div class="sortH">'.$sortfieldnames[$i].'&nbsp;'.arrowsPOST($i, $sortby, $direction).'</div></th>';
						}
					?>
				</tr>
			</thead>
			<tbody>
<?php
	$query = "SELECT register.id, register.userid, login, companyName, CONCAT(regionR.code, regionD.code, '-', LPAD(register.id, 7, '0')) as formID, waybill, creationDate, places, weight, sum, IFNULL(deliveryDate, concat(DATE(receptionDate), ' - ', deliveryterms.value)) as deliveryDate,
				concat(LPAD(timeSpanFrom DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanFrom, 2)*30, 2, '0'), ' - ', LPAD(timeSpanTo DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanTo, 2)*30, 2, '0')) as deliveryInterval,
				regionD.value AS deliveryRegion, address, receptionDate, orderstatuses.value as status, deleted,
				CONCAT(regionR.code, regionD.code, '&ndash;', LPAD(register.id, 7, '0')) as rowid,
				CONCAT_WS(' ', IFNULL(quickrun_status, '-'), CONCAT('(на ',DATE_FORMAT(quickrunconnector.updatedOn, '%H:%i %d.%m.%Y'),')')) as quickrun_status
				FROM register
				LEFT JOIN orderstatuses ON register.status = orderstatuses.id
				LEFT JOIN regions as regionD ON register.deliveryRegion = regionD.id
				LEFT JOIN regions as regionR ON register.receptionRegion = regionR.id
				LEFT JOIN deliveryterms ON register.deliveryDay = deliveryterms.id
				LEFT JOIN users ON users.id = register.userid
				LEFT JOIN quickrunconnector ON quickrunconnector.orderid = register.id
				WHERE 1".$and.$sorting.$limit;
	//die($query);
	
	$rowIDs = array();
	if($res = $connect->query($query))
	{
		while($row = $res->fetch_assoc())
		{
			//$deleted= $row['deleted'] ? ' class="deleted"' : '';
			//echo "<tr onclick=\"document.location.href = 'viewregisterline.php?lineid=".$row['id']."'\"$deleted>";
			if ($row['deleted'])
				echo "<tr onclick=\"window.open('viewregisterline.php?lineid=".$row['id']."');\" class=\"deleted\"><td></td>";
			else
				echo "<tr onclick=\"window.open('viewregisterline.php?lineid=".$row['id']."');\"><td class=\"checkrow\" id=\"checkrow_".$row['id']."\" onclick=\"event.stopPropagation(); setcheck(this, ".$row['id'].');"><input type="hidden" name="chkRow['.$row['id'].']" id="chkRow['.$row['id'].']" data-id="'.$row['id'].'" value="0" class="chkRow"></td>';
			for($i=1;$i<=count($sortfields);$i++)
			{
				if(in_array($i,$columns))
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
			echo '</tr>';
			$rowIDs[] = $row['id'];
		}
	}
?>
			</tbody>
		</table>
		<form name="exportForm" method="POST" target="_blank" action="../print/commonregister_excel.php">
			<input name="orderStatuses" type="hidden" value="<?=isset($_POST['filtervals']) ? implode_numbers(';', $_POST['orderStatuses']) : 1?>"/>
			<input name="company" type="hidden" value="<?=$_POST['company']?>"/>
			<input name="minDeliveryDate" type="hidden" value="<?=htmlspecialchars($_POST['minDeliveryDate'])?>"/>
			<input name="maxDeliveryDate" type="hidden" value="<?=htmlspecialchars($_POST['maxDeliveryDate'])?>"/>
			<input name="minReceptionDate" type="hidden" value="<?=htmlspecialchars($_POST['minReceptionDate'])?>"/>
			<input name="maxReceptionDate" type="hidden" value="<?=htmlspecialchars($_POST['maxReceptionDate'])?>"/>
		</form>
<?php
 //phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
 ?>