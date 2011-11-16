<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Event_BeforeActions
			public static function Event_BeforeActions() {
				//buttons
				F::$DOMBinders["button_download"] = F::$PageNamespace .".html?action=Download&". F::$Request->QueryString();
				F::$DOMBinders["button_delete"] = F::$PageNamespace .".html?action=Delete&". F::$Request->QueryString();
				
				//date_from/to
				F::$SQLKeyBinders["_date_from_"] = F::$DateTime->Now()->Parse(F::$Request->Input("date_from"))->ToSQLString("yyyy-MM-dd");
				F::$SQLKeyBinders["_date_to_"] = F::$DateTime->Now()->Parse(F::$Request->Input("date_to"))->ToSQLString("yyyy-MM-dd");
			}
		//<-- End Method :: Event_BeforeActions
		
		//##################################################################################
		
		//--> Begin Method :: RowHandler
			public static function RowHandler($Node, $Data) {
				$ID = uniqid();
				$Node->setAttribute("data-bind-id", $ID);
				$Row = F::$Doc->Traverse("//*[@data-bind-id='". $ID ."']")->GetDOMChunk();
				
				$ThisDT = F::$DateTime->Now()->Parse($Data["timestamp_created"]);
				$Row->GetNodesByDataSet("label", "date")->SetInnerText($ThisDT->ToString("MM/dd/yyyy"));
				$Row->GetNodesByDataSet("label", "time")->SetInnerText($ThisDT->ToString("hh:mm:ss TT"));
				
				if($ThisDT->ToString("yyyy-MM-dd") == F::$DateTime->Now()->ToString("yyyy-MM-dd")) {
					$Row->GetNodesByDataSet("label", "date_time")->SetAttribute("class", "time-today");
				}
				else if($ThisDT->ToString("yyyy-MM-dd") == F::$DateTime->Now()->AddDays(-1)->ToString("yyyy-MM-dd")) {
					$Row->GetNodesByDataSet("label", "date_time")->SetAttribute("class", "time-yesterday");
				}
				else {
					$Row->GetNodesByDataSet("label", "date_time")->SetAttribute("class", "time-past");
				}
				
				//remove data-bind-id
				$Node->removeAttribute("data-bind-id");
			}
		//<-- End Method :: RowHandler
		
		//##################################################################################
		
		//--> Begin Method :: Action_Download
			public static function Action_Download() {
				F::$DB->LoadCommand("get-download-data", F::$PageInput);
				$tmpCSV = F::$DB->GetDataString("\n", ",", "\"", 1);
				
				//close the db (we're not going back)
				F::$DB->Close();
				
				//lets change the output header so we download instead show
				F::$Response->AddHeader("Content-disposition", "attachment; filename=". F::$DateTime->Now()->ToString("yyyy-MM-dd") ."_user_history.csv");
				F::$Response->AddHeader("Content-type", "text/csv");
				
				//write the response
				F::$Response->Finalize($tmpCSV);
			}
		//<-- End Method :: Action_Download
		
		//##################################################################################
		
		//--> Begin Method :: Action_Delete
			public static function Action_Delete() {
				F::$DB->LoadCommand("delete-user-history", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				//redirect back here
				F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
			}
		//<-- End Method :: Action_Delete
	}
//<-- End Class :: Page

//##########################################################################################
?>