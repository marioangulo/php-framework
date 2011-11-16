<?php
//##########################################################################################

//--> Begin Function :: ServerError
	function ServerError() {
		try {
			//set 500 server error status code
			F::$Response->AddHeader("HTTP/1.1 500","");
			
			//get page template
			F::$Doc->LoadFile(F::FilePath("_LIB/footprint/server-error.html"), F::$Config->Get("root-path"));
			
			//set some binders
			F::$DOMBinders["mail-to-email"] = F::$Config->Get("admin-email");
			F::$DOMBinders["mail-to-href"] = "mailto:". F::$Config->Get("admin-email");
			
			//collect log data
			$ErrorLogData = array();
			
			//build data
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//an exception was called
				if($NumberOfArgs == 1){
					$Exception = $Args[0];
					$ErrorLogData["error-message"] = $Exception->getMessage();
				}
				//an error was called
				else if($NumberOfArgs == 5){
					$ErrorLogData["error-message"] .= "Error number: ". $Args[0] ."-". $Args[1] ." in ". $Args[2] ." on line number ". $Args[3] .".";
				}
				
				//continue building data
				$ErrorLogData["application"] = F::$Request->ServerVariables("SERVER_NAME");
				$ErrorLogData["source"] = F::$Request->ServerVariables("SCRIPT_FILENAME");
				$ErrorLogData["url"] = F::$Request->ServerVariables("REQUEST_URI");
				$ErrorLogData["timestamp"] = F::$DateTime->Now()->ToString();
				$ErrorLogData["stack-trace"] = print_r(debug_backtrace(), true);
				$ErrorLogData["http-get"] = print_r($_GET, true);
				$ErrorLogData["http-post"] = print_r($_POST, true);
				$ErrorLogData["session"] = print_r($_SESSION, true);
				$ErrorLogData["cookies"] = print_r($_COOKIE, true);
				$ErrorLogData["environment"] = print_r($_SERVER, true);
				$ErrorLogData["debug-log"] = print_r(F::$DebugLog, true);
				F::$CustomHashes["log"] = $ErrorLogData;
			//end build data
			
			//should we show the stack trace on the page?
			if(F::$Config->Get("show-stack-trace") == false) {
				F::$Doc->GetNodesByDataSet("label", "stack-trace")->Remove();
			}
			
			//should we log the error?
			if(F::$Config->Get("log-errors") == true) {
				F::$DB->Open();
				F::$DB->SQLCommand = "
					INSERT INTO error
		            SET
		            	application = '#application#',
		                source = '#source#',
		                error_message = '#error-message#',
		                debug_log = '#debug-log#',
		                stack_trace = '#stack-trace#',
		                request_url = '#url#',
		                request_get = '#http-get#',
		                request_post = '#http-post#',
		                request_cookie = '#cookies#',
		                request_session = '#session#',
		                environment_variables = '#environment#',
		                timestamp_created = '#timestamp#'
				";
				F::$DB->BindKeys($ErrorLogData);
				F::$DB->ExecuteNonQuery();
				
				//replace error id on page
				F::$DOMBinders["error-id"] = F::$DB->GetLastInsertID();
			}
			else {
				F::$Doc->GetNodesByDataSet("label", "error-identifier")->Remove();
			}
			
			//do data binding
			F::$Doc->BindResources(F::$Doc);
			F::$Doc->FinalBind();
			
			//should we email the error?
			if(F::$Config->Get("email-errors") == true) {
				//build up message
				$Message = new DOMTemplate_Ext();
				$Message->LoadFile(F::FilePath("_LIB/footprint/server-error.email.html"));
				$Message->BindResources($Message);
				$Message->DataBinder(array_merge((array)F::$PageInput, (array)F::$DOMBinders));
				
				//get email ready to send
				F::$Email->AddTo(F::$Config->Get("admin-email"));
				F::$Email->Subject = "Server Error @ ". F::$Request->ServerVariables("SERVER_NAME");
				F::$Email->Message = $Message->ToString();
				F::$Email->IsHTML = true;
				
				//try to send the email
				try{
					//send email
					F::$Email->Send();
					//reset
					F::$Email->Reset();
				}
				catch(Exception $e) {
					//it didn't get sent
				}
			}
			
			//never hurts to try closing the database, 
			F::$DB->Close();
			
			//finalize the request
			F::$Response->Finalize(F::$Doc->ToString());
		}
		catch(Exception $e) {
			//never hurts to try closing the database, 
			F::$DB->Close();
			
			//show something
			print("<h1>500 Fatal Error (Uncaught)</h1>");
			print("<h3>". get_class($e)." thrown within the exception handler.</h3> Message: ".$e->getMessage()." on line ".$e->getLine());
			print("<xmp>". print_r(debug_backtrace(), true) ."</xmp>");
			
			//try to email the debug log
			try { F::EmailDebugLog(true); }
			catch(Exception $e) { /*damn that sucks*/ }
		}
		
	}
//<-- End Function :: ServerError

//##########################################################################################
?>