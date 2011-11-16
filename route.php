<?php
//##########################################################################################
	
//--> Begin :: Route
	//get the current path
	$CurrentBasePath = (count(Router::$Paths) == 0 ? Router::$FileName : Router::$Paths[0]);
	
	//if this is for "/" finalize on the home page
	if($CurrentBasePath == "") { 
		Router::Finalize("/", "index.html");
	}
	if($CurrentBasePath == "index.html") { 
		Router::Redirect("/");
	}
	
	//call the fallback function
	$RouteFallbackFunction = Router::GetParam("RouteFallbackFunction");
	$RouteFallbackFunction();
//<-- End :: Route

//##########################################################################################
?>