<?php

class Helpers {
    /**
     * handles the before binding event
     */
    public static function eventBeforeBinding() {
        if(F::$engineNamespace != "root/users/index") {
            //get details
            F::$db->sqlCommand = "
                SELECT 
                    username, 
                    '/root/users/add-edit.html?id=#id#' AS details_url 
                FROM user 
                WHERE id = '#id#'
            ";
            F::$db->bindKeys(F::$engineArgs);
            $tmpUserDetails = F::$db->getDataRow();
            F::$doc->domBinders["username"] = $tmpUserDetails["username"];
            F::$doc->domBinders["details_url"] = $tmpUserDetails["details_url"];
            
            //tab links
            if(F::$request->input("id") != "") {
                F::$doc->domBinders["tab_details_url"] = "/root/users/add-edit.html?id=". F::$request->input("id");
                F::$doc->domBinders["tab_groups_url"] = "/root/users/groups.html?id=". F::$request->input("id");
                F::$doc->domBinders["tab_permissions_url"] = "/root/users/permissions.html?id=". F::$request->input("id");
                F::$doc->domBinders["tab_history_url"] = "/root/users/history.html?id=". F::$request->input("id");
            }
            else {
                F::$doc->traverse("//*[@id='tab-root/users/groups']//a")->setAttribute("class", "disabled");
                F::$doc->traverse("//*[@id='tab-root/users/permissions']//a")->setAttribute("class", "disabled");
                F::$doc->traverse("//*[@id='tab-root/users/history']//a")->setAttribute("class", "disabled");
            }
        }
    }
    
    /**
     * generates the group drop down
     */
    public static function groupDD($node) {
        //create a list menu
        $dropDown = new WebFormMenu("tmp", 1, 0);
        
        //contextual
        if(F::$engineNamespace == "root/users/index") {
            $dropDown->addOption("--- any ---", "");
            $dropDown->addOption("--- none ---", "0");
        }
        else if(F::$engineNamespace == "root/users/add-edit") {
            $dropDown->addOption("--- none ---", "0");
        }
        else if(F::$engineNamespace == "root/users/groups") {
            $dropDown->addOption("--- Select a Group ---", "");
        }
        
        //get categories
        F::$db->sqlCommand = "
            SELECT id, name
            FROM user_group_category
            WHERE is_active = 'yes'
            ORDER BY name
        ";
        $tblCategories = F::$db->getDataTable();
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
            for($j = 0 ; $j < count($tblItems) ; $j++) {
                $dropDown->addOption($tblItems[$j]["name"], $tblItems[$j]["id"]);
            }
        }
        
        //populate the options
        F::$doc->setInnerHTML($node, $dropDown->getOptionTags());
    }
    
    /**
     * generates the timezone drop down
     */
    public static function timezoneDD($node) {
        //populate the options
        F::$doc->setInnerHTML($node, F::$system->timeZoneDD(false, false));
    }
}
