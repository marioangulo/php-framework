<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Validate
			public static function Validate() {
				if(F::$Request->Input("ip_address") == "") {
					F::$Errors->Add("ip_address", "required");
				}
				else {
					F::$DB->LoadCommand("duplicate-ip-check", F::$PageInput);
					if(F::$DB->GetDataString() != "") {
						F::$Errors->Add("ip_address", "ip not available");
					}
				}
				if(F::$Request->Input("description") == "") {
					F::$Errors->Add("description", "required");
				}
				else {
					if(!DataValidator::MaxLength(F::$Request->Input("description"), 255)) {
						F::$Errors->Add("Descriptions must be 255 characters or less. The description you supplied was '". strlen($inpDescription) ."' characters.");
					}
				}
			}
		//<-- End Method :: Validate
		
		//##################################################################################
		
		//--> Begin Method :: Action_CreateNew
			public static function Action_CreateNew() {
				//validate
				self::Validate();
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("create-new", F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$DB->GetLastInsertID());
				}
			}
		//<-- End Method :: Action_CreateNew
		
		//##################################################################################
		
		//--> Begin Method :: Action_Update
			public static function Action_Update() {
				//validate
				self::Validate();
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("update", F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
				}
			}
		//<-- End Method :: Action_Update
		
		//##################################################################################
		
		//--> Begin Method :: Action_Delete
			public static function Action_Delete() {
				F::$DB->LoadCommand("delete", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$Response->RedirectURL = F::URL("root/ip-access/index.html");
			}
		//<-- End Method :: Action_Delete
	}
//<-- End Class :: Page

//##########################################################################################
?>