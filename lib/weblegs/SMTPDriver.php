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

require_once("SocketDriver.php");

class SMTPDriver {
    public $username;
    public $password;
    public $host;
    public $port;
    public $protocol; //ssl/tls/tcp
    public $timeout;
    public $annoucement;
    public $replyCode;
    public $replyText;
    public $reply;
    public $command;
    public $socket;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->username = "";
        $this->password = "";
        $this->host = "";
        $this->port = 25;
        $this->protocol = "tcp";//ssl/tls/tcp
        $this->timeout = 10;
        $this->annoucement = "";
        $this->replyCode = -1;
        $this->replyText = "";
        $this->reply = "";
        $this->command = "";
        $this->socket = new SocketDriver();
    }
    
    /**
     * opens the connection
     */
    public function open() {
        //make sure that we are not already connected
        if($this->socket->isOpen()) {
            return;
        }
        
        //make sure host was specified
        if($this->host == "") {
            throw new Exception("Weblegs.SMTPDriver.open(): No host specified.");
        }
        
        //attempt to connect
        $this->socket->host = $this->host;
        $this->socket->port = $this->port;
        $this->socket->protocol = $this->protocol;
        $this->socket->timeout = $this->timeout;
        
        try {
            $this->socket->open();
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.SMTPDriver.open(): Failed to connect to host. ". $e->getMessage());
        }
        
        //retrieve announcements (also clears the response from socket)
        $this->annoucement = $this->socket->readLine();
        
        //try "EHLO" command first
        $this->request("EHLO ". $this->host);
        
        if($this->replyCode != 250) {
            //try "HELO" now
            $this->request("HELO ". $this->host);
            
            if($this->replyCode != 250) {
                throw new Exception("Weblegs.SMTPDriver.open(): 'HELO' and 'EHLO' command(s) were not accepted by the server (SMTP Error Number: ". $this->replyCode .". SMTP Error: ". $this->replyText ." Full Text: ". $this->reply .").");
            }
        }
        
        //if username is not blank this implies use of authentication
        if($this->username != "") {
            if($this->authenticate("AUTH LOGIN") == false) {
                if($this->authenticate("AUTH PLAIN") == false) {
                    if($this->authenticate("AUTH CRAM-MD5") == false) {
                        throw new Exception("Weblegs.SMTPDriver.open(): '". $this->command ."' command was not accepted by the server (SMTP Error Number: ". $this->replyCode .". SMTP Error: ". $this->replyText ." Full Text: ". $this->reply .").");
                    }
                }
            }
        }
    }
    
    /**
     * closes the connection
     */
    public function close() {
        //see if there is an open connection
        if(!$this->socket->isOpen()) {
            return;
        }
        
        //set from 
        $this->request("QUIT");
        if($this->replyCode != 221) {
            throw new Exception("Weblegs.SMTPDriver.close(): '". $this->command ."' command was not accepted by the server (SMTP Error Number: ". $this->replyCode .". SMTP Error: ". $this->replyText ." Full Text: ". $this->reply .").");
        }
        
        //close socket
        $this->socket->close();
    }
    
    /**
     * attempts to authenticate
     * @param string $command
     * @return bool If we authenticated or not
     */
    public function authenticate($command) {
        switch($command) {
            //- - - - - - - - - - - - - - - - - - - -//
            case "AUTH CRAM-MD5":
                $this->request($command);
                
                if($this->replyCode != 334) {
                    return false;
                }
                
                //get the hmac-md5 hash
                $digest = Codec::HMACMD5Encrypt($this->password, Codec::base64Decode($this->replyText));
                
                $this->request(trim(Codec::base64Encode($this->username . ' ' . $digest)));
                
                if($this->replyCode != 235) {
                    return false;
                }
        
                //everything went through
                return true;
                
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            case "AUTH LOGIN":
                $this->request($command);
                
                if($this->replyCode != 334) {
                    return false;
                }
                
                $this->request(trim(Codec::base64Encode($this->username)));
                
                if($this->replyCode != 334) {
                    return false;
                }
                
                $this->request(trim(Codec::base64Encode($this->password)));
                
                if($this->replyCode != 235) {
                    return false;
                }
                
                //everything went through
                return true;
                
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            case "AUTH PLAIN":
                $this->request($command);
                
                if($this->replyCode != 334) {
                    return false;
                }
                
                $this->request(trim(Codec::base64Encode(chr(0) . $this->username . chr(0) . $this->password)));
                
                if($this->replyCode != 235) {
                    return false;
                }
                
                //everything went through
                return true;
                
                break;
            //- - - - - - - - - - - - - - - - - - - -//
            default:
                //do nothing
                break;
        }
        
        return false;
    }
    
    /**
     * sets the from address
     * @param string $fromAddress
     */
    public function setFrom($fromAddress) { 
        $this->request("MAIL FROM:<". $fromAddress .">");
    
        if($this->replyCode != 250) {
            throw new Exception("Weblegs.SMTPDriver.setFrom(): '". $this->command ."' command was not accepted by the server (SMTP Error Number: ". $this->replyCode .". SMTP Error: ". $this->replyText ." Full Text: ". $this->reply .").");
        }
    }
    
    /**
     * adds a recipient
     * @param string $emailAddress
     */
    public function addRecipient($emailAddress) {
        $this->request("RCPT TO:<". $emailAddress .">");
    
        if($this->replyCode != 250 && $this->replyCode != 251) {
            throw new Exception("Weblegs.SMTPDriver.addRecipient(): '". $this->command ."' command was not accepted by the server (SMTP Error Number: ". $this->replyCode .". SMTP Error: ".$this->replyText ." Full Text: ". $this->reply .").");
        }
    }
    
    /**
     * sends the message
     * @param string $data
     */
    public function send($data) {
        $this->request("DATA");
    
        //mak sure no error codes were returned
        if($this->replyCode != 354 && $this->replyCode != 250) {
            throw new Exception("Weblegs.SMTPDriver.send(): '". $this->command ."' command was not accepted by the server (SMTP Error Number: ". $this->replyCode .". SMTP Error: ". $this->replyText ." Full Text: ". $this->reply .").");
        }
    
        //prepare data
        $messageDataArray = explode("\n", $data);
    
        //write lines to connection
        foreach($messageDataArray as $key => $value) {
            $this->socket->write($value ."\r\n");
        }
        
        //finalize DATA command
        $this->request("\r\n.");
        
        //mak sure no error codes were returned
        if($this->replyCode != 250) {
            throw new Exception("Weblegs.SMTPDriver.send(): '". $this->command ."' command was not accepted by the server (SMTP Error Number: ". $this->replyCode .". SMTP Error: ". $this->replyText ." Full Text: ". $this->reply .").");
        }
    }
    
    /**
     * makes a request
     * @param string $command
     * @return string The reply
     */
    public function request($command) {
        $this->replyCode = "";
        $this->replyText = "";
        $this->reply = "";
        
        if(!$this->socket->isOpen()) {
            throw new Exception("Weblegs.SMTPDriver.request(): No Connection found.");
        }
        
        //set command property        
        if($command != "") {
            $this->command = $command;
        }
        
        //write to connection
        $this->socket->write($this->command ."\r\n");
        
        //'250 TEXT' <- break on this type of line, not this type of line '250-TEXT'
        while($line = $this->socket->readLine()) {
            if(substr($line, 3, 1) == " ") {                
                $this->reply = $line;
                break;
            }
        }
        
        //parse reply data
        $this->replyText = substr($this->reply, 4);
        $this->replyCode = substr($this->reply, 0, 3);
        return $this->reply;
    }
}
