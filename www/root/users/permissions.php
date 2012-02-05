<?php

class Page {
    /**
     * the main permission html generator
     */
    public static function getPermissions($node, $data) {
        F::$db->loadCommand("get-user-group-ids", F::$engineArgs);
        $tmpGroupIDs = F::$db->getDataString(",");
        if($tmpGroupIDs == "") { $tmpGroupIDs = "0"; }
        F::$doc->setInnerHTML($node, self::getPermissionBranch($tmpGroupIDs, ""));
    }
    
    /**
     * recursive html generating of permission branches
     */
    public static function getPermissionBranch($groupIDs, $securityID) {
        $tmpSecurityOptions = "";
        F::$db->loadCommand("get-permission-branch");
        F::$db->sqlKey("#group_ids#", $groupIDs);
        F::$db->sqlKey("#dk_id_parent#", $securityID);
        F::$db->bindKeys(F::$engineArgs);
        $tblData = F::$db->getDataTable();
        for($i = 0 ; $i < count($tblData) ; $i++) {
            $groupHasPermission = ($tblData[$i]["group_permit"] == "yes" ? true : false);
            
            $tmpSecurityOptions .= "<li>";
            $tmpSecurityOptions .= "<span class=\"". ($groupHasPermission ? "group_permitted" : "group_restricted") ."\">";
            $tmpSecurityOptions .= WebForm::hiddenField("fk_security_id", $tblData[$i]["fk_security_id"]);
            $tmpSecurityOptions .= WebForm::dropDown("permission_". $tblData[$i]["fk_security_id"], $tblData[$i]["permit"], 1, "|yes|no", "", 0, "");
            $tmpSecurityOptions .= " ". $tblData[$i]["name"] ." ";
            $tmpSecurityOptions .= "</span>";
            $tmpSecurityOptions .= self::getPermissionBranch($groupIDs, $tblData[$i]["fk_security_id"]);
            $tmpSecurityOptions .= "</li>";
        }
        if($tmpSecurityOptions == "") {
            return "";
        }
        else {
            return "<ul class=\"security\">". $tmpSecurityOptions ."</ul>";
        }
    }
    
    /**
     * handles the update action
     */
    public static function actionUpdate() {
        F::$db->loadCommand("delete-user-permissions", F::$engineArgs);
        F::$db->executeNonQuery();
        
        $arrSecurityIDs = explode(",", F::$request->input("fk_security_id"));
        for($i = 0 ; $i < count($arrSecurityIDs) ; $i++) {
            $thisPermission = F::$request->input("permission_". $arrSecurityIDs[$i]);
            if($thisPermission == "") {
                //skip insert
            }
            else {
                F::$db->loadCommand("add-user-permission");
                F::$db->sqlKey("#fk_security_id#", $arrSecurityIDs[$i]);
                F::$db->sqlKey("#permit#", $thisPermission);
                F::$db->bindKeys(F::$engineArgs);
                F::$db->executeNonQuery();
            }
        }
        
        F::$alerts->add("Changes saved.");
    }
    
    /**
     * handles the delete all action
     */
    public static function actionDeleteAll() {
        F::$db->loadCommand("delete-user-permissions", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$warnings->add("Deleted user permissions.");
    }
}
