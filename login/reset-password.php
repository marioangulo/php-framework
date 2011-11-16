<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Event_BeforeBinding
			public static function Event_BeforeBinding() {
				if(F::$Request->Input("action") == "") {
					if(F::$Request->Input("s") == "") {
						F::$Errors->Add("You don't have a valid reset password request.");
						F::$Doc->GetNodeByID("reset")->Remove();
					}
					else {
						//make sure this is a real request
						F::$DB->LoadCommand("validate-session", F::$PageInput);
						if(F::$DB->GetDataString() == "") {
							F::$Errors->Add("Your reset password request is invalid.");
							F::$Doc->GetNodeByID("reset")->Remove();
						}
					}
				}
			}
		//<-- End Method :: Event_BeforeBinding
		
		//##################################################################################
		
		//--> Begin Method :: Action_SetPassword
			public static function Action_SetPassword() {
				//validate
				if(F::$Request->Input("password") == "") {
					F::$Errors->Add("password", "required");
				}
				if(F::$Request->Input("password_confirm") == "") {
					F::$Errors->Add("password_confirm", "required");
				}
				if(F::$Request->Input("password") != "" && F::$Request->Input("password_confirm") != "") {
					if(F::$Request->Input("password") != F::$Request->Input("password_confirm")) {
						F::$Errors->Add("Your passwords you entered did not match.");
					}
				}
				
				//take action
				if(F::$Errors->Count() == 0) {
					F::$DB->LoadCommand("set-password", F::$PageInput);
					F::$DB->ExecuteNonQuery();
					
					F::$Alerts->Add("Your password has been updated, <a href=\"login/index.html\">login to confirm</a>.");
					F::$Doc->GetNodeByID("reset")->Remove();
				}
			}
		//<-- End Method :: Action_SetPassword
	}
//<-- End Class :: Page

//##########################################################################################
?>