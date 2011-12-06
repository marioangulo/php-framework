<?php

class Route {
    /**
     * handles the on load event
     */
    public static function eventOnLoad() {
        //get the base path
        $currentBasePath = (count(Router::$paths) == 0 ? Router::$fileName : Router::$paths[0]);
        
        //if there is no path (home page) finalize on /index.html
        if($currentBasePath == "") { 
            Router::finalize("/", "index");
        }
        
        //make sure /index.html requests get routed to the root domain
        if($currentBasePath == "index.html") { 
            Router::redirect("/");
        }
        
        //call the router fallback
        Router::callFallback();
    }
}
