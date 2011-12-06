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

class POPDriver {
    public $username;
    public $password;
    public $host;
    public $port;
    public $protocol;
    public $timeout;
    public $command;
    public $reply;
    public $isError;
    public $socket;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->username = "";
        $this->password = "";
        $this->host = "";
        $this->port = 110;
        $this->protocol = "tcp";//ssl/tls/tcp
        $this->timeout = 10;
        $this->command = "";
        $this->reply = "";
        $this->isError = false;
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
            throw new Exception("Weblegs.POPDriver.open(): No host specified.");
        }
        
        //make sure that a username was specified
        if($this->username == "") {
            throw new Exception("Weblegs.POPDriver.open(): No username specified.");
        }
        
        //make sure that a password was specified
        if($this->password == "") {
            throw new Exception("Weblegs.POPDriver.open(): No password specified.");
        }            
        
        //attempt to connect
        $this->socket->host = $this->host;
        $this->socket->port = $this->port;
        $this->socket->protocol = $this->protocol;
        $this->socket->timeout = $this->timeout;
        try {
            //attempt to connect
            $this->socket->open();
            
        }catch(Exception $e) {
            throw new Exception("Weblegs.POPDriver.open(): Failed to connect to host '". $this->host ."'. ". $e->getMessage());
        }
            
        //read to clear buffer
        $this->socket->readLine();
                
        //send username
        $this->request("USER ". $this->username);
        if($this->isError) {
            throw new Exception("Weblegs.POPDriver.open(): '". $this->command ."' command was not accepted by the server. (POP Error: ". $this->reply .").");
        }
        
        //send password
        $this->request("PASS ". $this->password);
        if($this->isError) {
            throw new Exception("Weblegs.POPDriver.open(): '". $this->command ."' command was not accepted by the server. (POP Error: ". $this->reply .").");
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
        
        //finalize session
        $this->request("QUIT");
        if($this->isError) {
            throw new Exception("Weblegs.POPDriver.close(): '". $this->command ."' command was not accepted by the server. (POP Error: ". $this->reply .").");
        }
        
        //close socket
        $this->socket->close();
    }
    
    /**
     * gets the message count
     * @return string Message count
     */
    public function getMessageCount() {
        $this->request("STAT");
        if($this->isError) {
            throw new Exception("Weblegs.POPDriver.getMessageCount(): '". $this->command ."' command was not accepted by the server. (POP Error: ". $this->reply .").");
        }
        
        $messageCount = split(" ", $this->reply);
        
        //the second element is the number of messages
        return $messageCount[1];
    }
    
    /**
     * gets the mail box size
     * @return string Mailbox size
     */
    public function getMailBoxSize() {
        $this->request("STAT");
        if($this->isError) {
            throw new Exception("Weblegs.POPDriver.getMailBoxSize(): '". $this->command ."' command was not accepted by the server. (POP Error: ". $this->reply .").");
        }
        
        $messageCount = split(" ", $this->reply);
        
        //the third element is the number of messages
        return $messageCount[2];
    }
    
    /**
     * gets the headers for the specified message
     * @param int $messageNumber the index of the message
     * @return string The message headers
     */
    public function getHeaders($messageNumber) {
        //send command and collect response and read until eol
        $this->request("TOP ". $messageNumber ." 0");
        if($this->isError) {
            throw new Exception("Weblegs.POPDriver.getHeaders(): '". $this->command ."' command was not accepted by the server. (POP Error: ". $this->reply .").");
        }
        
        return $this->reply;
    }
    
    /**
     * gets the specified message
     * @param int $messageNumber the index of the message
     * @return string The message
     */
    public function getMessage($messageNumber) {
        //send command and collect response and read until eol
        $this->request("RETR ". $messageNumber);
        if($this->isError) {
            throw new Exception("Weblegs.POPDriver.getMessage(): '". $this->command ."' command was not accepted by the server. (POP Error: ". $this->reply .").");
        }
        
        //check on this
        return $this->reply;
    }
    
    /**
     * deletes the specified message
     * @param int $messageNumber the index of the message
     */
    public function deleteMessage($messageNumber) {
        //send command
        $this->request("DELE ". $messageNumber);
        if($this->isError) {
            throw new Exception("Weblegs.POPDriver.deleteMessage(): '". $this->command ."' command was not accepted by the server. (POP Error: ". $this->reply .").");
        }
    }
    
    /**
     * makes a request
     * @param string $command the command to run
     * @return string The reply
     */
    public function request($command) {
        $this->command = $command;
        
        //write to connection
        $this->socket->write($this->command ."\r\n");
    
        //read from connect    
        $myResponse = "";
        $tmpResponse = $this->socket->readLine();
        
        if(substr($tmpResponse, 0, 1) == "-") {
            $this->isError = true;
            $myResponse = $tmpResponse;
        }
        else {
            //should we read a multi-line response?
            if(
                ($this->command == "LIST" && substr($this->command, 0, 5) == "LIST ") || 
                substr($this->command, 0, 4) == "RETR" || 
                substr($this->command, 0, 3) == "TOP" || 
                substr($this->command, 0, 4) == "UIDL"
            ) {
                while(trim($tmpResponse) != ".") {
                    $myResponse .= $tmpResponse ."\r\n";
                    $tmpResponse = $this->socket->readLine();
                }
            }
            else {
                $myResponse = $tmpResponse;
            }
        }
        
        //read from connection
        $this->reply = $myResponse;
        
        return $this->reply;
    }
}
