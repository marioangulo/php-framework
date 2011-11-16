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

//--> Begin Class :: Footprint
	class F {
		//--> Begin :: Properties
			//main
			public static $Config;
			public static $AdminEmail;
			public static $RootPath;
			public static $HostName;
			public static $RootURL;
			public static $CookieDomain;
			public static $BaseHREF;
			
			//footprint objects
			public static $User;
			public static $Admin;
			public static $Account;
			public static $System;
			public static $Utility;
			
			//footprint properties
			public static $PageNamespace;
			public static $PageInput;
			public static $SQLKeyBinders;
			public static $DOMBinders;
			public static $ResultsCache;
			public static $FormCache;
			public static $RequestedSections;
			public static $ResponseSections;
			public static $ResponseJSON;
			public static $MetaData;
			public static $ForceUniqueAutoIncludes;
			public static $EventReadyClasses;
			public static $CustomHashes;
			public static $CustomRows;
			public static $CacheExpirationDate;
			public static $CacheGetData;
			
			//weblegs properties
			public static $DateTime;
			public static $DB;
			public static $DataPager;
			public static $Timer;
			public static $DebugTimer;
			public static $DebugLog;
			public static $Email;
			public static $Request;
			public static $Response;
			public static $Doc;
			
			//alerts and messages
			public static $Warnings;
			public static $Errors;
			public static $Alerts;
			public static $Info;
			
			//debugging
			public static $ShowStackTrace;
			public static $LogErrors;
			public static $EmailErrors;
			public static $EmailDebugLog;
		//<-- End :: Properties
		
		//####################################################################################
		
		//--> Begin Method :: FilePath
			public static function FilePath($Input) {
				return self::$Config->Get("root-path") . $Input;
			}
		//<-- End Method :: FilePath
		
		//####################################################################################
		
		//--> Begin Method :: URL
			public static function URL($Input) {
				return self::$Config->Get("root-url") . $Input;
			}
		//<-- End Method :: URL
		
		//####################################################################################
		
		//--> Begin Method :: FullURI
			public static function FullURI($Input) {
				return self::$Config->Get("host-name") . self::$Config->Get("root-url") . $Input;
			}
		//<-- End Method :: FullURI
		
		//####################################################################################
		
		//--> Begin Method :: FireEvents
			public static function FireEvents($Name) {
				for($i = 0 ; $i < count(self::$EventReadyClasses) ; $i++) {
					self::Log("<!--func lookup (". self::$EventReadyClasses[$i] ."::". $Name .")-->");
					
					if(method_exists(self::$EventReadyClasses[$i], $Name)) {
						self::Log("<Fire|". self::$EventReadyClasses[$i] ."::". $Name .">");
						call_user_func(self::$EventReadyClasses[$i] ."::". $Name);
						self::Log("</Fire|". self::$EventReadyClasses[$i] ."::". $Name .">");
					}
				}
			}
		//<-- End Method :: FireEvents
		
		//####################################################################################
		
		//--> Begin Method :: Constructor
			public static function Constructor() {
				self::$User = new User();
				self::$Admin = new Admin();
				self::$Account = new Account();
				self::$System = new System();
				
				//global objects
				self::$Doc = new DOMTemplate_Ext(); //extended
				self::$DB = new MySQLDriver_Ext(); //extended
				self::$DateTime = new DateTimeDriver_Ext(); //extended
				self::$DataPager = new DataPager();
				self::$Timer = new Timer();
				self::$DebugTimer = new Timer();
				self::$Email = new SMTPClient_Ext();
				self::$DebugLog = new Alert();
				self::$Response = new WebResponse_Ext(); //extended
				self::$Request = new WebRequest();
				
				//alerts and messages
				self::$Warnings = new Alert();
				self::$Errors = new Alert_Ext(); //extended
				self::$Alerts = new Alert();
				self::$Info = new Alert();
				
				//setup mysql
				self::$DB->Host = self::$Config->Get("mysql-host");
				self::$DB->Username = self::$Config->Get("mysql-username");
				self::$DB->Password = self::$Config->Get("mysql-password");
				self::$DB->Schema = self::$Config->Get("mysql-schema");
				
				//setup email
				self::$Email->SetFrom(self::$Config->Get("email-from-address"), self::$Config->Get("email-from-name"));
				self::$Email->Host = self::$Config->Get("email-host");
				self::$Email->Username = self::$Config->Get("email-username");
				self::$Email->Password = self::$Config->Get("email-password");
				
				//engine properties
				self::$EventReadyClasses = array("G", "P", "H");
				self::$CacheExpirationDate = null;
				self::$CacheGetData = false;
				self::$SQLKeyBinders = array();
				self::$DOMBinders = array();
				self::$ForceUniqueAutoIncludes = false;
				self::$PageInput = array_merge(self::$Request->QueryStringArray, self::$Request->FormArray);
				self::$ResultsCache = array();
				self::$FormCache = array();
				self::$RequestedSections = explode(",", self::$Request->Input("data-section"));
				self::$ResponseSections = array();
				self::$ResponseJSON = array();
				self::$MetaData = array();
			}
		//<-- End Method :: Constructor
		
		//####################################################################################
		
		//--> Begin Method :: Init
			public static function Init() {
				//construct
				self::Constructor();
				
				//setup error handlers
				if(function_exists("ServerError")) {
					set_exception_handler("ServerError");
					set_error_handler("ServerError");
				}
				
				//enable sessions
				session_set_cookie_params(60*60*24, "/", self::$Config->Get("cookie-domain"));
				session_start();
								
				//start debug timer and log
				self::$DebugTimer->Start();
				self::Log("<debug>");
				self::Log("<!--request uri (". $_SERVER["REQUEST_URI"] .")-->");
				
				//setup some sql shortcuts
				self::$SQLKeyBinders["_now_"] = self::$DateTime->Now()->ToSQLString();
				self::$SQLKeyBinders["_output-tz-offset_"] = "+00:00";
				if(self::$Request->Session("timezone_offset") != "") {
					self::$SQLKeyBinders["_user-tz-diff_"] = self::$Request->Session("timezone_offset");
				}
				
				//parse namespace
				$ParsedURL = parse_url($_SERVER["SCRIPT_URI"]);
				$Basepath = pathinfo($ParsedURL["path"]);
				self::Log("<!--parsed url (". print_r($ParsedURL, true) .")-->");
				self::Log("<!--base path (". print_r($Basepath, true) .")-->");
				
				//create container for custom hashes
				$CustomHashList = array();
				
				//create container for php script includes
				$PHPScripts = array();
				
				//set namespace
				$Namespace = ($Basepath["dirname"] == "/" ? "" : $Basepath["dirname"] ."/") . $Basepath["filename"]; //trimming off extension
				if(substr($Namespace, 0, 1) == "/") { $Namespace = substr($Namespace, 1); } //trim leading '/'
				
				//load html and execute instructions?
				if($Namespace != "") {
					self::$PageNamespace = $Namespace;
					self::Log("<!--set page namespace (". $Namespace .")-->");
					
					//load php script if it exists
					if(file_exists(self::FilePath(self::$PageNamespace .".php"))){
						require_once(self::FilePath(self::$PageNamespace .".php"));
					}
					
					//////////////////////////////////////////
					self::FireEvents("Event_OnLoad");
					/////////////////////////////////////////
					
					//load html?
					if(file_exists(self::FilePath(self::$PageNamespace .".html"))) {
						//load template
						self::$Doc->LoadFile(self::FilePath(self::$PageNamespace .".html"), self::$Config->Get("root-path"));
						self::Log("<!--loaded page template (". self::$PageNamespace .".html)-->");
						
						//find any meta tags
						$METATags = self::$Doc->Traverse("//meta")->GetNodes();
						for($i = 0; $i < count($METATags); $i++){
							$Name = "";
							$Content = "";
							
							//make sure attributes are set correctly
							if($METATags[$i]->getAttribute("name") != null) {
								$Name = $METATags[$i]->getAttribute("name");
							}
							if($METATags[$i]->getAttribute("content") != null) {
								$Content = $METATags[$i]->getAttribute("content");
							}
							
							//set meta name/value
							if($Name != ""){
								self::$MetaData[$Name] = $Content;
							}
							
							//check for custom functionality
								if($Name == "require-session") {
									//remove the meta tag
									self::$Doc->Remove($METATags[$i]);
								}
								if($Name == "permission-id") {
									//remove the meta tag
									self::$Doc->Remove($METATags[$i]);
								}
								if($Name == "email-debug-log") {
									self::$EmailDebugLog = true;
									
									//remove the meta tag
									self::$Doc->Remove($METATags[$i]);
								}
								if($Name == "cache-rule") {
									$Day = 0;
									$Hour = 0;
									$Minute = 0;
									$Second = 0;
									
									//find command properties
									if($METATags[$i]->getAttribute("day") != null) {
										$Day = (int)$METATags[$i]->getAttribute("day");
									}
									if($METATags[$i]->getAttribute("hour") != null) {
										$Hour = (int)$METATags[$i]->getAttribute("hour");
									}
									if($METATags[$i]->getAttribute("minute") != null) {
										$Minute = (int)$METATags[$i]->getAttribute("minute");
									}
									if($METATags[$i]->getAttribute("second") != null) {
										$Second = (int)$METATags[$i]->getAttribute("second");
									}
									
									//cache get data?
									if($METATags[$i]->getAttribute("http-get") != null) {
										self::$CacheGetData = true;
									}
									
									//set our cache expiration
									self::$CacheExpirationDate = time() + ($Day * 24 * 60 * 60) + ($Hour * 60 * 60) + ($Minute * 60) + $Second;
									
									//remove the meta tag
									self::$Doc->Remove($METATags[$i]);
								}
								if($Name == "force-unique-auto-includes") {
									self::$ForceUniqueAutoIncludes = true;
									
									//remove the meta tag
									self::$Doc->Remove($METATags[$i]);
								}
								if($Name == "preload-sql") {
									$HashName = $Content;
									
									//remove the meta tag
									self::$Doc->Remove($METATags[$i]);
									
									//alias support
									if($METATags[$i]->getAttribute("alias") != null) {
										$HashName = $METATags[$i]->getAttribute("alias");
									}
									
									//keep track of these, we open the db later and can't get the data right now
									$CustomHashList[$HashName] = $Content;
								}
							//end check for custom functionality
						}
						
						//auto include css
						if(file_exists(self::FilePath(self::$PageNamespace .".css"))){
							$NewCSSNode = self::$Doc->DOMDocument->createElement("link");
							$NewCSSNode->setAttribute("href", self::$PageNamespace .".css". (self::$ForceUniqueAutoIncludes ? "?nocache=". uniqid() : ""));
							$NewCSSNode->setAttribute("rel", "stylesheet");
							$NewCSSNode->setAttribute("type", "text/css");
							self::$Doc->GetNodesByTagName("head")->AppendChild($NewCSSNode);
						}
						
						//auto include js
						if(file_exists(self::FilePath(self::$PageNamespace .".js"))){
							$NewJSNode = self::$Doc->DOMDocument->createElement("script", "");
							$NewJSNode->setAttribute("language", "javascript");
							$NewJSNode->setAttribute("src", self::$PageNamespace .".js". (self::$ForceUniqueAutoIncludes ? "?nocache=". uniqid() : ""));
							$NewJSNode->setAttribute("type", "text/javascript");
							self::$Doc->GetNodesByTagName("head")->AppendChild($NewJSNode);
						}
						
						//find any php includes
						$PHPScriptsIncludes = self::$Doc->Traverse("//script[@language='php']")->GetNodes();
						for($i = 0; $i < count($PHPScriptsIncludes); $i++){
							//make sure script exists
							if(file_exists(self::FilePath($PHPScriptsIncludes[$i]->getAttribute("src")))) {
								//keep track of these, we don't require them right now
								$PHPScripts[] = self::FilePath($PHPScriptsIncludes[$i]->getAttribute("src"));
							}
						}
						
						//remove those php script tags
						self::$Doc->Traverse("//script[@language='php']")->Remove();
					}
					
					//load php include scripts
					for($i = 0; $i < count($PHPScripts); $i++){
						require_once($PHPScripts[$i]);
					}
				}
				
				//open db
				self::$DB->Open();
				
				//require login?
				if(isset(self::$MetaData["require-session"])){
					self::$User->RequireSession();
				}
				//require permission?
				if(isset(self::$MetaData["permission-id"])){
					self::$User->ContinueOrDenyPermission(self::$MetaData["permission-id"]);
				}
				
				//////////////////////////////////////////
				self::FireEvents("Event_BeforeActions");
				/////////////////////////////////////////
				
				//check for action events
				if(self::$Request->Input("action") != "") {
					//////////////////////////////////////////
					$CleanAction = trim(str_replace(" ", "", self::$Request->Input("action")));
					$Method = "Action_". $CleanAction;
					self::FireEvents($Method);
					//////////////////////////////////////////
				}
				
				//load custom hashes
				foreach($CustomHashList as $Key => $Value) {
					self::$DB->LoadCommand($Value, self::$PageInput);
					self::$CustomHashes[$Key] = self::$DB->GetDataRow();
				}
				
				//////////////////////////////////////////
				self::FireEvents("Event_BeforeBinding");
				/////////////////////////////////////////
				
				//do we have data section requests?
				if(self::$Request->Input("data-section") != "") {
					//data sections
					$Nodes = self::$Doc->GetNodesByDataSet("section")->GetNodes();
					for($i = 0 ; $i < count($Nodes) ; $i++) {
						//get the resource name
						$Name = self::$Doc->GetAttribute($Nodes[$i], "data-section");
						
						if(in_array($Name, self::$RequestedSections) || self::$Request->Input("data-section") == "all") {
							$ID = uniqid();
							$Nodes[$i]->setAttribute("data-bind-id", $ID);
							$Section = self::$Doc->Traverse("//*[@data-bind-id='". $ID ."']")->GetDOMChunk();
							
							//bind resources
							self::Log("<data-section name=\"". $Name ."\">");
							self::$Doc->BindResources($Section);
							self::$Doc->DataBinder(array_merge((array)self::$PageInput, (array)self::$DOMBinders), $Section);
							self::Log("</data-section>");
							
							//remove data bind id
							$Nodes[$i]->removeAttribute("data-bind-id");
							
							//add this section to the output
							//self::$ResponseSections[$Name] = self::$Doc->GetInnerHTML($Nodes[$i]);
						}
					}
				}
				//if not, bind resources to the whole document
				else {
					self::$Doc->BindResources(self::$Doc);
				}
				
				//////////////////////////////////////////
				self::FireEvents("Event_BeforeFinalize");
				/////////////////////////////////////////
				
				//finalize
				self::Finalize();
			}
		//<-- End Method :: Init
		
		//####################################################################################
		
		//--> Begin Method :: Finalize
			public static function Finalize() {
				//build data sections object
				if(self::$Request->Input("data-section") != "") {
					$Nodes = self::$Doc->GetNodesByDataSet("section")->GetNodes();
					for($i = 0 ; $i < count($Nodes) ; $i++) {
						$Name = self::$Doc->GetAttribute($Nodes[$i], "data-section");
						if(in_array($Name, self::$RequestedSections) || self::$Request->Input("data-section") == "all") {
							self::$Doc->ProcessAlerts($Nodes[$i]);
							self::$ResponseSections[$Name] = self::$Doc->GetInnerHTML($Nodes[$i]);
						}
					}
				}
				
				//if this is for ajax json/section requests?
				$IsAjaxRequest = false;
				if(count(self::$ResponseSections) > 0 || count(self::$ResponseJSON) > 0) {
					$IsAjaxRequest = true;
					self::$Response->AddHeader("Content-Type", "application/json");
				}
				
				//should we run the final document bind?
				if(!$IsAjaxRequest) {
					self::Log("<final-data-binder>");
					self::Log("<data>");
					self::Log(print_r(array_merge(self::$PageInput, self::$DOMBinders), true));
					self::Log("</data>");
					self::$Doc->FinalBind();
					self::Log("</final-data-binder>");
					
					//move seo elements to the top of the head tag
					$tmpBase = self::$Doc->GetNodesByTagName("base")->GetNode();
					$tmpTitle = self::$Doc->GetNodesByTagName("title")->GetNode();
					$tmpDesc = self::$Doc->Traverse("//meta[@name='description']")->GetNode();
					$tmpKeywords = self::$Doc->Traverse("//meta[@name='keywords']")->GetNode();
					if($tmpBase) {
						if($tmpTitle) {
							self::$Doc->InsertBefore($tmpBase, $tmpTitle);
						}
						if($tmpDesc) {
							self::$Doc->InsertBefore($tmpBase, $tmpDesc);
						}
						if($tmpKeywords) {
							self::$Doc->InsertBefore($tmpBase, $tmpKeywords);
						}
					}
					
					//process alerts
					self::$Doc->ProcessAlerts();
				}
				
				//generate the final output
				$OutputData = null;
				if($IsAjaxRequest) {
					$tmpJSON = array();
					$tmpJSON["sections"] = self::$ResponseSections;
					$tmpJSON["data"] = self::$ResponseJSON;
					$OutputData = json_encode($tmpJSON);
				}
				else {
					$OutputData = self::$Doc->ToString();
				}
				
				//////////////////////////////////////////
				self::FireEvents("Event_Final");
				/////////////////////////////////////////
				
				//always try and close the db, can't hurt right :)
				self::$DB->Close();
				
				//create cache files?
					if(isset(self::$CacheExpirationDate)) {
						if(!self::$CacheGetData && $_SERVER["X_ORIGINAL_QUERY_STRING"] != "") {
							//don't cache these requests
						}
						else if($_SERVER["REQUEST_METHOD"] == "POST") {
							//we only cache GET reqeusts
						}
						else {
							//let's make some cache
							$ParsedURL = parse_url($_SERVER["X_ORIGINAL_SCRIPT_URI"]);
							$Basepath = pathinfo($ParsedURL["path"]);
							
							//create unique filename
							$SignatureFile = $Basepath["basename"];
							if(self::$CacheGetData) {
								$SignatureFile = $SignatureFile ."?". $_SERVER["X_ORIGINAL_QUERY_STRING"];
							}
							
							//make unique md5 name of file
							$SignatureFile = md5($SignatureFile);
							
							//final save path
							$SavePath = "_CACHE". $Basepath["dirname"] ."/". $SignatureFile;
							
							//create rule file
							$CacheRuleSavePath = $SavePath .".rule";
							$CacheRuleData = array();
							$CacheRuleData["expires"] = self::$CacheExpirationDate;
							if(isset(self::$Response->Headers["content-type"])) {
								$CacheRuleData["content-type"] = self::$Response->Headers["content-type"];
							}
							$CacheRuleData = json_encode($CacheRuleData);
							
							//check for directory
							if(!file_exists(self::FilePath("_CACHE". $Basepath["dirname"]))) {
								mkdir(self::FilePath("_CACHE". $Basepath["dirname"]), 0755, true);
							}
							
							//save cache data
							file_put_contents(self::FilePath($SavePath), $OutputData);
							
							//save cache rule file
							file_put_contents(self::FilePath($CacheRuleSavePath), $CacheRuleData);
							
							//log that we cached
							self::Log("<!--created cache files-->");
						}
					}
				//end create cache files
				
				//log the request type
				if($IsAjaxRequest) {
					self::Log("<!--finalized with json data-->");
				}
				else {
					self::Log("<!--finalized with default page-->");
				}
				
				//end debug log
				self::Log("</debug>");
				
				//email debug log
				if(self::$EmailDebugLog) { self::EmailDebugLog(); }
				
				//final page
				self::$Response->Finalize($OutputData);
			}
		//<-- End Method :: Finalize
		
		//####################################################################################
		
		//--> Begin Method :: GetBindDataValue
			public static function GetBindDataValue($Index, $LocalData = null) {
				if(strpos($Index, ":") > -1) {
					$Signature = explode(":", $Index);
					
					//check for custom hashes
					if(self::$CustomHashes) {
						if(isset(self::$CustomHashes[$Signature[0]])) {
							if(isset(self::$CustomHashes[$Signature[0]][$Signature[1]])) {
								return self::$CustomHashes[$Signature[0]][$Signature[1]];
							}
						}
					}
					
					//check for config
					if($Signature[0] == "config") {
						return self::$Config->Get($Signature[1]);
					}
					
					//check for input
					if($Signature[0] == "input") {
						if(isset(self::$PageInput[$Signature[1]])) {
							return self::$PageInput[$Signature[1]];
						}
					}
					
					//check for get
					if($Signature[0] == "get") {
						return self::$Request->QueryString($Signature[1]);
					}
					
					//check for post
					if($Signature[0] == "post") {
						return self::$Request->Form($Signature[1]);
					}
					
					//check for sessions
					if($Signature[0] == "session") {
						if(!is_null(self::$Request->Session($Signature[1]))) {
							return self::$Request->Session($Signature[1]);
						}
					}
					
					//check for cookies
					if($Signature[0] == "cookies") {
						if(!is_null(self::$Request->Cookies($Signature[1]))) {
							return self::$Request->Cookies($Signature[1]);
						}
					}
					
					//check for cookies
					if($Signature[0] == "server") {
						if(!is_null(self::$Request->ServerVariables(strtoupper($Signature[1])))) {
							return self::$Request->ServerVariables(strtoupper($Signature[1]));
						}
					}
					
					//check for sql
					if($Signature[0] == "sql") {
						self::$DB->LoadCommand($Signature[1], array_merge(self::$PageInput, self::$SQLKeyBinders));
						return self::$DB->GetDataString();
					}
				}
				
				if(isset($LocalData[$Index])) {
					return $LocalData[$Index];
				}
				
				//not caught
				return null;
			}
		//<-- End Method :: GetBindDataValue
		
		//##################################################################################
		
		//--> Begin Method :: GetAlerts
			public static function GetAlerts() {
				//output container
				$Notifications = "";
				
				//warnings
				if(self::$Warnings->Count() > 0) {
					$Warnings = "";
					for($i = 0 ; $i < self::$Warnings->Count() ; $i++) {
						$Warnings .= "<p>". self::$Warnings->Item($i) ."</p>";
					}
					
					$Notifications .= "<div class=\"alert-message warning\">". $Warnings ."</div>";
				}
				
				//errors
				if(self::$Errors->Count() > 0) {
					$Errors = "";
					for($i = 0 ; $i < self::$Errors->Count() ; $i++) {
						$Error = self::$Errors->Item($i);
						if(is_array($Error)) {
							$Errors .= "<p for=\"". $Error[0] ."\">". $Error[1] ."</p>";
						}
						else {
							$Errors .= "<p>". $Error ."</p>";
						}
					}
					
					$Notifications .= "<div class=\"alert-message error\">". $Errors ."</div>";
				}
				
				//alerts
				if(self::$Alerts->Count() > 0) {
					$Alerts = "";
					for($i = 0 ; $i < self::$Alerts->Count() ; $i++) {
						$Alerts .= "<p>". self::$Alerts->Item($i) ."</p>";
					}
					
					$Notifications .= "<div class=\"alert-message success\">". $Alerts ."</div>";
				}
				
				//info
				if(self::$Info->Count() > 0) {
					$Info = "";
					for($i = 0 ; $i < self::$Info->Count() ; $i++) {
						$Info .= "<p>". self::$Info->Item($i) ."</p>";
					}
					
					$Notifications .= "<div class=\"alert-message info\">". $Info ."</div>";
				}
				
				
				#return notifications
				return $Notifications;
			}
		//<-- End Method :: GetAlerts
	
		//####################################################################################
		
		//--> Begin Method :: Log
			public static function Log($Data) {
				self::$DebugLog->Add($Data);
			}
		//<-- End Method :: Log
		
		//####################################################################################
		
		//--> Begin Method :: EmailDebugLog
			public static function EmailDebugLog($Fatal = false) {
				//stop debug timer
				self::$DebugTimer->Stop();
				
				//compile debug data
				$ErrorLogData["application"] = self::$Request->ServerVariables("SERVER_NAME");
				$ErrorLogData["source"] = self::$Request->ServerVariables("SCRIPT_FILENAME");
				$ErrorLogData["url"] = self::$Request->ServerVariables("REQUEST_URI");
				$ErrorLogData["timestamp"] = self::$DateTime->Now()->ToString();
				$ErrorLogData["stack-trace"] = print_r(debug_backtrace(), true);
				$ErrorLogData["http-get"] = print_r($_GET, true);
				$ErrorLogData["http-post"] = print_r($_POST, true);
				$ErrorLogData["session"] = print_r($_SESSION, true);
				$ErrorLogData["cookies"] = print_r($_COOKIE, true);
				$ErrorLogData["environment"] = print_r($_SERVER, true);
				$ErrorLogData["debug-log"] = print_r(self::$DebugLog, true);
				self::$CustomHashes["log"] = $ErrorLogData;
				
				//build up message
				$Message = new DOMTemplate_Ext();
				$Message->LoadFile(self::FilePath("_LIB/footprint/debug-log.email.html"));
				$Message->BindResources($Message);
				$Message->DataBinder(array_merge((array)self::$PageInput, (array)self::$DOMBinders));
				
				//get email ready to send
				self::$Email->AddTo(self::$Config->Get("admin-email"));
				self::$Email->Subject = ($Fatal ? "~~Fatal~~ " : "") ."Debug Log @ ". self::$Request->ServerVariables("SERVER_NAME");
				self::$Email->Message = $Message->ToString();
				self::$Email->IsHTML = true;
				
				//try to send the email
				try{
					//send email
					self::$Email->Send();
					//reset
					self::$Email->Reset();
				}
				catch(Exception $e) {
					//it didn't get sent
				}
			}
		//<-- End Method :: EmailDebugLog
	}
//<-- End Class :: Footprint

//############################################################################################
?>