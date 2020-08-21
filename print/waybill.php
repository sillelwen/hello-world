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

if(!empty($_POST['chkRow']))
{
	$rowIDs = array_keys(array_filter($_POST['chkRow'], function($v) {return $v == 1;}));
	if(count($rowIDs)==0)
		$rowIDs = array_keys($_POST['chkRow']);	
}
elseif(!empty($_GET['lineid']))
{
	$rowIDs[] = $_GET['lineid'];
}

if(empty($rowIDs) || count($rowIDs)==0)
{
	header("Location: ../listregisters.php");
	die();
}
if(!($res = getUserRegisterRowsByIDs($connect, $rowIDs)) || $res->num_rows==0)
{
	header("Location: ../listregisters.php");
	die();
}
?>

<!DOCTYPE html>
<html>
	<head>
		<link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<style>
table.waybill {
	font-family: 'Open Sans', sans-serif;
	font-size: 3.5mm;
	border-collapse: collapse;
	width: 19cm;
	border-spacing: 0;
	margin-bottom: 30px;
	
}
table.waybill tr {
	vertical-align: bottom;
}
table.waybill td {
	border: 3px double black;
	padding-top: 1.5mm;
	padding-left: 2.5mm;
	padding-right: 2.5mm;
}
table.waybill tr:first-child td {
	border-top: 2px solid black;
}
table.waybill tr:last-child td {
	border-bottom: 2px solid black;
}
table.waybill tr td:first-child {
	border-left: 2px solid black;
}
table.waybill tr td:last-child {
	border-right: 2px solid black;
}
table.waybill td.small {
	font-size: 3.2mm;
}
table.waybill td.date {
	text-align: right;
}
table.waybill td.nr {
	font-size: 6.4mm;
	font-weight: bold;
	padding-left: 5mm;
}
table.waybill td.sum, td.right {
	text-align: right;
}
table.waybill td.sum big {
	font-size: 5.6mm;
	font-weight: bold;
}
table.waybill td[colspan="6"] {
	font-weight: bold;
	font-size: 4.2mm;
}
table.waybill *.bold {
	border: 2px solid black;
}
table.waybill tr.high {
	height: 16mm;
}
table.waybill tr.high td {
	border-bottom: 2px solid black;
}
table.waybill tr td.nospace {
	padding-left: 1mm;
	padding-right: 1mm;
}
button, input {
	background-color: white;
	border-color: white;
	padding: 2.5px;
}
button#btnPrint {
	position: absolute;
	left: 19.5cm;
	height: 30px;
	width: 30px;
}
@media print {
	form, button {
		display: none;
		height:0;
		overflow: hidden
	}

	@page {
		size: A4 portrait;
		margin: 1.3cm;
	}
	
	table.waybill {
		margin-bottom: auto;
		page-break-after: always;
	}
}
		</style>
	</head>
	<body>
	<button id="btnPrint" onclick="window.print();"><img src="../style/i/printer_icon_003.png" width="20px"/></button>
<?php

while($row = $res->fetch_assoc())
{
	$waybill = htmlspecialchars($row['waybill']);
	$receptionDate = $row['receptionDate'];
	$addressee = htmlspecialchars($row['addressee']);
	$address = str_replace("\n", "<br/>", htmlspecialchars($row['deliveryAddress']));
	$contactFIO = str_replace("\n", "<br/>", htmlspecialchars($row['contactFIO']));
	$contactPhone = htmlspecialchars($row['contactPhone1']).((!empty($row['contactPhone1']) && !empty($row['contactPhone2'])) ? ', ': '').htmlspecialchars($row['contactPhone2']);
	$places = $row['places'];
	$weight = number_format($row['weight'],1,',',' ');
	$deliveryDay = htmlspecialchars($row['deliveryDay']);
	$deliveryDate = $row['deliveryDate'];
	$timeSpan = $row['deliveryInterval'];
	$paymentType = htmlspecialchars($row['paymentType']);
	$sum = $row['sum'];
	$note = str_replace("\n", "<br/>", htmlspecialchars($row['note']));
	$creationDate = $row['creationDate'];
	$status = htmlspecialchars($row['status']);
	$receptionRegion = htmlspecialchars($row['receptionRegion']);
	$deliveryRegion = htmlspecialchars($row['deliveryRegion']);
	$receptionRegionCode = $row['receptionRegionCode'];
	$deliveryRegionCode = $row['deliveryRegionCode'];
	$dimensions = $row['dimensions'];
	$receptionCity = htmlspecialchars($row['receptionCity']);
	$deliveryCity = htmlspecialchars($row['deliveryCity']);
	$receptionAddress = htmlspecialchars($row['receptionAddress']);
	$receptionContactFIO = htmlspecialchars($row['receptionContactFIO']);
	$rowID=$row['rowid'];

	if($row['simple'] == 1)
		include('waybill_simple.php');
	else
		include('waybill_full.php');
}
 ?>
	</body>
</html>

<?php
//phpinfo(32);
 //закрываем соединение с БД
 $connect->close();
?>