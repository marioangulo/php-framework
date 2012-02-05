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

class F {
    //main
    public static $config;
    public static $adminEmail;
    public static $rootPath;
    public static $hostName;
    public static $rootURL;
    public static $cookieDomain;
    public static $baseHREF;
    
    //footprint objects
    public static $user;
    public static $admin;
    public static $account;
    public static $system;
    
    //footprint properties
    public static $engineNamespace;
    public static $engineArgs;
    public static $formCache;
    public static $requestedSections;
    public static $responseSections;
    public static $responseJSON;
    public static $metaData;
    public static $forceUniqueAutoIncludes;
    public static $eventReadyClasses;
    public static $customHashes;
    public static $customRows;
    public static $cacheExpirationDate;
    public static $cacheGetData;
    
    //weblegs properties
    public static $dateTime;
    public static $db;
    public static $dataPager;
    public static $timer;
    public static $debugTimer;
    public static $debugLog;
    public static $emailClient;
    public static $request;
    public static $response;
    public static $doc;
    
    //alerts and messages
    public static $warnings;
    public static $errors;
    public static $alerts;
    public static $info;
    
    //debugging
    public static $showStackTrace;
    public static $logErrors;
    public static $emailErrors;
    public static $emailDebugLog;
    
    /**
     * returns a file path relative to the root path
     * @param string $input
     * @return string Transformed input
     */
    public static function filePath($input) {
        return self::$config->get("root-path") . $input;
    }
    
    /**
     * returns a url path relative to the root url
     * @param string $input
     * @return string Transformed input
     */
    public static function url($input) {
        return self::$config->get("root-url") . $input;
    }
    
    /**
     * returns a full url path relative to the root url
     * @param string $input
     * @return string Transformed input
     */
    public static function fullURI($input) {
        return self::$config->get("host-name") . self::$config->get("root-url") . $input;
    }
    
    /**
     * fires matching events in the event ready classes
     * @param string $name of the event
     */
    public static function fireEvents($name) {
        for($i = 0 ; $i < count(self::$eventReadyClasses) ; $i++) {
            self::sysLog("<!--func lookup (". self::$eventReadyClasses[$i] ."::". $name .")-->");
            
            if(method_exists(self::$eventReadyClasses[$i], $name)) {
                self::sysLog("<Fire|". self::$eventReadyClasses[$i] ."::". $name .">");
                call_user_func(self::$eventReadyClasses[$i] ."::". $name);
                self::sysLog("</Fire|". self::$eventReadyClasses[$i] ."::". $name .">");
            }
        }
    }
    
    /**
     * sets up our properties (pseudo constructor)
     */
    public static function setup() {
        global $argv;
        
        //system parties
        self::$user = new User();
        self::$admin = new Admin();
        self::$account = new Account();
        
        //global objects
        self::$doc = new DOMTemplate_Ext(); //extended
        self::$db = new MySQLDriver_Ext(); //extended
        self::$dateTime = new DateTimeDriver_Ext(); //extended
        self::$dataPager = new DataPager();
        self::$timer = new Timer();
        self::$debugTimer = new Timer();
        self::$emailClient = new SMTPClient_Ext(); //extended
        self::$debugLog = new Alert();
        
        //tasks (cli)
        if(self::$config->get("mode") == "cli") {
            //command line arguments?
            self::$engineArgs = $argv;
        }
        //web requests
        else if(self::$config->get("mode") == "www") {
            self::$response = new WebResponse_Ext(); //extended
            self::$request = new WebRequest();
            self::$engineArgs = array_merge(self::$request->queryStringArray, self::$request->formArray);
            self::$requestedSections = explode(",", self::$request->input("data-section"));
            self::$cacheExpirationDate = null;
            self::$cacheGetData = false;
            self::$forceUniqueAutoIncludes = false;
            self::$formCache = array();
            self::$responseSections = array();
            self::$responseJSON = array();
            self::$metaData = array();
        }
        
        //alerts and messages
        self::$warnings = new Alert();
        self::$errors = new Alert_Ext(); //extended
        self::$alerts = new Alert();
        self::$info = new Alert();
        
        //setup mysql
        self::$db->host = self::$config->get("mysql-host");
        self::$db->username = self::$config->get("mysql-username");
        self::$db->password = self::$config->get("mysql-password");
        self::$db->schema = self::$config->get("mysql-schema");
        
        //setup email
        self::$emailClient->setFrom(self::$config->get("email-from-address"), self::$config->get("email-from-name"));
        self::$emailClient->host = self::$config->get("email-host");
        self::$emailClient->username = self::$config->get("email-username");
        self::$emailClient->password = self::$config->get("email-password");
        if(self::$config->get("email-port") != null) {
            self::$emailClient->port = self::$config->get("email-port");
        }
        if(self::$config->get("email-protocol") != null) {
            self::$emailClient->protocol = self::$config->get("email-protocol");
        }
        
        //classes to look for events in
        self::$eventReadyClasses = array("Page", "Helpers", "Task");
    }
    
