<?php

/*
 * This file is part of the Weblegs package.
 * (C) Weblegs, Inc. <software@weblegs.com>
 *
 * This program is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 */

class User {
    /**
     * will continue script execution if user has permission otherwise a
     * permission denied message is shown and script execution stops
     * @param int $securityID
     */
    public function continueOrDenyPermission($securityID) {
        //check if this user has permission
        if(!$this->hasPermission(F::$request->session("user_id"), $securityID)) {
            //get page template
            $denyDoc = new DOMTemplate_Ext();
            $denyDoc->loadFile(F::filePath("_theme/system/permission-denied.html"), F::$config->get("root-path"));
            
            //get the security label
            F::$db->sqlCommand = "SELECT name FROM user_security WHERE id = '#security_id#'";
            F::$db->sqlKey("#security_id#", $securityID);
            $tmpSecurityLabel = F::$db->getDataString();
            
            //set some binders
            $denyDoc->domBinders["mail-to-email"] = F::$config->get("admin-email");
            $denyDoc->domBinders["mail-to-href"] = "mailto:". F::$config->get("admin-email");
            $denyDoc->domBinders["message"] = "You do not have access to '". $tmpSecurityLabel ."'.";
            
            //log user history
            $this->logHistory("User was denied access to '". $tmpSecurityLabel ."'. security.id='". $securityID ."'.");
            
            //do data binding
            $denyDoc->bindResources();
            $denyDoc->finalBind();
            
            //close db
            F::$db->close();
            
            //finalize request
            F::$response->finalize($denyDoc->toString());
        }
        else if(!$this->hasValidIP(F::$request->session("user_id"))) {
            //get page template
            $denyDoc = new DOMTemplate_Ext();
            $denyDoc->loadFile(F::filePath("_theme/system/permission-denied.html"), F::$config->get("root-path"));
            
            //set some binders
            $denyDoc->domBinders["mail-to-email"] = F::$config->get("admin-email");
            $denyDoc->domBinders["mail-to-href"] = "mailto:". F::$config->get("admin-email");
            $denyDoc->domBinders["message"] = "Your IP address could not be validated.";
            
            //log user history
            $this->logHistory("User was denied access because their IP address could not be validated.");
            
            //do data binding
            $denyDoc->bindResources();
            $denyDoc->finalBind();
            
            //close db
            F::$db->close();
            
            //finalize request
            F::$response->finalize($denyDoc->toString());
        }
    }
    
    /**
     * will continue script execution if the user has a valid session
     * otherwise the user is redirected to the login screen
     */
    public function requireSession() {
        //check if they have a cookie
        if(F::$request->cookies("session_id") == null) {
            //close the db
            F::$db->close();
            
            //redirect to logout
            $returnURL = "";
            $thisPage = "";
            if(F::$request->serverVariables("SCRIPT_NAME") == null) {
                $thisPage = F::$request->serverVariables("PATH_INFO");
            }
            else {
                $thisPage = F::$request->serverVariables("SCRIPT_NAME");
            }
            $thisQuery = F::$request->serverVariables("QUERY_STRING");
            $returnURL = Codec::urlEncode($thisPage . ($thisQuery != "" ? "?". $thisQuery : ""));
            F::$response->redirect(F::url("login/index.html?return=". $returnURL));
            F::$response->finalize();
        }
        else {
            //get session data
            $userID = "";
            if(F::$request->cookies("user_id") != null) {
                $userID = F::$request->cookies("user_id");
            }
            $username = "";
            if(F::$request->cookies("username") != null) {
                $username = F::$request->cookies("username");
            }
            $timezone = "";
            if(F::$request->cookies("timezone") != null) {
                $timezone = F::$request->cookies("timezone");
            }
            $timezoneOffset = "";
            if(F::$request->cookies("timezone_offset") != null) {
                $timezoneOffset = F::$request->cookies("timezone_offset");
            }
            $cookieSessionID =  "";
            if(F::$request->cookies("session_id") != null) {
                $cookieSessionID = F::$request->cookies("session_id");
            }
            
            //get session id
            $dbSessionID = $this->getSessionID($userID);
            
            //compare database and cookie session
            if($dbSessionID == $cookieSessionID) {
                //make sure we still have a session
                F::$response->session("user_id", $userID);
                F::$response->session("username", $username);
                F::$response->session("timezone", $timezone);
                F::$response->session("timezone_offset", $timezoneOffset);
                F::$response->session("session_id", $cookieSessionID);
            }
            else {
                //close the db
                F::$db->close();
                
                //redirect to logout
                $returnURL = "";
                $thisPage = "";
                if(F::$request->serverVariables("SCRIPT_NAME") == null) {
                    $thisPage = F::$request->serverVariables("PATH_INFO");
                }
                else {
                    $thisPage = F::$request->serverVariables("SCRIPT_NAME");
                }
                $thisQuery = F::$request->serverVariables("QUERY_STRING");
                $returnURL = Codec::urlEncode($thisPage . ($thisQuery != "" ? "?". $thisQuery : ""));
                F::$response->redirect(F::url("login/index.html?return=". $returnURL));
                F::$response->finalize();
            }
        }
    }
    
