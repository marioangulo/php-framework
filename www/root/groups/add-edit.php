<?php

class Page {
    /**
     * validates input data
     */
    public static function validate() {
        if(F::$request->input("fk_category_id") == "0") {
            F::$errors->add("fk_category_id", "select a category");
        }
        if(F::$request->input("name") == "") {
            F::$errors->add("name", "required");
        }
        else {
            F::$db->loadCommand("duplicate-name-check", F::$engineArgs);
            if(F::$db->getDataString() != "") {
                F::$errors->add("name", "name not available");
            }
        }
        if(F::$request->input("description") == "") {
            F::$errors->add("description", "required");
        }
        else {
            if(!DataValidator::maxLength(F::$request->input("description"), 255)) {
                F::$errors->add("Descriptions must be 255 characters or less. The description you supplied was '". strlen(F::$request->input("description")) ."' characters.");
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
            F::$db->loadCommand("create-new", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$responseJSON["id"] = F::$db->getLastInsertID();
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
            F::$db->loadCommand("update", F::$engineArgs);
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
        
        F::$db->loadCommand("delete-group", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$responseJSON["delete"] = true;
    }
}
