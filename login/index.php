<?php
//##########################################################################################
	
//--> Begin Class :: Page
	class P {
		public static function Event_Final() {
			F::$Response->AddHeader("X-LOGIN-STATUS", "logged_out");
		}
		
		//##################################################################################
		
		public static function Event_BeforeActions() {
			//figure out if we should redirect to admin, client or publisher.
			if(F::$User->IsLoggedIn()) {
				self::DefaultRedirect();
			}
		}
		
		//##################################################################################
		
		public static function DefaultRedirect() {
			//get default group
			$tmpDefaultGroup = F::$User->GetDefaultGroupID(F::$Request->Session("user_id"));
			
			//admin or root?
			if($tmpDefaultGroup == "1" || $tmpDefaultGroup == "2") {
				F::$Response->RedirectURL = F::URL("admin/index.html");
			}
			//account?
			else if($tmpDefaultGroup == "3") {
				F::$Response->RedirectURL = F::URL("account/index.html");
			}
			//make more requests to figure out where we should go
			else {
				//root?
				if(F::$User->IsMemberOfGroup(F::$Request->Session("user_id"), "1")) {
					F::$Response->RedirectURL = F::URL("admin/index.html");
				}
				//admin?
				else if(F::$User->IsMemberOfGroup(F::$Request->Session("user_id"), "2")) {
					F::$Response->RedirectURL = F::URL("admin/index.html");
				}
				//account?
				else if(F::$User->IsMemberOfGroup(F::$Request->Session("user_id"), "3")) {
					F::$Response->RedirectURL = F::URL("account/index.html");
				}
				//send to the home page
				else {
					F::$Response->RedirectURL = F::URL("index.html");
				}
			}
		}
		
		//##################################################################################
		
		public static function Action_Login() {
			//validate
			if(F::$Request->Input("username") == "") {
				F::$Errors->Add("username", "required");
			}
			if(F::$Request->Input("password") == "") {
				F::$Errors->Add("password", "required");
			}
			
			//take action
			if(F::$Errors->Count() == 0) {
				//lookup login information
				F::$DB->LoadCommand("lookup-login", F::$PageInput);
				$dtrData = F::$DB->GetDataRow();
				
				if(F::$DB->GetFoundRows() == 1) {
					//validate remote IP
					if(!F::$User->HasValidIP($dtrData["id"])) {
						//add alert
						F::$Errors->Add("Login failed: your IP address could not be validated.");
					}
					//their IP is good, lets give them a session
					else {
						//log this user in
						F::$User->Login($dtrData["id"], $dtrData["username"], $dtrData["timezone"], 1440);
						
						//log user history
						F::$User->LogHistory("User logged in.", $dtrData["id"]);
						
						//are we going somewhere specific?
						if(F::$Request->Input("return") != "") {
							F::$Response->RedirectURL = F::$Request->Input("return");
						}
						else {
							self::DefaultRedirect();
						}
					}
				}
				else {
					//log anonymous user history
					F::$User->LogHistory("Attempted user login with username: '". F::$Request->Input("username") ."'.");
					
					//set notice
					F::$Errors->Add("Login failed: username and password combination not found or your account is inactive.");
				}
			}
		}
	}
//<-- End Class :: Page

//##########################################################################################
?>