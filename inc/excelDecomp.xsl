<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">
	<xsl:output method="xml"/>
	<xsl:template match="/">
		<table>
			<xsl:for-each select="//Workbook/Worksheet/Table/Row">
			<tr>
				<xsl:for-each select="Cell">
					<xsl:element name="td">
						<xsl:if test="@ss:MergeAcross">
							<xsl:attribute name="colspan">
								<xsl:value-of select="@ss:MergeAcross"/>
							</xsl:attribute>
						</xsl:if>
						<xsl:value-of select="text()"/>
					</xsl:element>
				</xsl:for-each>
			</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>