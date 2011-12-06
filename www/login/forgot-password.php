<?php

class Page {
    /**
     * handles the reset password action
     */
    public static function actionResetPassword() {
        //validate
        if(F::$request->input("email") == "") {
            F::$errors->add("email", "required");
        }
        else if(!DataValidator::isValidEmail(F::$request->input("email"))) {
            F::$errors->add("email", "invalid email address");
        }
        else {
            F::$db->loadCommand("validate-email", F::$engineArgs);
            if(F::$db->getDataString() == "") {
                F::$errors->add("Account inactive or not found.");
            }
        }
        
        //take action
        if(F::$errors->count() == 0) {
            //create new session id for this user account
            $newSessionID = F::$user->createSessionID();
            
            //update user account with new session
            F::$db->loadCommand("update-user-session", F::$engineArgs);
            F::$db->sqlKey("#session_id#", $newSessionID);
            F::$db->executeNonQuery();
            
            //lookup username
            F::$db->loadCommand("get-username", F::$engineArgs);
            $username = F::$db->getDataString();
            
            //build up message
            $message = new DOMTemplate();
            $message->loadFile(F::filePath("/login/forgot-password.email.html"));
            $message->getNodesByDataSet("label", "username")->setInnerText($username);
            $message->getNodesByDataSet("label", "reset_link")->setAttribute("href", F::fullURI("login/reset-password.html?s=". $newSessionID));
            
            //get email ready to send
            F::$emailClient->addTo(F::$request->input("email"));
            F::$emailClient->subject = "Password Reset Request";
            F::$emailClient->message = $message->toString();
            F::$emailClient->isHTML = true;
            
            //try to send the email
            try{
                //send email
                F::$emailClient->send();
                
                //alert user to check email
                F::$alerts->add("Please check your email for instructions on how to reset your password.");
                F::$doc->getNodeByID("reset")->remove();
            }
            catch(Exception $e){
                F::$errors->add("Email failed to send.");
            }
        }
    }
}
