<?php
//##########################################################################################

//--> Begin Class :: Helpers
	class H {
		//--> Begin Method :: PivotTableDD
			public static function PivotTableDD($Node, $Data) {
				//create a list menu
				$DropDown = new WebFormMenu("tmp", 1, 0);
				
				//contextual
				if(F::$PageNamespace == "admin/statuses/index") {
					F::$DB->SQLCommand = "SELECT DISTINCT fk_pivot_table FROM status";
					$DropDown->AddOption("--- any ---", "");
					$DropDown->AddOption("--- none ---", "0");

				}
				else if(F::$PageNamespace == "admin/statuses/add-edit") {
					F::$DB->SQLCommand = "SHOW TABLES FROM #db#";
					F::$DB->SQLKey("#db#", F::$DB->Schema);
					$DropDown->AddOption("--- none ---", "0");
				}
				
				//get data
				$tblData = F::$DB->GetDataTable();
				
				//build options
				for($i = 0 ; $i < count($tblData) ; $i++) {
					foreach ($tblData[$i] as $Key => $Value) {
						$DropDown->AddOption($Value, $Value);
					}
				}
				
				//populate the options
				F::$Doc->SetInnerHTML($Node, $DropDown->GetOptionTags());
			}
		//<-- End Method :: PivotTableDD
	}
//<-- End Class :: Helpers

//##########################################################################################
?>