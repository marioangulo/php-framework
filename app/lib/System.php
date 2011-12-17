<?php

class System {
    /**
     * converts the time from the user's tz into the server's tz
     * @param DateTimeDriver $dateTime
     * @param string $format
     * @return string Transformed input
     */
    public function convertTZIn($dateTime, $format = null) {
        //create a temporary date time object
        $tmpDateTime = null;
        $newDateTime = null;
        
        //make sure this is an object
        if(is_object($dateTime)){
            //was this a datetime driver?
            if(get_class($dateTime) == "DateTimeDriver" || get_class($dateTime) == "DateTimeDriver_Ext"){
                $tmpDateTime = $dateTime->toString();
            }
            else {
                $tmpDateTime = $dateTime;
            }
        }
        else{
            $tmpDateTime = $dateTime;
        }
        
        //does the user have a timezone preference?
        if(F::$request->session("timezone") != ""){
            //set the user's timezone
            $tmpTimeZone = new DateTimeZone(F::$request->session("timezone"));
            $newDateTime = new DateTime($tmpDateTime, $tmpTimeZone);
            
            //now convert to UTC
            $tmpTimeZone = new DateTimeZone("UTC");
            $newDateTime->setTimezone($tmpTimeZone);
        }
        else {
            //just recreate a datetime object
            $newDateTime = new DateTime($tmpDateTime);
        }
        
        //create a new DateTimeDriver
        $outDateTime = new DateTimeDriver_Ext();
        $outDateTime->dateTime = $newDateTime;
        $outDateTime->refreshProperties();
        
        //return the new DateTimeDriver
        if(is_null($format)) {
            return $outDateTime;
        }
        //otherwise return the formatted string
        else if($format == "") {
            return $outDateTime->toString();
        }
        else {
            return $outDateTime->toString($format);
        }
    }
    
    /**
     * converts the time from the server's tz into the user's tz
     * @param DateTimeDriver $dateTime
     * @param string $format
     * @return string Transformed input
     */
    public function convertTZOut($dateTime, $format = null) {
        //create a temporary date time object
        $tmpDateTime = null;
        $newDateTime = null;
        
        //make sure this is an object
        if(is_object($dateTime)){
            //was this a datetime driver?
            if(get_class($dateTime) == "DateTimeDriver" || get_class($dateTime) == "DateTimeDriver_Ext"){
                $tmpDateTime = $dateTime->toString();
            }
            else {
                $tmpDateTime = $dateTime;
            }
        }
        else{
            $tmpDateTime = $dateTime;
        }
        
        //does the user have a timezone preference?
        if(F::$request->session("timezone") != ""){
            //set UTC as the timezone
            $tmpTimeZone = new DateTimeZone("UTC");
            $newDateTime = new DateTime($tmpDateTime, $tmpTimeZone);
            
            //set convert to the user's timezone
            $tmpTimeZone = new DateTimeZone(F::$request->session("timezone"));
            $newDateTime->setTimezone($tmpTimeZone);
        }
        else {
            //just recreate a datetime object
            $newDateTime = new DateTime($tmpDateTime);
        }
        
        //create a new DateTimeDriver
        $outDateTime = new DateTimeDriver_Ext();
        $outDateTime->dateTime = $newDateTime;
        $outDateTime->refreshProperties();
        
        //return the new DateTimeDriver
        if(is_null($format)) {
            return $outDateTime;
        }
        //otherwise return the formatted string
        else if($format == "") {
            return $outDateTime->toString();
        }
        else {
            return $outDateTime->toString($format);
        }
    }
    
    /**
     * generates an html list menu field of timezones grouped by region 
     * @param bool $anyOption
     * @param bool $noOption
     * @return string The generated html
     */
    public function timezoneDD($anyOption = false, $noOption = false) {
        //create a list menu
        $dropDown = new WebFormMenu("tmp", 1, 0);
        
        //add default option(s)
        if($anyOption) {
            $dropDown->addOption("--- any ---", "");
        }
        if($noOption) {
            $dropDown->addOption("--- none ---", "");
        }
        else {
            $dropDown->addOption("--- Select A Timezone ---", "");
        }
        
        //build options
            //tmp array container
            $tmpTimeZones = array();
            
            //option groups
            $tzGroups = array(
                "UTC" => 1024,
                "America" => 2,
                "Africa" => 1,
                "Antarctica" => 4,
                "Arctic" => 8,
                "Asia" => 16,
                "Atlantic" => 32,
                "Australia" => 64,
                "Europe" => 128,
                "Indian" => 256,
                "Pacific" => 512
            );
            
            //add the options
            foreach($tzGroups as $group => $code) {
                $dropDown->addOptionGroup($group);
                $tmpTimeZones = DateTimeZone::listIdentifiers($code);
                for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
                    $dropDown->addOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
                }
            }
        //end build options
        
