<?php

class Page {
    /**
     * the main permission html generator
     */
    public static function getPermissions($node, $data) {
        //replace permissions
        F::$doc->setInnerHTML($node, self::getPermissionBranch(""));
    }
    
    /**
     * recursive html generating of permission branches
     */
    public static function getPermissionBranch($securityID) {
        //get branch of security hierarchy
        F::$db->loadCommand("get-permission-branch");
        F::$db->sqlKey("#dk_id_parent#", $securityID);
        F::$db->bindKeys(F::$engineArgs);
        $tblData = F::$db->getDataTable();
        
        //loop through this branch
        $tmpSecurityOptions = "";
        for($i = 0 ; $i < count($tblData) ; $i++) {
            $tmpSecurityOptions .= "<li>";
            $tmpSecurityOptions .= WebForm::hiddenField("fk_security_id", $tblData[$i]["fk_security_id"]);
            $tmpSecurityOptions .= WebForm::dropDown("permission_". $tblData[$i]["fk_security_id"], $tblData[$i]["permit"], 1, "|yes|no", "", 0, "");
            $tmpSecurityOptions .= " ". $tblData[$i]["name"] ." ";
            $tmpSecurityOptions .= self::getPermissionBranch($tblData[$i]["fk_security_id"]);
            $tmpSecurityOptions .= "</li>";
        }
        
        //return
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
        F::$db->loadCommand("delete-group-permissions", F::$engineArgs);
        F::$db->executeNonQuery();
        
        //break apart security ids
        $arrSecurityIDs = explode(",", F::$request->input("fk_security_id"));
        
        //loop through permissions and insert where appropriate
        for($i = 0 ; $i < count($arrSecurityIDs) ; $i++) {
            $thisPermission = F::$request->input("permission_". $arrSecurityIDs[$i]);
            if($thisPermission == "") {
                //do nothing
            }
            else {
                F::$db->loadCommand("add-group-permission");
                F::$db->sqlKey("#fk_security_id#", $arrSecurityIDs[$i]);
                F::$db->sqlKey("#permit#", $thisPermission);
                F::$db->bindKeys(F::$engineArgs);
                F::$db->executeNonQuery();
            }
        }
        
        F::$alerts->add("Changes saved.");
    }
    
    /**
     * handles teh delete all action
     */
    public static function actionDeleteAll() {
        F::$db->loadCommand("delete-group-permissions", F::$engineArgs);
        F::$db->executeNonQuery();
        
        F::$warnings->add("Deleted group permissions.");
    }
}
