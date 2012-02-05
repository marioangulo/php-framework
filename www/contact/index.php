<?php

class Page {
    /**
     * sends teh submitted message
     */
    public static function actionSendMessage() {
        //validate
        if(F::$request->input("name") == "") {
            F::$errors->add("name", "required");
        }
        if(F::$request->input("email") == "") {
            F::$errors->add("email", "required");
        }
        else {
            if(!DataValidator::isValidEmail(F::$request->input("email"))) {
                F::$errors->add("email", "invalid email address");
            }
        }
        if(F::$request->input("phone") == "") {
            F::$errors->add("phone", "required");
        }
        if(F::$request->input("message") == "") {
            F::$errors->add("message", "required");
        }
        
        //take action
        if(F::$errors->count() == 0) {
            //build up message
            $message = new DOMTemplate_Ext();
            $message->loadFile(F::filePath("contact/index.email.html"));
            $message->dataBinder(F::$engineArgs);
            
            //get email ready to send
            F::$emailClient->addTo(F::$config->get("admin-email"));
            F::$emailClient->subject = F::$config->get("project-name") .": Contact Form Submission";
            F::$emailClient->message = $message->toString();
            F::$emailClient->isHTML = true;
            
            //try to send the email
            try {
                F::$emailClient->send();
                F::$doc->getNodeByID("form")->remove();
                F::$alerts->add("Success. We have received your message.");
            }
            catch(Exception $e){
                F::$errors->add("Email failed to send.". Codec::htmlEncode($e->getMessage()));
            }
        }
    }
}
