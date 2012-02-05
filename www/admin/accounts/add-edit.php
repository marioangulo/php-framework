<?php

class Page {
    /**
     * handles the before actions event
     */
    public static function eventBeforeActions() {
        //user id magic
        F::$db->keyBinders["fk_user_id"] = F::$account->getUserID(F::$request->input("id"));
        
        //default timezone
        F::$engineArgs["timezone"] = F::$request->input("timezone", "UTC");
    }
    
    /**
     * validates input data
     */
    public static function validate() {
        if(F::$request->input("username") == "") {
            F::$errors->add("username", "required");
        }
        else {
            if(!F::$user->isUsernameAvailable(F::$request->input("username"), F::$db->keyBinders["fk_user_id"])) {
                F::$errors->add("username", "username not available");
            }
        }
        if(F::$request->input("email") == "") {
            F::$errors->add("email", "required");
        }
        else {
            if(!DataValidator::isValidEmail(F::$request->input("email"))) {
                F::$errors->add("email", "invalid email address");
            }
            else {
                if(!F::$user->isEmailAvailable(F::$request->input("email"), F::$db->keyBinders["fk_user_id"])) {
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
                    F::$errors->add("The passwords you entered did not match.");
                }
            }
        }
        if(F::$request->input("name_first") == "") {
            F::$errors->add("name_first", "required");
        }
        if(F::$request->input("name_last") == "") {
            F::$errors->add("name_last", "required");
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
            
            F::$db->keyBinders["fk_user_id"] = F::$db->getLastInsertID();
            
            F::$db->loadCommand("add-to-group", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$db->loadCommand("create-new-account", F::$engineArgs);
            F::$db->executeNonQuery();
            $tmpAccountID = F::$db->getLastInsertID();
            
            F::$responseJSON["id"] = $tmpAccountID;
        }
    }
    
    /**
     * handles the update action
     */
    public static function actionUpdate() {
        //validate
        self::validate();
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("update-user", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$db->loadCommand("update-account", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$alerts->add("Changes saved.");
        }
    }
    
    /**
     * handles the delete action
     */
    public static function actionDelete() {
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("delete-user-permissions", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$db->loadCommand("delete-user-membership", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$db->loadCommand("delete-user-history", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$db->loadCommand("delete-user", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$db->loadCommand("delete-account-notes", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$db->loadCommand("delete-account", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$responseJSON["delete"] = true;
        }
    }
}
