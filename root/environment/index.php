<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Event_BeforeBinding
			public static function Event_BeforeBinding() {
				//footprint data
				$Footprint = array();
				foreach(F::$Config->Get() AS $Key => $Value) {
					if(is_bool($Value)) {
						$Value = $Value ? "true" : "false";
					}
					$Footprint[] = array("property" => $Key, "value" => $Value);
				}
				F::$CustomRows["footprint-data"] = $Footprint;
				
				//environment variables
				function MapKeyValues($k, $v) { return(array("property" => $k, "value" => print_r($v, true))); }
				F::$CustomRows["env-data"] = array_map("MapKeyValues", array_keys($_SERVER), $_SERVER);
			}
		//<-- End Method :: Event_BeforeBinding
	}
//<-- End Class :: Page

//##########################################################################################
?>