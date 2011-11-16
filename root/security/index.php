<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Event_BeforeBinding
			public static function Event_BeforeBinding() {
				F::$DOMBinders["button_new"] = "root/security/add-edit.html?dk_id_parent=". F::$Request->Input("dk_id_parent");
			}
		//<-- End Method :: Event_BeforeBinding
	}
//<-- End Class :: Page

//##########################################################################################
?>