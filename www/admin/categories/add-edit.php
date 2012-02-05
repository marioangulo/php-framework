<?php

class Page {
    /**
     * validates input data
     */
    public static function validate() {
        if(F::$request->input("fk_pivot_table") == "" || F::$request->input("fk_pivot_table") == "0") {
            F::$errors->add("fk_pivot_table", "select a pivot");
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
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("delete", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$responseJSON["delete"] = true;
        }
    }
}
