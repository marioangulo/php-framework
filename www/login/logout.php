<?php

class Page {
    /**
     * handles the before finalize event
     */
    public static function eventBeforeFinalize() {
        //is this user logged in?
        if(F::$user->hasSession()) {
            //log user history
            F::$user->logHistory("User logged out.");
            
            //log user out
            F::$user->logout();
        }
        
        //redirect
        F::$response->redirectURL = F::url("index.html");
    }
}
