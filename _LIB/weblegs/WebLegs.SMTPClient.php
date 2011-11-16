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

//--> Begin Class :: SMTPClient
	class SMTPClient {
		//--> Begin :: Properties
			public $From;
			public $ReplyTo;
			public $To;
			public $CC;
			public $BCC;
			public $Priority;
			public $Subject;
			public $Message;
			public $IsHTML;
			public $Attachments;
			public $Host;
			public $Port;
			public $Protocol;
			public $Timeout;
			public $Username;
			public $Password;
			public $SMTPDriver;
			public $MIMEMessage;
			public $ContentTypeList;
			public $OpenedManually;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function SMTPClient() {
				$this->From = array();
				$this->ReplyTo = array();
				$this->To = array();
				$this->CC = array();
				$this->BCC = array();
				$this->Priority = "3";
				$this->Subject = "";
				$this->Message = "";
				$this->IsHTML = false;
				$this->Attachments = array();
				$this->Host = "";
				$this->Port = "25";
				$this->Protocol = "tcp";
				$this->Timeout = 10;
				$this->Username = "";
				$this->Password = "";
				$this->SMTPDriver = new SMTPDriver();
				$this->MIMEMessage = new MIMEMessage();
				$this->ContentTypeList = $this->BuildContentTypeList();
				$this->OpenedManually = false;
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Open
			public function Open() {
				//setup the SMTPDriver
				$this->SMTPDriver->Host = $this->Host;
				$this->SMTPDriver->Port = $this->Port;
				$this->SMTPDriver->Protocol = $this->Protocol;
				$this->SMTPDriver->Timeout = $this->Timeout;
				$this->OpenedManually = true;
				
				try {
					$this->SMTPDriver->Open();
				}
				catch(Exception $e) {
					throw new Exception("Weblegs.SMTPClient.Open(): Failed to open connection. ". $e->getMessage());
				}
			}
		//<-- End Method :: Open
		
		//##################################################################################
		
		//--> Begin Method :: Close
			public function Close() {
				try {
					$this->SMTPDriver->Close();
				}
				catch(Exception $e) {
					throw new Exception("Weblegs.SMTPClient.Close(): Failed to close connection. ". $e->getMessage());
				}
			}
		//<-- End Method :: Close
		
		//##################################################################################
		
		//--> Begin Method :: SetFrom
			public function SetFrom($EmailAddress, $Name = "") {
				$this->From[0] = $EmailAddress;
				$this->From[1] = $Name;
			}
		//<-- End Method :: SetFrom
		
		//##################################################################################
		
		//--> Begin Method :: SetReplyTo
			public function SetReplyTo($EmailAddress, $Name = "") {
				$this->ReplyTo[0] = $EmailAddress;
				$this->ReplyTo[1] = $Name;
			}
		//<-- End Method :: SetReplyTo
		
		//##################################################################################
		
		//--> Begin Method :: AddTo
			public function AddTo($EmailAddress, $Name = "") {
				$this->To[] = array("address" => $EmailAddress, "name" => $Name);
			}
		//<-- End Method :: AddTo
		
		//##################################################################################
		
		//--> Begin Method :: AddCC
			public function AddCC($EmailAddress, $Name = "") {
				$this->CC[] = array("address" => $EmailAddress, "name" => $Name);
			}
		//<-- End Method :: AddCC
		
		//##################################################################################
		
		//--> Begin Method :: AddBCC
			public function AddBCC($EmailAddress, $Name = "") {
				$this->BCC[] = array("address" => $EmailAddress, "name" => $Name);
			}
		//<-- End Method :: AddBCC
		
		//##################################################################################
		
		//--> Begin Method :: AddHeader
			public function AddHeader($Name, $Value) {
				$this->MIMEMessage->AddHeader($Name, $Value);
			}
		//<-- End Method :: AddHeader
		
		//##################################################################################
		
		//--> Begin Method :: AttachFile
			public function AttachFile($FilePath) {
				$this->Attachments[] = $FilePath;
			}
		//<-- End Method :: AttachFile
		
		//##################################################################################
		
		//--> Begin Method :: CompileHeaders
			public function CompileHeaders() {
				//add from field and optionally include the from name
				if($this->From[1] == "") {
					$this->AddHeader("From", $this->From[0]);
				}
				else{
					$this->MIMEMessage->AddHeader("From", "\"".  $this->From[1] ."\" <". $this->From[0] .">");
				}
				
				//add reply to field and optionally include the reply to name
				if(count($this->ReplyTo) == 0) {
					//do nothing
				}
				else if($this->ReplyTo[0] != "" && $this->ReplyTo[1] == "") {
					$this->MIMEMessage->AddHeader("ReplyTo", $this->ReplyTo[0]);
				}
				else if($this->ReplyTo[0] != "" && $this->ReplyTo[1] != "") {
					$this->MIMEMessage->AddHeader("ReplyTo", "\"". $this->ReplyTo[1] ."\" <". $this->ReplyTo[0] .">");
				}
				
				//add subject
				$this->MIMEMessage->AddHeader("Subject", $this->Subject);
				
				//Add To to header data
				$ToAddresses = "";
				for($i = 0; $i < count($this->To); $i++) {
					//if the name field is specified add it
					if($this->To[$i]["name"] != "") {
						$ToAddresses .= "\"". $this->To[$i]["name"] ."\" ";
					}
					
					//detect whether we need to add a comma
					$ToAddresses .= "<". $this->To[$i]["address"] .">". ($i + 1 == count($this->To) ? "" :  ", ");	
				}
				
				$this->MIMEMessage->AddHeader("To", $ToAddresses);
				
				//add CC to header data
				$CCAddresses = "";
				for($i = 0; $i < count($this->CC); $i++) {	
					//if the name field is specified add it
					if($this->CC[$i]["name"] != "") {
						$CCAddresses .= "\"". $this->CC[$i]["name"] ."\" ";
					}
						
					$CCAddresses .= $this->CC[$i]["address"] . ($i + 1 == count($this->CC) ? "" :  ", ");			
				}
				if($CCAddresses != ""){
					$this->MIMEMessage->AddHeader("Cc", $CCAddresses);
				}
				
				//add date - Date: Thu, 18 Jun 2009 15:07:55 -0700
				date_default_timezone_set("GMT");
				$this->MIMEMessage->AddHeader("Date", date("r"));
				
				//add message-id
				$this->MIMEMessage->AddHeader("Message-Id",  "<". md5(uniqid(time())) ."@". $this->Host .">");
				
				//add priority
				$this->MIMEMessage->AddHeader("X-Priority", $this->Priority);
				
				//add x-mailer
				$this->MIMEMessage->AddHeader("X-Mailer", "WebLegs.SMTPClient (www.weblegs.org)");
				
				//add mime version
				$this->MIMEMessage->AddHeader("MIME-Version", "1.0");
			}
		//<-- End Method :: CompileHeaders
		
		//##################################################################################
		
		//--> Begin Method :: CompileMessage
			public function CompileMessage() {
				//setup our *empty* MIME objects for alternative message
				$AlternativeMessage = new MIMEMessage();
				$HTMLMessage = new MIMEMessage();
				$TextMessage = new MIMEMessage();

				//create the main boundry for this message (not always used)
				$MainBoundary = "----=_Part_". md5(uniqid(time()));
				
				//lets figure out how to handle this message
				if($this->IsHTML == false && count($this->Attachments) == 0) {
					//this is just a plain text message
					$this->MIMEMessage->AddHeader("Content-Type", "text/plain;\n\tcharset=US-ASCII;");
					$this->MIMEMessage->AddHeader("Content-Transfer-Encoding", "quoted-printable");
					
					//put the content into the message body
					$this->MIMEMessage->Body = $this->Message;
				}
				else if($this->IsHTML == true && count($this->Attachments) == 0) {
					//this is an alternative html/text based message
					$this->MIMEMessage->AddHeader("Content-Type", "multipart/alternative;\n\tboundary=\"".  $MainBoundary ."\"");
					$this->MIMEMessage->Preamble = "This is a multi-part message in MIME format.";
					
					//build the html part of this message
					$HTMLMessage = new MIMEMessage();
					$HTMLMessage->AddHeader("Content-Type", "text/html; charset=US-ASCII;");
					$HTMLMessage->AddHeader("Content-Transfer-Encoding", "quoted-printable");
					$HTMLMessage->Body = $this->Message;
					
					//build the text part of this message
					$TextMessage = new MIMEMessage();
					$TextMessage->AddHeader("Content-Type", "text/plain; charset=US-ASCII;");
					$TextMessage->AddHeader("Content-Transfer-Encoding", "quoted-printable");
					$TextMessage->Body = $this->HTMLToText($this->Message); 
					
					//add html/text parts to the main message
					$this->MIMEMessage->Parts[] = $TextMessage;
					$this->MIMEMessage->Parts[] = $HTMLMessage;
					
				}
				else if(count($this->Attachments) != 0) {
					//this message is mixed
					$this->MIMEMessage->AddHeader("Content-Type", "multipart/mixed;\n\tboundary=\"". $MainBoundary  ."\"");
					$this->MIMEMessage->Preamble = "This is a multi-part message in MIME format.";
					
					//is this an alternative message?
					if($this->IsHTML == true) {
						//create the alternative boundry for this message
						$AlternativeBoundary = "----=_Part_". md5(uniqid(time()));
						
						//build the Alternative part of this message
						$AlternativeMessage = new MIMEMessage();
						$AlternativeMessage->AddHeader("Content-Type", "multipart/alternative;\n\tboundary=\"".  $AlternativeBoundary ."\"");
						
						//build the html part of this message
						$HTMLMessage = new MIMEMessage();
						$HTMLMessage->AddHeader("Content-Type", "text/html; charset=US-ASCII;");
						$HTMLMessage->AddHeader("Content-Transfer-Encoding", "quoted-printable");
						$HTMLMessage->Body = $this->Message;
						
						//build the text part of this message
						$TextMessage = new MIMEMessage();
						$TextMessage->AddHeader("Content-Type", "text/plain; charset=US-ASCII;");
						$TextMessage->AddHeader("Content-Transfer-Encoding", "quoted-printable");
						$TextMessage->Body = $this->HTMLToText($this->Message); 
						
						//add html/text parts to the alternative message
						$AlternativeMessage->Parts[] = $TextMessage;
						$AlternativeMessage->Parts[] = $HTMLMessage;
						
						//add the alternative message to the main message
						$this->MIMEMessage->Parts[] = $AlternativeMessage;
					}
					else {
						//build the text part of this message
						$TextMessage = new MIMEMessage();
						$TextMessage->AddHeader("Content-Type", "text/plain; charset=US-ASCII;");
						$TextMessage->AddHeader("Content-Transfer-Encoding", "quoted-printable");
						$TextMessage->Body = $this->Message;
						
						//add the text message to the main message
						$this->MIMEMessage->Parts[] = $TextMessage;
					}

					//add attachments to the main message message
					for($i = 0 ; $i < count($this->Attachments); $i++) {
						//create mime message object
						$FilePath = $this->Attachments[$i];
		
						//get file info
						$thisFileInfo = pathinfo($FilePath);

						//setup new MIME message for this attachment
						$AttachmentMessage = new MIMEMessage();
						$AttachmentMessage->AddHeader("Content-Type", $this->GetContentTypeByExtension($thisFileInfo['extension']) .";\n\tname=\"". $thisFileInfo['basename'] ."\"");
						$AttachmentMessage->AddHeader("Content-Transfer-Encoding", "base64");
						$AttachmentMessage->AddHeader("Content-Disposition", "attachment;\n\tfilename=\"". $thisFileInfo['basename'] ."\"");
						$AttachmentMessage->FileBody = file_get_contents($FilePath);
						
						//add this attachment to the main message
						$this->MIMEMessage->Parts[] = $AttachmentMessage;
					}
				}
			}
		//<-- End Method :: CompileMessage
		
		//##################################################################################
		
		//--> Begin Method :: Send
			public function Send() {
				//make sure host was supplied
				if($this->Host == "") {
					throw new Exception("Weblegs.SMTPClient.Send(): No host specified. ");
				}
				
				//assign credentials if username is supplied
				if($this->Username != "") {
					$this->SMTPDriver->Username = $this->Username;
					$this->SMTPDriver->Password = $this->Password;
				}
		
				//should we open the socket for them?
				if(!$this->OpenedManually) {
					//setup the SMTPDriver
					$this->SMTPDriver->Host = $this->Host;
					$this->SMTPDriver->Port = $this->Port;
					$this->SMTPDriver->Protocol = $this->Protocol;
					$this->SMTPDriver->Timeout = $this->Timeout;
					$this->OpenedManually = true;
					
					//open up
					try {
						$this->SMTPDriver->Open();
					}
					catch(Exception $e) {
						throw new Exception("Weblegs.SMTPClient.Open(): Failed to open connection. ". $e->getMessage());
					}
				}
				
				//set the from address
				$this->SMTPDriver->SetFrom($this->From[0]);
				
				//add recipients
					//to addresses
					for($i = 0 ; $i < count($this->To) ; $i++) {
						$this->SMTPDriver->AddRecipient($this->To[$i]["address"]);
					}
					
					//cc addresses
					for($i = 0 ; $i < count($this->CC); $i++) {
						$this->SMTPDriver->AddRecipient($this->CC[$i]["address"]);
					}
					
					//bcc addresses
					for($i = 0 ; $i < count($this->BCC); $i++) {
						$this->SMTPDriver->AddRecipient($this->BCC[$i]["address"]);
					}
				//end add recipients
				
				//prepare headers
				$this->CompileHeaders();
				
				//prepair message
				$this->CompileMessage();
				
				//try sending
				try {
					$this->SMTPDriver->Send($this->MIMEMessage->ToString());
				}
				catch(Exception $e) {
					throw new Exception("Weblegs.SMTPClient.Send(): Failed to send message. ". $e->getMessage());
				}
				
				//should we close the socket for them?
				if(!$this->OpenedManually) {
					$this->Close();
				}
			}
		//<-- End Method :: Send
		
		//##################################################################################
		
		//--> Begin Method :: Reset
			public function Reset() {
				$this->From = array();
				$this->ReplyTo = array();
				$this->To = array();
				$this->CC = array();
				$this->BCC = array();
				$this->Priority = "3";
				$this->Subject = "";
				$this->Message = "";
				$this->IsHTML = false;
				$this->Attachments = array();
				$this->MIMEMessage = new MIMEMessage();
			}
		//<-- End Method :: Reset
			
		//##################################################################################
		
		//--> Begin Method :: GetContentTypeByExtension
			public function GetContentTypeByExtension($Extension = "") {
				$Extension = strtolower($Extension);
				
				if(!isset($this->ContentTypeList[$Extension])) {
					return 'application/x-unknown-content-type';
				}
				else{
					return $this->ContentTypeList[$Extension];
				}
			}
		//<-- End Method :: GetContentTypeByExtension
		
		//##################################################################################
			
		//--> Begin Method :: HTMLToText
			public function HTMLToText($HTML) {
				//keep copy of HTML
				$TextOnly = $HTML;
				
				//trim it down
				$TextOnly = trim($TextOnly);
				
				//make custom mods to HTML
					//seperators (80 chars on purpose)
					$HorizontalRule = "--------------------------------------------------------------------------------";
					$TableTopBottom = "********************************************************************************";
					
					//remove all line breaks
					$TextOnly = preg_replace("/\r/", "", $TextOnly);
					$TextOnly = preg_replace("/\n/", "", $TextOnly);
					
					//remove head
					$TextOnly = preg_replace("/<(head|HEAD).*?\/(head|HEAD)>/", "", $TextOnly);
					
					//heading tags
					$TextOnly = preg_replace("/<\/*(h|H)(1|2|3|4|5|6).*?>/", "\n", $TextOnly);
					
					//paragraph tags
					$TextOnly = preg_replace("/<(p|P).*?>/", "\n\n", $TextOnly);
					
					//div tags
					$TextOnly = preg_replace("/<(div|DIV).*?>/", "\n\n", $TextOnly);
					
					//br tags
					$TextOnly = preg_replace("/<(br|BR|bR|Br).*?>/", "\n", $TextOnly);
					
					//hr tags
					$TextOnly = preg_replace("/<(hr|HR|hR|Hr).*?>/", "\n". $HorizontalRule, $TextOnly);
					
					//table tags
					$TextOnly = preg_replace("/<\/*(table|TABLE).*?>/", "\n". $TableTopBottom, $TextOnly);
					$TextOnly = preg_replace("/<(tr|TR|tR|Tr).*?>/", "\n", $TextOnly);
					$TextOnly = preg_replace("/<\/(td|TD|tD|Td).\*?>/", "\t", $TextOnly);
					
					//list tags
					$TextOnly = preg_replace("/<\/*(ol|OL|oL|Ol).*?>/", "\n", $TextOnly);
					$TextOnly = preg_replace("/<\/\*(ul|UL|uL|Ul).*?>/", "\n", $TextOnly);
					$TextOnly = preg_replace("/<(li|LI|lI|Li).*?>/", "\n\t(*) ", $TextOnly);
					
					//lets not lose our links
					$TextOnly = preg_replace("/<a href=(\"|\')(.*?)(\"|\')>(.*?)<\/a>/i", "$4 [$2]", $TextOnly);
					$TextOnly = preg_replace("/<a HREF=(\"|\')(.*?)(\"|\')>(.*?)<\/a>/i", "$4 [$2]", $TextOnly);
					
					//strip the remaining HTML out of string
					$TextOnly = preg_replace("/<(.|\n)*?>/", "", $TextOnly);
					
					//loop over each line and truncate lines more than 74 characters
					$tmpFixedText = "";
					$TextOnlyLines = explode("\n", $TextOnly);
					for($i = 0 ; $i < count($TextOnlyLines); $i++) {
						$tmpThisFixedLine = "";
						if(strlen($TextOnlyLines[$i]) > 74) {
							while(strlen($TextOnlyLines[$i]) > 74) {
								//find the next space character furthest to the end (or the 74th character)
								if(strrpos(substr($TextOnlyLines[$i], 0, 73), " ") !== false){
									$tmpThisFixedLine .= substr($TextOnlyLines[$i], 0, strrpos(substr($TextOnlyLines[$i], 0, 73), " ")) ."\n";
									//remove from TextOnlyLines[i]
									$TextOnlyLines[$i] = trim(substr($TextOnlyLines[$i], strrpos(substr($TextOnlyLines[$i], 0, 73), " ")));
								}
								else{
									//if there is a space in this line after the 74th character lets break at the first chance we get and continue
									if(strpos($TextOnlyLines[$i], " ") !== false) {
										$tmpThisFixedLine .= substr($TextOnlyLines[$i], 0, strpos($TextOnlyLines[$i], " ") + 1) ."\n";
										$TextOnlyLines[$i] = substr($TextOnlyLines[$i], strpos($TextOnlyLines[$i], " ") + 1);
									}
									else {
										//this is a long line w/ no breaking potential
										$tmpThisFixedLine .= $TextOnlyLines[$i];
										$TextOnlyLines[$i] = "";
									}
								}
							}
							//if there is still content in TextOnlyLines[i] ... append it w/ a new line
							if(strlen($TextOnlyLines[$i]) > 0) {
								$tmpThisFixedLine .= $TextOnlyLines[$i];
							}
						}
						else {
							$tmpThisFixedLine = $TextOnlyLines[$i] ;
						}
						$tmpThisFixedLine .= "\n";
						$tmpFixedText .= $tmpThisFixedLine;
					}
					$TextOnly = $tmpFixedText;
				//end make custom mods to HTML
				return $TextOnly;
			}
		//<-- End Method :: HTMLToText
		
		//##################################################################################
		
		//--> Begin Method :: BuildContentTypeList
			public function BuildContentTypeList() {
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
		//<-- End Method :: BuildContentTypeList
	}
//<-- End Class :: SMTPClient

//##########################################################################################
?>