<?php

/*
 * This file is part of the Weblegs package.
 * (C) Weblegs, Inc. <software@weblegs.com>
 *
 * This program is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 */

class Codec {
    /**
     * encode a string to be safe in urls
     * @param string $input the data to encode
     * @return string Transformed input
     */
    public static function urlEncode($input){
        return urlencode($input);
    }
    
    /**
     * decode from url encoded string
     * @param string $input the data to decode
     * @return string Transformed input
     */
    public static function urlDecode($input) {
        return urldecode($input);
    }
    
    /**
     * encode a string to be safe in html
     * @param string $input the data to encode
     * @return string Transformed input
     */
    public static function htmlEncode($input) {
        return htmlentities($input);
    }
    
    /**
     * decode from html encoded string
     * @param string $input the data to decode
     * @return string Transformed input
     */
    public static function htmlDecode($input){
        return html_entity_decode($input);
    }
    
    /**
     * encode a string to be safe in xml
     * @param string $input the data to encode
     * @return string Transformed input
     */
    public static function xmlEncode($input) {
        $input = str_replace("&", "&amp;", $input);
        $input = str_replace("<", "&lt;", $input);
        $input = str_replace(">", "&gt;", $input);
        $input = str_replace("\"", "&quot;", $input);
        $input = str_replace("'", "&apos;", $input);
        return $input;
    }
    
    /**
     * decode from xml encoded string
     * @param string $input the data to decode
     * @return string Transformed input
     */
    public static function xmlDecode($input){
        $input = str_replace("&amp;", "&", $input);
        $input = str_replace("&lt;", "<", $input);
        $input = str_replace("&gt;", ">", $input);
        $input = str_replace("&quot;", "\"", $input);
        $input = str_replace("&apos;", "'", $input);
        return $input;
    }
    
    /**
     * replaces special characters with unicode entities and also replaces html entities with
     * unicode entities. for a less string version see the xhtmlCleanEntities method.
     * @param string $input the string to clean
     * @return string Transformed input
     */
    public static function xhtmlCleanText($input){
        //convert to single byte
        $input = utf8_decode($input);
        
        //encode &s
        $input = str_replace("&", "&amp;", $input);
        
        //build our translation table
        $html_entity_hash = get_html_translation_table(HTML_ENTITIES); //, ENT_QUOTES
        $html_entity_hash[chr(130)] = "&sbquo;";    // Single Low-9 Quotation Mark
        $html_entity_hash[chr(131)] = "&fnof;";    // Latin Small Letter F With Hook
        $html_entity_hash[chr(132)] = "&bdquo;";    // Double Low-9 Quotation Mark
        $html_entity_hash[chr(133)] = "&hellip;";    // Horizontal Ellipsis
        $html_entity_hash[chr(134)] = "&dagger;";    // Dagger
        $html_entity_hash[chr(135)] = "&Dagger;";    // Double Dagger
        $html_entity_hash[chr(136)] = "&circ;";    // Modifier Letter Circumflex Accent
        $html_entity_hash[chr(137)] = "&permil;";    // Per Mille Sign
        $html_entity_hash[chr(138)] = "&Scaron;";    // Latin Capital Letter S With Caron
        $html_entity_hash[chr(139)] = "&lsaquo;";    // Single Left-Pointing Angle Quotation Mark
        $html_entity_hash[chr(140)] = "&OElig;";    // Latin Capital Ligature OE
        $html_entity_hash[chr(145)] = "&lsquo;";    // Left Single Quotation Mark
        $html_entity_hash[chr(146)] = "&rsquo;";    // Right Single Quotation Mark
        $html_entity_hash[chr(147)] = "&ldquo;";    // Left Double Quotation Mark
        $html_entity_hash[chr(148)] = "&rdquo;";    // Right Double Quotation Mark
        $html_entity_hash[chr(149)] = "&bull;";    // Bullet
        $html_entity_hash[chr(150)] = "&ndash;";    // En Dash
        $html_entity_hash[chr(151)] = "&mdash;";    // Em Dash
        $html_entity_hash[chr(152)] = "&tilde;";    // Small Tilde
        $html_entity_hash[chr(153)] = "&trade;";    // Trade Mark Sign
        $html_entity_hash[chr(154)] = "&scaron;";    // Latin Small Letter S With Caron
        $html_entity_hash[chr(155)] = "&rsaquo;";    // Single Right-Pointing Angle Quotation Mark
        $html_entity_hash[chr(156)] = "&oelig;";    // Latin Small Ligature OE
        $html_entity_hash[chr(159)] = "&Yuml;";    // Latin Capital Letter Y With Diaeresis
        
        //remove &s (we took care of this already)
        unset($html_entity_hash[chr(38)]);
        
        //create replacement arrays
        $utf8Entities = array();
        $htmlEntities = array_values($html_entity_hash); 
        $entitiesDecoded = array_keys($html_entity_hash);
        
        //build the unicode entities array
        $num = count($entitiesDecoded); 
        for($i = 0 ; $i < $num ; $i++) { 
            $utf8Entities[$i] = "&#". ord($entitiesDecoded[$i]) .";"; 
        } 
        
        //replace raw entities with html entities
        $input = str_replace($entitiesDecoded, $htmlEntities, $input); 
        
        //replace html entities with unicode entities
        $input = str_replace($htmlEntities, $utf8Entities, $input); 
        
        //give it back
        return $input;
    }
    
