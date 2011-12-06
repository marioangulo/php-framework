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
 
class SocketDriver {
    public $connection;
    public $host;
    public $port;
    public $protocol;
    public $timeout;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->connection = null;
        $this->host = "";
        $this->port = -1;
        $this->protocol = "tcp"; //ssl/tls/tcp
        $this->timeout = 10;
    }
     
    /**
     * destruct the object
     */
    public function __destruct() {
        $this->close();
    }
     
    /**
     * opens the socket
     */
    public function open() {
        $host = $this->host;
        $port = $this->port;
        $errorNumber = "";
        $errorString = "";
        $timeout = $this->timeout;
         
        //attempt to connect
        $this->connection = @fsockopen($this->protocol ."://". $host, (int)$port, $errorNumber, $errorString, $timeout);
        
        //verify connection was made
        if(empty($this->connection)) {
            throw new Exception("Weblegs.SocketDriver.open(): Failed to connect. (Error: '". $errorString ."' Error Number: '". $errorNumber ."')");
        }
    }
     
    /**
     * closes the socket
     */
    public function close() {
        if(is_null($this->connection)) {
            return;
        }
        //see if there is an open connection
        else if(!$this->isOpen()) {
            return;
        }
        else {
            fclose($this->connection);
            $this->connection = null;
        }
    }
     
    /**
     * reads bytes from the connection
     * @param int $bytes
     * @return bytes The bytes
     */
    public function readBytes($bytes) {
        return @fgets($this->connection, $bytes);
    }
     
    /**
     * reads a line from the connection
     * @return string The line
     */
    public function readLine() {
        //remove \r\n - the developer can add it again if they want
        return str_replace(array("\r\n", "\n", "\r"), "", @fgets($this->connection));
    }
    
    /**
     * reads from the connection
     * @return string The response
     */
    public function read() {
        $line = "";
        $data = "";
         
        while($line = @fgets($this->connection)) {
            //collect data
            $data .= $line;
        }
         
        return $data;
    }
     
    /**
     * writes to the connection
     * @param string $data
     */
    public function write($data) {
        //write data to connection
        fputs($this->connection, $data);
    }
     
    /**
     * flag if connection is open or not
     * @returns bool If connection is open
     */
    public function isOpen() {
        //make sure there is a connection
        if(!empty($this->connection)) {
            //get socket status
            $socketStatus = socket_get_status($this->connection);
             
            if($socketStatus["eof"]) {
                return false;
            }
            else {
                return true;
            }
        }
        else {
            return false;
        }
    }
}
