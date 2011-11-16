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

//--> Begin Class :: SocketDriver
	class SocketDriver {
		//--> Begin :: Properties
			public $Connection;
			public $Host;
			public $Port;
			public $Protocol;
			public $Timeout;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function SocketDriver() {
				$this->Connection = null;
				$this->Host = "";
				$this->Port = -1;
				$this->Protocol = "tcp"; //ssl/tls/tcp
				$this->Timeout = 10;
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin :: Destructor
			public function __destruct() {
				$this->Close();
			}
		//<-- End :: Destructor
		
		//##################################################################################
		
		//--> Begin Method :: Open
			public function Open() {
				$Host = $this->Host;
				$Port = $this->Port;
				$ErrorNumber = "";
				$ErrorString = "";
				$Timeout = $this->Timeout;
				
				//attempt to connect
				$this->Connection = @fsockopen($this->Protocol ."://". $Host, (int)$Port, $ErrorNumber, $ErrorString, $Timeout);
				
				//verify connection was made
				if(empty($this->Connection)) {
					throw new Exception("Weblegs.SocketDriver.Open(): Failed to connect. (Error: '". $ErrorString ."' Error Number: '". $ErrorNumber ."')");
				}
			}
		//<-- End Method :: Open
		
		//##################################################################################
		
		//--> Begin Method :: Close
			public function Close() {
				if(is_null($this->Connection)) {
					return;
				}
				//see if there is an open connection
				else if(!$this->IsOpen()) {
					return;
				}
				else {
					fclose($this->Connection);
					$this->Connection = null;
				}
			}
		//<-- End Method :: Close
		
		//##################################################################################
		
		//--> Begin Method :: ReadBytes
			public function ReadBytes($Bytes) {
				return @fgets($this->Connection, $Bytes);
			}
		//<-- End Method :: ReadBytes
		
		//##################################################################################
		
		//--> Begin Method :: ReadLine
			public function ReadLine() {
				//remove \r\n - the developer can add it again if they want
				return str_replace(array("\r\n", "\n", "\r"), "", @fgets($this->Connection));
			}
		//<-- End Method :: ReadLine
		
		//##################################################################################
		
		//--> Begin Method :: Read
			public function Read() {
				$Line = "";
				$Data = "";
				
				while($Line = @fgets($this->Connection)) {
					//collect data
					$Data .= $Line;
				}
				
				return $Data;
			}
		//<-- End Method :: Read
		
		//##################################################################################
		
		//--> Begin Method :: Write
			public function Write($Data) {
				//write data to connection
				fputs($this->Connection, $Data);
			}
		//<-- End Method :: Write
		
		//##################################################################################
		
		//--> Begin Method :: IsOpen
			public function IsOpen() {
				//make sure there is a connection
				if(!empty($this->Connection)) {
					//get socket status
					$SocketStatus = socket_get_status($this->Connection);
					
					if($SocketStatus["eof"]) {
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
		//<-- End Method :: IsOpen
	}
//<-- End Class :: SocketDriver

//##########################################################################################
?>