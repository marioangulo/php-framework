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
                <script src="admin/search/init.js" language="javascript"></script>
                <script src="admin/notes/init.js" language="javascript"></script>
                
                <!--css-->
                <link href="_theme/admin/template.css" rel="stylesheet" type="text/css" />
                
                <!--xslt::head-->
                <xsl:apply-templates select="head"/>
            </head>
            <body>
                <div class="navbar navbar-fixed-top">
                    <div class="navbar-inner">
                        <div class="container">
                            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </a>
                            <a class="brand" href="admin/index.html" data-bind-text="config:project-name">footprint</a>
                            <div class="nav-collapse">
                                <!--xslt::tabs-->
                                <xsl:call-template name="xslt-include">
                                    <xsl:with-param name="href" select="concat($root_path, 'admin/tabs.html')"/>
                                </xsl:call-template>
                                
                                <span class="ajax-running pull-left hide">
                                    <img src="_theme/common/media/ajax-loading-small.gif" alt="ajax-loading-small" width="16" height="11" />
                                </span>
                                
                                <form class="navbar-search pull-right">
                                    <input class="search-query" type="text" placeholder="search" name="global_search" id="global_search" size="40" maxlength="255" tabindex="1" />
                                    <ul class="dropdown open nav-search-results">
                                        <span class="global-search-results" data-section="global_search_results"></span>
                                    </ul>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nav-space"></div>
                
                <div class="page">
                    <div class="container">
                        <!--xslt::body-->
                        <xsl:apply-templates select="body"/>
                    </div>
                </div>
                
                <div class="footer">
                    <div class="container">
                        <div class="inner">
                            <p class="right">&#169; <span data-bind-text="config:copyright-year">1901</span>&#160;<span data-bind-text="config:company-name">Acme, Inc.</span> [<a href="root/index.html">root</a>]</p>
                            <p><a data-bind-attr="href=config:base-href">Home</a> | <a href="login/logout.html">Logout</a></p>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>