        //return the option tags
        return $dropDown->getOptionTags();
    }
    
    /**
     * the cli error handler
     */
    public static function cliErrorHandler() {
        $args = func_get_args();
        print(print_r($args, true));
    }
    
    /**
     * the cli exception handler
     */
    public static function cliExceptionHandler() {
        $args = func_get_args();
        print(print_r($args, true));
    }
    
    /**
     * the cli shutdown handler
     */
    public static function cliShutdownHandler() {
    }
    
    /**
     * the web error handler
     */
    public static function webErrorHandler() {
        try {
            //set 500 server error status code
            F::$response->addHeader("HTTP/1.1 500","");
            
            //get page template
            F::$doc->loadFile(F::filePath("_theme/system/server-error.html"), F::$config->get("root-path"));
            
            //set some binders
            F::$doc->domBinders["mail-to-email"] = F::$config->get("admin-email");
            F::$doc->domBinders["mail-to-href"] = "mailto:". F::$config->get("admin-email");
            
            //collect log data
            $errorLogData = array();
            
            //build data
                $argCount = func_num_args();
                $args = func_get_args();
                
                //an exception was called
                if($argCount == 1){
                    $exception = $args[0];
                    $errorLogData["error-message"] = $exception->getMessage();
                }
                //an error was called
                else if($argCount == 5){
                    $errorLogData["error-message"] = "Error number: ". $args[0] ."-". $args[1] ." in ". $args[2] ." on line number ". $args[3] .".";
                }
                
                //continue building data
                $errorLogData["application"] = F::$request->serverVariables("SERVER_NAME");
                $errorLogData["source"] = F::$request->serverVariables("SCRIPT_FILENAME");
                $errorLogData["url"] = F::$request->serverVariables("REQUEST_URI");
                $errorLogData["timestamp"] = F::$dateTime->now()->toString();
                $errorLogData["stack-trace"] = print_r(debug_backtrace(), true);
                $errorLogData["http-get"] = print_r($_GET, true);
                $errorLogData["http-post"] = print_r($_POST, true);
                $errorLogData["session"] = print_r($_SESSION, true);
                $errorLogData["cookies"] = print_r($_COOKIE, true);
                $errorLogData["environment"] = print_r($_SERVER, true);
                $errorLogData["debug-log"] = print_r(F::$debugLog, true);
                F::$customHashes["log"] = $errorLogData;
            //end build data
            
            //should we show the stack trace on the page?
            if(F::$config->get("show-stack-trace") == false) {
                F::$doc->getNodesByDataset("label", "stack-trace")->remove();
            }
            
            //should we log the error?
            if(F::$config->get("log-errors") == true) {
                F::$db->open();
                F::$db->sqlCommand = "
                    INSERT INTO error
                    SET
                        application = '#application#',
                        source = '#source#',
                        error_message = '#error-message#',
                        debug_log = '#debug-log#',
                        stack_trace = '#stack-trace#',
                        request_url = '#url#',
                        request_get = '#http-get#',
                        request_post = '#http-post#',
                        request_cookie = '#cookies#',
                        request_session = '#session#',
                        environment_variables = '#environment#',
                        timestamp_created = '#timestamp#'
                ";
                F::$db->bindKeys($errorLogData);
                F::$db->executeNonQuery();
                
                //replace error id on page
                F::$doc->domBinders["error-id"] = F::$db->getLastInsertID();
            }
            else {
                F::$doc->getNodesByDataset("label", "error-identifier")->remove();
            }
            
            //do data binding
            F::$doc->bindResources();
            F::$doc->finalBind();
            
            //should we email the error?
            if(F::$config->get("email-errors") == true) {
                //build up message
                $message = new DOMTemplate_Ext();
                $message->loadFile(F::filePath("_theme/system/server-error.email.html"));
                $message->bindResources($message);
                $message->dataBinder(F::$engineArgs);
                
                //get email ready to send
                F::$emailClient->addTo(F::$config->get("admin-email"));
                F::$emailClient->subject = "Server Error @ ". F::$request->serverVariables("SERVER_NAME");
                F::$emailClient->message = $message->toString();
                F::$emailClient->isHTML = true;
                
                //try to send the email
                try{
                    //send email
                    F::$emailClient->send();
                    //reset
                    F::$emailClient->reset();
                }
                catch(Exception $e) {
                    //it didn't get sent
                }
            }
            
            //never hurts to try closing the database, 
            F::$db->close();
            
            //finalize the request
            F::$response->finalize(F::$doc->toString());
        }
        catch(Exception $e) {
            //never hurts to try closing the database, 
            F::$db->close();
            
            //show something
            print("<h1>500 Fatal Error (Uncaught)</h1>");
            print("<h3>". get_class($e)." thrown within the exception handler.</h3> Message: ".$e->getMessage()." on line ".$e->getLine());
            print("<xmp>". print_r(debug_backtrace(), true) ."</xmp>");
            
            //try to email the debug log
            try { F::emailDebugLog(true); }
            catch(Exception $e) { /*damn that sucks*/ }
        }
    }
    
    /**
     * the web exception handler
     */
    public static function webExceptionHandler() {
       
    }
    
    /**
     * the web shutdown handler
     */
    public static function webShutdownHandler() {
        $err = error_get_last(); 
        if($err == null) {
            //do nothing
        }
        else {
            print_r($err, true); 
        }
    }
    
    /**
     * the default router fallback function
     */
    public static function defaultRouteFallback(){
        //directory requests redirect to the default index file
        if(Router::$fileName == ""){
            Router::redirect(Router::$redirectPath . Router::$indexFile .".". F::$config->get("router-enforced-file-extension"));
        }
        //enforce file entension
        else if(Router::$fileExtension != F::$config->get("router-enforced-file-extension")){
            F::webServerStatus(404);
        }
        //does an html file exists? if so, then start the engine
        else if(file_exists(Router::$documentRoot . Router::$redirectPath . Router::$redirectResource . ".html")){
            Router::finalize(Router::$redirectPath, Router::$redirectResource, "html");
        }
        //does a php file exists? if so, then start the engine
        else if(file_exists(Router::$documentRoot . Router::$redirectPath . Router::$redirectResource . ".php")){
            Router::finalize(Router::$redirectPath, Router::$redirectResource, "php");
        }
        //throw error - file not found
        else {
            //if all else fails
            F::webServerStatus(404);
        }
    }
}