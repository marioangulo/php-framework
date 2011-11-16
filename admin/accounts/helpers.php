<?php
//##########################################################################################

//--> Begin Class :: Helpers
	class H {
		//--> Begin Method :: TimezoneDD
			public static function TimezoneDD($Node) {
				//populate the options
				F::$Doc->SetInnerHTML($Node, F::$System->TimeZoneDD(false, false));
			}
		//<-- End Method :: TimezoneDD
	}
//<-- End Class :: Helpers

############################################################################################
?>