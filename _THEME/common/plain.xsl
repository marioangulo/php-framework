<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<!--include XSLTEngine-->
	<xsl:include href="../../_LIB/weblegs/WebLegs.XSLTEngine.xsl" />

	<!--handle the HTML element-->
	<xsl:template match="html">
		<html>
			<head>
                <!--base-->
                <base data-bind-attr="href=config:base-href" />
                
                <!--jquery-->
				<script src="_LIB/jquery/jquery-1.6.3.min.js"></script>
				<script src="_LIB/jquery/jquery-ui-1.8.16.custom.min.js"></script>
                <link href="_LIB/jquery/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
                
                <!--footprint-js-->
				<script language="javascript" src="_GLOBAL/init.js" type="text/javascript"></script>
                
				<!--XSLTEngine::head-->
				<xsl:apply-templates select="head"/>
			</head>
			<body>
				<!--XSLTEngine::body-->
				<xsl:apply-templates select="body"/>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>