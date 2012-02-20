<?php

class Helpers {
    /**
     * handles the before binding event
     */
    public static function eventBeforeBinding() {
        if(F::$engineNamespace != "root/security/index") {
            //tab links
            if(F::$request->input("id") != "") {
                F::$doc->domBinders["tab_details_url"] = "/root/security/add-edit.html?id=". F::$request->input("id");
                F::$doc->domBinders["tab_groups_url"] = "/root/security/groups.html?id=". F::$request->input("id");
                F::$doc->domBinders["tab_users_url"] = "/root/security/users.html?id=". F::$request->input("id");
            }
            else {
                F::$doc->traverse("//*[@id='tab-root/security/groups']//a")->setAttribute("class", "disabled");
                F::$doc->traverse("//*[@id='tab-root/security/users']//a")->setAttribute("class", "disabled");
            }
        }
    }
    
    /**
     * the main html bird seed generator
     */
    public static function getBirdSeed($node, $data) {
        if(F::$engineNamespace == "root/security/index") {
            F::$doc->setInnerHTML($node, self::birdSeed(F::$request->input("dk_id_parent"), 0));
        }
        else {
            F::$doc->setInnerHTML($node, self::birdSeed((F::$request->input("id") == "" ? F::$request->input("dk_id_parent") : F::$request->input("id")), 0));
        }
    }
    
    /**
     * recursive functino to generate levels of the bird seed
     */
    public static function birdSeed($securityID, $counter) {
        //get data
        F::$db->sqlCommand = "
            SELECT
                id,
                dk_id_parent,
                name
            FROM user_security
            WHERE id = '#security_id#'
        ";
        F::$db->sqlKey("#security_id#", $securityID);
        $data = F::$db->getDataRow();
        
        //check for rows
        if($data == null) {
            return "Security";
        }
        else {
            $tmpTailLink = "";
            $tmpThisLink = "<a href=\"". F::url("root/security/index.html?dk_id_parent=". $data["id"]) ."\">". $data["name"] ."</a>";
            
            //continue getting seed
            if($data["dk_id_parent"] == "0") {
                //no parent security
                $tmpTailLink = "<a href=\"". F::url("root/security/index.html") ."\">Security</a> / ";
                $tmpTailLink .= $tmpThisLink;
            }
            else {
                //get this parent task first
                $tmpTailLink .= self::birdSeed($data["dk_id_parent"], $counter + 1);
                $tmpTailLink .= " / ". $tmpThisLink;
            }
            
            return $tmpTailLink;
        }
    }
}
