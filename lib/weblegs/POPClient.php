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

require_once("POPDriver.php");
require_once("MIMEMessage.php");

class POPClient {
    public $username;
    public $password;
    public $host;
    public $port;
    public $protocol;
    public $timeout;
    public $popDriver;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->username;
        $this->password;
        $this->host;
        $this->port = 110;
        $this->protocol = "tcp";//ssl/tls/tcp
        $this->timeout = 10;
        $this->popDriver = new POPDriver();
    }
    
    /**
     * opens the connection
     */
    public function open() {
        //set properties
        $this->popDriver->username = $this->username;
        $this->popDriver->password = $this->password;
        $this->popDriver->host = $this->host;
        $this->popDriver->port = $this->port;
        $this->popDriver->protocol = $this->protocol;
        $this->popDriver->timeout = $this->timeout;
        
        try {
            //connect        
            $this->popDriver->open();
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.POPClient.open(): Failed to connect to host '". $this->host ."'. ". $e->getMessage());
        }
    }
    
    /**
     * closes the connection
     */
    public function close() {
        try {
            //disconnect        
            $this->popDriver->close();
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.POPClient.close(): Failed to close connection. ". $e->getMessage());
        }
    }
    
    /**
     * gets the message count
     * @return int Message count
     */
    public function getMessageCount() {
        try {
            return $this->popDriver->getMessageCount();
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.POPClient.getMessageCount(): Failed to get message count. ". $e->getMessage());
        }
    }
    
    /**
     * gets the mail box size
     * @return int Mailbox size
     */
    public function getMailboxSize() {
        try {
            return $this->popDriver->getMailboxSize();
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.POPClient.getMailboxSize(): Failed to get mailbox size. ". $e->getMessage());
        }
    }
    
    /**
     * deletes the specified message
     * @param int $messageNumber the index of the message
     */
    public function deleteMessage($messageNumber) {
        $this->popDriver->deleteMessage($messageNumber);
    }
    
    /**
     * deletes the specified messages
     * @param int $start the start index of the messages
     * @param int $end the end index of the messages
     */
    public function deleteMessages($start = null, $end = null) {
        if(is_null($start) && is_null($end)) {
            $start = 1;
            $end = $this->getMessageCount();
        }
        
        //collect all mime messages
        for($start; $start <= $end; $start++) {
            $this->popDriver->deleteMessage($start);
        }
    }
    
    /**
     * gets the specified message as a mime message object
     * @param int $messageNumber the index of the message
     * @return MIMEMessage The message
     */
    public function getMIMEMessage($messageNumber) {
        return new MIMEMessage($this->popDriver->getMessage($messageNumber));
    }
    
    /**
     * gets the specified messages as a mime message objects
     * @param int $start the start index of the messages
     * @param int $end the end index of the messages
     * @return MIMEMessage The messages
     */
    public function getMIMEMessages($start = null, $end = null) {
        //get all messages
        if(is_null($start) && is_null($end)) {
            $start = 1;
            $end = $this->getMessageCount();
        }
        
        //create collection array
        $mimeMessages = array();
        
        //collect all mime messages
        for($start; $start <= $end; $start++) {
            $mimeMessages[] =  new MIMEMessage($this->popDriver->getMessage($start));
        }
        
        return $mimeMessages;
    }
    
    /**
     * gets the headers for the specified message
     * @param int $messageNumber the index of the message
     * @return string|null The message headers
     */
    public function getHeader($messageNumber) {
        $headers = $this->popDriver->getHeaders($messageNumber);
        if(count($headers) > 0) {
            return $headers[0];
        }
        return;
    }
    
    /**
     * gets the headers for the specified messages
     * @param int $start the start index of the messages
     * @param int $end the end index of the messages
     * @return array Of message headers
     */
    public function getHeaders($start = null, $end = null) {
        //get all headers
        if(is_null($start) && is_null($end)) {
            $start = 1;
            $end = $this->getMessageCount();
        }
        
        //create collection array
        $myHeaders = array();
        
        //collect all mime messages
        for($start; $start <= $end; $start++) {
            $myHeaders[] = $this->popDriver->getHeaders($start);
        }
        
        return $myHeaders;
    }
    
    /**
     * gets the specified message
     * @param int $messageNumber the index of the message
     * @return string The message
     */
    public function getMessage($messageNumber) {
        return $this->popDriver->getMessage($messageNumber);
    }
    
    /**
     * gets the specified messages
     * @param int $start the start index of the messages
     * @param int $end the end index of the messages
     * @return array The messages
     */
    public function getMessages($start = null, $end = null) {
        //get all messages
        if(is_null($start) && is_null($end)) {
            $start = 1;
            $end = $this->getMessageCount();
        }
        
        //create collection variable
        $messages = array();
        
        //collect all messages
        for($start; $start <= $end; $start++) {
            try {
                $messages[] =  $this->popDriver->getMessage($start);
            }
            catch(Exception $e) {
                throw new IndexOutOfRangeException("Weblegs.POPClient.getMessages(): Failed to get message #". $i .". ". $e->getMessage());
            }
        }
        
        return $messages;
    }
}
