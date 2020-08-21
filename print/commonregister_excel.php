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

$filename = "commonregister_excel.xml";
header('HTTP/1.1 200 OK');
header('Content-Type: application/force-download');
header('Content-Description: File Transfer');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Content-Transfer-Encoding: binary');

$orderStatuses = explode_numbers(';', $_POST['orderStatuses']);
$companyName = $_POST['company'];
$minReceptionDate = $_POST['minReceptionDate'];
$maxReceptionDate = $_POST['maxReceptionDate'];
$minDeliveryDate = $_POST['minDeliveryDate'];
$maxDeliveryDate = $_POST['maxDeliveryDate'];

$orderStatusNames = getValueNames('orderstatuses', $connect, $orderStatuses);

$rows=array();
$deliveryDates=array();

if($res = getCommonRegisterRows($connect, $orderStatuses, $companyName, $minReceptionDate, $maxReceptionDate, $minDeliveryDate, $maxDeliveryDate))
	while($row=$res->fetch_assoc())
	{
		$rows[] = $row;
		if(!in_array($row['deliveryDate'], $deliveryDates))
			$deliveryDates[] = $row['deliveryDate'];
	}

echo '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>';
?>

<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Dedal</Author>
  <LastAuthor>Dedal</LastAuthor>
  <Created><?=date('c')?></Created>
  <Version>16.00</Version>
 </DocumentProperties>
 <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
  <AllowPNG/>
 </OfficeDocumentSettings>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
