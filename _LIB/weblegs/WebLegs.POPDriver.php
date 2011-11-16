<?php
//##########################################################################################

/*
Copyright (C) 2005-2011 WebLegs, Inc.
This program is free software: you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation, either
version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.
If not, see <http://www.gnu.org/licenses/>.
*/

//##########################################################################################

//--> Begin Class :: POPClient
	require_once("WebLegs.SocketDriver.php");
	
	class POPDriver {
		//--> Begin :: Properties
			public $Username;
			public $Password;
			public $Host;
			public $Port;
			public $Protocol;
			public $Timeout;
			public $Command;
			public $Reply;
			public $IsError;
			public $Socket;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function POPDriver() {
				$this->Username = "";
				$this->Password = "";
				$this->Host = "";
				$this->Port = 110;
				$this->Protocol = "tcp";//ssl/tls/tcp
				$this->Timeout = 10;
				$this->Command = "";
				$this->Reply = "";
				$this->IsError = false;
				$this->Socket = new SocketDriver();
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Open
			public function Open() {
				//make sure that we are not already connected
				if($this->Socket->IsOpen()) {
					return;
				}
			
				//make sure host was specified
				if($this->Host == "") {
					throw new Exception("Weblegs.POPDriver.Open(): No host specified.");
				}
				
				//make sure that a username was specified
				if($this->Username == "") {
					throw new Exception("Weblegs.POPDriver.Open(): No username specified.");
				}
				
				//make sure that a password was specified
				if($this->Password == "") {
					throw new Exception("Weblegs.POPDriver.Open(): No password specified.");
				}			
				
				//attempt to connect
				$this->Socket->Host = $this->Host;
				$this->Socket->Port = $this->Port;
				$this->Socket->Protocol = $this->Protocol;
				$this->Socket->Timeout = $this->Timeout;
				try {
					//attempt to connect
					$this->Socket->Open();
					
				}catch(Exception $e) {
					throw new Exception("Weblegs.POPDriver.Open(): Failed to connect to host '". $this->Host ."'. ". $e->getMessage());
				}
					
				//read to clear buffer
				$this->Socket->ReadLine();
						
				//send username
				$this->Request("USER ". $this->Username);
				if($this->IsError) {
					throw new Exception("Weblegs.POPDriver.Open(): '". $this->Command ."' command was not accepted by the server. (POP Error: ". $this->Reply .").");
				}
				
				//send password
				$this->Request("PASS ". $this->Password);
				if($this->IsError) {
					throw new Exception("Weblegs.POPDriver.Open(): '". $this->Command ."' command was not accepted by the server. (POP Error: ". $this->Reply .").");
				}
			}
		//<-- End Method :: Open
		
		//##################################################################################
		
		//--> Begin Method :: Close
			public function Close() {
				//see if there is an open connection
				if(!$this->Socket->IsOpen()) {
					return;
				}
				
				//finalize session
				$this->Request("QUIT");
				if($this->IsError) {
					throw new Exception("Weblegs.POPDriver.Close(): '". $this->Command ."' command was not accepted by the server. (POP Error: ". $this->Reply .").");
				}
				
				//close socket
				$this->Socket->Close();
			}
		//<-- End Method :: Close
		
		//##################################################################################
		
		//--> Begin Method :: GetMessageCount
			public function GetMessageCount() {
				$this->Request("STAT");
				if($this->IsError) {
					throw new Exception("Weblegs.POPDriver.GetMessageCount(): '". $this->Command ."' command was not accepted by the server. (POP Error: ". $this->Reply .").");
				}
				
				$MessageCount = split(" ", $this->Reply);
				
				//the second element is the number of messages
				return $MessageCount[1];
			}
		//<-- End Method :: GetMessageCount
		
		//##################################################################################
		
		//--> Begin Method :: GetMailBoxSize
			public function GetMailBoxSize() {
				$this->Request("STAT");
				if($this->IsError) {
					throw new Exception("Weblegs.POPDriver.GetMailBoxSize(): '". $this->Command ."' command was not accepted by the server. (POP Error: ". $this->Reply .").");
				}
				
				$MessageCount = split(" ", $this->Reply);
				
				//the third element is the number of messages
				return $MessageCount[2];
			}
		//<-- End Method :: GetMailBoxSize
		
		//##################################################################################
		
		//--> Begin Method :: GetHeaders
			public function GetHeaders($MessageNumber) {
				//send command and collect response and read until eol
				$this->Request("TOP ". $MessageNumber ." 0");
				if($this->IsError) {
					throw new Exception("Weblegs.POPDriver.GetHeaders(): '". $this->Command ."' command was not accepted by the server. (POP Error: ". $this->Reply .").");
				}
				
				return $this->Reply;
			}
		//<-- End Method :: GetHeaders
		
		//##################################################################################
		
		//--> Begin Method :: GetMessage
			public function GetMessage($MessageNumber) {
				//send command and collect response and read until eol
				$this->Request("RETR ". $MessageNumber);
				if($this->IsError) {
					throw new Exception("Weblegs.POPDriver.GetMessage(): '". $this->Command ."' command was not accepted by the server. (POP Error: ". $this->Reply .").");
				}
				
				//check on this
				return $this->Reply;
			}
		//<-- End Method :: GetMessage
		
		//##################################################################################
		
		//--> Begin Method :: DeleteMessage
			public function DeleteMessage($MessageNumber) {
				//send command
				$this->Request("DELE ". $MessageNumber);
				if($this->IsError) {
					throw new Exception("Weblegs.POPDriver.DeleteMessage(): '". $this->Command ."' command was not accepted by the server. (POP Error: ". $this->Reply .").");
				}
			}
		//<-- End Method :: DeleteMessage
		
		//##################################################################################
		
		//--> Begin Method :: Request
			public function Request($Command) {
				$this->Command = $Command;
				
				//write to connection
				$this->Socket->Write($this->Command ."\r\n");
			
				//read from connect	
				$MyResponse = "";
				$tmpResponse = $this->Socket->ReadLine();
				
				if(substr($tmpResponse, 0, 1) == "-") {
					$this->IsError = true;
					$MyResponse = $tmpResponse;
				}
				else {
					//should we read a multi-line response?
					if(
						($this->Command == "LIST" && substr($this->Command, 0, 5) == "LIST ") || 
						substr($this->Command, 0, 4) == "RETR" || 
						substr($this->Command, 0, 3) == "TOP" || 
						substr($this->Command, 0, 4) == "UIDL"
					) {
						while(trim($tmpResponse) != ".") {
							$MyResponse .= $tmpResponse ."\r\n";
							$tmpResponse = $this->Socket->ReadLine();
						}
					}
					else {
						$MyResponse = $tmpResponse;
					}
				}
		
				//read from connection
				$this->Reply = $MyResponse;
				
				return $this->Reply;
			}
		//<-- End Method :: Request
	}
//<-- End Class :: POPDriver

//##########################################################################################
?>