<?php
//##########################################################################################

//--> Begin Class :: Helpers
	class H {
		//--> Begin Method :: Event_BeforeBinding
			public static function Event_BeforeBinding() {
				if(F::$PageNamespace != "root/security/index") {
					//tab links
					if(F::$Request->Input("id") != "") {
						F::$DOMBinders["tab_details_url"] = "root/security/add-edit.html?id=". F::$Request->Input("id");
						F::$DOMBinders["tab_groups_url"] = "root/security/groups.html?id=". F::$Request->Input("id");
						F::$DOMBinders["tab_users_url"] = "root/security/users.html?id=". F::$Request->Input("id");
					}
					else {
						F::$Doc->Traverse("//*[@id='tab-root/security/groups']//a")->SetAttribute("class", "disabled");
						F::$Doc->Traverse("//*[@id='tab-root/security/users']//a")->SetAttribute("class", "disabled");
					}
				}
			}
		//<-- End Method :: Event_BeforeBinding
		
		//##################################################################################
		
		//--> Begin Method :: GetBirdSeed
			public static function GetBirdSeed($Node, $Data) {
				if(F::$PageNamespace == "root/security/index") {
					F::$Doc->SetInnerHTML($Node, self::BirdSeed(F::$Request->Input("dk_id_parent"), 0));
				}
				else {
					F::$Doc->SetInnerHTML($Node, self::BirdSeed((F::$Request->Input("id") == "" ? F::$Request->Input("dk_id_parent") : F::$Request->Input("id")), 0));
				}
			}
		//<-- End Method :: GetBirdSeed
		
		//##################################################################################
		
		//--> Begin Method :: BirdSeed
			public static function BirdSeed($SecurityID, $Counter) {
				//get data
				F::$DB->SQLCommand = "
					SELECT
						id,
						dk_id_parent,
						name
					FROM user_security
					WHERE id = '#security_id#'
				";
				F::$DB->SQLKey("#security_id#", $SecurityID);
				$Data = F::$DB->GetDataRow();
				
				//check for rows
				if($Data == null) {
					return "Security";
				}
				else {
					$tmpTailLink = "";
					$tmpThisLink = "<a href=\"". F::URL("root/security/index.html?dk_id_parent=". $Data["id"]) ."\">". $Data["name"] ."</a>";
					
					//continue getting seed
					if($Data["dk_id_parent"] == "0") {
						//no parent security
						$tmpTailLink = "<a href=\"". F::URL("root/security/index.html") ."\">Security</a> &#8594; ";
						$tmpTailLink .= $tmpThisLink;
					}
					else {
						//get this parent task first
						$tmpTailLink .= self::BirdSeed($Data["dk_id_parent"], $Counter + 1);
						$tmpTailLink .= " &#8594; ". $tmpThisLink;
					}
					
					return $tmpTailLink;
				}
			}
		//<-- End Method :: BirdSeed
	}
//<-- End Class :: Helpers

//##########################################################################################
?>