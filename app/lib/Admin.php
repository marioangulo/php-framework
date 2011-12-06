<?php

class Admin {
    /**
     * gets the user id by the admin id
     * @param int $adminID
     * @return string The user id
     */
    public function getUserID($adminID) {
        F::$db->sqlCommand = "
            SELECT fk_user_id
            FROM admin
            WHERE id = '#id#'
            LIMIT 1
        ";
        F::$db->sqlKey("#id#", $adminID);
        return F::$db->getDataString();
    }
    
    /**
     * gets the admin id by the user id
     * @param int $userID
     * @return string The admin id
     */
    public function getAdminID($userID) {
        F::$db->sqlCommand = "
            SELECT id
            FROM admin
            WHERE fk_user_id = '#fk_user_id#'
            LIMIT 1
        ";
        F::$db->sqlKey("#fk_user_id#", $userID);
        return F::$db->getDataString();
    }
}
