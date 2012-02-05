<?php

class Page {
    /**
     * generates the group drop down
     */
    public static function groupDD($node) {
        //create a list menu
        $dropDown = new WebFormMenu("tmp", 1, 0);
        
        //default option
        $dropDown->addOption("--- Select a Group ---", "");
        
        //get categories
        F::$db->sqlCommand = "
            SELECT id, name
            FROM user_group_category
            WHERE is_active = 'yes'
            ORDER BY name
        ";
        $tblCategories = F::$db->getDataTable();
        
        //get items for this category
        for($i = 0 ; $i < count($tblCategories) ; $i++) {
            $dropDown->addOptionGroup($tblCategories[$i]["name"]);
            
            //get category items
            F::$db->sqlCommand = "
                SELECT id, name
                FROM user_group
                WHERE
                    is_active = 'yes'
                    AND fk_category_id = '#fk_category_id#'
                ORDER BY name
            ";
            F::$db->sqlKey("#fk_category_id#", $tblCategories[$i]["id"]);
            $tblItems = F::$db->getDataTable();
            
            //build options
            for($j = 0 ; $j < count($tblItems) ; $j++) {
                $dropDown->addOption($tblItems[$j]["name"], $tblItems[$j]["id"]);
            }
        }
        
        //populate the options
        F::$doc->setInnerHTML($node, $dropDown->getOptionTags());
    }
    
    /**
     * handles the add action
     */
    public static function actionAdd() {
        //validate
        if(F::$request->input("fk_group_id") == "0" || F::$request->input("fk_group_id") == "") {
            F::$errors->add("You must select a group.");
        }
        else {
            F::$db->loadCommand("duplicate-permission-check", F::$engineArgs);
            if(F::$db->getDataString() != "") {
                F::$errors->add("A permission has already been added for that group.");
            }
        }
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("add-group", F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$alerts->add("Changes saved.");
        }
    }
    
    /**
     * handles the update action
     */
    public static function actionUpdate() {
        //validate
        if(F::$request->input("permission_id") == "0" || F::$request->input("permission_id") == "") {
            F::$errors->add("Didn't know what permission that's for.");
        }
        
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
        F::$db->loadCommand("delete", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$warnings->add("Deleted permissions.");
    }
}
