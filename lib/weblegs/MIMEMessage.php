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

class MIMEMessage {
    public $headers;
    public $preamble;
    public $body;
    public $fileBody;
    public $parts;
    
    /**
     * construct the object
     * @param string $data the data to automatically parse
     */
    public function __construct($data = "") {
        //this fixes the usue of this constant not being defined
        if(!defined("PCRE_MULTILINE")) {
            define("PCRE_MULTILINE", "m");
        }
        
        $this->headers = array();
        $this->preamble;
        $this->body;
        $this->fileBody;
        $this->parts = array();
        
        if($data != "") {
            $this->parse($data);
        }
    }
    
    /**
     * prase in raw data
     * @param string $data the data to parse
     */
    public function parse($data) {
        str_replace(array("\r\n", "\r", "\n"), "\n", $data);
        preg_match_all("/(.*?)\r\n\r\n(.*)/is", $data, $matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);
        $this->parseHeaders(@$matches[1][0]);
        $this->parseBody(@$matches[2][0], true);
    }
    
    /**
     * prases in headers
     * @param string $data the data to parse
     */
    public function parseHeaders($data) {
        //get matches
        preg_match_all("/(?<=\n|^)(\S*?): (.*?)(?=\n\S*?: |$)/is", $data, $matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);
        
        //add headers
        for($i = 0; $i < count($matches[1]); $i++) {
            $this->addHeader($matches[1][$i], preg_replace('/\s+/', " ", $matches[2][$i]));
        }
    }
    
    /**
     * prases in the body
     * @param string $data the data to parse
     */
    public function parseBody($data, $decode) {
        switch(strtolower($this->getMediaType())) {
            //- - - - - - - - - - - - - - - - - - - -//
            case "application":
                //check for encoding
                if(strstr($this->getContentTransferEncoding(), "base64")) {
                    if($decode) {
                        $this->body = $data;
                        $this->fileBody = Codec::base64Decode($data);
                    }
                    else {
                        $this->body = Codec::base64Encode($this->fileBody);
                    }
                }
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            case "audio":
                //check for encoding
                if(strstr($this->getContentTransferEncoding(), "base64")) {
                    if($decode) {
                        $this->body = $data;
                        $this->fileBody = Codec::base64Decode($data);
                    }
                    else {
                        $this->body = Codec::base64Encode($this->fileBody);
                    }
                }
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            case "image":
                //check for encoding
                if(strstr($this->getContentTransferEncoding(), "base64")) {
                    if($decode) {
                        $this->body = $data;
                        $this->fileBody = Codec::base64Decode($data);
                    }
                    else {
                        $this->body = Codec::base64Encode($this->fileBody);
                    }
                }
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            case "message":
                switch(strtolower($this->getMediaSubType())) {
                    //* * * * * * * * * * * * * * * * * * * *//
                    case "rfc822":
                        //make the first part of this message
                        //the parsed message
                        $this->parts[] = new MIMEMessage($data);
                        break;
                    //* * * * * * * * * * * * * * * * * * * *//
                    default:
                        //do nothing
                        break;
                }
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            case "model":
                //do nothing
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            case "multipart":
                //the boundry is required for multipart messages
                $multiPartBoundry = $this->getMediaBoundary();
                
                //get the preamble
                $pattern = "/(.*?)(--". $multiPartBoundry .")/is";
                preg_match_all($pattern, $data, $preambleMatches, PREG_PATTERN_ORDER | PCRE_MULTILINE);
                if($preambleMatches[1]) {
                    $this->preamble = $preambleMatches[1][0];
                }
                
                //get message parts
                $pattern = "--". $multiPartBoundry;
                $partMatches = spliti($pattern, $data);
                
                //remove the first element (the preamble area)
                array_shift($partMatches);
                
                //remove the last item its empty
                array_pop($partMatches);
                
                //loop over all parts
                for($i = 0; $i < count($partMatches); $i++) {
                    //the parsed message
                    $this->parts[] = new MIMEMessage($partMatches[$i]);
                }
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            case "text":
                //check for encoding
                if(strstr($this->getContentTransferEncoding(), "base64")) {
                    if($decode) {
                        $this->body = Codec::base64Decode($data);
                    }
                    else {
                        $this->body = Codec::base64Encode($data);
                    }
                }
                else if(strstr($this->getContentTransferEncoding(), "quoted-printable")) {
                    if($decode) {
                        $this->body = Codec::quotedPrintableDecode($data);
                    }
                    else {
                        $this->body = Codec::quotedPrintableEncode($data);
                    }
                }
                else{
                    $this->body = $data;
                }
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            case "video":
                //check for encoding
                if(strstr($this->getContentTransferEncoding(), "base64")) {
                    if($decode) {
                        $this->body = $data;
                        $this->fileBody = Codec::base64Decode($data);
                    }
                    else {
                        $this->body = Codec::base64Encode($this->fileBody);
                    }
                }
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            default:
                //do nothing
                break;
        
        }
    }
    