<!--  <WindowHeight>12300</WindowHeight>
  <WindowWidth>28800</WindowWidth>-->
  <WindowTopX>0</WindowTopX>
  <WindowTopY>0</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s72">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat ss:Format="@"/>
  </Style>
  <Style ss:ID="s73">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat ss:Format="0.0"/>
  </Style>
  <Style ss:ID="s74">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat ss:Format="#,##0.00\ &quot;₽&quot;"/>
  </Style>
  <Style ss:ID="s75">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat ss:Format="0"/>
  </Style>
  <Style ss:ID="s78">
   <Alignment ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s79">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat ss:Format="dd/mm/yy;@"/>
  </Style>
  <Style ss:ID="s62">
   <Alignment ss:Horizontal="Right" ss:Vertical="Bottom"/>
  </Style>
  <Style ss:ID="s63">
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
 </Styles>
 
 <Worksheet ss:Name="Установки">
  <Table ss:ExpandedColumnCount="4" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:Width="154.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="75.75" ss:Span="2"/>
   <Row>
	<Cell ss:StyleID="s63"><Data ss:Type="String">Статус</Data></Cell>
	<Cell ss:StyleID="s63"><Data ss:Type="String">Отправитель</Data></Cell>
	<Cell ss:StyleID="s63"><Data ss:Type="String">Даты доставки</Data></Cell>
	<Cell ss:StyleID="s63"><Data ss:Type="String">Даты забора</Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
	<Cell><Data ss:Type="String"><?=htmlspecialchars($orderStatusNames[$orderStatuses[0]], ENT_XML1, 'UTF-8')?></Data></Cell>
	<Cell><Data ss:Type="String"><?=htmlspecialchars($companyName, ENT_XML1, 'UTF-8')?></Data></Cell>
	<Cell ss:StyleID="s62"><Data ss:Type="String"><?=!empty($minDeliveryDate) ? 'с '.dateForPrint($minDeliveryDate) : ''?></Data></Cell>
	<Cell ss:StyleID="s62"><Data ss:Type="String"><?=!empty($minReceptionDate) ? 'с '.dateForPrint($minReceptionDate) : ''?></Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
	<Cell><Data ss:Type="String"><?=htmlspecialchars($orderStatusNames[$orderStatuses[1]], ENT_XML1, 'UTF-8')?></Data></Cell>
	<Cell><Data ss:Type="String"></Data></Cell>
	<Cell ss:StyleID="s62"><Data ss:Type="String"><?=!empty($maxDeliveryDate) ? 'по '.dateForPrint($maxDeliveryDate) : ''?></Data></Cell>
	<Cell ss:StyleID="s62"><Data ss:Type="String"><?=!empty($maxReceptionDate) ? 'по '.dateForPrint($maxReceptionDate) : ''?></Data></Cell>
   </Row>
   <?php
   for($i=2; $i<count($orderStatuses); $i++)
	   echo '<Row ss:AutoFitHeight="0">
	<Cell><Data ss:Type="String">'.htmlspecialchars($orderStatusNames[$orderStatuses[$i]], ENT_XML1, 'UTF-8').'</Data></Cell>
	<Cell><Data ss:Type="String"></Data></Cell>
	<Cell><Data ss:Type="String"></Data></Cell>
	<Cell><Data ss:Type="String"></Data></Cell>
   </Row>';
   ?>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <Unsynced/>
   <Print>
    <ValidPrinterInfo/>
    <PaperSizeIndex>9</PaperSizeIndex>
    <HorizontalResolution>600</HorizontalResolution>
    <VerticalResolution>600</VerticalResolution>
   </Print>
   <Selected/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>6</ActiveRow>
     <ActiveCol>9</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <?php
 sort($deliveryDates);
 foreach($deliveryDates as $deliveryDate)
 {
?>
 <Worksheet ss:Name="<?=dateForPrint($deliveryDate)?>">
  <Table ss:ExpandedColumnCount="23" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="56.25"/>
   <Column ss:Width="59.25"/>
   <Column ss:Width="70.5"/>
   <Column ss:Width="38.25"/>
   <Column ss:Width="36"/>
   <Column ss:Index="8" ss:Width="40.5"/>
   <Column ss:Width="67.5"/>
   <Column ss:Index="11" ss:Width="51.75"/>
   <Column ss:Width="91.5"/>
   <Column ss:Width="60.75" ss:Span="1"/>
   <Column ss:Index="15" ss:Width="50.25"/>
   <Column ss:Width="78"/>
   <Column ss:Width="60.75"/>
   <Column ss:Width="66"/>
   <Column ss:Index="23" ss:Width="50.25"/>
   <Row ss:Height="45">
    <Cell ss:StyleID="s78"><Data ss:Type="String">Статус</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Адрес &#10;получателя</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Номер &#10;заказа (от отправителя)</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Кол-во мест</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Вес, кг</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Город доставки</Data></Cell>
    <Cell ss:StyleID="s78"/>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Тип оплаты</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Сумма &#10;с получателя</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Дата &#10;доставки</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Интервал &#10;доставки</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Дополнительный телефон</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Телефон &#10;получателя</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">ФИО &#10;получателя</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">ID &#10;печатной &#10;формы</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Наименование&#10;отправителя</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Габариты</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Примечание</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Регион &#10;доставки</Data></Cell>
    <Cell ss:StyleID="s78"/>
    <Cell ss:StyleID="s78"/>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Дата &#10;забора</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Компания-&#10;получатель</Data></Cell>
   </Row>
   <?php
	//$daterows = array_filter($rows, function($row) {return ($row['deliveryDate'] == $deliveryDate);});
	foreach($rows as $row)
	{
		if($row['deliveryDate'] == $deliveryDate)
		{
	?>
   <Row>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['status'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['address'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['waybill'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="Number"><?=$row['places']?></Data></Cell>
    <Cell ss:StyleID="s73"><Data ss:Type="Number"><?=$row['weight']?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['deliveryCity'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"/>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['paymentType'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s75"><Data ss:Type="Number"><?=$row['sum']?></Data></Cell>
    <Cell ss:StyleID="s79"><Data ss:Type="String"><?=dateForPrint($row['deliveryDate'])?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['deliveryInterval'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['contactPhone2'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['contactPhone1'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['contactFIO'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=$row['receptionRegionCode'].$row['deliveryRegionCode'].'-'.str_pad($row['id'],7,'0', STR_PAD_LEFT)?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['companyName'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=$row['dimensions']?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['note'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['deliveryRegion'], ENT_XML1, 'UTF-8')?></Data></Cell>
    <Cell ss:StyleID="s72"/>
    <Cell ss:StyleID="s72"/>
    <Cell ss:StyleID="s79"><Data ss:Type="String"><?=dateForPrint($row['receptionDate'])?></Data></Cell>
    <Cell ss:StyleID="s72"><Data ss:Type="String"><?=htmlspecialchars($row['addressee'], ENT_XML1, 'UTF-8')?></Data></Cell>
   </Row>
	<?php
		}
	}   
	?>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <Print>
    <ValidPrinterInfo/>
    <PaperSizeIndex>9</PaperSizeIndex>
    <HorizontalResolution>600</HorizontalResolution>
    <VerticalResolution>600</VerticalResolution>
   </Print>
   <Selected/>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
<?php
 }
 ?>
</Workbook>