    /**
     * initializes execution for tasks or web requests
     */
    public static function init() {
        if(self::$config->get("mode") == "cli") {
            self::initTask();
        }
        else {
            self::initWebRequest();
        }
    }
    
    /**
     * initializes execution for tasks
     */
    public static function initTask() {
        //setup the object
        self::setup();
        
        //setup error handlers
        set_error_handler(array("System", "cliErrorHandler"));
        set_exception_handler(array("System", "cliExceptionHandler"));
        register_shutdown_function(array("System", "cliShutdownHandler"));
        
        //start debug timer and log
        self::$debugTimer->start();
        self::sysLog("<debug init=\"cli\">");
        
        //setup some sql shortcuts
        self::$db->keyBinders["_now_"] = self::$dateTime->now()->toSQLString();
        
        //parse namespace
        $basePath = pathinfo(self::$config->get("cli-request"));
        self::sysLog("<!--base path (". print_r($basePath, true) .")-->");
        
        //set namespace
        $namespace = $basePath["dirname"] ."/". $basePath["filename"]; //trimming off extension
        self::$engineNamespace = $namespace;
        self::sysLog("<!--set cli namespace (". self::$engineNamespace .")-->");
        
        //open db
        self::$db->open();
        
        //////////////////////////////////////////
        self::fireEvents("eventOnLoad");
        /////////////////////////////////////////
        
        //load html?
        if(file_exists(self::$engineNamespace .".html")) {
            //load template
            self::$doc->loadFile(self::$engineNamespace .".html", self::$config->get("project-root"));
            self::sysLog("<!--loaded page template (". self::$engineNamespace .".html)-->");
        }
        
        //////////////////////////////////////////
        self::fireEvents("eventBeforeFinalize");
        /////////////////////////////////////////
        
        //run final data binder
        self::sysLog("<final-data-binder>");
        self::$doc->finalBind(F::$engineArgs);
        self::sysLog("</final-data-binder>");
        
        //////////////////////////////////////////
        self::fireEvents("eventFinal");
        /////////////////////////////////////////
        
        //always try and close the db, can't hurt right :)
        self::$db->close();
        
        //end debug log
        self::sysLog("</debug>");
        
        //email debug log
        if(self::$emailDebugLog) { self::emailDebugLog(); }
    }
    
