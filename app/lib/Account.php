<?php

class Account {
    /**
     * gets the user id by the account id
     * @param int $accountID
     * @return string The user id
     */
    public function getUserID($accountID) {
        F::$db->sqlCommand = "
            SELECT fk_user_id
            FROM account
            WHERE id = '#id#'
            LIMIT 1
        ";
        F::$db->sqlKey("#id#", $accountID);
        return F::$db->getDataString();
    }
    
    /**
     * gets the account id by the user id
     * @param int $userID
     * @return string The account id
     */
    public function getAccountID($userID) {
        F::$db->sqlCommand = "
            SELECT id
            FROM account
            WHERE fk_user_id = '#fk_user_id#'
            LIMIT 1
        ";
        F::$db->sqlKey("#fk_user_id#", $userID);
        return F::$db->getDataString();
    }
}
