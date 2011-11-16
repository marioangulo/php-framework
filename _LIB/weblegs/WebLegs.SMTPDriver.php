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

//--> Begin Class :: SMTPDriver
	require_once("WebLegs.SocketDriver.php");
	
	class SMTPDriver {
		//--> Begin :: Properties
			public $Username;
			public $Password;
			public $Host;
			public $Port;
			public $Protocol; //ssl/tls/tcp
			public $Timeout;
			public $Annoucement;
			public $ReplyCode;
			public $ReplyText;
			public $Reply;
			public $Command;
			public $Socket;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function SMTPDriver() {
				$this->Username = "";
				$this->Password = "";
				$this->Host = "";
				$this->Port = 25;
				$this->Protocol = "tcp";//ssl/tls/tcp
				$this->Timeout = 10;
				$this->Annoucement = "";
				$this->ReplyCode = -1;
				$this->ReplyText = "";
				$this->Reply = "";
				$this->Command = "";
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
					throw new Exception("Weblegs.SMTPDriver.Open(): No host specified.");
				}
				
				//attempt to connect
				$this->Socket->Host = $this->Host;
				$this->Socket->Port = $this->Port;
				$this->Socket->Protocol = $this->Protocol;
				$this->Socket->Timeout = $this->Timeout;
				
				try {
					$this->Socket->Open();
				}
				catch(Exception $e) {
					throw new Exception("Weblegs.SMTPDriver.Open(): Failed to connect to host. ". $e->getMessage());
				}
				
				//retrieve announcements (also clears the response from socket)
				$this->Annoucement = $this->Socket->ReadLine();
				
				//try "EHLO" command first
				$this->Request("EHLO ". $this->Host);
				
				if($this->ReplyCode != 250) {
					//try "HELO" now
					$this->Request("HELO ". $this->Host);
					
					if($this->ReplyCode != 250) {
						throw new Exception("Weblegs.SMTPDriver.Open(): 'HELO' and 'EHLO' command(s) were not accepted by the server (SMTP Error Number: ". $this->ReplyCode .". SMTP Error: ". $this->ReplyText ." Full Text: ". $this->Reply .").");
					}
				}
				
				//if username is not blank this implies use of authentication
				if($this->Username != "") {
					if($this->Authenticate("AUTH LOGIN") == false) {
						if($this->Authenticate("AUTH PLAIN") == false) {
							if($this->Authenticate("AUTH CRAM-MD5") == false) {
								throw new Exception("Weblegs.SMTPDriver.Open(): '". $this->Command ."' command was not accepted by the server (SMTP Error Number: ". $this->ReplyCode .". SMTP Error: ". $this->ReplyText ." Full Text: ". $this->Reply .").");
							}
						}
					}
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
				
				//set from 
				$this->Request("QUIT");
				if($this->ReplyCode != 221) {
					throw new Exception("Weblegs.SMTPDriver.Close(): '". $this->Command ."' command was not accepted by the server (SMTP Error Number: ". $this->ReplyCode .". SMTP Error: ". $this->ReplyText ." Full Text: ". $this->Reply .").");
				}
				
				//close socket
				$this->Socket->Close();
			}
		//<-- End Method :: Close
		
		//##################################################################################
		
		//--> Begin Method :: Authenticate
			public function Authenticate($Command) {
				switch($Command) {
					//- - - - - - - - - - - - - - - - - - - -//
					case "AUTH CRAM-MD5":
						$this->Request($Command);
						
						if($this->ReplyCode != 334) {
							return false;
						}
						
						//get the hmac-md5 hash
						$Digest = Codec::HMACMD5Encrypt($this->Password, Codec::Base64Decode($this->ReplyText));
						
						$this->Request(trim(Codec::Base64Encode($this->Username . ' ' . $Digest)));
						
						if($this->ReplyCode != 235) {
							return false;
						}
				
						//everything went through
						return true;
						
						break;
					//- - - - - - - - - - - - - - - - - - - -//
					case "AUTH LOGIN":
						$this->Request($Command);
						
						if($this->ReplyCode != 334) {
							return false;
						}
						
						$this->Request(trim(Codec::Base64Encode($this->Username)));
						
						if($this->ReplyCode != 334) {
							return false;
						}
						
						$this->Request(trim(Codec::Base64Encode($this->Password)));
						
						if($this->ReplyCode != 235) {
							return false;
						}
						
						//everything went through
						return true;
						
						break;
					//- - - - - - - - - - - - - - - - - - - -//
					case "AUTH PLAIN":
						$this->Request($Command);
						
						if($this->ReplyCode != 334) {
							return false;
						}
						
						$this->Request(trim(Codec::Base64Encode(chr(0) . $this->Username . chr(0) . $this->Password)));
						
						if($this->ReplyCode != 235) {
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
		//<-- End Method :: Authenticate
		
		//##################################################################################
		
		//--> Begin Method :: SetFrom
			public function SetFrom($FromAddress) { 
				$this->Request("MAIL FROM:<". $FromAddress .">");
			
				if($this->ReplyCode != 250) {
					throw new Exception("Weblegs.SMTPDriver.SetFrom(): '". $this->Command ."' command was not accepted by the server (SMTP Error Number: ". $this->ReplyCode .". SMTP Error: ". $this->ReplyText ." Full Text: ". $this->Reply .").");
				}
			}
		//<-- End Method :: SetFrom
		
		//##################################################################################
		
		//--> Begin Method :: AddRecipient
			public function AddRecipient($EmailAddress) {
				$this->Request("RCPT TO:<". $EmailAddress .">");
			
				if($this->ReplyCode != 250 && $this->ReplyCode != 251) {
					throw new Exception("Weblegs.SMTPDriver.AddRecipient(): '". $this->Command ."' command was not accepted by the server (SMTP Error Number: ". $this->ReplyCode .". SMTP Error: ".$this->ReplyText ." Full Text: ". $this->Reply .").");
				}
			}	
		//<-- End Method :: AddRecipient
		
		//##################################################################################
		
		//--> Begin Method :: Send
			public function Send($Data) {
				$this->Request("DATA");
			
				//mak sure no error codes were returned
				if($this->ReplyCode != 354 && $this->ReplyCode != 250) {
					throw new Exception("Weblegs.SMTPDriver.Send(): '". $this->Command ."' command was not accepted by the server (SMTP Error Number: ". $this->ReplyCode .". SMTP Error: ". $this->ReplyText ." Full Text: ". $this->Reply .").");
				}
			
				//prepare data
				$MessageDataArray = explode("\n", $Data);
			
				//write lines to connection
				foreach($MessageDataArray as $Key => $Value) {
					$this->Socket->Write($Value ."\r\n");
				}
				
				//finalize DATA command
				$this->Request("\r\n.");
				
				//mak sure no error codes were returned
				if($this->ReplyCode != 250) {
					throw new Exception("Weblegs.SMTPDriver.Send(): '". $this->Command ."' command was not accepted by the server (SMTP Error Number: ". $this->ReplyCode .". SMTP Error: ". $this->ReplyText ." Full Text: ". $this->Reply .").");
				}
			}
		//<-- End Method :: Send
		
		//##################################################################################
		
		//--> Begin Method :: Request
			public function Request($Command) {
				$this->ReplyCode = "";
				$this->ReplyText = "";
				$this->Reply = "";
				
				if(!$this->Socket->IsOpen()) {
					throw new Exception("Weblegs.SMTPDriver.Request(): No Connection found.");
				}
				
				//set Command property		
				if($Command != "") {
					$this->Command = $Command;
				}
				
				//write to connection
				$this->Socket->Write($this->Command ."\r\n");
				
				//'250 TEXT' <- break on this type of line, not this type of line '250-TEXT'
				while($Line = $this->Socket->ReadLine()) {
					if(substr($Line, 3, 1) == " ") {				
						$this->Reply = $Line;
						break;
					}
				}
				
				//parse reply data
				$this->ReplyText = substr($this->Reply, 4);
				$this->ReplyCode = substr($this->Reply, 0, 3);
				return $this->Reply;
			}
		//<-- End Method :: Request
	}
//<-- End Class :: SMTPDriver

//##########################################################################################
?>