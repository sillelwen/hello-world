<?php include_once ("../inc/util.php");
if(!isset($_SESSION['userid']))
{
	header("Location: ../login.php");
	die();
}
elseif(!hasNamedRight($_SESSION['userid'], 'viewregisters', $connect))
{
	header("Location: ../index.php");
	die();
}
elseif(!isset($_POST['chkRow']))
{
	header("Location: ../listregisters.php");
	die();
}
$rowIDs = array_keys(array_filter($_POST['chkRow'], function($v) {return $v == 1;}));
if(count($rowIDs)==0)
	$rowIDs = array_keys($_POST['chkRow']);
if(!checkUserRegisterRowsMatch($connect, $rowIDs))
	die('<h2>Печать реестра невозможна.</h2><p>Вероятно, для печати выбраны отправления с разной датой забора или с различными статусами.</p>');
if(!($res = getUserRegisterRowsByIDs($connect, $rowIDs)))
{
	header("Location: ../listregisters.php");
	die();
}

$showstatus = hasNamedRight($_SESSION['userid'], 'viewstatus', $connect);


$sortfields = [1=>'waybill', 2=>'rowid', 3=>'receptionDate', 4=>'deliveryDate', 5=>'weight', 6=>'places', 7=>'dimensions', 8=>'address', 9=>'deliveryInterval', 10=>'paymentType', 11=>'sum', 12=>'note'];
$sortfieldnames = [1=>'Номер заказа', 2=>'ID печатной формы', 3=>'Дата забора', 4=>'Дата доставки', 5=>'Вес (кг)', 6=>'Кол-во мест', 7=>'Габариты (см)', 8=>'Адрес', 9=>'Время доставки', 10=>'Тип оплаты', 11=>'Сумма с клиента', 12=>'Примечание'];
$formatfields = [1=>'', 2=>'', 3=>'date', 4=>'date', 5=>1, 6=>0, 7=>'', 8=>'', 9=>'', 10=>'', 11=>2, 12=>''];
$fieldswith = [1=>'', 2=>'', 3=>'', 4=>'', 5=>'4%', 6=>'4%', 7=>'', 8=>'20%', 9=>'', 10=>'', 11=>'5%', 12=>'15%'];
//$fieldswith = [1=>'', 2=>'', 3=>'', 4=>'', 5=>'11', 6=>'11', 7=>'', 8=>'56', 9=>'', 10=>'', 11=>'14', 12=>'42'];

?>

<!DOCTYPE html>
<html lang="ru-Ru">
	<head>
		<title>Личный кабинет - Печать элементов реестра</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<style>
		* {
			font-family: serif;
			font-size: 3.7mm;

    -webkit-hyphens: auto;
    -webkit-hyphenate-limit-before: 3;
    -webkit-hyphenate-limit-after: 3;
    -webkit-hyphenate-limit-chars: 6 3 3;
    -webkit-hyphenate-limit-lines: 2;
    -webkit-hyphenate-limit-last: always;   
    -webkit-hyphenate-limit-zone: 8%;

    -moz-hyphens: auto;
    -moz-hyphenate-limit-chars: 6 3 3;
    -moz-hyphenate-limit-lines: 2;  
    -moz-hyphenate-limit-last: always;
    -moz-hyphenate-limit-zone: 8%;

    -ms-hyphens: auto;
    -ms-hyphenate-limit-chars: 6 3 3;
    -ms-hyphenate-limit-lines: 2;
    -ms-hyphenate-limit-last: always;   
    -ms-hyphenate-limit-zone: 8%;

    hyphens: auto;
    hyphenate-limit-chars: 6 3 3;
    hyphenate-limit-lines: 2;
    hyphenate-limit-last: always;   
    hyphenate-limit-zone: 8%;
		}

		table {
			border-collapse: collapse;
			width: 27cm;
			border-spacing: 0;
			table-layout: fixed;
		}
		td, th {
			vertical-align: top;
			text-align: left;
			padding: 0 1mm;
			overflow-wrap: break-word;
			hyphens: auto;
		}
		table tbody tr.regline td {
			border: 1px solid black;
		}
		tr.wide td {
			padding-top: 0.7cm;
		}
		
		button, input {
			background-color: white;
			border-color: white;
			padding: 2.5px;
		}
		
		button#btnPrint {
			position: absolute;
			left: calc(27cm - 20px);
			height: 30px;
			width: 30px;
		}
		
		@media print {
			form, button {
				display: none;
				height: 0;
				overflow: hidden
			}
			
			@page {
				size: A4 landscape;
				margin: 0.5cm;
			}
		}
		
		form {
			display: none;
			grid-template-columns: auto min-content;
			width: 27cm;
			padding-top: 15px;
		}
				
		div.valueslist {
			display: grid;
			grid-template-columns: min-content max-content;
			grid-auto-rows: min-content;
			margin: auto;
			grid-column: 2;
		}
		
		div.valueslist h4, div.valueslist input[type="submit"] {
			grid-column: span 2;
		}
		</style>
	</head>
	<body>
	<button id="btnPrint" onclick="window.print();"><img src="../style/i/printer_icon_003.png" width="20px"/></button>
	<?php
		printregister_settings($connect, 'chkRow');
		$columns = explode(';', getUserSettingStr('columns_print', $connect, COLUMNS_PRINT_DEFAULT));
		$columnsW = explode(';', getUserSettingStr('columns_width', $connect, COLUMNS_WIDTH_DEFAULT));
	?>
	<p>Реестр заказов <?=$_SESSION['companyName']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Дата забора: <?=$_POST['receptionDate']?></p>
	<table>
		<thead>
			<tr>
			<?php
				for($i=1;$i<=count($sortfieldnames);$i++)
				{
					if(in_array($i,$columns))
						echo '<th'.($columnsW[$i]!='' ? ' style="width:'.$columnsW[$i].'mm"' :'').'>'.$sortfieldnames[$i].'</th>';
				}
			?>
		</thead>
		<tbody>
<?php
	while($row = $res->fetch_assoc())
	{
		echo '<tr class="regline">';
		for($i=1;$i<=count($sortfields);$i++)
		{
			if(in_array($i,$columns))
			{
				if(is_numeric($formatfields[$i]))
					$val = number_format($row[$sortfields[$i]], $formatfields[$i], ',', ' ');
				elseif($formatfields[$i]='date')
					$val = dateForPrint($row[$sortfields[$i]]);
				else
					$val = $row[$sortfields[$i]];
				echo '<td>'.$val.'</td>';
			}
		}
		echo '</tr>';
	}
?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="<?=count($columns)?>">
					<table>
						<col width="5"/>
						<col width="3"/>
						<tr class="wide"><td>Заказы по списку переданы, информация указана верно</td><td>Заказы по списку приняты в доставку</td></tr>
						<tr class="wide"><td>_____________________________</td><td>_____________________________</td></tr>
					</table>
				</td>
			</tr>
		</tfoot>
	</table>
	</body>
</html>

<?php
 //phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
?>