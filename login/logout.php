<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Event_BeforeFinalize
			public static function Event_BeforeFinalize() {
				//is this user logged in?
				if(F::$User->HasSession()) {
					//log user history
					F::$User->LogHistory("User logged out.");
					
					//log user out
					F::$User->Logout();
				}
				
				//redirect
				F::$Response->RedirectURL = F::URL("index.html");
			}
		//<-- End Method :: Event_BeforeFinalize
	}
//<-- End Class :: Page

//##########################################################################################
?>