    /**
     * refreshes the user's session
     */
    public function refreshSession() {
        //check if they have a cookie
        if(F::$request->cookies("session_id") == null) {
            //do nothing
        }
        else {
            //get session data
            $userID = "";
            if(F::$request->cookies("user_id") != null) {
                $userID = F::$request->cookies("user_id");
            }
            $username = "";
            if(F::$request->cookies("username") != null) {
                $username = F::$request->cookies("username");
            }
            $timezone = "";
            if(F::$request->cookies("timezone") != null) {
                $timezone = F::$request->cookies("timezone");
            }
            $timezoneOffset = "";
            if(F::$request->cookies("timezone_offset") != null) {
                $timezoneOffset = F::$request->cookies("timezone_offset");
            }
            $cookieSessionID =  "";
            if(F::$request->cookies("session_id") != null) {
                $cookieSessionID = F::$request->cookies("session_id");
            }
            
            //get session id
            $dbSessionID = $this->getSessionID($userID);
            
            //compare database and cookie session
            if($dbSessionID == $cookieSessionID) {
                //make sure we still have a session
                F::$response->session("user_id", $userID);
                F::$response->session("username", $username);
                F::$response->session("timezone", $timezone);
                F::$response->session("timezone_offset", $timezoneOffset);
                F::$response->session("session_id", $cookieSessionID);
            }
        }
    }
    
    /**
     * gets the session id for a user
     * @param string $userID
     * @return string|null The data
     */
    public function getSessionID($userID) {
        F::$db->sqlCommand = "
            SELECT session_id 
            FROM user 
            WHERE id = '#user_id#' 
        ";
        F::$db->sqlKey("#user_id#", $userID);
        $dtrUserSession = F::$db->getDataRow();
        if($dtrUserSession != null) {
            return $dtrUserSession["session_id"];
        }
        else{
            return null;
        }
    }
    