    /**
     * initializes execution for web requests
     */
    public static function initWebRequest() {
        //setup the object
        self::setup();
        
        //misc/tmp variables
        $customHashList = array();
        $phpScripts = array();
        
        //enable sessions
        ini_set('session.gc_probability', 1);
        $setSecureCookies = ($_SERVER['SERVER_PORT'] == "443");
        if(isset($_SERVER['HTTP_X_FORWARDED_PORT'])) { $setSecureCookies = ($_SERVER['HTTP_X_FORWARDED_PORT'] == "443"); }
        session_set_cookie_params(60*60*24, "/", self::$config->get("cookie-domain"), $setSecureCookies);
        session_start();
        
        //setup error handlers
        set_error_handler(array("System", "webErrorHandler"));
        set_exception_handler(array("System", "webErrorHandler")); //we use webErrorHandler for both errors and exceptions
        register_shutdown_function(array("System", "webShutdownHandler"));
        
        //start debug timer and log
        self::$debugTimer->start();
        self::sysLog("<debug init=\"www\">");
        self::sysLog("<!--request uri (". $_SERVER["REQUEST_URI"] .")-->");
        
        //setup some sql shortcuts
        self::$db->keyBinders["_now_"] = self::$dateTime->now()->toSQLString();
        self::$db->keyBinders["_output-tz-offset_"] = "+00:00";
        if(self::$request->session("timezone_offset") != "") {
            self::$db->keyBinders["_user-tz-diff_"] = self::$request->session("timezone_offset");
        }
        
        //parse namespace
        $parsedURL = parse_url($_SERVER["SCRIPT_URI"]);
        $basePath = pathinfo($parsedURL["path"]);
        self::sysLog("<!--parsed url (". print_r($parsedURL, true) .")-->");
        self::sysLog("<!--base path (". print_r($basePath, true) .")-->");
        
        //set namespace
        $namespace = ($basePath["dirname"] == "/" ? "" : $basePath["dirname"] ."/") . $basePath["filename"]; //trimming off extension
        if(substr($namespace, 0, 1) == "/") { $namespace = substr($namespace, 1); } //trim leading '/'
        self::$engineNamespace = $namespace;
        self::sysLog("<!--set page namespace (". self::$engineNamespace .")-->");
        
        //autoload peer level helpers
        if(file_exists(substr(self::$config->get("root-path"), 0, -1) . $basePath["dirname"] ."/helpers.php")){
            require_once(substr(self::$config->get("root-path"), 0, -1) . $basePath["dirname"] ."/helpers.php");
        }
        
        //load php script if it exists
        if(file_exists(self::filePath(self::$engineNamespace .".php"))){
            require_once(self::filePath(self::$engineNamespace .".php"));
        }
        
        //////////////////////////////////////////
        self::fireEvents("eventOnLoad");
        //////////////////////////////////////////
        
        //load html?
        if(file_exists(self::filePath(self::$engineNamespace .".html"))) {
            //load template
            self::$doc->loadFile(self::filePath(self::$engineNamespace .".html"), self::$config->get("root-path"));
            self::sysLog("<!--loaded page template (". self::$engineNamespace .".html)-->");
            
            //find any meta tags
            $metaTags = self::$doc->traverse("//meta")->getNodes();
            for($i = 0; $i < count($metaTags); $i++){
                $name = "";
                $content = "";
                
                //make sure attributes are set correctly
                if($metaTags[$i]->getAttribute("name") != null) {
                    $name = $metaTags[$i]->getAttribute("name");
                }
                if($metaTags[$i]->getAttribute("content") != null) {
                    $content = $metaTags[$i]->getAttribute("content");
                }
                
                //set meta name/value
                if($name != ""){
                    self::$metaData[$name] = $content;
                }
                
                //check for custom functionality
                    if($name == "require-session") {
                        //remove the meta tag
                        self::$doc->remove($metaTags[$i]);
                    }
                    if($name == "permission-id") {
                        //remove the meta tag
                        self::$doc->remove($metaTags[$i]);
                    }
                    if($name == "email-debug-log") {
                        self::$emailDebugLog = true;
                        
                        //remove the meta tag
                        self::$doc->remove($metaTags[$i]);
                    }
                    if($name == "cache-rule") {
                        $day = 0;
                        $hour = 0;
                        $minute = 0;
                        $second = 0;
                        
                        //find command properties
                        if($metaTags[$i]->getAttribute("day") != null) {
                            $day = (int)$metaTags[$i]->getAttribute("day");
                        }
                        if($metaTags[$i]->getAttribute("hour") != null) {
                            $hour = (int)$metaTags[$i]->getAttribute("hour");
                        }
                        if($metaTags[$i]->getAttribute("minute") != null) {
                            $minute = (int)$metaTags[$i]->getAttribute("minute");
                        }
                        if($metaTags[$i]->getAttribute("second") != null) {
                            $second = (int)$metaTags[$i]->getAttribute("second");
                        }
                        
                        //cache get data?
                        if($metaTags[$i]->getAttribute("http-get") != null) {
                            self::$cacheGetData = true;
                        }
                        
                        //set our cache expiration
                        self::$cacheExpirationDate = time() + ($day * 24 * 60 * 60) + ($hour * 60 * 60) + ($minute * 60) + $second;
                        
                        //remove the meta tag
                        self::$doc->remove($metaTags[$i]);
                    }
                    if($name == "force-unique-auto-includes") {
                        self::$forceUniqueAutoIncludes = true;
                        
                        //remove the meta tag
                        self::$doc->remove($metaTags[$i]);
                    }
                    if($name == "preload-sql") {
                        $hashName = $content;
                        
                        //remove the meta tag
                        self::$doc->remove($metaTags[$i]);
                        
                        //alias support
                        if($metaTags[$i]->getAttribute("alias") != null) {
                            $hashName = $metaTags[$i]->getAttribute("alias");
                        }
                        
                        //keep track of these, we open the db later and can't get the data right now
                        $customHashList[$hashName] = $content;
                    }
                //end check for custom functionality
            }
            
            //auto include css
            if(file_exists(self::filePath(self::$engineNamespace .".css"))){
                $newCSSNode = self::$doc->domDocument->createElement("link");
                $newCSSNode->setAttribute("href", self::$engineNamespace .".css". (self::$forceUniqueAutoIncludes ? "?nocache=". uniqid() : ""));
                $newCSSNode->setAttribute("rel", "stylesheet");
                $newCSSNode->setAttribute("type", "text/css");
                self::$doc->getNodesByTagName("head")->appendChild($newCSSNode);
            }
            
            //auto include js
            if(file_exists(self::filePath(self::$engineNamespace .".js"))){
                $newJSNode = self::$doc->domDocument->createElement("script", "");
                $newJSNode->setAttribute("language", "javascript");
                $newJSNode->setAttribute("src", self::$engineNamespace .".js". (self::$forceUniqueAutoIncludes ? "?nocache=". uniqid() : ""));
                $newJSNode->setAttribute("type", "text/javascript");
                self::$doc->getNodesByTagName("head")->appendChild($newJSNode);
            }
            
            //find any php includes
            $phpScriptsIncludes = self::$doc->traverse("//script[@language='php']")->getNodes();
            for($i = 0; $i < count($phpScriptsIncludes); $i++){
                //make sure script exists
                if(file_exists(self::filePath($phpScriptsIncludes[$i]->getAttribute("src")))) {
                    //keep track of these, we don't require them right now
                    $phpScripts[] = self::filePath($phpScriptsIncludes[$i]->getAttribute("src"));
                }
            }
            
            //remove those php script tags
            self::$doc->traverse("//script[@language='php']")->remove();
        }
        
        //load php include scripts
        for($i = 0; $i < count($phpScripts); $i++){
            require_once($phpScripts[$i]);
        }
        
        //open db
        self::$db->open();
        
        //require login?
        if(isset(self::$metaData["require-session"])){
            self::$user->requireSession();
        }
        //require permission?
        if(isset(self::$metaData["permission-id"])){
            self::$user->continueOrDenyPermission(self::$metaData["permission-id"]);
        }
        
        //////////////////////////////////////////
        self::fireEvents("eventBeforeActions");
        //////////////////////////////////////////
        
        //check for action events
        if(self::$request->input("action") != "") {
            //////////////////////////////////////////
            $cleanAction = trim(str_replace(" ", "", self::$request->input("action")));
            $method = "action". $cleanAction;
            self::fireEvents($method);
            //////////////////////////////////////////
        }
        
        //load custom hashes
        foreach($customHashList as $key => $value) {
            self::$db->loadCommand($value, self::$engineArgs);
            self::$customHashes[$key] = self::$db->getDataRow();
        }
        
        //////////////////////////////////////////
        self::fireEvents("eventBeforeBinding");
        //////////////////////////////////////////
        
        //do we have data section requests?
        if(self::$request->input("data-section") != "") {
            //data sections
            $nodes = self::$doc->getNodesByDataSet("section")->getNodes();
            for($i = 0 ; $i < count($nodes) ; $i++) {
                //get the resource name
                $name = self::$doc->getAttribute($nodes[$i], "data-section");
                
                if(in_array($name, self::$requestedSections) || self::$request->input("data-section") == "all") {
                    $id = uniqid();
                    $nodes[$i]->setAttribute("data-bind-id", $id);
                    $section = self::$doc->traverse("//*[@data-bind-id='". $id ."']")->getDOMChunk();
                    
                    //bind resources
                    self::sysLog("<data-section name=\"". $name ."\">");
                    self::$doc->bindResources($section);
                    self::$doc->dataBinder(array_merge(self::$engineArgs, self::$doc->domBinders), $section);
                    self::sysLog("</data-section>");
                    
                    //remove data bind id
                    $nodes[$i]->removeAttribute("data-bind-id");
                }
            }
        }
        //if not, bind resources to the whole document
        else {
            self::$doc->bindResources(self::$doc);
        }
        
        //////////////////////////////////////////
        self::fireEvents("eventBeforeFinalize");
        //////////////////////////////////////////
        
        //build data sections object
        if(self::$request->input("data-section") != "") {
            $nodes = self::$doc->getNodesByDataSet("section")->getNodes();
            for($i = 0 ; $i < count($nodes) ; $i++) {
                $name = self::$doc->getAttribute($nodes[$i], "data-section");
                if(in_array($name, self::$requestedSections) || self::$request->input("data-section") == "all") {
                    self::$doc->processWebAlerts($nodes[$i]);
                    self::$responseSections[$name] = self::$doc->getInnerHTML($nodes[$i]);
                }
            }
        }
        
        //if this is for ajax json/section requests?
        $isAjaxRequest = false;
        if(count(self::$responseSections) > 0 || count(self::$responseJSON) > 0) {
            $isAjaxRequest = true;
            self::$response->addHeader("Content-Type", "application/json");
        }
        
        //should we run the final document bind?
        if(!$isAjaxRequest) {
            self::sysLog("<final-data-binder>");
            self::$doc->finalBind(F::$engineArgs);
            self::sysLog("</final-data-binder>");
            
            //move seo elements to the top of the head tag
            $tmpBase = self::$doc->getNodesByTagName("base")->getNode();
            $tmpTitle = self::$doc->getNodesByTagName("title")->getNode();
            $tmpDesc = self::$doc->traverse("//meta[@name='description']")->getNode();
            $tmpKeywords = self::$doc->traverse("//meta[@name='keywords']")->getNode();
            if($tmpBase) {
                if($tmpTitle) {
                    self::$doc->insertBefore($tmpBase, $tmpTitle);
                }
                if($tmpDesc) {
                    self::$doc->insertBefore($tmpBase, $tmpDesc);
                }
                if($tmpKeywords) {
                    self::$doc->insertBefore($tmpBase, $tmpKeywords);
                }
            }
            
            //process alerts
            self::$doc->processWebAlerts();
        }
        
        //////////////////////////////////////////
        self::fireEvents("eventBeforeOutputGeneration");
        //////////////////////////////////////////
        
        //generate the final output
        $outputData = null;
        if($isAjaxRequest) {
            $tmpJSON = array();
            if(count(self::$responseSections) > 0) {
                $tmpJSON["sections"] = self::$responseSections;
            }
            if(count(self::$responseJSON) > 0) {
                $tmpJSON["data"] = self::$responseJSON;
            }
            $outputData = json_encode($tmpJSON);
        }
        else {
            $outputData = self::$doc->toString();
        }
        
        //////////////////////////////////////////
        self::fireEvents("eventFinal");
        //////////////////////////////////////////
        
        //always try and close the db, can't hurt right :)
        self::$db->close();
        
        //create cache files?
            if(F::$config->get("cache-generation-enabled") == true && isset(self::$cacheExpirationDate)) {
                if(!self::$cacheGetData && $_SERVER["X_ORIGINAL_QUERY_STRING"] != "") {
                    //don't cache these requests
                }
                else if($_SERVER["REQUEST_METHOD"] == "POST") {
                    //we only cache GET reqeusts
                }
                else {
                    //let's make some cache
                    $parsedURL = parse_url($_SERVER["X_ORIGINAL_SCRIPT_URI"]);
                    $basePath = pathinfo($parsedURL["path"]);
                    
                    //create unique filename
                    $signatureFile = $basePath["basename"];
                    if(self::$cacheGetData) {
                        $signatureFile = $signatureFile ."?". $_SERVER["X_ORIGINAL_QUERY_STRING"];
                    }
                    
                    //make unique md5 name of file
                    $signatureFile = md5($signatureFile);
                    
                    //final save path
                    $savePath = substr(self::$config->get("project-root-cache"), 0, -1) . $basePath["dirname"] ."/". $signatureFile;
                    
                    //create rule file
                    $cacheRuleSavePath = $savePath .".rule";
                    $cacheRuleData = array();
                    $cacheRuleData["expires"] = self::$cacheExpirationDate;
                    if(isset(self::$response->headers["content-type"])) {
                        $cacheRuleData["content-type"] = self::$response->headers["content-type"];
                    }
                    $cacheRuleData = json_encode($cacheRuleData);
                    
                    //check for directory
                    if(!file_exists(substr(self::$config->get("project-root-cache"), 0, -1) . $basePath["dirname"])) {
                        mkdir(substr(self::$config->get("project-root-cache"), 0, -1) . $basePath["dirname"], 0755, true);
                    }
                    
                    //save cache data
                    file_put_contents($savePath, $outputData);
                    
                    //save cache rule file
                    file_put_contents($cacheRuleSavePath, $cacheRuleData);
                    
                    //log that we cached
                    self::sysLog("<!--created cache files-->");
                }
            }
        //end create cache files
        
        //log the request type
        if($isAjaxRequest) {
            self::sysLog("<!--finalized with json data-->");
        }
        else {
            self::sysLog("<!--finalized with default page-->");
        }
        
        //end debug log
        self::sysLog("</debug>");
        
        //email debug log
        if(self::$emailDebugLog) { self::emailDebugLog(); }
        
        //finalize request
        self::$response->finalize($outputData);
    }
    
