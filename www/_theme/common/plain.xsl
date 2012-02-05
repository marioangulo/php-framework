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
                <script src="_theme/common/jquery/jquery-1.7.1.min.js" language="javascript"></script>
                <script src="_theme/common/jquery/jquery-ui-1.8.16.custom.min.js" language="javascript"></script>
                <script src="_theme/common/bootstrap/js/bootstrap.min.js" language="javascript"></script>
                <script src="_theme/common/footprint.js" language="javascript"></script>
                
                <!--xslt::head-->
                <xsl:apply-templates select="head"/>
            </head>
            <body>
                <!--xslt::body-->
                <xsl:apply-templates select="body"/>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>