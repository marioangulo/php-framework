<?php

class Page {
    /**
     * custom row handling
     */
    public static function rowHandler($node, $data) {
        $id = uniqid();
        $node->setAttribute("data-bind-id", $id);
        $row = F::$doc->traverse("//*[@data-bind-id='". $id ."']")->getDOMChunk();
        
        if(isset($data["timestamp_cancelled"])) {
            if($data["timestamp_cancelled"] != "---"){
                $row->root()->setAttribute("class", "inactive");
                $row->traverse("//*[contains(@class, 'button_update')]")->setAttribute("disabled", "disabled");
                $row->traverse("//*[contains(@class, 'button_update')]")->setAttribute("class", "btn");
                $row->traverse("//*[contains(@class, 'button_cancel')]")->setAttribute("disabled", "disabled");
                $row->traverse("//*[contains(@class, 'button_cancel')]")->setAttribute("class", "btn");
            }
        }
        
        //remove data-bind-id
        $node->removeAttribute("data-bind-id");
    }
    
    /**
     * handles the add action
     */
    public static function actionAdd() {
        //validate
        if(F::$request->input("fk_group_id") == "0" || F::$request->input("fk_group_id") == "") {
            F::$errors->add("You must select a group.");
        }
        if(F::$request->input("fk_group_id") != "") {
            F::$db->loadCommand("duplicate-group-check", F::$engineArgs);
            if(F::$db->getDataString() != "") {
                F::$errors->add("This user has already been added to this group.");
            }
        }
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("add-to-group");
            F::$db->sqlKey("#timestamp_created#", F::$dateTime->now()->toString());
            F::$db->bindKeys(F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$alerts->add("Changes saved.");
        }
    }
    
    /**
     * handles the update action
     */
    public static function actionUpdate() {
        if(F::$request->input("is_default") == "yes") {
            F::$db->loadCommand("remove-default-group", F::$engineArgs);
            F::$db->executeNonQuery();
        }
        
        F::$db->loadCommand("set-default-group", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$alerts->add("Changes saved.");
    }
    
    /**
     * handles the cancel action
     */
    public static function actionCancel() {
        //validate
        if(F::$request->input("id") == "1" && F::$request->input("fk_membership_id") == "1") {
            F::$errors->add("You cannot cancel the root user's membership from the root group.");
        }
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("cancel-membership");
            F::$db->sqlKey("#timestamp_cancelled#", F::$dateTime->now()->toString());
            F::$db->bindKeys(F::$engineArgs);
            F::$db->executeNonQuery();
            
            F::$warnings->add("Canceled membership.");
        }
    }
}