    /**
     * gets the data bind value
     * @param string $index
     * @param array|null $localData
     * @return string|null The data
     */
    public static function getBindDataValue($index, $localData = null) {
        if(strpos($index, ":") > -1) {
            $signature = explode(":", $index);
            
            //check for custom hashes
            if(self::$customHashes) {
                if(isset(self::$customHashes[$signature[0]])) {
                    if(isset(self::$customHashes[$signature[0]][$signature[1]])) {
                        return self::$customHashes[$signature[0]][$signature[1]];
                    }
                }
            }
            
            //check for config
            if($signature[0] == "config") {
                return self::$config->get($signature[1]);
            }
            
            //check for engine args
            if($signature[0] == "engine") {
                if(isset(self::$engineArgs[$signature[1]])) {
                    return self::$engineArgs[$signature[1]];
                }
            }
            
            //check for input
            if($signature[0] == "input") {
                return self::$request->input($signature[1]);
            }
            
            //check for get
            if($signature[0] == "get") {
                return self::$request->queryString($signature[1]);
            }
            
            //check for post
            if($signature[0] == "post") {
                return self::$request->form($signature[1]);
            }
            
            //check for sessions
            if($signature[0] == "session") {
                if(!is_null(self::$request->session($signature[1]))) {
                    return self::$request->session($signature[1]);
                }
            }
            
            //check for cookies
            if($signature[0] == "cookies") {
                if(!is_null(self::$request->cookies($signature[1]))) {
                    return self::$request->cookies($signature[1]);
                }
            }
            
            //check for cookies
            if($signature[0] == "server") {
                if(!is_null(self::$request->serverVariables(strtoupper($signature[1])))) {
                    return self::$request->serverVariables(strtoupper($signature[1]));
                }
            }
            
            //check for sql
            if($signature[0] == "sql") {
                self::$db->loadCommand($signature[1], self::$engineArgs);
                return self::$db->getDataString();
            }
        }
        
        if(isset($localData[$index])) {
            return $localData[$index];
        }
        
        //not caught
        return null;
    }
    