    /**
     * replaces html entities with unicode entities.
     * @param string $input the string to clean
     * @return string Transformed input
     */
    public static function xhtmlCleanEntities($input){
        //convert to single byte
        $input = utf8_decode($input);
        
        //build our translation table
        $html_entity_hash = get_html_translation_table(HTML_ENTITIES); //, ENT_QUOTES
        $html_entity_hash[chr(130)] = "&sbquo;";    // Single Low-9 Quotation Mark
        $html_entity_hash[chr(131)] = "&fnof;";    // Latin Small Letter F With Hook
        $html_entity_hash[chr(132)] = "&bdquo;";    // Double Low-9 Quotation Mark
        $html_entity_hash[chr(133)] = "&hellip;";    // Horizontal Ellipsis
        $html_entity_hash[chr(134)] = "&dagger;";    // Dagger
        $html_entity_hash[chr(135)] = "&Dagger;";    // Double Dagger
        $html_entity_hash[chr(136)] = "&circ;";    // Modifier Letter Circumflex Accent
        $html_entity_hash[chr(137)] = "&permil;";    // Per Mille Sign
        $html_entity_hash[chr(138)] = "&Scaron;";    // Latin Capital Letter S With Caron
        $html_entity_hash[chr(139)] = "&lsaquo;";    // Single Left-Pointing Angle Quotation Mark
        $html_entity_hash[chr(140)] = "&OElig;";    // Latin Capital Ligature OE
        $html_entity_hash[chr(145)] = "&lsquo;";    // Left Single Quotation Mark
        $html_entity_hash[chr(146)] = "&rsquo;";    // Right Single Quotation Mark
        $html_entity_hash[chr(147)] = "&ldquo;";    // Left Double Quotation Mark
        $html_entity_hash[chr(148)] = "&rdquo;";    // Right Double Quotation Mark
        $html_entity_hash[chr(149)] = "&bull;";    // Bullet
        $html_entity_hash[chr(150)] = "&ndash;";    // En Dash
        $html_entity_hash[chr(151)] = "&mdash;";    // Em Dash
        $html_entity_hash[chr(152)] = "&tilde;";    // Small Tilde
        $html_entity_hash[chr(153)] = "&trade;";    // Trade Mark Sign
        $html_entity_hash[chr(154)] = "&scaron;";    // Latin Small Letter S With Caron
        $html_entity_hash[chr(155)] = "&rsaquo;";    // Single Right-Pointing Angle Quotation Mark
        $html_entity_hash[chr(156)] = "&oelig;";    // Latin Small Ligature OE
        $html_entity_hash[chr(159)] = "&Yuml;";    // Latin Capital Letter Y With Diaeresis
        
        //create replacement arrays
        $utf8Entities = array();
        $htmlEntities = array_values($html_entity_hash); 
        $entitiesDecoded = array_keys($html_entity_hash);
        
        //build the unicode entities array
        $num = count($entitiesDecoded); 
        for($i = 0 ; $i < $num ; $i++) { 
            $utf8Entities[$i] = "&#". ord($entitiesDecoded[$i]) .";"; 
        } 
        
        //replace html entities with unicode entities
        $input = str_replace($htmlEntities, $utf8Entities, $input); 
        
        //give it back
        return $input;
    }
    
