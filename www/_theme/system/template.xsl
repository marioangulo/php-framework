<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <!--include XSLTEngine-->
    <xsl:include href="../lib/footprint/XSLTEngine.xsl" />
    
    <!--handle the HTML element-->
    <xsl:template match="html">
        <html>
            <head>
                <!--base-->
                <base data-bind-attr="href=config:base-href" />
                <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                
                <!--js-->
                <script src="_theme/common/jquery/jquery-1.6.3.min.js" language="javascript"></script>
                <script src="_theme/common/jquery/jquery-ui-1.8.16.custom.min.js" language="javascript"></script>
                <script src="_theme/common/utility.js" language="javascript"></script>
                
                <!--css-->
                <link href="_theme/system/template.css" rel="stylesheet" type="text/css" />
                
                <!--XSLTEngine::head-->
                <xsl:apply-templates select="head"/>
            </head>
            <body>
                <div class="topbar">
                    <div class="topbar-inner">
                        <div class="container">
                            <h3><a data-bind-attr="href=config:base-href" data-bind-text="config:project-name">footprint</a></h3>
                            
                            <ul class="nav secondary-nav">
                                <a data-bind-attr="href=config:base-href">Home</a>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="topspace"></div>
                
                <div class="page">
                    <div class="container">
                        <!--XSLTEngine::body-->
                        <xsl:apply-templates select="body"/>
                    </div>
                </div>
                
                <div class="container">
                    <div class="footer">
                        <p class="right">&#169; <span data-bind-text="config:copyright-year">1901</span>&#160;<span data-bind-text="config:company-name">Acme, Inc.</span></p>
                        <p><a data-bind-attr="href=config:base-href">Home</a> | <a href="contact/index.html">Contact</a></p>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>