    /**
     * generates web alert messages
     * @return string The generated html
     */
    public static function getWebAlerts() {
        //output container
        $notifications = "";
        
        //warnings
        if(isset(self::$warnings)) {
            if(self::$warnings->count() > 0) {
                $warnings = "";
                for($i = 0 ; $i < self::$warnings->count() ; $i++) {
                    $warnings .= "<p>". self::$warnings->item($i) ."</p>";
                }
                
                $notifications .= "<div class=\"alert alert-block alert-warning\">". $warnings ."</div>";
            }
        }
        
        //errors
        if(self::$errors) {
            if(self::$errors->count() > 0) {
                $errors = "";
                for($i = 0 ; $i < self::$errors->count() ; $i++) {
                    $error = self::$errors->item($i);
                    if(is_array($error)) {
                        $errors .= "<p for=\"". $error[0] ."\">". $error[1] ."</p>";
                    }
                    else {
                        $errors .= "<p>". $error ."</p>";
                    }
                }
                
                $notifications .= "<div class=\"alert alert-block alert-error\"><h4 class=\"alert-heading\">Error!</h4>". $errors ."</div>";
            }
        }
        
        //alerts
        if(self::$alerts) {
            if(self::$alerts->count() > 0) {
                $alerts = "";
                for($i = 0 ; $i < self::$alerts->count() ; $i++) {
                    $alerts .= "<p>". self::$alerts->item($i) ."</p>";
                }
                
                $notifications .= "<div class=\"alert alert-block alert-success\">". $alerts ."</div>";
            }
        }
        
        //info
        if(self::$info) {
            if(self::$info->count() > 0) {
                $info = "";
                for($i = 0 ; $i < self::$info->count() ; $i++) {
                    $info .= "<p>". self::$info->item($i) ."</p>";
                }
                
                $notifications .= "<div class=\"alert alert-block alert-info\">". $info ."</div>";
            }
        }
        
        #return notifications
        return $notifications;
    }
    
