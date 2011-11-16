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
	require_once("WebLegs.POPDriver.php");
	require_once("WebLegs.MIMEMessage.php");
	
	class POPClient {
		//--> Begin :: Properties
			public $Username;
			public $Password;
			public $Host;
			public $Port;
			public $Protocol;
			public $Timeout;
			public $POPDriver;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function POPClient() {
				$this->Username;
				$this->Password;
				$this->Host;
				$this->Port = 110;
				$this->Protocol = "tcp";//ssl/tls/tcp
				$this->Timeout = 10;
				$this->POPDriver = new POPDriver();
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Open
			public function Open() {
				//set properties
				$this->POPDriver->Username = $this->Username;
				$this->POPDriver->Password = $this->Password;
				$this->POPDriver->Host = $this->Host;
				$this->POPDriver->Port = $this->Port;
				$this->POPDriver->Protocol = $this->Protocol;
				$this->POPDriver->Timeout = $this->Timeout;
				
				try {
					//connect		
					$this->POPDriver->Open();
				}
				catch(Exception $e) {
					throw new Exception("Weblegs.POPClient.Open(): Failed to connect to host '". $this->Host ."'. ". $e->getMessage());
				}
			}
		//<-- End Method :: Open
		
		//##################################################################################
		
		//--> Begin Method :: Close
			public function Close() {
				try {
					//disconnect		
					$this->POPDriver->Close();
				}
				catch(Exception $e) {
					throw new Exception("Weblegs.POPClient.Close(): Failed to close connection. ". $e->getMessage());
				}
			}
		//<-- End Method :: Close
		
		//##################################################################################
		
		//--> Begin Method :: GetMessageCount
			public function GetMessageCount() {
				try {
					return $this->POPDriver->GetMessageCount();
				}
				catch(Exception $e) {
					throw new Exception("Weblegs.POPClient.GetMessageCount(): Failed to GetMessageCount. ". $e->getMessage());
				}
			}
		//<-- End Method :: GetMessageCount
		
		//##################################################################################
		
		//--> Begin Method :: GetMailBoxSize
			public function GetMailBoxSize() {
				try {
					return $this->POPDriver->GetMailBoxSize();
				}
				catch(Exception $e) {
					throw new Exception("Weblegs.POPClient.GetMailBoxSize(): Failed to get mailbox size. ". $e->getMessage());
				}
			}
		//<-- End Method :: GetMailBoxSize
		
		//##################################################################################
		
		//--> Begin Method :: DeleteMessage
			public function DeleteMessage($MessageNumber) {
				$this->POPDriver->DeleteMessage($MessageNumber);
			}
		//<-- End Method :: DeleteMessage
		
		//##################################################################################
		
		//--> Begin Method :: DeleteMessages
			public function DeleteMessages($Start = null, $End = null) {
				if(is_null($Start) && is_null($End)) {
					$Start = 1;
					$End = $this->GetMessageCount();
				}
				
				//collect all mime messages
				for($Start; $Start <= $End; $Start++) {
					$this->POPDriver->DeleteMessage($Start);
				}
			}
		//<-- End Method :: DeleteMessages
		
		//##################################################################################
		
		//--> Begin Method :: GetMIMEMessage
			public function GetMIMEMessage($MessageNumber) {
				return new MIMEMessage($this->POPDriver->GetMessage($MessageNumber));
			}
		//<-- End Method :: GetMIMEMessage
		
		//##################################################################################
		
		//--> Begin Method :: GetMIMEMessages
			public function GetMIMEMessages($Start = null, $End = null) {
				//get all messages
				if(is_null($Start) && is_null($End)) {
					$Start = 1;
					$End = $this->GetMessageCount();
				}
				
				//create collection array
				$MIMEMessages = array();
				
				//collect all mime messages
				for($Start; $Start <= $End; $Start++) {
					$MIMEMessages[] =  new MIMEMessage($this->POPDriver->GetMessage($Start));
				}
				
				return $MIMEMessages;
			}
		//<-- End Method :: GetMIMEMessages
		
		//##################################################################################
		
		//--> Begin Method :: GetHeader
			public function GetHeader($MessageNumber) {
				return $this->POPDriver->GetHeaders($MessageNumber);
			}
		//<-- End Method :: GetHeader
		
		//##################################################################################
		
		//--> Begin Method :: GetHeaders
			public function GetHeaders($Start = null, $End = null) {
				//get all headers
				if(is_null($Start) && is_null($End)) {
					$Start = 1;
					$End = $this->GetMessageCount();
				}
				
				//create collection array
				$myHeaders = array();
				
				//collect all mime messages
				for($Start; $Start <= $End; $Start++) {
					$myHeaders[] = $this->POPDriver->GetHeaders($Start);
				}
				
				return $myHeaders;
			}
		//<-- End Method :: GetHeaders
		
		//##################################################################################
		
		//--> Begin Method :: GetMessage
			public function GetMessage($MessageNumber) {
				return $this->POPDriver->GetMessage($MessageNumber);
			}
		//<-- End Method :: GetMessage
		
		//##################################################################################
		
		//--> Begin Method :: GetMessages
			public function GetMessages($Start = null, $End = null) {
				//get all messages
				if(is_null($Start) && is_null($End)) {
					$Start = 1;
					$End = $this->GetMessageCount();
				}
				
				//create collection variable
				$Messages = array();
				
				//collect all messages
				for($Start; $Start <= $End; $Start++) {
					try {
						$Messages[] =  $this->POPDriver->GetMessage($Start);
					}
					catch(Exception $e) {
						throw new IndexOutOfRangeException("Weblegs.POPClient.GetMessages(): Failed to get message #". $i .". ". $e->getMessage());
					}
				}
				
				return $Messages;
			}
		//<-- End Method :: GetMessages
	}
//<-- End Class :: POPClient

//##########################################################################################
?>