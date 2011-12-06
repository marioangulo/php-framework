<?php

class Page {
    /**
     * handles the add user action
     */
    public static function actionAddUser() {
        //validate
        if(F::$request->input("fk_user_id") == "0" || F::$request->input("fk_user_id") == "") {
            F::$errors->add("You must select a user.");
        }
        else {
            F::$db->loadCommand("duplicate-permission-check", F::$engineArgs);
            if(F::$db->getDataString() != "") {
                F::$errors->add("A permission has already been added for that user.");
            }
        }
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("add-user", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$response->redirectURL = F::url(F::$engineNamespace .".html?id=". F::$request->input("id"));
        }
    }
    
    /**
     * handles the update action
     */
    public static function actionUpdate() {
        F::$db->loadCommand("update", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$response->redirectURL = F::url(F::$engineNamespace .".html?id=". F::$request->input("id"));
    }
    
    /**
     * handles the delete action
     */
    public static function actionDelete() {
        F::$db->loadCommand("delete", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$response->redirectURL = F::url(F::$engineNamespace .".html?id=". F::$request->input("id"));
    }
}
