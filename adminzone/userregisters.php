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
	$companyName = $row['companyName'];
}
else
{
	header("Location: userlist.php");
	die();
}
if(isset($_POST['filtervals']))
{
	$filter == '';
	if(!empty($_POST['minPlaces']) && !empty($_POST['maxPlaces']))
		$filter .= ' AND places BETWEEN '.intval($_POST['minPlaces']).' AND '.intval($_POST['maxPlaces']);
	elseif(!empty($_POST['minPlaces']))
		$filter .= ' AND places >= '.intval($_POST['minPlaces']);
	elseif(!empty($_POST['maxPlaces']))
		$filter .= ' AND places <= '.intval($_POST['maxPlaces']);

	if(!empty($_POST['minWeight']) && !empty($_POST['maxWeight']))
		$filter .= ' AND weight BETWEEN '.prepNum($_POST['minWeight']).' AND '.prepNum($_POST['maxWeight']);
	elseif(!empty($_POST['minWeight']))
		$filter .= ' AND weight >= '.prepNum($_POST['minWeight']);
	elseif(!empty($_POST['maxWeight']))
		$filter .= ' AND weight <= '.prepNum($_POST['maxWeight']);

	if(!empty($_POST['minSum']) && !empty($_POST['maxSum']))
		$filter .= ' AND sum BETWEEN '.prepNum($_POST['minSum']).' AND '.prepNum($_POST['maxSum']);
	elseif(!empty($_POST['minSum']))
		$filter .= ' AND sum >= '.prepNum($_POST['minSum']);
	elseif(!empty($_POST['maxSum']))
		$filter .= ' AND sum <= '.prepNum($_POST['maxSum']);

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

	if(!empty($_POST['timeSpanFrom']) && !empty($_POST['timeSpanTo']))
		$filter .= ' AND timeSpanFrom <= '.intval($_POST['timeSpanTo']).' AND timeSpanTo >='.intval($_POST['timeSpanFrom']);
		
	if(!empty($_POST['deliveryRegions']) && count($_POST['deliveryRegions'])>0)
		$filter .= ' AND deliveryRegion IN ('.implode_numbers(', ', $_POST['deliveryRegions']).')';
	
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

$link = "/adminzone/userregisters.php?userid=$userid&";
$search = '';
if(isset($_GET['search']))
{
	$search = prepStr($_GET['search']);
	$link .= 'search='.urlencode($search).'&';
}
$sortfields = [1=>'status', 2=>'address', 3=>'waybill', 4=>'places', 5=>'weight', 6=>'sum', 7=>'deliveryDate', 8=>'deliveryInterval', 9=>'deliveryRegion', 10=>'receptionDate', 11=>'id'];
$sortby = (isset($_POST['sortby']) && array_key_exists($_POST['sortby'], $sortfields)) ? $_POST['sortby'] : 1;
$sortfield = $sortfields[$sortby];
$direction = (!isset($_POST['back']) || $_POST['back']==1) ? 'DESC' : 'ASC';
$rows = isset($_POST['rows']) && is_numeric($_POST['rows']) ? $_POST['rows'] : $_SESSION['rows'];
updateUserSettingRows($rows, $connect);
$page = isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 1;

$and = ($search=='') ? '' : " AND (waybill LIKE '%".$search."%' OR address LIKE '%".$search."%' OR addressee LIKE '%".$search."%' OR contactFIO LIKE '%".$search."%')";
$and .= $filter;
$sorting = " ORDER BY $sortfield $direction";

//получаем общее количество страниц, соответствующих условиям
if($rows>0)
{
	$querycount = "SELECT count(*) as totalrows FROM register WHERE userid=".$userid.$and;
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
		<title>Администрирование - Отправления пользователя</title>
		<script>
		
		function toggleFilter()
		{
			document.getElementsByName('btnFilter')[0].classList.toggle('grey');
			filterform.classList.toggle('hidden');
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
			//здесь очистить поля
			[].forEach.call(x.elements.orderStatuses.options,
				function (currentValue)
				{currentValue.selected = false;}
				);
			x.minPlaces.value = '';
			x.maxPlaces.value = '';
			x.minWeight.value = '';
			x.maxWeight.value = '';
			x.minSum.value = '';
			x.maxSum.value = '';
			x.minDeliveryDate.value = '';
			x.maxDeliveryDate.value = '';
			[].forEach.call(x.elements.deliveryDays.options,
				function (currentValue)
				{currentValue.selected = false;}
				);
			x.timeSpanFrom.selectedIndex = 0;
			x.timeSpanTo.selectedIndex = x.timeSpanTo.options.length-1;
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
		<h3>Отправления пользователя <?php echo($login)?></h3>
		<p><a href="../adminzone.php">Администрирование</a>&nbsp;&gt;&nbsp;<a href="userlist.php">Пользователи</a>&nbsp;&gt;&nbsp;Реестры и отправления пользователя</p>
		<form method="GET" name="searchbox">
		<input type="hidden" name="userid" value="<?php echo $userid;?>"/>
		<input type="search" name="search" type="search" onsearch="searchbox.submit();" style="width:250px" placeholder="Поиск отправления" value="<?php echo $search;?>"/><input type="submit" value="Найти" class="search"/>
		</form>
		<form name="filterform" method="POST" class="filterform">
			<input type="hidden" name="filtervals" id="filtervals" value="<?=$filtervals?>"/>
			<input type="hidden" name="sortby" id="sortby" value="<?=$sortby?>"/>
			<input type="hidden" name="back" id="back" value="<?=isset($_POST['back']) ? intval($_POST['back']) : 1?>"/>
			<input type="hidden" name="rows" id="rows" value="<?=$rows?>"/>
			<input type="hidden" name="page" id="page" value="<?=$page?>"/>
			<div class="tableformpart">
				<h4>Фильтр</h4>
				<div class="gridform">
					<h4>Статус</h4>
					<select id="orderStatuses" name="orderStatuses[]" multiple class="span2" title="Используйте зажатую клавишу Ctrl для выбора нескольких значений или снятия выбора"><?=listOptions("orderstatuses", $connect, (isset($_POST['filtervals'])? $_POST['orderStatuses'] : 1))?></select>
					<h4>Количество мест</h4>
					<span>От <input type="number" name="minPlaces" min="1" max="50" value="<?=htmlspecialchars($_POST['minPlaces'])?>"/></span>
					<span>до <input type="number" name="maxPlaces" min="1" max="50" value="<?=htmlspecialchars($_POST['maxPlaces'])?>"/></span>
					<h4>Вес (кг)</h4>
					<span>От <input type="number" name="minWeight" min="0" max="200" step="0.1" value="<?=htmlspecialchars($_POST['minWeight'])?>"/></span>
					<span>до <input type="number" name="maxWeight" min="0" max="200" step="0.1" value="<?=htmlspecialchars($_POST['maxWeight'])?>"/></span>
					<h4>Сумма с получателя</h4>
					<span>От <input type="number" name="minSum" min="0" max="100000000" maxlength="9" step="0.01" value="<?=htmlspecialchars($_POST['minSum'])?>"/></span>
					<span>до <input type="number" name="maxSum" min="0" max="100000000" maxlength="9" step="0.01" value="<?=htmlspecialchars($_POST['maxSum'])?>"/></span>
				</div>
				<div class="gridform">
					<h4>Дата доставки</h4>
					С<input type="date" name="minDeliveryDate" value="<?=htmlspecialchars($_POST['minDeliveryDate'])?>"/>
					по<input type="date" name="maxDeliveryDate" value="<?=htmlspecialchars($_POST['maxDeliveryDate'])?>"/>
					или <select id="deliveryDays"  name="deliveryDays[]" multiple title="Используйте зажатую клавишу Ctrl для выбора нескольких значений или снятия выбора"><?=listOptions("deliveryterms", $connect, $_POST['deliveryDays'])?></select>
					<h4>Интервал доставки</h4>					
					<span>C <?=buildTimeList("timeSpanFrom", $_POST['timeSpanFrom'], 'from')?></span>
					<span>до <?=buildTimeList("timeSpanTo", $_POST['timeSpanTo'], 'to')?></span>
				</div>
				<div class="gridform">
					<h4>Регион доставки</h4>
					<select id="deliveryRegions" name="deliveryRegions[]" multiple class="span2" title="Используйте зажатую клавишу Ctrl для выбора нескольких значений или снятия выбора"><?=listRegions($connect, false, true, $_POST['deliveryRegions'])?></select>
					<h4>Дата забора</h4>
					С<input type="date" name="minReceptionDate" value="<?=htmlspecialchars($_POST['minReceptionDate'])?>"/>
					по<input type="date" name="maxReceptionDate" value="<?=htmlspecialchars($_POST['maxReceptionDate'])?>"/>
				</div>
			</div>
			<input type="button" onclick="clearfilter();" value="Сброс"/>
			<input type="submit" value="Применить"/>
		</form>
		<div class="paginating print">
			<div class="buttongroup">
				<!--button onclick="" class="print">Печать</button-->
				<button onclick="exportForm.submit()" class="export">Экспорт</button>
				<button onclick="toggleFilter();" class="filter<?=count($filtered)>0 ? ' grey' :''?>" name="btnFilter" type="button">Фильтр</button>
			</div>
			<label for="rowscount">Показывать на странице: </label>
			<select class="rowscount" onchange="setrows(this.value)">
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
				<th><div class="sortH">Статус<?=arrowsPOST(1, $sortby, $direction)?></div></th>
				<th><div class="sortH">Адрес<?=arrowsPOST(2, $sortby, $direction)?></div></th>
				<th><div class="sortH">Номер заказа<?=arrowsPOST(3, $sortby, $direction)?></div></th>
				<th><div class="sortH">Кол-во мест<?=arrowsPOST(4, $sortby, $direction)?></div></th>
				<th><div class="sortH">Вес (кг)<?=arrowsPOST(5, $sortby, $direction)?></div></th>
				<th><div class="sortH">Сумма<?=arrowsPOST(6, $sortby, $direction)?></div></th>
				<th><div class="sortH">Дата доставки<?=arrowsPOST(7, $sortby, $direction)?></div></th>
				<th><div class="sortH">Интервал доставки<?=arrowsPOST(8, $sortby, $direction)?></div></th>
				<th><div class="sortH">Регион доставки<?=arrowsPOST(9, $sortby, $direction)?></div></th>
				<th><div class="sortH">Дата забора<?=arrowsPOST(10, $sortby, $direction)?></div></th>
				<th><div class="sortH">ID печатной формы<?=arrowsPOST(11, $sortby, $direction)?></div></th>
			</thead>
			<tbody>
<?php
	$query = "SELECT register.id, CONCAT(regionR.code, regionD.code, '-', LPAD(register.id, 7, '0')) as formID, waybill, creationDate, places, weight, sum, IFNULL(deliveryDate, concat(DATE(receptionDate), ' - ', deliveryterms.value)) as deliveryDate,
				concat(LPAD(timeSpanFrom DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanFrom, 2)*30, 2, '0'), ' - ', LPAD(timeSpanTo DIV 2, 2, '0'), ':', LPAD(MOD(timeSpanTo, 2)*30, 2, '0')) as deliveryInterval,
				regionD.value AS deliveryRegion, address, receptionDate, orderstatuses.value as status, deleted FROM register
				LEFT JOIN orderstatuses ON register.status = orderstatuses.id
				LEFT JOIN regions as regionD ON register.deliveryRegion = regionD.id
				LEFT JOIN regions as regionR ON register.receptionRegion = regionR.id
				LEFT JOIN deliveryterms ON register.deliveryDay = deliveryterms.id
				WHERE userid=".$userid.$and.$sorting.$limit;
	//die($query);
	
	$rowIDs = array();
	if($res = $connect->query($query))
	{
		while($row = $res->fetch_assoc())
		{
			$deleted= $row['deleted'] ? ' class="deleted"' : '';
			echo "<tr onclick=\"document.location.href = 'viewregisterline.php?lineid=".$row['id']."'\"$deleted>
					<td>".htmlspecialchars($row['status']).'</td>
					<td>'.htmlspecialchars($row['address']).'</td>
					<td>'.htmlspecialchars($row['waybill']).'</td>
					<td>'.$row['places'].'</td>
					<td>'.number_format($row['weight'],1,',',' ').'</td>
					<td>'.number_format($row['sum'],2,',',' ').'</td>
					<td>'.dateForPrint($row['deliveryDate']).'</td>
					<td>'.$row['deliveryInterval'].'</td>
					<td>'.htmlspecialchars($row['deliveryRegion']).'</td>
					<td>'.dateForPrint($row['receptionDate']).'</td>
					<td>'.$row['formID'].'</td>
					</tr>';
			$rowIDs[] = $row['id'];
		}
	}
	$dates = '';
	if(!empty($_POST['minReceptionDate']))
		$dates .= 'с '.date_format(date_create($_POST['minReceptionDate']), 'd.m.Y');
	if(!empty($_POST['maxReceptionDate']))
		$dates .= (!empty($dates)?' ':'').'по '.date_format(date_create($_POST['maxReceptionDate']), 'd.m.Y');
?>
			</tbody>
		</table>
		<form name="exportForm" method="POST" target="_blank" action="../print/register_excel.php">
			<input type="hidden" name="rowIDs" value="<?=implode(';',$rowIDs)?>"/>
			<input type="hidden" name="companyName" value="<?=htmlspecialchars($companyName)?>"/>
			<input type="hidden" name="dates" value="<?=$dates?>"/>
		</form>
<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
 include_once ("../inc/footer.php");
 ?>