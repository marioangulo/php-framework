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
				<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				
                <!--jquery-->
				<script src="_LIB/jquery/jquery-1.6.3.min.js"></script>
				<script src="_LIB/jquery/jquery-ui-1.8.16.custom.min.js"></script>
                <link href="_LIB/jquery/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
                
                <!--footprint-js-->
				<script language="javascript" src="_GLOBAL/init.js" type="text/javascript"></script>
                
                <!--bootstrap-css-->
                <link href="_THEME/common/bootstrap-1.3.0.css" rel="stylesheet" type="text/css" />
                <link href="_THEME/common/bootstrap-additions.css" rel="stylesheet" type="text/css" />
                <link href="_THEME/common/shortcuts.css" rel="stylesheet" type="text/css" />
                
                <!--theme-css-->
				<link href="_THEME/account/page.css" rel="stylesheet" type="text/css" />
                
				<!--XSLTEngine::head-->
				<xsl:apply-templates select="head"/>
			</head>
			<body>
				<div class="topbar">
					<div class="topbar-inner">
						<div class="container">
							<h3><a href="account/index.html" data-bind-text="config:project-name">footprint</a></h3>
							
							<xsl:call-template name="xslt-include">
								<xsl:with-param name="href" select="string('../../account/tabs.html')"/>
							</xsl:call-template>
							
							<span class="ajax-running">
								<img src="_THEME/common/media/ajax-loading-small.gif" alt="ajax-loading-small" width="16" height="11" />
							</span>
							
					        <ul class="nav secondary-nav">
								<li><a href="login/logout.html">Logout</a></li>
					        </ul>
						</div>
					</div>
				</div>
				<div class="topspace"></div>
				
				<div class="masthead">
					<!--XSLTEngine::region-->
					<xsl:apply-templates select="body//*[@xslt-region='masthead']" mode="regions"/>
				</div>
				
				<div class="page">
					<div class="container">
						<!--XSLTEngine::region-->
						<xsl:apply-templates select="body//*[@xslt-region='nav-trail']" mode="regions"/>
						<!--XSLTEngine::region-->
						<xsl:apply-templates select="body//*[@xslt-region='sub-tabs']" mode="regions"/>
						<!--XSLTEngine::body-->
						<xsl:apply-templates select="body"/>
					</div>
				</div>
				
				<div class="footer">
					<div class="inner">
						<div class="container">
							<p class="right">&#169; <span data-bind-text="config:copyright-year">1901</span>&#160;<span data-bind-text="config:company-name">Acme, Inc.</span></p>
							<p><a data-bind-attr="href=config:base-href">Home</a> | <a href="contact/index.html">Contact</a><br/></p>
						</div>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>