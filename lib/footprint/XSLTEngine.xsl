<?xml version="1.0"?>

<!--
This file is part of the Weblegs package.
(C) Weblegs, Inc. <software@weblegs.com>
 
This program is free software: you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation, either
version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.
If not, see <http://www.gnu.org/licenses/>.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <!--imports the head-->
    <xsl:template match="head">
        <xsl:copy-of select="node()"/>
    </xsl:template>
    
    <!--imports the body-->
    <xsl:template match="body">
        <!--recursively walk the branches looking for library items-->
        <xsl:apply-templates select="*|@*|text()|comment()"/>
    </xsl:template>
    
    <!--############################################################################-->
    
    <!--check for XSLTEngine items-->
    <xsl:template match="/|*|@*|text()">
        <xsl:choose>
            <!--found a region-->
            <xsl:when test="@xslt-region">
                <!--do nothing-->
            </xsl:when>
            
            <!--keep copying and drilling downward-->
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="*|@*|text()|comment()"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!--check for XSLTEngine items (MODE = REGIONS)-->
    <xsl:template match="/|*|@*|text()|comment()" mode="regions">
        <!--don't copy the region container-->
        <xsl:choose>
            <xsl:when test="@xslt-region">
                <!--just don't match attributes-->
                <xsl:apply-templates select="*|text()|comment()" mode="regions"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="*|@*|text()|comment()" mode="regions"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!--############################################################################-->
    
    <!--check for XSLTEngine include comments-->
    <xsl:template match="comment()">
        <xsl:variable name="raw_comment" select="."/>
        <xsl:choose>
            <xsl:when test="contains($raw_comment, 'xslt-include')">
                <xsl:variable name="include_sub_string" select="substring-after($raw_comment,'&quot;')"/>
                <xsl:variable name="include_file" select="substring-before($include_sub_string,'&quot;')"/>
                <xsl:call-template name="xslt-include">
                    <xsl:with-param name="href" select="concat($root_path, $include_file)"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="*|@*|text()|comment()" mode="regions"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!--check for XSLTEngine include comments (MODE = REGIONS)-->
    <xsl:template match="comment()" mode="regions">
        <xsl:variable name="raw_comment" select="."/>
        <xsl:choose>
            <xsl:when test="contains($raw_comment, 'xslt-include')">
                <xsl:variable name="include_sub_string" select="substring-after($raw_comment,'&quot;')"/>
                <xsl:variable name="include_file" select="substring-before($include_sub_string,'&quot;')"/>
                <xsl:call-template name="xslt-include">
                    <xsl:with-param name="href" select="concat($root_path, $include_file)"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <!--do nothing-->
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <!--############################################################################-->
    
    <!--imports library content-->
    <xsl:template name="xslt-include">
        <xsl:param name="href"/>
        <xsl:choose>
            <!--use param with direct calls-->
            <xsl:when test="$href">
                <xsl:copy-of select="document($href)//body/node()"/>
            </xsl:when>
            <!--use attribute when traversing-->
            <xsl:otherwise>
                <xsl:copy-of select="document(@href)//body/node()"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>