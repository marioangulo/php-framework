<?php

class Page {
    /**
     * handles the before binding event
     */
    public static function eventBeforeBinding() {
        if(F::$request->input("action") == "") {
            if(F::$request->input("s") == "") {
                F::$errors->add("You don't have a valid reset password request.");
                F::$doc->getNodeByID("reset")->remove();
            }
            else {
                //make sure this is a real request
                F::$db->loadCommand("validate-session", F::$engineArgs);
                if(F::$db->getDataString() == "") {
                    F::$errors->add("Your reset password request is invalid.");
                    F::$doc->getNodeByID("reset")->remove();
                }
            }
        }
    }
    
    /**
     * handles the set password action
     */
    public static function actionSetPassword() {
        //validate
        if(F::$request->input("password") == "") {
            F::$errors->add("password", "required");
        }
        if(F::$request->input("password_confirm") == "") {
            F::$errors->add("password_confirm", "required");
        }
        if(F::$request->input("password") != "" && F::$request->input("password_confirm") != "") {
            if(F::$request->input("password") != F::$request->input("password_confirm")) {
                F::$errors->add("Your passwords you entered did not match.");
            }
        }
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("set-password", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$alerts->add("Your password has been updated, <a href=\"login/index.html\">login to confirm</a>.");
            F::$doc->getNodeByID("form")->remove();
        }
    }
}
