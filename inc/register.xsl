<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="Register">
<xsl:processing-instruction name="mso-application"> progid="Excel.Sheet"</xsl:processing-instruction>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
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
   <Font ss:FontName="Arial"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="m1004637760">
   <Alignment ss:Horizontal="Center" ss:Vertical="Top"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Color="#000000"
    ss:Bold="1" ss:Italic="1"/>
   <Interior ss:Color="#4F81BD" ss:Pattern="Solid"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s16">
   <Alignment ss:Vertical="Top"/>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s17">
   <Alignment ss:Horizontal="Left" ss:Vertical="Top"/>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s18">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s20">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s21">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s28">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Color="#000000"
    ss:Bold="1"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s32">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Size="8"
    ss:Color="#000000"/>
   <Interior ss:Color="#FF9900" ss:Pattern="Solid"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s33">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Size="8"
    ss:Color="#000000"/>
   <Interior ss:Color="#FF9900" ss:Pattern="Solid"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s34">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Size="8"
    ss:Color="#000000"/>
   <Interior ss:Color="#FF9900" ss:Pattern="Solid"/>
   <NumberFormat/>
  </Style>
  <Style ss:ID="s35">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Color="#000000"/>
   <NumberFormat/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Лист 1">
  <Table ss:ExpandedColumnCount="14" ss:ExpandedRowCount="9" x:FullColumns="1"
   x:FullRows="1" ss:StyleID="s16">
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="87.75"/>
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="127.5" ss:Span="1"/>
   <Column ss:Index="4" ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="126.75"/>
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="103.5"/>
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="105.75"/>
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="58.5"/>
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="36.75"/>
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="64.5"/>
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="68.25"/>
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="77.25"/>
   <Column ss:StyleID="s16" ss:AutoFitWidth="0" ss:Width="61.5"/>
   <Row ss:AutoFitHeight="0" ss:Height="21">
    <Cell ss:MergeAcross="11" ss:StyleID="m1004637760"><Data ss:Type="String">Реестр отправленных грузов  компании        <xsl:value-of select="companyName"/></Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0" ss:Height="39.75">
    <Cell ss:StyleID="s32"><Data ss:Type="String">Номер накладной</Data></Cell>
    <Cell ss:StyleID="s33"><Data ss:Type="String">Компания получатель</Data></Cell>
    <Cell ss:StyleID="s33"><Data ss:Type="String">Почтовый адес получателя</Data></Cell>
    <Cell ss:StyleID="s33"><Data ss:Type="String">Контактное лицо (Ф.И.О.)</Data></Cell>
    <Cell ss:StyleID="s33"><Data ss:Type="String">Телефон раб/дом</Data></Cell>
    <Cell ss:StyleID="s32"><Data ss:Type="String">Телефон моб.</Data></Cell>
    <Cell ss:StyleID="s32"><Data ss:Type="String"> Кол-во мест</Data></Cell>
    <Cell ss:StyleID="s32"><Data ss:Type="String">Вес (кг)</Data></Cell>
    <Cell ss:StyleID="s32"><Data ss:Type="String">Срочность</Data></Cell>
    <Cell ss:StyleID="s34"><Data ss:Type="String">Оплата нал, б/н</Data></Cell>
    <Cell ss:StyleID="s34"><Data ss:Type="String">Сумма с клиента</Data></Cell>
    <Cell ss:StyleID="s34"><Data ss:Type="String">Примечание</Data></Cell>
   </Row>
   <xsl:for-each select="registerRow">
	   <Row ss:AutoFitHeight="0" ss:Height="39.75">
		<Cell ss:StyleID="s28"><Data ss:Type="String"><xsl:value-of select="waybill"/></Data></Cell>
		<Cell ss:StyleID="s20"><Data ss:Type="String"><xsl:value-of select="addressee"/></Data></Cell>
		<Cell ss:StyleID="s18"><Data ss:Type="String"><xsl:value-of select="address"/></Data></Cell>
		<Cell ss:StyleID="s21"><Data ss:Type="String"><xsl:value-of select="contactFIO"/></Data></Cell>
		<Cell ss:StyleID="s35"><Data ss:Type="String"><xsl:value-of select="contactPhone"/></Data></Cell>
		<Cell ss:StyleID="s35"><Data ss:Type="String"><xsl:value-of select="contactMobile"/></Data></Cell>
		<Cell ss:StyleID="s21"><Data ss:Type="Number"><xsl:value-of select="places"/></Data></Cell>
		<Cell ss:StyleID="s21"><Data ss:Type="Number"><xsl:value-of select="weight"/></Data></Cell>
		<Cell ss:StyleID="s21"><Data ss:Type="String"><xsl:value-of select="time"/></Data></Cell>
		<Cell ss:StyleID="s21"><Data ss:Type="String"><xsl:value-of select="paymentType"/></Data></Cell>
		<Cell ss:StyleID="s21"><Data ss:Type="Number"><xsl:value-of select="sum"/></Data></Cell>
		<Cell ss:StyleID="s18"><Data ss:Type="String"><xsl:value-of select="note"/></Data></Cell>
	   </Row>
	</xsl:for-each>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Layout x:Orientation="Landscape"/>
   </PageSetup>
   <FitToPage/>
   <Print>
    <ValidPrinterInfo/>
    <PaperSizeIndex>9</PaperSizeIndex>
    <Scale>66</Scale>
    <HorizontalResolution>600</HorizontalResolution>
    <VerticalResolution>600</VerticalResolution>
   </Print>
   <Zoom>80</Zoom>
   <Selected/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <RangeSelection>R1C1:R1C12</RangeSelection>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
</xsl:template>
</xsl:stylesheet>