    /**
     * adds a header to the internal array
     * @param string $name the name of the header
     * @param string $value the value of the header
     */
    public function addHeader($name, $value) {
        //add header
        $this->headers[] = array($name, $value);
    }
    
    /**
     * removes a header from the internal array
     * @param string $name the name of the header
     */
    public function removeHeader($name) {
        //create new header array container
        $newHeaders = array();
        
        for($i = 0; $i < count($this->headers); $i++){
            if($this->headers[$i][0] != $name){
                $newHeaders[] = array($this->headers[$i][0], $this->headers[$i][1]);
            }
        }
        $this->headers = $newHeaders;
    }
    
    /**
     * gets a header from the internal array
     * @param string $name the name of the header
     */
    public function getHeader($name) {
        for($i = 0; $i < count($this->headers); $i++){
            if($this->headers[$i][0] == $name){
                return $this->headers[$i][1];
            }
        }
        return null;
    }
    
    /**
     * gets the content transfer encoding
     * @return string Content transfer encoding
     */
    public function getContentTransferEncoding() {
        //return content transfer encoding
        return $this->getHeader("Content-Transfer-Encoding");
    }
    
    /**
     * gets the media type
     * @return string Media type
     */
    public function getMediaType() {        
        //get matches
        preg_match_all("/^(.*?)\\/(.*?);|$/is", $this->getHeader("Content-Type"), $matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);
        
        $match = $matches[1][0];
        
        if($match == "") {
            $match = "text";
        }
        
        return $match;
    }
    
    /**
     * gets the media sub type
     * @return string Media sub type
     */
    public function getMediaSubType() {
        //get matches
        preg_match_all("/^(.*?)\\/(.*?);|$/is", $this->getHeader("Content-Type"), $matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);    
        
        $match = $matches[2][0];
        
        if($match == "") {
            $match = "plain";
        }
        
        return $match;
    }
    
    /**
     * gets the media file name
     * @return string Media file name
     */
    public function getMediaFileName() {
        //get matches
        preg_match_all("/name=\"{0,1}(.*?)(\"|;|$)/", $this->getHeader("Content-Type"), $matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);    
        return $matches[1][0];    
    }
    
    /**
     * gets the media character set
     * @return string Media character set
     */
    public function getMediaCharacterSet() {
        //get matches
        preg_match_all("/charset=\"{0,1}(.*?)(?:\"|;|$)/", $this->getHeader("Content-Type"), $matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);    
        
        $match = $matches[1][0];
        
        if($match == "") {
            $match = "us-ascii";
        }
        
        return $match;
    }
    
    /**
     * gets the media boundary
     * @return string Media boundry
     */
    public function getMediaBoundary() {
        preg_match_all("/boundary=\"{0,1}(.*?)(?:\"|$)/", $this->getHeader("Content-Type"), $matches, PCRE_MULTILINE | PREG_PATTERN_ORDER);
        if($matches[1]) {
            return str_replace(array("'", "\""), "", $matches[1][0]);
        }
        else {
            return "";
        }
    }
    
    /**
     * @return bool If content disposition is attachement
     */
    public function isAttachment() {
        if(strstr($this->getHeader("Content-Disposition"), "attachment")) {
            return true;
        }
        return false;
    }
    
