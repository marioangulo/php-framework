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

require_once("SMTPDriver.php");
require_once("MIMEMessage.php");

class SMTPClient {
    public $from;
    public $replyTo;
    public $to;
    public $cc;
    public $bcc;
    public $priority;
    public $subject;
    public $message;
    public $isHTML;
    public $attachments;
    public $host;
    public $port;
    public $protocol;
    public $timeout;
    public $username;
    public $password;
    public $smtpDriver;
    public $mimeMessage;
    public $contentTypeList;
    public $openedManually;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->from = array();
        $this->replyTo = array();
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        $this->priority = "3";
        $this->subject = "";
        $this->message = "";
        $this->isHTML = false;
        $this->attachments = array();
        $this->host = "";
        $this->port = "25";
        $this->protocol = "tcp";
        $this->timeout = 10;
        $this->username = "";
        $this->password = "";
        $this->smtpDriver = new SMTPDriver();
        $this->mimeMessage = new MIMEmessage();
        $this->contentTypeList = $this->buildContentTypeList();
        $this->openedManually = false;
    }
    
    /**
     * opens the connection
     */
    public function open() {
        //setup the SMTPDriver
        $this->smtpDriver->host = $this->host;
        $this->smtpDriver->port = $this->port;
        $this->smtpDriver->protocol = $this->protocol;
        $this->smtpDriver->timeout = $this->timeout;
        $this->openedManually = true;
        
        try {
            $this->smtpDriver->open();
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.SMTPClient.open(): Failed to open connection. ". $e->getmessage());
        }
    }
    
    /**
     * closes the connection
     */
    public function close() {
        try {
            $this->smtpDriver->close();
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.SMTPClient.close(): Failed to close connection. ". $e->getmessage());
        }
    }
    
    /**
     * sets the from address
     * @param string $emailAddress
     * @param string $name
     */
    public function setFrom($emailAddress, $name = "") {
        $this->from[0] = $emailAddress;
        $this->from[1] = $name;
    }
    
    /**
     * sets the reply-to address
     * @param string $emailAddress
     * @param string $name
     */
    public function setReplyTo($emailAddress, $name = "") {
        $this->replyTo[0] = $emailAddress;
        $this->replyTo[1] = $name;
    }
    
    /**
     * adds a to address
     * @param string $emailAddress
     * @param string $name
     */
    public function addTo($emailAddress, $name = "") {
        $this->to[] = array("address" => $emailAddress, "name" => $name);
    }
    
    /**
     * adds a cc address
     * @param string $emailAddress
     * @param string $name
     */
    public function addCC($emailAddress, $name = "") {
        $this->cc[] = array("address" => $emailAddress, "name" => $name);
    }
    
    /**
     * adds a bcc address
     * @param string $emailAddress
     * @param string $name
     */
    public function addBCC($emailAddress, $name = "") {
        $this->bcc[] = array("address" => $emailAddress, "name" => $name);
    }
    
    /**
     * adds a header
     * @param string $name
     * @param string $value
     */
    public function addHeader($name, $value) {
        $this->mimeMessage->addHeader($name, $value);
    }
    
    /**
     * adds a file path to the attachments array
     * @param string $filePath
     */
    public function attachFile($filePath) {
        $this->attachments[] = $filePath;
    }
    
    /**
     * complies the headers
     */
    public function compileHeaders() {
        //add from field and optionally include the from name
        if($this->from[1] == "") {
            $this("from", $this->from[0]);
        }
        else{
            $this->mimeMessage->addHeader("from", "\"".  $this->from[1] ."\" <". $this->from[0] .">");
        }
        
        //add reply to field and optionally include the reply to name
        if(count($this->replyTo) == 0) {
            //do nothing
        }
        else if($this->replyTo[0] != "" && $this->replyTo[1] == "") {
            $this->mimeMessage->addHeader("replyto", $this->replyTo[0]);
        }
        else if($this->replyTo[0] != "" && $this->replyTo[1] != "") {
            $this->mimeMessage->addHeader("replyto", "\"". $this->replyTo[1] ."\" <". $this->replyTo[0] .">");
        }
        
        //add subject
        $this->mimeMessage->addHeader("subject", $this->subject);
        
        //Add to to header data
        $toAddresses = "";
        for($i = 0; $i < count($this->to); $i++) {
            //if the name field is specified add it
            if($this->to[$i]["name"] != "") {
                $toAddresses .= "\"". $this->to[$i]["name"] ."\" ";
            }
            
            //detect whether we need to add a comma
            $toAddresses .= "<". $this->to[$i]["address"] .">". ($i + 1 == count($this->to) ? "" :  ", ");    
        }
        
        $this->mimeMessage->addHeader("to", $toAddresses);
        
        //add cc to header data
        $ccAddresses = "";
        for($i = 0; $i < count($this->cc); $i++) {    
            //if the name field is specified add it
            if($this->cc[$i]["name"] != "") {
                $ccAddresses .= "\"". $this->cc[$i]["name"] ."\" ";
            }
                
            $ccAddresses .= $this->cc[$i]["address"] . ($i + 1 == count($this->cc) ? "" :  ", ");            
        }
        if($ccAddresses != ""){
            $this->mimeMessage->addHeader("Cc", $ccAddresses);
        }
        
        //add date - Date: Thu, 18 Jun 2009 15:07:55 -0700
        date_default_timezone_set("GMT");
        $this->mimeMessage->addHeader("Date", date("r"));
        
        //add message-id
        $this->mimeMessage->addHeader("message-Id",  "<". md5(uniqid(time())) ."@". $this->host .">");
        
        //add priority
        $this->mimeMessage->addHeader("X-priority", $this->priority);
        
        //add x-mailer
        $this->mimeMessage->addHeader("X-Mailer", "Weblegs.SMTPClient (www.weblegs.org)");
        
        //add mime version
        $this->mimeMessage->addHeader("MIME-Version", "1.0");
    }
    
    /**
     * complies the message
     */
    public function compileMessage() {
        //setup our *empty* MIME objects for alternative message
        $alternativeMessage = new MIMEmessage();
        $htmlMessage = new MIMEmessage();
        $textMessage = new MIMEmessage();
        
        //create the main boundry for this message (not always used)
        $mainBoundary = "----=_Part_". md5(uniqid(time()));
        
        //lets figure out how to handle this message
        if($this->isHTML == false && count($this->attachments) == 0) {
            //this is just a plain text message
            $this->mimeMessage->addHeader("Content-Type", "text/plain;\n\tcharset=US-ASCII;");
            $this->mimeMessage->addHeader("Content-Transfer-Encoding", "quoted-printable");
            
            //put the content into the message body
            $this->mimeMessage->body = $this->message;
        }
        else if($this->isHTML == true && count($this->attachments) == 0) {
            //this is an alternative html/text based message
            $this->mimeMessage->addHeader("Content-Type", "multipart/alternative;\n\tboundary=\"".  $mainBoundary ."\"");
            $this->mimeMessage->preamble = "This is a multi-part message in MIME format.";
            
            //build the html part of this message
            $htmlMessage = new MIMEmessage();
            $htmlMessage->addHeader("Content-Type", "text/html; charset=US-ASCII;");
            $htmlMessage->addHeader("Content-Transfer-Encoding", "quoted-printable");
            $htmlMessage->body = $this->message;
            
            //build the text part of this message
            $textMessage = new MIMEmessage();
            $textMessage->addHeader("Content-Type", "text/plain; charset=US-ASCII;");
            $textMessage->addHeader("Content-Transfer-Encoding", "quoted-printable");
            $textMessage->body = $this->htmlToText($this->message); 
            
            //add html/text parts to the main message
            $this->mimeMessage->parts[] = $textMessage;
            $this->mimeMessage->parts[] = $htmlMessage;
            
        }
        else if(count($this->attachments) != 0) {
            //this message is mixed
            $this->mimeMessage->addHeader("Content-Type", "multipart/mixed;\n\tboundary=\"". $mainBoundary  ."\"");
            $this->mimeMessage->preamble = "This is a multi-part message in MIME format.";
            
            //is this an alternative message?
            if($this->isHTML == true) {
                //create the alternative boundry for this message
                $alternativeBoundary = "----=_Part_". md5(uniqid(time()));
                
                //build the Alternative part of this message
                $alternativeMessage = new MIMEmessage();
                $alternativeMessage->addHeader("Content-Type", "multipart/alternative;\n\tboundary=\"".  $alternativeBoundary ."\"");
                
                //build the html part of this message
                $htmlMessage = new MIMEmessage();
                $htmlMessage->addHeader("Content-Type", "text/html; charset=US-ASCII;");
                $htmlMessage->addHeader("Content-Transfer-Encoding", "quoted-printable");
                $htmlMessage->body = $this->message;
                
                //build the text part of this message
                $textMessage = new MIMEmessage();
                $textMessage->addHeader("Content-Type", "text/plain; charset=US-ASCII;");
                $textMessage->addHeader("Content-Transfer-Encoding", "quoted-printable");
                $textMessage->body = $this->htmlToText($this->message); 
                
                //add html/text parts to the alternative message
                $alternativeMessage->parts[] = $textMessage;
                $alternativeMessage->parts[] = $htmlMessage;
                
                //add the alternative message to the main message
                $this->mimeMessage->parts[] = $alternativeMessage;
            }
            else {
                //build the text part of this message
                $textMessage = new MIMEmessage();
                $textMessage->addHeader("Content-Type", "text/plain; charset=US-ASCII;");
                $textMessage->addHeader("Content-Transfer-Encoding", "quoted-printable");
                $textMessage->body = $this->message;
                
                //add the text message to the main message
                $this->mimeMessage->parts[] = $textMessage;
            }
            
            //add attachments to the main message message
            for($i = 0 ; $i < count($this->attachments); $i++) {
                //create mime message object
                $filePath = $this->attachments[$i];
                
                //get file info
                $thisFileInfo = pathinfo($filePath);
                
                //setup new MIME message for this attachment
                $attachmentMessage = new MIMEmessage();
                $attachmentMessage->addHeader("Content-Type", $this->getContentTypeByExtension($thisFileInfo['extension']) .";\n\tname=\"". $thisFileInfo['basename'] ."\"");
                $attachmentMessage->addHeader("Content-Transfer-Encoding", "base64");
                $attachmentMessage->addHeader("Content-Disposition", "attachment;\n\tfilename=\"". $thisFileInfo['basename'] ."\"");
                $attachmentMessage->fileBody = file_get_contents($filePath);
                
                //add this attachment to the main message
                $this->mimeMessage->parts[] = $attachmentMessage;
            }
        }
    }
    
    /**
     * sends the message
     */
    public function send() {
        //make sure host was supplied
        if($this->host == "") {
            throw new Exception("Weblegs.SMTPClient.send(): No host specified. ");
        }
        
        //assign credentials if username is supplied
        if($this->username != "") {
            $this->smtpDriver->username = $this->username;
            $this->smtpDriver->password = $this->password;
        }
        
        //should we open the socket for them?
        if(!$this->openedManually) {
            //setup the SMTPDriver
            $this->smtpDriver->host = $this->host;
            $this->smtpDriver->port = $this->port;
            $this->smtpDriver->protocol = $this->protocol;
            $this->smtpDriver->timeout = $this->timeout;
            $this->openedManually = true;
            
            //open up
            try {
                $this->smtpDriver->open();
            }
            catch(Exception $e) {
                throw new Exception("Weblegs.SMTPClient.send(): Failed to open connection. ". $e->getmessage());
            }
        }
        
        //set the from address
        $this->smtpDriver->setFrom($this->from[0]);
        
        //add recipients
            //to addresses
            for($i = 0 ; $i < count($this->to) ; $i++) {
                $this->smtpDriver->addRecipient($this->to[$i]["address"]);
            }
            
            //cc addresses
            for($i = 0 ; $i < count($this->cc); $i++) {
                $this->smtpDriver->addRecipient($this->cc[$i]["address"]);
            }
            
            //bcc addresses
            for($i = 0 ; $i < count($this->bcc); $i++) {
                $this->smtpDriver->addRecipient($this->bcc[$i]["address"]);
            }
        //end add recipients
        
        //prepare headers
        $this->compileHeaders();
        
        //prepair message
        $this->compilemessage();
        
        //try sending
        try {
            $this->smtpDriver->send($this->mimeMessage->toString());
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.SMTPClient.send(): Failed to send message. ". $e->getmessage());
        }
        
        //should we close the socket for them?
        if(!$this->openedManually) {
            $this->close();
        }
    }
    
    /**
     * resets most object properties
     */
    public function reset() {
        $this->from = array();
        $this->replyTo = array();
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        $this->priority = "3";
        $this->subject = "";
        $this->message = "";
        $this->isHTML = false;
        $this->attachments = array();
        $this->mimeMessage = new MIMEmessage();
    }
    
    /**
     * gets the content type based on the file extension
     * @param string $extension the file's extension
     * @return string The content type
     */
    public function getContentTypeByExtension($extension = "") {
        $extension = strtolower($extension);
        
        if(!isset($this->contentTypeList[$extension])) {
            return 'application/x-unknown-content-type';
        }
        else{
            return $this->contentTypeList[$extension];
        }
    }
    
    /**
     * converts html into text
     * @param string $html
     * @return string Transformed input
     */
    public function htmlToText($html) {
        //keep copy of HTML
        $textOnly = $html;
        
        //trim it down
        $textOnly = trim($textOnly);
        
        //make custom mods to HTML
            //seperators (80 chars on purpose)
            $horizontalRule = "--------------------------------------------------------------------------------";
            $tabletopBottom = "********************************************************************************";
            
            //remove all line breaks
            $textOnly = preg_replace("/\r/", "", $textOnly);
            $textOnly = preg_replace("/\n/", "", $textOnly);
            
            //remove head
            $textOnly = preg_replace("/<(head|HEAD).*?\/(head|HEAD)>/", "", $textOnly);
            
            //heading tags
            $textOnly = preg_replace("/<\/*(h|H)(1|2|3|4|5|6).*?>/", "\n", $textOnly);
            
            //paragraph tags
            $textOnly = preg_replace("/<(p|P).*?>/", "\n\n", $textOnly);
            
            //div tags
            $textOnly = preg_replace("/<(div|DIV).*?>/", "\n\n", $textOnly);
            
            //br tags
            $textOnly = preg_replace("/<(br|BR|bR|Br).*?>/", "\n", $textOnly);
            
            //hr tags
            $textOnly = preg_replace("/<(hr|HR|hR|Hr).*?>/", "\n". $horizontalRule, $textOnly);
            
            //table tags
            $textOnly = preg_replace("/<\/*(table|TABLE).*?>/", "\n". $tabletopBottom, $textOnly);
            $textOnly = preg_replace("/<(tr|TR|tR|Tr).*?>/", "\n", $textOnly);
            $textOnly = preg_replace("/<\/(td|TD|tD|Td).\*?>/", "\t", $textOnly);
            
            //list tags
            $textOnly = preg_replace("/<\/*(ol|OL|oL|Ol).*?>/", "\n", $textOnly);
            $textOnly = preg_replace("/<\/\*(ul|UL|uL|Ul).*?>/", "\n", $textOnly);
            $textOnly = preg_replace("/<(li|LI|lI|Li).*?>/", "\n\t(*) ", $textOnly);
            
            //lets not lose our links
            $textOnly = preg_replace("/<a href=(\"|\')(.*?)(\"|\')>(.*?)<\/a>/i", "$4 [$2]", $textOnly);
            $textOnly = preg_replace("/<a HREF=(\"|\')(.*?)(\"|\')>(.*?)<\/a>/i", "$4 [$2]", $textOnly);
            
            //strip the remaining HTML out of string
            $textOnly = preg_replace("/<(.|\n)*?>/", "", $textOnly);
            
            //loop over each line and truncate lines more than 74 characters
            $tmpFixedText = "";
            $textOnlyLines = explode("\n", $textOnly);
            for($i = 0 ; $i < count($textOnlyLines); $i++) {
                $tmpThisFixedLine = "";
                if(strlen($textOnlyLines[$i]) > 74) {
                    while(strlen($textOnlyLines[$i]) > 74) {
                        //find the next space character furthest to the end (or the 74th character)
                        if(strrpos(substr($textOnlyLines[$i], 0, 73), " ") !== false){
                            $tmpThisFixedLine .= substr($textOnlyLines[$i], 0, strrpos(substr($textOnlyLines[$i], 0, 73), " ")) ."\n";
                            //remove from TextOnlyLines[i]
                            $textOnlyLines[$i] = trim(substr($textOnlyLines[$i], strrpos(substr($textOnlyLines[$i], 0, 73), " ")));
                        }
                        else{
                            //if there is a space in this line after the 74th character lets break at the first chance we get and continue
                            if(strpos($textOnlyLines[$i], " ") !== false) {
                                $tmpThisFixedLine .= substr($textOnlyLines[$i], 0, strpos($textOnlyLines[$i], " ") + 1) ."\n";
                                $textOnlyLines[$i] = substr($textOnlyLines[$i], strpos($textOnlyLines[$i], " ") + 1);
                            }
                            else {
                                //this is a long line w/ no breaking potential
                                $tmpThisFixedLine .= $textOnlyLines[$i];
                                $textOnlyLines[$i] = "";
                            }
                        }
                    }
                    //if there is still content in TextOnlyLines[i] ... append it w/ a new line
                    if(strlen($textOnlyLines[$i]) > 0) {
                        $tmpThisFixedLine .= $textOnlyLines[$i];
                    }
                }
                else {
                    $tmpThisFixedLine = $textOnlyLines[$i] ;
                }
                $tmpThisFixedLine .= "\n";
                $tmpFixedText .= $tmpThisFixedLine;
            }
            $textOnly = $tmpFixedText;
        //end make custom mods to HTML
        return $textOnly;
    }
    
    /**
     * builds the content type array
     * @return array Content types
     */
    public function buildcontentTypeList() {
        return array(
            'hqx'   =>  'application/mac-binhex40',
            'cpt'   =>  'application/mac-compactpro',
            'doc'   =>  'application/msword',
            'bin'   =>  'application/macbinary',
            'dms'   =>  'application/octet-stream',
            'lha'   =>  'application/octet-stream',
            'lzh'   =>  'application/octet-stream',
            'exe'   =>  'application/octet-stream',
            'class' =>  'application/octet-stream',
            'psd'   =>  'application/octet-stream',
            'so'    =>  'application/octet-stream',
            'sea'   =>  'application/octet-stream',
            'dll'   =>  'application/octet-stream',
            'oda'   =>  'application/oda',
            'pdf'   =>  'application/pdf',
            'ai'    =>  'application/postscript',
            'eps'   =>  'application/postscript',
            'ps'    =>  'application/postscript',
            'smi'   =>  'application/smil',
            'smil'  =>  'application/smil',
            'mif'   =>  'application/vnd.mif',
            'xls'   =>  'application/vnd.ms-excel',
            'ppt'   =>  'application/vnd.ms-powerpoint',
            'wbxml' =>  'application/vnd.wap.wbxml',
            'wmlc'  =>  'application/vnd.wap.wmlc',
            'dcr'   =>  'application/x-director',
            'dir'   =>  'application/x-director',
            'dxr'   =>  'application/x-director',
            'dvi'   =>  'application/x-dvi',
            'gtar'  =>  'application/x-gtar',
            'php'   =>  'application/x-httpd-php',
            'php4'  =>  'application/x-httpd-php',
            'php3'  =>  'application/x-httpd-php',
            'phtml' =>  'application/x-httpd-php',
            'phps'  =>  'application/x-httpd-php-source',
            'js'    =>  'application/x-javascript',
            'swf'   =>  'application/x-shockwave-flash',
            'sit'   =>  'application/x-stuffit',
            'tar'   =>  'application/x-tar',
            'tgz'   =>  'application/x-tar',
            'xhtml' =>  'application/xhtml+xml',
            'xht'   =>  'application/xhtml+xml',
            'zip'   =>  'application/zip',
            'mid'   =>  'audio/midi',
            'midi'  =>  'audio/midi',
            'mpga'  =>  'audio/mpeg',
            'mp2'   =>  'audio/mpeg',
            'mp3'   =>  'audio/mpeg',
            'aif'   =>  'audio/x-aiff',
            'aiff'  =>  'audio/x-aiff',
            'aifc'  =>  'audio/x-aiff',
            'ram'   =>  'audio/x-pn-realaudio',
            'rm'    =>  'audio/x-pn-realaudio',
            'rpm'   =>  'audio/x-pn-realaudio-plugin',
            'ra'    =>  'audio/x-realaudio',
            'rv'    =>  'video/vnd.rn-realvideo',
            'wav'   =>  'audio/x-wav',
            'bmp'   =>  'image/bmp',
            'gif'   =>  'image/gif',
            'jpeg'  =>  'image/jpeg',
            'jpg'   =>  'image/jpeg',
            'jpe'   =>  'image/jpeg',
            'png'   =>  'image/png',
            'tiff'  =>  'image/tiff',
            'tif'   =>  'image/tiff',
            'css'   =>  'text/css',
            'html'  =>  'text/html',
            'htm'   =>  'text/html',
            'shtml' =>  'text/html',
            'txt'   =>  'text/plain',
            'text'  =>  'text/plain',
            'log'   =>  'text/plain',
            'rtx'   =>  'text/richtext',
            'rtf'   =>  'text/rtf',
            'xml'   =>  'text/xml',
            'xsl'   =>  'text/xml',
            'mpeg'  =>  'data-lavid',
            'mpg'   =>  'video/mpeg',
            'mpe'   =>  'video/mpeg',
            'qt'    =>  'video/quicktime',
            'mov'   =>  'video/quicktime',
            'avi'   =>  'video/x-msvideo',
            'movie' =>  'video/x-sgi-movie',
            'doc'   =>  'application/msword',
            'word'  =>  'application/msword',
            'xl'    =>  'application/excel',
            'eml'   =>  'message/rfc822'
        );
    }
}
