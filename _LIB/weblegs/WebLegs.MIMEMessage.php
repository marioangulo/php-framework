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

//--> Begin Class :: MIMEMessage
	class MIMEMessage {
		//--> Begin :: Properties
			public $Headers;
			public $Preamble;
			public $Body;
			public $FileBody;
			public $Parts;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function MIMEMessage($Data = "") {
				//this fixes the usue of this constant not being defined
				if(!defined("PCRE_MULTILINE")) {
					define("PCRE_MULTILINE", "m");
				}
				
				$this->Headers = array();
				$this->Preamble;
				$this->Body;
				$this->FileBody;
				$this->Parts = array();
		
				if($Data != "") {
					$this->Parse($Data);
				}
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Parse
			public function Parse($Data) {
				str_replace(array("\r\n", "\r", "\n"), "\n", $Data);
				preg_match_all("/(.*?)\r\n\r\n(.*)/is", $Data, $Matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);
				$this->ParseHeaders(@$Matches[1][0]);
				$this->ParseBody(@$Matches[2][0], true);
			}
		//<-- End Method :: Parse
		
		//##################################################################################
		
		//--> Begin Method :: ParseHeaders
			public function ParseHeaders($Data) {
				//get matches
				preg_match_all("/(?<=\n|^)(\S*?): (.*?)(?=\n\S*?: |$)/is", $Data, $Matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);
		
				//add headers
				for($i = 0; $i < count($Matches[1]); $i++) {
					$this->AddHeader($Matches[1][$i], preg_replace('/\s+/', " ", $Matches[2][$i]));
				}
			}
		//<-- End Method :: ParseHeaders
		
		//##################################################################################
		
		//--> Begin Method :: ParseBody
			public function ParseBody($Data, $Decode) {
				switch(strtolower($this->GetMediaType())) {
					//- - - - - - - - - - - - - - - - - - - -//
					case "application":
						//check for encoding
						if(strstr($this->GetContentTransferEncoding(), "base64")) {
							if($Decode) {
								$this->Body = $Data;
								$this->FileBody = Codec::Base64Decode($Data);
							}
							else {
								$this->Body = Codec::Base64Encode($this->FileBody);
							}
						}
						break;
					//- - - - - - - - - - - - - - - - - - - -//
					case "audio":
						//check for encoding
						if(strstr($this->GetContentTransferEncoding(), "base64")) {
							if($Decode) {
								$this->Body = $Data;
								$this->FileBody = Codec::Base64Decode($Data);
							}
							else {
								$this->Body = Codec::Base64Encode($this->FileBody);
							}
						}
						break;
					//- - - - - - - - - - - - - - - - - - - -//
					case "image":
						//check for encoding
						if(strstr($this->GetContentTransferEncoding(), "base64")) {
							if($Decode) {
								$this->Body = $Data;
								$this->FileBody = Codec::Base64Decode($Data);
							}
							else {
								$this->Body = Codec::Base64Encode($this->FileBody);
							}
						}
						break;
					//- - - - - - - - - - - - - - - - - - - -//
					case "message":
						switch(strtolower($this->GetMediaSubType())) {
							//* * * * * * * * * * * * * * * * * * * *//
							case "rfc822":
								//make the first part of this message
								//the parsed message
								$this->Parts[] = new MIMEMessage($Data);
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
						$MultiPartBoundry = $this->GetMediaBoundary();
						
						//get the preamble
						$Pattern = "/(.*?)(--". $MultiPartBoundry .")/is";
						preg_match_all($Pattern, $Data, $PreambleMatchs, PREG_PATTERN_ORDER | PCRE_MULTILINE);
						if($PreambleMatchs[1]) {
							$this->Preamble = $PreambleMatchs[1][0];
						}
						
						//get message parts
						$Pattern = "--". $MultiPartBoundry;
						$PartMatchs = spliti($Pattern, $Data);
						
						//remove the first element (the preamble area)
						array_shift($PartMatchs);
						
						//remove the last item its empty
						array_pop($PartMatchs);
						
						//loop over all parts
						for($i = 0; $i < count($PartMatchs); $i++) {
							//the parsed message
							$this->Parts[] = new MIMEMessage($PartMatchs[$i]);
						}
						break;
					//- - - - - - - - - - - - - - - - - - - -//
					case "text":
						//check for encoding
						if(strstr($this->GetContentTransferEncoding(), "base64")) {
							if($Decode) {
								$this->Body = Codec::Base64Decode($Data);
							}
							else {
								$this->Body = Codec::Base64Encode($Data);
							}
						}
						else if(strstr($this->GetContentTransferEncoding(), "quoted-printable")) {
							if($Decode) {
								$this->Body = Codec::QuotedPrintableDecode($Data);
							}
							else {
								$this->Body = Codec::QuotedPrintableEncode($Data);
							}
						}
						else{
							$this->Body = $Data;
						}
						break;
					//- - - - - - - - - - - - - - - - - - - -//
					case "video":
						//check for encoding
						if(strstr($this->GetContentTransferEncoding(), "base64")) {
							if($Decode) {
								$this->Body = $Data;
								$this->FileBody = Codec::Base64Decode($Data);
							}
							else {
								$this->Body = Codec::Base64Encode($this->FileBody);
							}
						}
						break;
					//- - - - - - - - - - - - - - - - - - - -//
					default:
						//do nothing
						break;
				
				}
			}
		//<-- End Method :: ParseBody
		
		//##################################################################################
		
		//--> Begin Method :: AddHeader
			public function AddHeader($Name, $Value) {
				//add header
				$this->Headers[] = array($Name, $Value);
			}
		//<-- End Method :: AddHeader
		
		//##################################################################################
		
		//--> Begin Method :: RemoveHeader
			public function RemoveHeader($Name) {
				//create new header array container
				$NewHeaders = array();
				
				for($i = 0; $i < count($this->Headers); $i++){
					if($this->Headers[$i][0] != $Name){
						$NewHeaders[] = array($this->Headers[$i][0], $this->Headers[$i][1]);
					}
				}
				$this->Headers = $NewHeaders;
			}
		//<-- End Method :: RemoveHeader
		
		//##################################################################################
		
		//--> Begin Method :: GetHeader
			public function GetHeader($Name) {
				for($i = 0; $i < count($this->Headers); $i++){
					if($this->Headers[$i][0] == $Name){
						return $this->Headers[$i][1];
					}
				}
				return null;
			}
		//<-- End Method :: GetHeader
		
		//##################################################################################
		
		//--> Begin Method :: GetContentTransferEncoding
			public function GetContentTransferEncoding() {
				//return content transfer encoding
				return $this->GetHeader("Content-Transfer-Encoding");
			}
		//<-- End Method :: GetContentTransferEncoding
		
		//##################################################################################
		
		//--> Begin Method :: GetMediaType
			public function GetMediaType() {		
				//get matches
				preg_match_all("/^(.*?)\\/(.*?);|$/is", $this->GetHeader("Content-Type"), $Matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);	
				
				$Match = $Matches[1][0];
				
				if($Match == "") {
					$Match = "text";
				}
				
				return $Match;
			}
		//<-- End Method :: GetMediaType
		
		//##################################################################################
		
		//--> Begin Method :: GetMediaSubType
			public function GetMediaSubType() {
				//get matches
				preg_match_all("/^(.*?)\\/(.*?);|$/is", $this->GetHeader("Content-Type"), $Matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);	
				
				$Match = $Matches[2][0];
				
				if($Match == "") {
					$Match = "plain";
				}
				
				return $Match;
			}
		//<-- End Method :: GetMediaSubType
		
		//##################################################################################
		
		//--> Begin Method :: GetMediaFileName
			public function GetMediaFileName() {
				//get matches
				preg_match_all("/name=\"{0,1}(.*?)(\"|;|$)/", $this->GetHeader("Content-Type"), $Matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);	
				return $Matches[1][0];	
			}
		//<-- End Method :: GetMediaFileName
		
		//##################################################################################
		
		//--> Begin Method :: GetMediaCharacterSet
			public function GetMediaCharacterSet() {
				//get matches
				preg_match_all("/charset=\"{0,1}(.*?)(?:\"|;|$)/", $this->GetHeader("Content-Type"), $Matches, PREG_PATTERN_ORDER | PCRE_MULTILINE);	
		
				$Match = $Matches[1][0];
				
				if($Match == "") {
					$Match = "us-ascii";
				}
				
				return $Match;
			}
		//<-- End Method :: GetMediaCharacterSet
		
		//##################################################################################
		
		//--> Begin Method :: GetMediaBoundary
			public function GetMediaBoundary() {
				preg_match_all("/boundary=\"{0,1}(.*?)(?:\"|$)/", $this->GetHeader("Content-Type"), $Matches, PCRE_MULTILINE | PREG_PATTERN_ORDER);
				if($Matches[1]) {
					return str_replace(array("'","\""), "", $Matches[1][0]);
				}
				else {
					return "";
				}
			}
		//<-- End Method :: GetMediaBoundary
		
		//##################################################################################
		
		//--> Begin Method :: IsAttachment
			public function IsAttachment() {
				if(strstr($this->GetHeader("Content-Disposition"), "attachment")) {
					return true;
				}
				return false;
			}
		//<-- End Method :: IsAttachment
		
		//##################################################################################
		
		//--> Begin Method :: ToString
			public function ToString() {
				$tmpMIMEMessage = "";
				//loop over the headers
				for($i = 0 ; $i < count($this->Headers) ; $i++) {
					$tmpThisHeader = $this->Headers[$i][0] .": ". $this->Headers[$i][1];
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
				if(count($this->Headers) == 0) {
					$tmpMIMEMessage .= "\n";
				}
				
				//add header/body space
				$tmpMIMEMessage .= "\n";
				
				//add preamble
				if($this->Preamble != "") {
					$tmpMIMEMessage .= $this->Preamble ."\n";
				}
				
				//add body text
				if(count($this->Parts) == 0) {
					//en/decode on the way out
					$this->ParseBody($this->Body, false);
					$tmpMIMEMessage .= $this->Body;
				}
				else {
					//go through each part
					for($i = 0 ; $i < count($this->Parts) ; $i++) {
						//add boundary above
						if($this->GetMediaBoundary() != "") {
							$tmpMIMEMessage .= "\n--". $this->GetMediaBoundary() ."\n";
						}
						
						//add body content
						$tmpMIMEMessage .= $this->Parts[$i]->ToString();
						
						//add boundary above
						if($this->GetMediaBoundary() != "" && ($i + 1) == count($this->Parts)) {
							$tmpMIMEMessage .= "\n--". $this->GetMediaBoundary() ."--\n\n";
						}
					}
				}
				return $tmpMIMEMessage;
			}
		//<-- End Method :: ToString
		
		//##################################################################################
		
		//--> Begin Method :: SaveAs
			public function SaveAs($FilePath){
				try {
					file_put_contents($FilePath, $this->ToString());
				}
				catch(Exception $e){
					throw new Exception("WebLegs.MIMEMessage.SaveAs(): Was not able to save file.");
				}
			}
		//<-- End Method :: SaveAs
		
		//##################################################################################
		
		//--> Begin Method :: SaveBodyAs
			public function SaveBodyAs($FilePath){
				try {
					file_put_contents($FilePath, $this->Body);
				}
				catch(Exception $e){
					throw new Exception("WebLegs.MIMEMessage.SaveBodyAs(): Was not able to save file.");
				}
			}
		//<-- End Method :: SaveBodyAs
		
		//##################################################################################
		
		//--> Begin Method :: SaveFileAs
			public function SaveFileAs($FilePath){
				try {
					file_put_contents($FilePath, $this->FileBody);
				}
				catch(Exception $e){
					throw new Exception("WebLegs.MIMEMessage.SaveFileAs(): Was not able to save file.");
				}
			}
		//<-- End Method :: SaveFileAs
	}
//<-- End Class :: MIMEMessage

//##########################################################################################
?>