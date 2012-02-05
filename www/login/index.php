<?php

class Page {
    /**
     * handles the final event
     */
    public static function eventFinal() {
        F::$response->addHeader("X-LOGIN-STATUS", "logged_out");
    }
    
    /**
     * handles the before actions event
     */
    public static function eventBeforeActions() {
        //figure out if we should redirect to admin or account.
        if(F::$user->isLoggedIn()) {
            F::$response->redirectURL = self::defaultRedirect();
        }
    }
    
    /**
     * figures out the best place to redirect the user
     */
    public static function defaultRedirect() {
        //get default group
        $tmpDefaultGroup = F::$user->getDefaultGroupID(F::$request->session("user_id"));
        
        //admin or root?
        if($tmpDefaultGroup == "1" || $tmpDefaultGroup == "2") {
            return F::url("admin/index.html");
        }
        //account?
        else if($tmpDefaultGroup == "3") {
            return F::url("account/index.html");
        }
        //make more requests to figure out where we should go
        else {
            //root?
            if(F::$user->isMemberOfGroup(F::$request->session("user_id"), "1")) {
                return F::url("admin/index.html");
            }
            //admin?
            else if(F::$user->isMemberOfGroup(F::$request->session("user_id"), "2")) {
                return F::url("admin/index.html");
            }
            //account?
            else if(F::$user->isMemberOfGroup(F::$request->session("user_id"), "3")) {
                return F::url("account/index.html");
            }
            //send to the home page
            else {
                return F::url("index.html");
            }
        }
    }
    
    /**
     * handles the login action
     */
    public static function actionLogin() {
        //validate
        if(F::$request->input("username") == "") {
            F::$errors->add("username", "required");
        }
        if(F::$request->input("password") == "") {
            F::$errors->add("password", "required");
        }
        
        //take action
        if(F::$errors->count() == 0) {
            //lookup login information
            F::$db->loadCommand("lookup-login", F::$engineArgs);
            $dtrData = F::$db->getDataRow();
            
            if(F::$db->getFoundRows() == 1) {
                //validate remote IP
                if(!F::$user->hasValidIP($dtrData["id"])) {
                    //add alert
                    F::$errors->add("Login failed: your IP address could not be validated.");
                }
                //their IP is good, lets give them a session
                else {
                    //log this user in
                    F::$user->login($dtrData["id"], $dtrData["username"], $dtrData["timezone"], 1440);
                    
                    //log user history
                    F::$user->logHistory("User logged in.", $dtrData["id"]);
                    
                    //are we going somewhere specific?
                    if(F::$request->input("return") != "") {
                        F::$responseJSON["redirect"] = F::$request->input("return");
                    }
                    else {
                        F::$responseJSON["redirect"] = self::defaultRedirect();
                    }
                }
            }
            else {
                //log anonymous user history
                F::$user->logHistory("Attempted user login as: '". F::$request->input("username") ."'.");
                
                //set notice
                F::$errors->add("Login failed: username and password combination not found or your account is inactive.");
            }
        }
    }
}