    /**
     * does what xhtmlCleanText does but we replace [<|>|"|']
     */
    public static function xhtmlCleanTextNotTags($input){
        //convert to single byte
        $input = utf8_decode($input);
        
        //encode &s
        $input = str_replace("&", "&amp;", $input);
        
        //build our translation table
        $html_entity_hash = get_html_translation_table(HTML_ENTITIES); //, ENT_QUOTES
        $html_entity_hash[chr(130)] = "&sbquo;";    // Single Low-9 Quotation Mark
        $html_entity_hash[chr(131)] = "&fnof;";    // Latin Small Letter F With Hook
        $html_entity_hash[chr(132)] = "&bdquo;";    // Double Low-9 Quotation Mark
        $html_entity_hash[chr(133)] = "&hellip;";    // Horizontal Ellipsis
        $html_entity_hash[chr(134)] = "&dagger;";    // Dagger
        $html_entity_hash[chr(135)] = "&Dagger;";    // Double Dagger
        $html_entity_hash[chr(136)] = "&circ;";    // Modifier Letter Circumflex Accent
        $html_entity_hash[chr(137)] = "&permil;";    // Per Mille Sign
        $html_entity_hash[chr(138)] = "&Scaron;";    // Latin Capital Letter S With Caron
        $html_entity_hash[chr(139)] = "&lsaquo;";    // Single Left-Pointing Angle Quotation Mark
        $html_entity_hash[chr(140)] = "&OElig;";    // Latin Capital Ligature OE
        $html_entity_hash[chr(145)] = "&lsquo;";    // Left Single Quotation Mark
        $html_entity_hash[chr(146)] = "&rsquo;";    // Right Single Quotation Mark
        $html_entity_hash[chr(147)] = "&ldquo;";    // Left Double Quotation Mark
        $html_entity_hash[chr(148)] = "&rdquo;";    // Right Double Quotation Mark
        $html_entity_hash[chr(149)] = "&bull;";    // Bullet
        $html_entity_hash[chr(150)] = "&ndash;";    // En Dash
        $html_entity_hash[chr(151)] = "&mdash;";    // Em Dash
        $html_entity_hash[chr(152)] = "&tilde;";    // Small Tilde
        $html_entity_hash[chr(153)] = "&trade;";    // Trade Mark Sign
        $html_entity_hash[chr(154)] = "&scaron;";    // Latin Small Letter S With Caron
        $html_entity_hash[chr(155)] = "&rsaquo;";    // Single Right-Pointing Angle Quotation Mark
        $html_entity_hash[chr(156)] = "&oelig;";    // Latin Small Ligature OE
        $html_entity_hash[chr(159)] = "&Yuml;";    // Latin Capital Letter Y With Diaeresis
        
        //remove &s (we took care of this already)
        unset($html_entity_hash[chr(38)]);
        
        //create replacement arrays
        $utf8Entities = array();
        $htmlEntities = array_values($html_entity_hash); 
        $entitiesDecoded = array_keys($html_entity_hash);
        
        //build the unicode entities array
        $num = count($entitiesDecoded); 
        for($i = 0 ; $i < $num ; $i++) { 
            $utf8Entities[$i] = "&#". ord($entitiesDecoded[$i]) .";"; 
        } 
        
        //replace raw entities with html entities
        $input = str_replace($entitiesDecoded, $htmlEntities, $input); 
        
        //replace html entities with unicode entities
        $input = str_replace($htmlEntities, $utf8Entities, $input); 
        
        //fix greater than and less than signs
        $input = str_replace("&lt;", "<", $input);
        $input = str_replace("&gt;", ">", $input);
        $input = str_replace("&#60;", "<", $input);
        $input = str_replace("&#62;", ">", $input);
        
        $input = str_replace("&quot;", "\"", $input);
        $input = str_replace("&#34;", "\"", $input);
        $input = str_replace("&apos;", "'", $input);
        
        //give it back
        return $input;
    }
    
