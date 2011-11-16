<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Action_SendMessage
			public static function Action_SendMessage() {
				//validate
				if(F::$Request->Input("name_first") == "") {
					F::$Errors->Add("name_first", "required");
				}
				if(F::$Request->Input("name_last") == "") {
					F::$Errors->Add("name_last", "required");
				}
				if(F::$Request->Input("email") == "") {
					F::$Errors->Add("email", "required");
				}
				else {
					if(!DataValidator::IsValidEmail(F::$Request->Input("email"))) {
						F::$Errors->Add("email", "invalid email address");
					}
				}
				if(F::$Request->Input("phone") == "") {
					F::$Errors->Add("phone", "required");
				}
				if(F::$Request->Input("message") == "") {
					F::$Errors->Add("message", "required");
				}
				
				//take action
				if(F::$Errors->Count() == 0) {
					//build up message
					$Message = new DOMTemplate_Ext();
					$Message->LoadFile(F::FilePath("contact/index.email.html"));
					$Message->DataBinder(F::$PageInput);
					
					//get email ready to send
					F::$Email->AddTo(F::$Config->Get("admin-email"));
					F::$Email->Subject = F::$Config->Get("host-name") .": Contact Form Submission";
					F::$Email->Message = $Message->ToString();
					F::$Email->IsHTML = true;
					
					//try to send the email
					try {
						F::$Email->Send();
						F::$Doc->GetNodeByID("contact")->Remove();
						F::$Alerts->Add("Success. We have received your message.");
					}
					catch(Exception $e){
						F::$Errors->Add("", "Email failed to send.");
					}
				}
			}
		//<-- End Method :: Action_SendMessage
	}
//<-- End Class :: Page

//##########################################################################################
?>