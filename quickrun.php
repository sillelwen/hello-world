<?php
$dateForRoutes = empty($_POST['dateForRoutes']) ? date('Y-m-d') : $_POST['dateForRoutes'];
$orders = array();

if(!empty($_POST['dateForRoutes']))
{
	// создаем параметры контекста
	$options = array(
		'http' => array(  
					'method'  => 'GET',  // метод передачи данных
					'header'  => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: b47c4bb9-783a-4712-9c43-3f8cdd38cffb"
				)  
	);
	$i = 0;
	do
	{
		$context  = stream_context_create($options);  // создаём контекст потока
		$result = file_get_contents('http://www.quickrun.ru/api/1.0/client/orders/'.$dateForRoutes.'?skip='.($i*50).'&take=50', false, $context); //отправляем запрос
		$result = json_decode($result);

		//var_dump($result);
		//die();
		if($result->success && count($result->result)>0)
		{
			foreach ($result->result as $order)
			{
				$orders[] = array('number'=>$order->number, 'address'=>$order->address, 'latitude'=>$order->coordinate->latitude, 'longitude'=>$order->coordinate->longitude);
			}
		}

		$i++;
	}
	while(count($result->result)==50);
}

if(count($orders))
{
	header('HTTP/1.1 200 OK');
	header('Content-Type: application/force-download');
	header('Content-Description: File Transfer');
	header("Content-Disposition: attachment; filename=\"coordinates_{$dateForRoutes}.xml\"");
	header('Content-Transfer-Encoding: binary');
	
	echo"'<?xml version=\"1.0\"?>\r\n<?mso-application progid=\"Excel.Sheet\"?>";
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
  <Style ss:ID="s77">
   <NumberFormat ss:Format="@"/>
  </Style>
  <Style ss:ID="s78">
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
   <NumberFormat ss:Format="@"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Лист1">
  <Table ss:ExpandedColumnCount="4" ss:ExpandedRowCount="<?= count($orders)+1 ?>" x:FullColumns="1"
   x:FullRows="1" ss:StyleID="s77" ss:DefaultRowHeight="15">
   <Column ss:StyleID="s77" ss:Width="70.5"/>
   <Column ss:StyleID="s77" ss:Width="539.25"/>
   <Column ss:StyleID="s77" ss:Width="50.25" ss:Span="1"/>
   <Row>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Номер заказа</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Адрес</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Широта</Data></Cell>
    <Cell ss:StyleID="s78"><Data ss:Type="String">Долгота</Data></Cell>
   </Row>
   <?php
   foreach($orders as $order)
   {
	?>
   <Row>
    <Cell><Data ss:Type="String"><?=$order['number']?></Data></Cell>
    <Cell><Data ss:Type="String"><?=$order['address']?></Data></Cell>
    <Cell><Data ss:Type="String"><?=$order['latitude']?></Data></Cell>
    <Cell><Data ss:Type="String"><?=$order['longitude']?></Data></Cell>
   </Row>
   <?php
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
</Workbook>
<?php
exit;
}
else
{
?>
<p>Не найдено заказов на <?=$dateForRoutes?></p><hr/>
<?php
}?>
<form method="POST">
Выберите дату для получения списка заказов:<br/>
<input type="date" name="dateForRoutes" value="<?=$dateForRoutes?>"/><br/>
<input type="submit" value="Загрузить"/>
</form>