    /**
     * adds an entry to the log
     * @param string $data
     */
    public static function log($data) {
        self::$debugLog->add($data);
    }
    
    /**
     * a wrapped version of log(..) that the engine uses internally
     * this makes it easy to silence engine logs using a config flag
     * @param string $data
     */
    public static function sysLog($data) {
        if(self::$config->get("debug-enable-system-logs")) {
            self::log($data);
        }
    }
    
    /**
     * emails the debug log
     * @param bool $fatal if the debug log was sent in a fatal way
     */
    public static function emailDebugLog() {
        //stop debug timer
        self::$debugTimer->stop();
        
        //compile debug data
        $errorLogData = array();
        $errorLogData["timestamp"] = self::$dateTime->now()->toString();
        $errorLogData["stack-trace"] = print_r(debug_backtrace(), true);
        $errorLogData["environment"] = print_r($_SERVER, true);
        $errorLogData["debug-log"] = print_r(self::$debugLog, true);
        if(self::$config->get("mode") == "www") {
            $errorLogData["application"] = self::$request->serverVariables("SERVER_NAME");
            $errorLogData["source"] = self::$request->serverVariables("SCRIPT_FILENAME");
            $errorLogData["url"] = self::$request->serverVariables("REQUEST_URI");
            $errorLogData["http-get"] = print_r($_GET, true);
            $errorLogData["http-post"] = print_r($_POST, true);
            $errorLogData["session"] = print_r($_SESSION, true);
            $errorLogData["cookies"] = print_r($_COOKIE, true);
        }
        else {
            $errorLogData["application"] = self::$config->get("cli-request");
            $errorLogData["source"] = self::$engineNamespace;
        }
        self::$customHashes["log"] = $errorLogData;
        
        //build up message
        $message = new DOMTemplate_Ext();
        if(self::$config->get("mode") == "www") {
            $message->loadFile(self::filePath("_theme/system/debug-log.email.html"));
        }
        else {
            $message->loadFile(self::$config->get("project-root-www") ."_theme/system/debug-log.email.html");
        }
        $message->bindResources($message);
        $message->dataBinder(array_merge((array)self::$engineArgs, (array)self::$doc->domBinders));
        
        //get email ready to send
        self::$emailClient->addTo(self::$config->get("admin-email"));
        self::$emailClient->subject = "(". self::$config->get("environment") ."-". self::$config->get("mode") .") DebugLog @ ". self::$config->get("project-name");
        self::$emailClient->message = $message->toString();
        self::$emailClient->isHTML = true;
        
        //try to send the email
        try{
            //send email
            self::$emailClient->send();
            //reset
            self::$emailClient->reset();
        }
        catch(Exception $e) {
            //it didn't get sent :(
        }
    }
    
