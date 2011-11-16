<?php
//##########################################################################################

//--> Begin Function :: ServerStatus
	function ServerStatus($StatusCode) {
		//make sure we have what we need
		require_once($_SERVER["DOCUMENT_ROOT"] ."/_GLOBAL/init.php");
		
		//start up
		F::Constructor();
		
		//set 500 server error status code
		F::$Response->AddHeader("HTTP/1.1 ". $StatusCode,"");
		
		//get page template
		F::$Doc->LoadFile(F::FilePath("_LIB/footprint/server-status.html"), F::$Config->Get("root-path"));
		
		//set some binders
		F::$DOMBinders["status-code"] = $StatusCode;
		F::$DOMBinders["mail-to-email"] = F::$Config->Get("admin-email");
		F::$DOMBinders["mail-to-href"] = "mailto:". F::$Config->Get("admin-email");
		if($StatusCode == 404) {
			F::$DOMBinders["status-message"] = "Page Not Found";
		}
		
		//do data binding
		F::$Doc->BindResources(F::$Doc);
		F::$Doc->FinalBind();
		
		//finalize request
		F::Finalize();
	}
//<-- End Function :: ServerStatus

//##########################################################################################
?>