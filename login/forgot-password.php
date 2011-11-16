<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Action_ResetPassword
			public static function Action_ResetPassword() {
				//validate
				if(F::$Request->Input("email") == "") {
					F::$Errors->Add("email", "required");
				}
				else if(!DataValidator::IsValidEmail(F::$Request->Input("email"))) {
					F::$Errors->Add("email", "invalid email address");
				}
				else {
					F::$DB->LoadCommand("validate-email", F::$PageInput);
					if(F::$DB->GetDataString() == "") {
						F::$Errors->Add("Account inactive or not found.");
					}
				}
				
				//take action
				if(F::$Errors->Count() == 0) {
					//create new session id for this user account
					$NewSessionID = F::$User->CreateSessionID();
					
					//update user account with new session
					F::$DB->LoadCommand("update-user-session", F::$PageInput);
					F::$DB->SQLKey("#session_id#", $NewSessionID);
					F::$DB->ExecuteNonQuery();
					
					//lookup username
					F::$DB->LoadCommand("get-username", F::$PageInput);
					$Username = F::$DB->GetDataString();
					
					//build up message
					$Message = new DOMTemplate();
					$Message->LoadFile(F::FilePath("/login/forgot-password.email.html"));
					$Message->GetNodesByDataSet("label", "username")->SetInnerText($Username);
					$Message->GetNodesByDataSet("label", "reset_link")->SetAttribute("href", F::FullURI("login/reset-password.html?s=". $NewSessionID));
					
					//get email ready to send
					F::$Email->AddTo(F::$Request->Input("email"));
					F::$Email->Subject = "Password Reset Request";
					F::$Email->Message = $Message->ToString();
					F::$Email->IsHTML = true;
					
					//try to send the email
					try{
						//send email
						F::$Email->Send();
						
						//alert user to check email
						F::$Alerts->Add("Please check your email for instructions on how to reset your password.");
						F::$Doc->GetNodeByID("reset")->Remove();
					}
					catch(Exception $e){
						F::$Errors->Add("Email failed to send.");
					}
				}
			}
		//<-- End Method :: Action_ResetPassword
	}
//<-- End Class :: Page

//##########################################################################################
?>