    /**
     * returns a string representation of the message
     * @return string Transformed message
     */
    public function toString() {
        $tmpMIMEMessage = "";
        //loop over the headers
        for($i = 0 ; $i < count($this->headers) ; $i++) {
            $tmpThisHeader = $this->headers[$i][0] .": ". $this->headers[$i][1];
            $tmpThisFixedHeader = "";
            if(strlen($tmpThisHeader) > 74) {
                while(strlen($tmpThisHeader) > 74) {
                    //find the next space character furthest to the end (or the 74th character)
                    if(strrpos(substr($tmpThisHeader, 0, 73), " ") !== false) {
                        $tmpThisFixedHeader .= substr($tmpThisHeader, 0, strrpos(substr($tmpThisHeader, 0, 73), " ")) ."\n\t";
                        //remove from tmpThisHeader
                        $tmpThisHeader = substr($tmpThisHeader, strrpos(substr($tmpThisHeader, 0, 73), " "));
                        //trim it up (important)
                        $tmpThisHeader = trim($tmpThisHeader);
                    }
                    //try to find a space someone after that then
                    else if(strpos($tmpThisHeader, " ") !== false) {
                        $tmpThisFixedHeader .= substr($tmpThisHeader, 0, strpos($tmpThisHeader, " ")) ."\n\t";
                        //remove from tmpThisHeader
                        $tmpThisHeader = substr($tmpThisHeader, strpos($tmpThisHeader, " "));
                        //trim it up (important)
                        $tmpThisHeader = trim($tmpThisHeader);
                    }
                    else {
                        //this is a long line w/ no breaking potential
                        $tmpThisFixedHeader .= $tmpThisHeader;
                        $tmpThisHeader = "";
                    }
                }
                //if there is still content in tmpThisHeader ... append it
                if(strlen($tmpThisHeader) > 0) {
                    $tmpThisFixedHeader .= $tmpThisHeader;
                }
            }
            else {
                $tmpThisFixedHeader = $tmpThisHeader;
            }
            $tmpThisFixedHeader .= "\n";
            $tmpMIMEMessage .= $tmpThisFixedHeader;
        }
        
        //we should alrady have the first space from the last header
        //but fix it here if there are no headers (probably never happens)
        if(count($this->headers) == 0) {
            $tmpMIMEMessage .= "\n";
        }
        
        //add header/body space
        $tmpMIMEMessage .= "\n";
        
        //add preamble
        if($this->preamble != "") {
            $tmpMIMEMessage .= $this->preamble ."\n";
        }
        
        //add body text
        if(count($this->parts) == 0) {
            //en/decode on the way out
            $this->parseBody($this->body, false);
            $tmpMIMEMessage .= $this->body;
        }
        else {
            //go through each part
            for($i = 0 ; $i < count($this->parts) ; $i++) {
                //add boundary above
                if($this->getMediaBoundary() != "") {
                    $tmpMIMEMessage .= "\n--". $this->getMediaBoundary() ."\n";
                }
                
                //add body content
                $tmpMIMEMessage .= $this->parts[$i]->toString();
                
                //add boundary above
                if($this->getMediaBoundary() != "" && ($i + 1) == count($this->parts)) {
                    $tmpMIMEMessage .= "\n--". $this->getMediaBoundary() ."--\n\n";
                }
            }
        }
        return $tmpMIMEMessage;
    }
    
    /**
     * saves the message as a file
     * @param string $filePath the path to save to
     */
    public function saveAs($filePath){
        try {
            file_put_contents($filePath, $this->toString());
        }
        catch(Exception $e){
            throw new Exception("Weblegs.MIMEMessage.saveAs(): Was not able to save file.");
        }
    }
    
    /**
     * saves the message body as a file
     * @param string $filePath the path to save to
     */
    public function saveBodyAs($filePath){
        try {
            file_put_contents($filePath, $this->body);
        }
        catch(Exception $e){
            throw new Exception("Weblegs.MIMEMessage.saveBodyAs(): Was not able to save file.");
        }
    }
    
    /**
     * saves the message file body as a file
     * @param string $filePath the path to save to
     */
    public function saveFileAs($filePath){
        try {
            file_put_contents($filePath, $this->fileBody);
        }
        catch(Exception $e){
            throw new Exception("Weblegs.MIMEMessage.saveFileAs(): Was not able to save file.");
        }
    }
}
