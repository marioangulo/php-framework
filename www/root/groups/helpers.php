<?php

class Helpers {
    /**
     * handles teh before binding event
     */
    public static function eventBeforeBinding() {
        if(F::$engineNamespace != "root/groups/index") {
            //get details
            F::$db->sqlCommand = "
                SELECT 
                    name AS groupname, 
                    '/root/groups/add-edit.html?id=#id#' AS details_url 
                FROM user_group 
                WHERE id = '#id#'
            ";
            F::$db->bindKeys(F::$engineArgs);
            $tmpGroupDetails = F::$db->getDataRow();
            F::$doc->domBinders["groupname"] = $tmpGroupDetails["groupname"];
            F::$doc->domBinders["details_url"] = $tmpGroupDetails["details_url"];
            
            //tab links
            if(F::$request->input("id") != "") {
                F::$doc->domBinders["tab_details_url"] = "/root/groups/add-edit.html?id=". F::$request->input("id");
                F::$doc->domBinders["tab_permissions_url"] = "/root/groups/permissions.html?id=". F::$request->input("id");
            }
            else {
                F::$doc->traverse("//*[@id='tab-root/groups/permissions']//a")->setAttribute("class", "disabled");
            }
        }
    }
    
    /**
     * generates the category drop down
     */
    public static function categoryDD($node) {
        //create a list menu
        $dropDown = new WebFormMenu("tmp", 1, 0);
        
        //contextual
        if(F::$engineNamespace == "root/groups/index") {
            $dropDown->addOption("--- any ---", "");
            $dropDown->addOption("--- none ---", "0");
        }
        else if(F::$engineNamespace == "root/groups/add-edit") {
            $dropDown->addOption("--- none ---", "0");
        }
        
        //get categories
        F::$db->sqlCommand = "
            SELECT id, name
            FROM user_group_category
            WHERE is_active = 'yes'
            ORDER BY name
        ";
        $tblData = F::$db->getDataTable();
        
        //get items for this category
        for($i = 0 ; $i < count($tblData) ; $i++) {
            $dropDown->addOption($tblData[$i]["name"], $tblData[$i]["id"]);
        }
        
        //populate the options
        F::$doc->setInnerHTML($node, $dropDown->getOptionTags());
    }
}