    /**
     * encode a string to the base64 standard
     * @param string $input the data to encode
     * @return string Transformed input
     */
    public static function base64Encode($input){
        return chunk_split(base64_encode($input), 76, "\n");
    }
    
    /**
     * decode a string from the base64 standard
     * @param string $input the data to decode
     * @return string Transformed input
     */
    public static function base64Decode($input){
        return base64_decode($input);
    }
    
    /**
     * encode a string to the quoted printable standard
     * @param string $input the data to encode
     * @return string Transformed input
     */
    public static function quotedPrintableEncode($input){
        //container for our final string
        $tmpQPString = "";
        
        //container for our current line length
        $currentLineLength = 0;
        
        //loop over bytes and build new string
        for($i = 0 ; $i < strlen($input) ; $i++) {
            //Get one character at a time
            $current = $input[$i];
            
            //Keep track of the next character too... if its the last character
            //present return a CR character
            $next = (($i + 1) != strlen($input)) ? $input[$i + 1] : chr(0x0D);
            
            //make hex-style string out of the current byte
            $currentEncoded = "";
            
            //if this is the '=' character just encode it and return
            if($current == '=') {
                $currentEncoded = sprintf('=%02X', ord($current));
            }
            //if this is any of these characters, just encode them
            else if($current == '!' || $current == '"' || $current == '#' || $current == '$' || $current == '@' || $current == '[' || $current == '\\' || $current == ']' || $current == '^' || $current == '`' || $current == '{' || $current == '|' || $current == '}' || $current == '~' || $current == '\'') {
                $currentEncoded = sprintf('=%02X', ord($current));
            }
            //if we come across a tab or a space AND the next byte
            //represents CR or LF, we need to encode it too
            else if(($current == chr(0x09) || $current == chr(0x20)) && ($next == chr(0x0A) || $next == chr(0x0D))) {
                $currentEncoded = sprintf('=%02X', ord($current));
            }
            //is this character ok as is?
            else if((ord($current) >= 33 && ord($current) <= 126) || $current == chr(0x0D) || $current == chr(0x0A) || $current == chr(0x09) || $current == chr(0x20)) {
                $currentEncoded = $current;
            }
            else {
                //if we get here, we've fell from above, ecode anything that gets here
                $currentEncoded = sprintf('=%02X', ord($current));
            }
            
            //let's make sure that we keep track of line length while
            //we append characters together for the final output
            
            //check for CR and LF to get away from double lines
            if($current == chr(0x0D) || $current == chr(0x0A)) {
                //if we got here that means that we are at the end of the
                //line and we need to reset our line length tracking variable
                if($current == chr(0x0A)) {
                    $currentLineLength = 0;
                }
            }
            
            //check to see if this pushes us past 76 characters
            //if so lets add a soft line break
            if(strlen($currentEncoded) + $currentLineLength > 74) {
                $tmpQPString .= "=\n";
                $currentLineLength = 0;
            }
            
            //append this character and increase line length
            $tmpQPString .= $currentEncoded;
            $currentLineLength += strlen($currentEncoded);
        }
        
        //return our completed string
        return $tmpQPString;
    }
    
    /**
     * decode a string from the quoted printable standard
     * @param string $input the data to decode
     * @return string Transformed input
     */
    public static function quotedPrintableDecode($input){
        return quoted_printable_decode($input);
    }
    
    /**
     * encrypt a string using the md5 standard
     * @param string $input the data to encrypt
     * @return string Transformed input
     */
    public static function md5Encrypt($input){
        return md5($input);
    }
    
    /**
     * encrypt a string using the hmac md5 standard
     * @param string $input the data to encrypt
     * @return string Transformed input
     */
    public static function hmacmd5Encrypt($key, $input){
        if(strlen($key) > 64) {
            $key = pack('H32', md5($key));
        }
        elseif(strlen($key) < 64) {
            $key = str_pad($key, 64, chr(0));
        }
        
        $kipad = substr($key, 0, 64) ^ str_repeat(chr(0x36), 64);
        $kopad = substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64);
        $inner = pack('H32', md5($kipad . $input));
        return md5($kopad . $inner);
    }
}
