<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Event_OnLoad
			public static function Event_OnLoad() {
				F::$Response->RedirectURL = urldecode(F::$Request->Input("url"));
				F::$Response->Finalize();
			}
		//<-- End Method :: Event_OnLoad
	}
//<-- End Class :: Page

//##########################################################################################
?>