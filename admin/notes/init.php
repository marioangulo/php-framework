<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Event_BeforeBinding
			public static function Event_BeforeBinding() {
				if(F::$Request->Input("fk_pivot_id") == "" || F::$Request->Input("fk_pivot_id") == "0") {
					F::$Doc->GetNodeByID("save_button")->Remove();
					F::$Doc->GetNodesByTagName("textarea")->SetAttribute("placeholder", "*notes are disabled*");
				}
			}
		//<-- End Method :: Event_BeforeBinding
		
		//##################################################################################
		
		//--> Begin Method :: RowHandler
			public static function RowHandler($Node, $Data) {
				$ID = uniqid();
				$Node->setAttribute("data-bind-id", $ID);
				$Row = F::$Doc->Traverse("//*[@data-bind-id='". $ID ."']")->GetDOMChunk();
				
				//new formatted notes
				$OutputNote = $Data["data"];
				$LinksMap = array();
				
				//find URLs
					//get matches
					preg_match_all("/http\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/\S*)?/is", $OutputNote, $arrURLs, PREG_PATTERN_ORDER | PCRE_MULTILINE);
					
					//fix the results
					if(is_array($arrURLs)) {
						for($i = 0; $i < count($arrURLs[0]); $i++) {
							$tmpAnchor = "";
							$tmpURLParts = parse_url($arrURLs[0][$i]);
							if($tmpURLParts["path"] != "" && $tmpURLParts["path"] != "/") {
								$tmpAnchor = "[". $tmpURLParts["host"] ."/...]";
							}
							else {
								$tmpAnchor = "[". $tmpURLParts["host"] ."]";
							}
							$LinkID = "[". uniqid() ."]";
							$LinksMap[$LinkID] = "<a href=\"admin/redirect.html?url=". Codec::URLEncode($arrURLs[0][$i]) ."\" title=\"". Codec::HTMLEncode($arrURLs[0][$i]) ."\" target=\"_blank\">". $tmpAnchor ."</a>";
							$OutputNote = str_replace($arrURLs[0][$i], $LinkID, $OutputNote);
						}
					}
				//end find URLs
				
				//replace any remaining left over &s
				$OutputNote = Codec::XHTMLCleanText($OutputNote);
				
				//now we can put our html back in
					//replace line breaks
					$OutputNote = nl2br($OutputNote);
					
					//put our links back
					$OutputNote = str_replace(array_keys($LinksMap), array_values($LinksMap), $OutputNote);
				//end now we can put our html back in
				
				//set value
				$Row->GetNodesByDataSet("bind-html", "data")->SetInnerHTML($OutputNote);
				$Row->GetNodesByDataSet("bind-html", "data")->RemoveAttribute("data-bind-html");
				
				//remove data-bind-id
				$Node->removeAttribute("data-bind-id");
			}
		//<-- End Method :: RowHandler
		
		//##################################################################################
		
		//--> Begin Method :: Action_AddNote
			public static function Action_AddNote() {
				//add user_id input binders
				F::$SQLKeyBinders["fk_user_id"] = F::$Request->Session("user_id");
				
				//validate input data
				if(trim(F::$Request->Input("data")) == "") {
					F::$Errors->Add("You forgot to enter some notes.");
				}
				if(F::$Request->Input("fk_pivot_table") == "") {
					F::$Errors->Add("Missing table reference.");
				}
				if(F::$Request->Input("fk_pivot_id") == "") {
					F::$Errors->Add("Missing record ID.");
				}
				if(F::$Request->Input("data") == "") {
					F::$Errors->Add("You didn't enter any notes.");
				}
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("add-note", F::$PageInput);
					F::$DB->ExecuteNonQuery();
				}

			}
		//<-- End Method :: Action_AddNote
	}
//<-- End Class :: Page

//##########################################################################################
?>