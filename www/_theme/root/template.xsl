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
                <script src="root/search/init.js" language="javascript"></script>
                
                <!--css-->
                <link href="_theme/root/template.css" rel="stylesheet" type="text/css" />
                
                <!--XSLTEngine::head-->
                <xsl:apply-templates select="head"/>
            </head>
            <body>
                <div class="topbar">
                    <div class="topbar-inner">
                        <div class="container">
                            <h3><a href="root/index.html" data-bind-text="config:project-name">footprint</a></h3>
                            
                            <xsl:call-template name="xslt-include">
                                <xsl:with-param name="href" select="concat($root_path, 'root/tabs.html')"/>
                            </xsl:call-template>
                                                        
                            <span class="ajax-running">
                                <img src="_theme/common/media/ajax-loading-small.gif" alt="ajax-loading-small" width="16" height="11" />
                            </span>
                            
                            <ul class="nav secondary-nav">
                                <li class="dropdown open">
                                    <form><input type="text" placeholder="search" name="global_search" id="global_search" size="40" maxlength="255" tabindex="1" /></form>
                                    <span class="global-search-results" data-section="global_search_results"></span>
                                </li>
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
                
                <div class="container">
                    <div class="footer">
                        <p class="right">&#169; <span data-bind-text="config:copyright-year">1901</span>&#160;<span data-bind-text="config:company-name">Acme, Inc.</span> [<a href="admin/index.html">admin</a>]</p>
                        <p><a data-bind-attr="href=config:base-href">Home</a> | <a href="login/logout.html">Logout</a></p>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>