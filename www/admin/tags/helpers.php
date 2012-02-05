<?php

class Helpers {
    /**
     * generates the pivot table drop down
     */
    public static function pivotTableDD($node, $data) {
        //create a list menu
        $dropDown = new WebFormMenu("tmp", 1, 0);
        
        //contextual
        if(F::$engineNamespace == "admin/tags/index") {
            F::$db->sqlCommand = "SELECT DISTINCT fk_pivot_table FROM tag";
            $dropDown->addOption("--- any ---", "");
            $dropDown->addOption("--- none ---", "0");

        }
        else if(F::$engineNamespace == "admin/tags/add-edit") {
            F::$db->sqlCommand = "SHOW TABLES FROM #db#";
            F::$db->sqlKey("#db#", F::$db->schema);
            $dropDown->addOption("--- none ---", "0");
        }
        
        //get data
        $tblData = F::$db->getDataTable();
        
        //build options
        for($i = 0 ; $i < count($tblData) ; $i++) {
            foreach ($tblData[$i] as $key => $value) {
                $dropDown->addOption($value, $value);
            }
        }
        
        //populate the options
        F::$doc->setInnerHTML($node, $dropDown->getOptionTags());
    }
}
