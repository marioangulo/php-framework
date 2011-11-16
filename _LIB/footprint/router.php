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

//--> Begin Class :: Router
	class Router {
		//--> Begin :: Properties
			public static $RequestURI;
			public static $Paths;
			public static $Path;
 			public static $FileName;
			public static $FileBaseName;
			public static $FileExtension;
			public static $QueryString;
			public static $Params;
			public static $HasInitialized;
			public static $RouteFallBackFunction;
			public static $DebugLogArray;
			public static $OutputDebugLog;
		//<-- End :: Properties
		
		//##########################################################################################
		
		//--> Begin Method :: Initialize
			public static function Initialize($OutputDebugLog = false){
				//remember the raw request
				Router::$RequestURI = $_SERVER["REQUEST_URI"];
				Router::$DebugLogArray = array();
				Router::$OutputDebugLog = $OutputDebugLog;
				
				if(Router::$HasInitialized != true){
					Router::Log("<Router:DebugLog>Initializing</Router:DebugLog>");
					
					//remove query string from request uri
					$RequestedPath = str_replace(array($_SERVER["QUERY_STRING"], "?"), "", $_SERVER["REQUEST_URI"]);
					
					//keep a copy of the ORIGINAL querystring
					Router::$QueryString = $_SERVER["QUERY_STRING"];
					
					//determine if this is a dir request or a file request
					if(substr($RequestedPath, -1) != "/"){
						//get file parts
						$TmpFileParts = pathinfo($RequestedPath);

						//get file name
						Router::$FileName = null;
						if(!empty($TmpFileParts["basename"])){
							Router::$FileName = $TmpFileParts["basename"];
						}

						//get file base name
						Router::$FileBaseName = null;
						if(!empty($TmpFileParts["filename"])){
							Router::$FileBaseName = $TmpFileParts["filename"];
						}
						
						//get file extension
						Router::$FileExtension = null;
						if(!empty($TmpFileParts["extension"])){
							Router::$FileExtension = $TmpFileParts["extension"];
						}
						
						//remove file from path
						Router::$Path = str_replace(basename($RequestedPath), "", $RequestedPath);
					}
					//this is a dir request
					else{
						Router::$Path = $RequestedPath;
					}
					
					//prepare paths array
					Router::$Paths = null;
					if(Router::$Path != "/"){
						//explode by /
						Router::$Paths = explode("/",$RequestedPath);
						
						//remove first empty element
						array_shift(Router::$Paths);
						
						if(Router::$Paths[count(Router::$Paths) - 1] == ""){
							array_pop(Router::$Paths);
						}
					}
					
					//has been initialized
					Router::$HasInitialized = true;
					Router::Log("<Router:DebugLog>Finished Initializing</Router:DebugLog>");
				}
			}
				
		//--> End Method :: Initialize

		//##########################################################################################
		
		//--> Begin Method :: SetParam
			public static function SetParam($Name, $Value){
				Router::Log("<Router:DebugLog>SetParam ". $Name ."=". $Value ."</Router:DebugLog>");
				Router::$Params[$Name] = $Value;
			}
				
		//--> End Method :: SetParam

		//##########################################################################################
		
		//--> Begin Method :: GetParam
			public static function GetParam($Name){
				Router::Log("<Router:DebugLog>GetParam ". $Name ."=". Router::$Params[$Name] ."</Router:DebugLog>");
				return Router::$Params[$Name];
			}
				
		//--> End Method :: GetParam

		//##########################################################################################
		
		//--> Begin Method :: GetPath
			public static function GetPath(){
				$ReturnPath = "/";
				for($i = 0; $i < count(Router::$Paths); $i++){
					$ReturnPath .= Router::$Paths[$i] ."/";
				}
				Router::Log("<Router:DebugLog>GetPath ". $ReturnPath ."</Router:DebugLog>");
				return $ReturnPath;
			}
		//--> End Method :: GetPath

		//##########################################################################################
		
		//--> Begin Method :: AppendQueryString
			public static function AppendQueryString($Value){
				Router::Log("<Router:DebugLog>AppendQueryString ". $Value ."</Router:DebugLog>");
				Router::$QueryString .= $Value;
			}
		//--> End Method :: AppendQueryString
		
		//##########################################################################################
		
		//--> Begin Method :: Route
			public static function Route($DocumentRoot, $RouteFile, $RouteFallBackFunction){
				Router::Log("<Router:DebugLog>Route DocumentRoot=". $DocumentRoot ." RoutFile=". $RouteFile ." RouteFallBackFunction=". $RouteFallBackFunction ."</Router:DebugLog>");
				//set route fallback function
				Router::$RouteFallBackFunction = $RouteFallBackFunction;
				
				if(Router::$HasInitialized == true){
					$RoutePath = Router::$Path;
					for($i = count(Router::$Paths) - 1; $i >= 0; $i--){
						//handle first iteration
						if(count(Router::$Paths) - 1 == $i){
							if(file_exists($DocumentRoot . Router::$Path . $RouteFile)){
								Router::Log("<Router:DebugLog>Route 1. Routed= ". $DocumentRoot . Router::$Path . $RouteFile ."</Router:DebugLog>");
								require_once($DocumentRoot . Router::$Path . $RouteFile);
								return;
							}
						}
						else{
							//replace off the end - rather than anywhere the string is found
							if(substr($RoutePath, (strlen($RoutePath) - strlen(Router::$Paths[$i] ."/")), strlen(Router::$Paths[$i] ."/")) == Router::$Paths[$i]."/"){
								$RoutePath = substr($RoutePath, 0, (strlen($RoutePath) - strlen(Router::$Paths[$i] ."/")));
							}
							if(file_exists($DocumentRoot . $RoutePath . $RouteFile)){
								Router::Log("<Router:DebugLog>Route 2. Routed= ". $DocumentRoot . $RoutePath . $RouteFile ."</Router:DebugLog>");
								require_once($DocumentRoot . $RoutePath . $RouteFile);
								return;
							}
						}
					}
					if(file_exists($DocumentRoot . $RoutePath . $RouteFile)){
						Router::Log("<Router:DebugLog>Route 3. Routed= ". $DocumentRoot . $RoutePath . $RouteFile ."</Router:DebugLog>");
						require_once($DocumentRoot . $RoutePath . $RouteFile);
						return;
					}
					
					Router::Log("<Router:DebugLog>Route Call Fallback Function= ". Router::$RouteFallBackFunction ."</Router:DebugLog>");
					//execute route fallback function
					call_user_func(Router::$RouteFallBackFunction);
				}
				else{
					throw new Exception("Footprint.Router.Route(): The router has not been initialized.");
				}
			}
		//--> End Method :: Route
		
		//##########################################################################################
		
		//--> Begin Method :: Redirect
			public static function Redirect($RedirectURL){
				Router::Log("<Router:DebugLog>Redirect ". $RedirectURL ."</Router:DebugLog>");
				header("Location: ". $RedirectURL, TRUE, 301);
				exit();
			}
		//--> End Method :: Redirect
				
		//##########################################################################################
		
		//--> Begin Method :: ReconcilePaths
			public static function ReconcilePaths($RedirectPath, $RedirectFileName){
				//set enviroment values
				$_SERVER["SCRIPT_FILENAME"] = $_SERVER["DOCUMENT_ROOT"] . $RedirectPath . $RedirectFileName;
				$_SERVER["SCRIPT_NAME"] = $RedirectPath . $RedirectFileName;
				$_SERVER["PHP_SELF"] = $RedirectPath . $RedirectFileName;
				$_SERVER["QUERY_STRING"] = Router::$QueryString;
				
				Router::Log("<Router:DebugLog>ReconcilePaths RedirectPath=". $RedirectPath ." RedirectFileName=". $RedirectFileName ."</Router:DebugLog>");
				Router::Log("<Router:DebugLog>ReconcilePaths ". $_SERVER["SCRIPT_FILENAME"] ."</Router:DebugLog>");
			}
		//--> End Method :: ReconcilePaths
	
		//##########################################################################################
		
		//--> Begin Method :: Finalize
			public static function Finalize($RedirectPath, $RedirectFileName){
				//set enviroment values
				$_SERVER["SCRIPT_URI"] = "http://". $_SERVER["HTTP_HOST"] . $RedirectPath . $RedirectFileName; 
				$_SERVER["SCRIPT_FILENAME"] = $_SERVER["DOCUMENT_ROOT"] . $RedirectPath . $RedirectFileName;
				$_SERVER["SCRIPT_NAME"] = $RedirectPath . $RedirectFileName;
				$_SERVER["PHP_SELF"] = $RedirectPath . $RedirectFileName;
				$_SERVER["QUERY_STRING"] = Router::$QueryString;
				
				Router::Log("<Router:DebugLog>ReconcilePaths RedirectPath=". $RedirectPath ." RedirectFileName=". $RedirectFileName ."</Router:DebugLog>");
				Router::Log("<Router:DebugLog>ReconcilePaths ". $_SERVER["SCRIPT_FILENAME"] ."</Router:DebugLog>");
				
				//include init file
				require_once(Router::GetParam("DocumentRoot") ."/_GLOBAL/init.php");
				
				//Init!
				F::Init();
			}
		//--> End Method :: Finalize
				
		//##########################################################################################
		
		//--> Begin Method :: Log
			public static function Log($Data){
				//save debug log
				Router::$DebugLogArray[] = $Data;
				
				//check for output
				if(Router::$OutputDebugLog == true){
					echo($Data ."\n");
				}
			}
		//--> End Method :: Log

		//##########################################################################################
	}
//--> End Class :: Router

//##########################################################################################
?>