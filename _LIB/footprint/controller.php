<?php
require_once($_SERVER["DOCUMENT_ROOT"] ."/_LIB/footprint/router.php");
//##########################################################################################

//--> Begin :: Controller
	//cache check
		//keep track of the originals
		$_SERVER["X_ORIGINAL_SCRIPT_URI"] = $_SERVER["SCRIPT_URI"];
		$_SERVER["X_ORIGINAL_QUERY_STRING"] = $_SERVER["QUERY_STRING"];
		
		//we don't cache post requests
		if($_SERVER["REQUEST_METHOD"] != "POST") {
			$ParsedURL = parse_url($_SERVER["SCRIPT_URI"]);
			$Basepath = pathinfo($ParsedURL["path"]);
			$CachePath = $_SERVER["DOCUMENT_ROOT"] ."/_CACHE". $Basepath["dirname"];
			
			//check for cache directory
			if(file_exists($CachePath)) {
				//find the cache file path
				$FileSignature = $Basepath["basename"];
				if($_SERVER["QUERY_STRING"] != "") {
					$FileSignature = $FileSignature ."?". $_SERVER["QUERY_STRING"];
				}
				$CacheFilePath = $CachePath ."/". md5($FileSignature);
				
				//does a rule exist?
				if(file_exists($CacheFilePath .".rule")) {
					//get the rules
					$Rules = json_decode(file_get_contents($CacheFilePath .".rule"), true);
					
					//is it old?
					$ExpiresOn = (int)$Rules["expires"];
					if(time() > $ExpiresOn) {
						//doin't use cache
					}
					else {
						//should we add a content-type header?
						if(isset($Rules["content-type"])) {
							header("Content-Type: ". $Rules["content-type"]);
						}
						
						//print the file and exit
						print(file_get_contents($CacheFilePath));
						exit();
					}
				}
			}
		}
	//end cache check
	
	//initialize router
	Router::Initialize(false);
	
	//set params
	Router::SetParam("IndexScript", "index.php");
	Router::SetParam("IndexFile", "index.html");
	Router::SetParam("ScriptExtension", ".php");
	Router::SetParam("FileExtension", ".html");
	Router::SetParam("RouteFile", "route.php");
	Router::SetParam("DocumentRoot", $_SERVER["DOCUMENT_ROOT"]);
	
	//get file name
	Router::SetParam("RedirectFile", Router::$FileBaseName . Router::GetParam("FileExtension"));
	if(empty(Router::$FileBaseName)){
		//use this for finalize
		Router::SetParam("RedirectFile", Router::GetParam("IndexFile"));
	}
	
	//get redirect path
	Router::SetParam("RedirectPath", Router::$Path);
	
	//create fallback function
		function RouteFallback(){
			//redirect to index file
			if(Router::$FileName == ""){
				Router::Redirect(Router::GetParam("RedirectPath") . Router::GetParam("IndexFile"));
			}
			//does the requested file exist - then finalize it
			else if(file_exists(Router::GetParam("DocumentRoot") . Router::GetParam("RedirectPath") . Router::GetParam("RedirectFile"))){
				//include init file
				require_once(Router::GetParam("DocumentRoot") ."/_GLOBAL/init.php");
				
				//reconcile
				Router::ReconcilePaths(Router::GetParam("RedirectPath"), Router::GetParam("RedirectFile"));
				
				//Init!
				F::Init();
			}
			//throw error - file not found
			else {
				//include init file
				require_once(Router::GetParam("DocumentRoot") ."/_GLOBAL/init.php");
									
				//if all else fails
				ServerStatus(404);
			}
		}
	//end create fallback function

	//set callback function param
	Router::SetParam("RouteFallbackFunction", "RouteFallback");
	
	//route request
	Router::Route(Router::GetParam("DocumentRoot"), Router::GetParam("RouteFile"), "RouteFallback");
//<-- End :: Controller

//##########################################################################################
?>