<?php

class Page {
    /**
     * validates input data
     */
    public static function validate() {
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
                F::$errors->add("Descriptions must be 255 characters or less. The description you supplied was '". strlen($inpDescription) ."' characters.");
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
        //validate
        F::$db->loadCommand("groups-using-category-check", F::$engineArgs);
        if(F::$db->getDataString() != "") {
            F::$errors->add("User groups associated with categories cannot be deleted. You must change the category or delete those groups before deleting this category.");
        }
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("delete", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$responseJSON["delete"] = true;
        }
    }
}
