<?php

class Page {
    /**
     * validates input data
     */
    public static function validate() {
        if(F::$request->input("username") == "") {
            F::$errors->add("username", "required");
        }
        else {
            if(!F::$user->isUsernameAvailable(F::$request->input("username"), F::$request->input("id"))) {
                F::$errors->add("username", "username not available");
            }
        }
        if(F::$request->input("email") == "") {
            F::$errors->add("email", "required");
        }
        else {
            if(!DataValidator::isValidEmail(F::$request->input("email"))) {
                F::$errors->add("email", "invalid email address.");
            }
            else {
                if(!F::$user->isEmailAvailable(F::$request->input("email"), F::$request->input("id"))) {
                    F::$errors->add("email", "email not available");
                }
            }
        }
        if(F::$request->input("timezone") == "") {
            F::$engineArgs["timezone"] = "UTC";
        }
        if(F::$request->input("password") == "") {
            //do nothing
        }
        else {
            if(F::$request->input("password_confirm") == "") {
                F::$errors->add("password_confirm", "confirm the password");
            }
            if(F::$request->input("password") != "" && F::$request->input("password_confirm") != "") {
                if(F::$request->input("password") != F::$request->input("password_confirm")) {
                    F::$errors->add("Your passwords you entered did not match.");
                }
            }
        }
    }
    
    /**
     * handles the create new action
     */
    public static function actionCreateNew() {
        //validate
        self::validate();
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("create-new-user", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$responseJSON["id"] = F::$db->getLastInsertID();
        }
    }
    
    /**
     * handles the udpate action
     */
    public static function actionUpdate() {
        //validate
        self::validate();
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("update-user", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$alerts->add("Changes saved.");
        }
    }
    
    /**
     * handles the delete action
     */
    public static function actionDelete() {
        F::$db->loadCommand("delete-permissions", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$db->loadCommand("delete-membership", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$db->loadCommand("delete-history", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$db->loadCommand("delete-user", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$responseJSON["delete"] = true;
    }
}
