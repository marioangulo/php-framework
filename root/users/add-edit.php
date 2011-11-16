<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Validate
			public static function Validate() {
				if(F::$Request->Input("username") == "") {
					F::$Errors->Add("username", "required");
				}
				else {
					if(!F::$User->IsUsernameAvailable(F::$Request->Input("username"), F::$Request->Input("id"))) {
						F::$Errors->Add("username", "username not available");
					}
				}
				if(F::$Request->Input("email") == "") {
					F::$Errors->Add("email", "required");
				}
				else {
					if(!DataValidator::IsValidEmail(F::$Request->Input("email"))) {
						F::$Errors->Add("email", "invalid email address.");
					}
					else {
						if(!F::$User->IsEmailAvailable(F::$Request->Input("email"), F::$Request->Input("id"))) {
							F::$Errors->Add("email", "email not available");
						}
					}
				}
				if(F::$Request->Input("timezone") == "") {
					F::$PageInput["timezone"] = "UTC";
				}
				if(F::$Request->Input("password") == "") {
					//do nothing
				}
				else {
					if(F::$Request->Input("password_confirm") == "") {
						F::$Errors->Add("password_confirm", "confirm the password");
					}
					if(F::$Request->Input("password") != "" && F::$Request->Input("password_confirm") != "") {
						if(F::$Request->Input("password") != F::$Request->Input("password_confirm")) {
							F::$Errors->Add("Your passwords you entered did not match.");
						}
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
					F::$DB->LoadCommand("create-new-user", F::$PageInput);
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
					F::$DB->LoadCommand("update-user", F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Response->RedirectURL = F::URL(F::$PageNamespace .".html?id=". F::$Request->Input("id"));
				}
			}
		//<-- End Method :: Action_Update
		
		//##################################################################################
		
		//--> Begin Method :: Action_Delete
			public static function Action_Delete() {
				F::$DB->LoadCommand("delete-permissions", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$DB->LoadCommand("delete-membership", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$DB->LoadCommand("delete-history", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$DB->LoadCommand("delete-user", F::$PageInput);
				F::$DB->ExecuteNonQuery();
				
				F::$Response->RedirectURL = F::URL("root/users/index.html");
			}
		//<-- End Method :: Action_Delete
	}
//<-- End Class :: Page

//##########################################################################################
?>