    /**
     * checks user's ip against ip access table
     * @return bool If the ip is valid
     */
    public function validateIP() {
        //get ip data
        F::$db->sqlCommand = "
            SELECT IF(ISNULL(ip_access.id), 'no', 'yes') AS is_valid_ip
            FROM ip_access 
            WHERE 
                ip_address = '#ip_address#' 
                AND is_active = 'yes'
            LIMIT 1
        ";
        F::$db->sqlKey("#ip_address#", F::$request->serverVariables("REMOTE_ADDR"));
        if(F::$db->getDataString() == "yes") {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * does a lookup on the remote client ip with consideration to permissions
     * @param int $userID
     */
    public function hasValidIP($userID) {
        if($this->isRoot($userID)) {
            return true;
        }
        else {
            //may skip ip check?
            if($this->hasPermission($userID, "1000")) {
                return true;
            }
            else {
                return $this->validateIP();
            }
        }
    }
    
    /**
     * adds an entry to the history log
     * @param string $details
     * @param int $userID
     */
    public function logHistory($details, $userID = null) {
        if($userID == null){
            $userID = (F::$request->session("user_id") == null ? "" : F::$request->session("user_id"));
        }
        
        $thisPage = "";
        if(F::$request->serverVariables("SCRIPT_NAME") == null) {
            $thisPage = F::$request->serverVariables("PATH_INFO");
        }
        else {
            $thisPage = F::$request->serverVariables("SCRIPT_NAME");
        }
        $thisQuery = F::$request->serverVariables("QUERY_STRING");
        $tmpURL = $thisPage . ($thisQuery != "" ? "?". $thisQuery : "");
        
        F::$db->sqlCommand = "
            INSERT INTO user_history 
            SET 
                fk_user_id = IF(LENGTH('#user_id#') > 0, '#user_id#', '0'), 
                details = '#details#', 
                timestamp_created = '#timestamp_created#', 
                ip_address = '#ip_address#', 
                url = '#url#'
        ";
        F::$db->sqlKey("#user_id#", $userID);
        F::$db->sqlKey("#ip_address#", F::$request->serverVariables("REMOTE_ADDR"));
        F::$db->sqlKey("#details#", $details);
        F::$db->sqlKey("#timestamp_created#", F::$dateTime->now()->toString());
        F::$db->sqlKey("#url#", $tmpURL);
        F::$db->executeNonQuery();
    }
    
    /**
     * adds an entry to the action log
     * @param string $pivotTable
     * @param int $pivotID
     * @param string $action
     * @param int $userID
     */
    public function logAction($pivotTable, $pivotID, $action, $userID = null) {
        if($userID == null){
            $userID = (F::$request->session("user_id") == null ? "" : F::$request->session("user_id"));
        }
        F::$db->sqlCommand = "
            INSERT INTO user_action_log 
            SET 
                fk_user_id = IF(LENGTH('#user_id#') > 0, '#user_id#', '0'), 
                fk_pivot_table = '#fk_pivot_table#', 
                fk_pivot_id = '#fk_pivot_id#', 
                action = '#action#', 
                timestamp_created = '#timestamp_created#' 
        ";
        F::$db->sqlKey("#user_id#", $userID);
        F::$db->sqlKey("#fk_pivot_table#", $pivotTable);
        F::$db->sqlKey("#fk_pivot_id#", $pivotID);
        F::$db->sqlKey("#action#", $action);
        F::$db->sqlKey("#timestamp_created#", F::$dateTime->now()->toString());
        F::$db->executeNonQuery();
    }
    
    /**
     * flag if the user is root
     * @param int $userID
     * @return bool If the user is root
     */
    public function isRoot($userID) {
        //is this the root user
        F::$db->sqlCommand = "
            SELECT
                IF(ISNULL(user_membership.id), 'no', 'yes') AS is_root
            FROM
                user_membership
                LEFT JOIN user_group ON user_membership.fk_group_id = user_group.id
                LEFT JOIN user_permission ON user_permission.fk_group_id = user_group.id
            WHERE
                user_membership.fk_user_id = '#fk_user_id#'
                AND user_membership.fk_group_id = '1'
                AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00'
            LIMIT 1
        ";
        F::$db->sqlKey("#fk_user_id#", $userID);
        if(F::$db->getDataString() == "yes") {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * flag if the user is root
     * @param int $userID
     * @param int $securityID
     * @return bool If the user has permission
     */
    public function hasPermission($userID, $securityID) {
        if($this->isRoot($userID)) {
            return true;
        }
        
        //lookup group permissions
        F::$db->sqlCommand = "
            SELECT
                user_group.id AS group_id,
                user_group.name,
                user_permission.permit 
            FROM 
                user_membership 
                LEFT JOIN user_group ON user_membership.fk_group_id = user_group.id 
                LEFT JOIN user_permission ON user_permission.fk_group_id = user_group.id 
            WHERE 
                user_membership.fk_user_id = '#fk_user_id#' 
                AND user_permission.fk_security_id = '#fk_security_id#'
                AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00'
        ";
        F::$db->sqlKey("#fk_user_id#", $userID);
        F::$db->sqlKey("#fk_security_id#", $securityID);
        $tblGroupPermission = F::$db->getDataTable();
        
        //check group permissions
        $groupHasPermission = false;
        for($i = 0 ; $i < count($tblGroupPermission); $i++) {
            //check for group permission
            if($tblGroupPermission[$i]["permit"] == "yes") {
                $groupHasPermission = true;
            }
        }
        
        //lookup user permissions
        F::$db->sqlCommand = "
            SELECT
                user.id AS user_id,
                user_permission.permit
            FROM user
                LEFT JOIN user_permission ON user_permission.fk_user_id = user.id
            WHERE
                user.id = '#user_id#'
                AND user_permission.fk_security_id = '#fk_security_id#'
        ";
        F::$db->sqlKey("#user_id#", $userID);
        F::$db->sqlKey("#fk_security_id#", $securityID);
        $tblUserPermission = F::$db->getDataTable();
        
        //did we find a granular security setting
        if(count($tblUserPermission) > 0) {
            $userHasPermission = false;
            for($i = 0 ; $i < count($tblUserPermission); $i++) {
                if($tblUserPermission[$i]["permit"] == "yes") {
                    $userHasPermission = true;
                }
            }
            return $userHasPermission;
        }
        else {
            //permission not found for user return group
            return $groupHasPermission;
        }
    }
    
    /**
     * flag if the user is an active group member
     * @param int $userID
     * @param int $groupID
     * @return bool If the user is a member
     */
    public function isMemberOfGroup($userID, $groupID) {
        //get user groups
        F::$db->sqlCommand = "
            SELECT
                IF(ISNULL(user_membership.id), 'no', 'yes') AS is_member_of_group
            FROM 
                user_membership 
                LEFT JOIN user_group ON user_membership.fk_group_id = user_group.id 
                LEFT JOIN user_permission ON user_permission.fk_group_id = user_group.id 
            WHERE 
                user_membership.fk_user_id = '#fk_user_id#' 
                AND user_membership.fk_group_id = '#fk_group_id#'
                AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00' 
            LIMIT 1
        ";
        F::$db->sqlKey("#fk_user_id#", $userID);
        F::$db->sqlKey("#fk_group_id#", $groupID);
        if(F::$db->getDataString() == "yes") {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * gets the user's default group id
     * @param int $userID
     * @return string The group id
     */
    public function getDefaultGroupID($userID) {
        F::$db->sqlCommand = "
            SELECT fk_group_id
            FROM user_membership 
            WHERE 
                fk_user_id = '#fk_user_id#' 
                AND is_default = 'yes'
                AND timestamp_cancelled = '0000-00-00 00:00:00'
            LIMIT 1 
        ";
        F::$db->sqlKey("#fk_user_id#", $userID);
        $dtrUserSession = F::$db->getDataRow();
        if($dtrUserSession != null) {
            return $dtrUserSession["fk_group_id"];
        }
        else{
            return null;
        }
    }
    
    /**
     * gets the user's group ids seperated by commas
     * @param int $userID
     * @return string The group ids
     */
    public function getGroupIDs($userID) {
        F::$db->sqlCommand = "
            SELECT user_group.id AS group_id 
            FROM 
                user_membership 
                LEFT JOIN user_group ON user_membership.fk_group_id = user_group.id 
            WHERE 
                user_membership.fk_user_id = '#fk_user_id#' 
                AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00'
        ";
        F::$db->sqlKey("#fk_user_id#", $userID);
        $tmpDataIDs = F::$db->getDataString(",");
        if($tmpDataIDs == "") {
            $tmpDataIDs = "0";
        }
        return $tmpDataIDs;
    }
    
    /**
     * gets the group's user ids seperated by commas
     * @param int $groupID
     * @return string The user ids
     */
    public function getGroupUserIDs($groupID) {
        F::$db->sqlCommand = "
            SELECT user_membership.fk_user_id
            FROM user_membership
            WHERE 
                user_membership.fk_group_id = '#fk_group_id#' 
                AND user_membership.timestamp_cancelled = '0000-00-00 00:00:00'
        ";
        F::$db->sqlKey("#fk_group_id#", $groupID);
        $tmpDataIDs = F::$db->getDataString(",");
        if($tmpDataIDs == "") {
            $tmpDataIDs = "0";
        }
        return $tmpDataIDs;
    }
    
    /**
     * logs the user in
     * @param int $id
     * @param string $username
     * @param string $timezone
     * @param int $cookieMinutes
     */
    public function login($id, $username, $timezone, $cookieMinutes) {
        //session_id
            //get SessionID
            $sessionID = $this->createSessionID();
            
            //update session_id in db
            F::$db->sqlCommand = "
                UPDATE user
                SET session_id = '#session_id#'
                WHERE id = '#id#'
            ";
            F::$db->sqlKey("#id#", $id);
            F::$db->sqlKey("#session_id#", $sessionID);
            F::$db->executeNonQuery();
        //end session_id
        
        //give session
        F::$response->session("user_id", $id);
        F::$response->session("username", $username);
        F::$response->session("timezone", $timezone);
        F::$response->session("timezone_offset", F::$system->convertTZOut(F::$dateTime->now())->toString("tzo"));
        F::$response->session("session_id", $sessionID);
        
        //give cookie
        F::$response->cookies("user_id", $id, $cookieMinutes, "/", F::$config->get("cookie-domain"));
        F::$response->cookies("username", $username, $cookieMinutes, "/", F::$config->get("cookie-domain"));
        F::$response->cookies("timezone", $timezone, $cookieMinutes, "/", F::$config->get("cookie-domain"));
        F::$response->cookies("timezone_offset", F::$system->convertTZOut(F::$dateTime->now())->toString("tzo"), $cookieMinutes, "/", F::$config->get("cookie-domain"));
        F::$response->cookies("session_id", $sessionID, $cookieMinutes, "/", F::$config->get("cookie-domain"));
    }
    
    /**
     * logs the user out
     */
    public function logout() {
        F::$response->clearSession();
        F::$response->clearCookies(F::$config->get("root-url"), F::$config->get("cookie-domain"));
    }
    
    /**
     * creates a new session id
     * @param string The new session
     */
    public function createSessionID() {
        return Codec::md5Encrypt(rand(9999, 99999999));
    }
    
    /**
     * flag if the use is logged in
     * @return bool If the user is logged in
     */
    public function isLoggedIn() {
        //check if they have a cookie
        if(F::$request->cookies("session_id") == null) {
            return false;
        }
        else {
            //get session data
            $userID = "";
            if(F::$request->cookies("user_id") != null) {
                $userID = F::$request->cookies("user_id");
            }
            $username = "";
            if(F::$request->cookies("username") != null) {
                $username = F::$request->cookies("username");
            }
            $timezone = "";
            if(F::$request->cookies("timezone") != null) {
                $timezone = F::$request->cookies("timezone");
            }
            $timezoneOffset = "";
            if(F::$request->cookies("timezone") != null) {
                $timezoneOffset = F::$request->cookies("timezone_offset");
            }
            $cookieSessionID =  "";
            if(F::$request->cookies("session_id") != null) {
                $cookieSessionID = F::$request->cookies("session_id");
            }
            
            //get session id
            $dbSessionID = $this->getSessionID($userID);
            
            //compare database and cookie session
            if($dbSessionID == $cookieSessionID) {
                return true;
            }
            else {
                return false;
            }
        }
    }
    
    /**
     * flag if the user has a session
     * @return bool If the user has a session
     */
    public function hasSession() {
        //check if they have a cookie
        if(F::$request->cookies("session_id") == null) {
            return false;
        }
        else {
            return true;
        }
    }
    
    /**
     * flag if the email is available
     * @param string $email
     * @param string $userID
     * @return bool If the email is available
     */
    public function isEmailAvailable($email, $userID) {
        F::$db->sqlCommand = "
            SELECT username
            FROM user
            WHERE
                email = '#email#'
                AND NOT user.id = IF('#user_id#' = '', '0', '#user_id#')
        ";    
        F::$db->sqlKey("#email#", $email);
        F::$db->sqlKey("#user_id#", $userID);
        if(F::$db->getDataString() == "") {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * flag if the username is available
     * @param string $username
     * @param string $userID
     * @return bool If the username is available
     */
    public function isUsernameAvailable($username, $userID) {
        F::$db->sqlCommand = "
            SELECT username
            FROM user
            WHERE
                username = '#username#'
                AND NOT user.id = IF('#user_id#' = '', '0', '#user_id#')
        ";
        F::$db->sqlKey("#username#", $username);
        F::$db->sqlKey("#user_id#", $userID);
        if(F::$db->getDataString() == "") {
            return true;
        }
        else {
            return false;
        }
    }
}