    /**
     * the web server status method
     */
    public static function webServerStatus($statusCode, $statusMessage) {
        //set http status code
        header("HTTP/1.1 ". $statusCode);
        
        //get page template
        $statusDoc = new DOMTemplate_Ext();
        $statusDoc->loadFile(self::filePath("_theme/system/server-status.html"), self::$config->get("root-path"));
        
        //set some binders
        $statusDoc->domBinders["status-code"] = $statusCode;
        $statusDoc->domBinders["status-message"] = $statusMessage;
        $statusDoc->domBinders["mail-to-email"] = F::$config->get("admin-email");
        $statusDoc->domBinders["mail-to-href"] = "mailto:". self::$config->get("admin-email");
        $statusDoc->finalBind(F::$engineArgs);
        
        //show screen
        print($statusDoc->toString());
        
        //that's all folks
        exit;
    }
    
    /**
     * autoloads classes for us
     */
    public static function libAutoLoader($className) {
        //try to include this file
        if(file_exists(self::$config->get("project-root") ."lib/footprint/". $className .".php")) {
            require_once(self::$config->get("project-root") ."lib/footprint/". $className .".php");
        }
        if(file_exists(self::$config->get("project-root") ."lib/footprint/extensions/". $className .".php")) {
            require_once(self::$config->get("project-root") ."lib/footprint/extensions/". $className .".php");
        }
        if(file_exists(self::$config->get("project-root") ."lib/weblegs/". $className .".php")) {
            require_once(self::$config->get("project-root") ."lib/weblegs/". $className .".php");
        }
        if(file_exists(self::$config->get("project-root") ."app/lib/". $className .".php")) {
            require_once(self::$config->get("project-root") ."app/lib/". $className .".php");
        }
    }
}
