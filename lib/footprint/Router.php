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

//make sure we know have the SCRIPT_URI set
if(!isset($_SERVER["SCRIPT_URI"])) {
    $_SERVER["SCRIPT_URI"] = $_SERVER["REQUEST_URI"];
}

//keep track of the originals
$_SERVER["X_ORIGINAL_SCRIPT_URI"] = $_SERVER["SCRIPT_URI"];
$_SERVER["X_ORIGINAL_QUERY_STRING"] = $_SERVER["QUERY_STRING"];

class Router {
    //request info
    public static $paths;
    public static $path;
    public static $fileName;
    public static $fileBaseName;
    public static $fileExtension;
    public static $queryString;
    public static $params;
    
    //re-route config
    public static $indexFile;
    public static $routeFile;
    public static $documentRoot;
    public static $redirectResource;
    public static $redirectPath;
    public static $routeFallbackFunction;
    
    /**
     * initializes the router
     * @param bool $outputDebugLog
     */
    public static function init(){
        //remove query string from request uri
        $requestedPath = str_replace(array($_SERVER["QUERY_STRING"], "?"), "", $_SERVER["REQUEST_URI"]);
        
        //keep a copy of the current querystring
        self::$queryString = $_SERVER["QUERY_STRING"];
        
        //determine if this is a dir request or a file request
        if(substr($requestedPath, -1) != "/"){
            //get file parts
            $tmpfileParts = pathinfo($requestedPath);
            
            //get file name
            self::$fileName = null;
            if(!empty($tmpfileParts["basename"])){
                self::$fileName = $tmpfileParts["basename"];
            }
            
            //get file base name
            self::$fileBaseName = null;
            if(!empty($tmpfileParts["filename"])){
                self::$fileBaseName = $tmpfileParts["filename"];
            }
            
            //get file extension
            self::$fileExtension = null;
            if(!empty($tmpfileParts["extension"])){
                self::$fileExtension = $tmpfileParts["extension"];
            }
            
            //remove file from path
            self::$path = str_replace(basename($requestedPath), "", $requestedPath);
        }
        //this is a dir request
        else{
            self::$path = $requestedPath;
        }
        
        //prepare paths array
        self::$paths = null;
        if(self::$path != "/"){
            //explode by /
            self::$paths = explode("/",$requestedPath);
            
            //remove first and last empty itmes
            array_shift(self::$paths);
            if(self::$paths[count(self::$paths) - 1] == ""){
                array_pop(self::$paths);
            }
        }
        
        //set params
        self::$indexFile = F::$config->get("router-index-file");
        self::$routeFile = F::$config->get("router-route-file");
        self::$documentRoot = substr(F::$config->get("root-path"), 0, -1);
        
        //get file name
        self::$redirectResource = self::$fileBaseName;
        if(empty(self::$fileBaseName)){
            //use this for finalize
            self::$redirectResource = self::$indexFile;
        }
        
        //get redirect path
        self::$redirectPath = self::$path;
        
        //set callback function param
        self::$routeFallbackFunction = F::$config->get("router-fallback-function");
        
        //route request
        self::route();
    }
    
    /**
     * gets the path
     * @return string The data
     */
    public static function getPath(){
        $returnPath = "/";
        for($i = 0; $i < count(self::$paths); $i++){
            $returnPath .= self::$paths[$i] ."/";
        }
        return $returnPath;
    }
    
    /**
     * appends the querystring
     * @param string $value
     */
    public static function appendqueryString($value){
        self::$queryString .= $value;
    }
    
    /**
     * routes the request
     */
    public static function route(){
        //find route files in each path (priority on deepest found)
        $routepath = self::$path;
        for($i = count(self::$paths) - 1; $i >= 0; $i--){
            //handle first iteration
            if(count(self::$paths) - 1 == $i){
                if(file_exists(self::$documentRoot . self::$path . self::$routeFile)){
                    require_once(self::$documentRoot . self::$path . self::$routeFile);
                    break;
                }
            }
            else{
                //replace off the end - rather than anywhere the string is found
                if(substr($routepath, (strlen($routepath) - strlen(self::$paths[$i] ."/")), strlen(self::$paths[$i] ."/")) == self::$paths[$i]."/"){
                    $routepath = substr($routepath, 0, (strlen($routepath) - strlen(self::$paths[$i] ."/")));
                }
                if(file_exists(self::$documentRoot . $routepath . self::$routeFile)){
                    require_once(self::$documentRoot . $routepath . self::$routeFile);
                    break;
                }
            }
        }
        
        //the root most route file (backup)
        if(file_exists(self::$documentRoot . $routepath . self::$routeFile)){
            require_once(self::$documentRoot . $routepath . self::$routeFile);
        }
        
        //fire the onload event
        if(method_exists("Route", "eventOnLoad")) {
            call_user_func("Route::eventOnLoad");
            return;
        }
        
        //execute route fallback function
        self::callFallback();
    }
    
    /**
     * sends a redirect header and stops script execution
     * @param string $url
     */
    public static function redirect($url){
        header("Location: ". $url, TRUE, 301);
        exit();
    }
    
    /**
     * finalizes the routing and initializes the web request engine
     * @param string $redirectPath
     * @param string $redirectFileName
     */
    public static function finalize($redirectPath, $redirectFileName, $executionExtension = "html"){
        //set enviroment values
        $_SERVER["SCRIPT_URI"] = "http://". $_SERVER["HTTP_HOST"] . $redirectPath . $redirectFileName .".". $executionExtension; 
        $_SERVER["SCRIPT_FILENAME"] = substr(F::$config->get("project-root-www"), 0, -1) . $redirectPath . $redirectFileName .".". $executionExtension;
        $_SERVER["SCRIPT_NAME"] = $redirectPath . $redirectFileName .".". $executionExtension;
        $_SERVER["PHP_SELF"] = $redirectPath . $redirectFileName .".". $executionExtension;
        $_SERVER["QUERY_STRING"] = self::$queryString;
        
        //Init!
        F::init();
    }
    
    /**
     * executes the fallback function
     */
    public static function callFallback(){
        call_user_func(self::$routeFallbackFunction);
    }
    
    /**
     * checks for cached web requests
     */
    public static function cacheCheck(){
        //we don't cache any post requests
        if(F::$config->get("cache-check-enabled") == true && $_SERVER["REQUEST_METHOD"] != "POST") {
            $parsedURL = parse_url($_SERVER["SCRIPT_URI"]);
            $basePath = pathinfo($parsedURL["path"]);
            $cachePath = substr(F::$config->get("project-root-cache"), 0, -1) . $basePath["dirname"];
            
            //check for the www-cache/*dir*
            if(file_exists($cachePath)) {
                //find the cache file path
                $fileSignature = $basePath["basename"];
                if($_SERVER["QUERY_STRING"] != "") {
                    $fileSignature = $fileSignature ."?". $_SERVER["QUERY_STRING"];
                }
                $cacheFilePath = $cachePath ."/". md5($fileSignature);
                
                //does a rule exist?
                if(file_exists($cacheFilePath .".rule")) {
                    //get the rules
                    $rules = json_decode(file_get_contents($cacheFilePath .".rule"), true);
                    
                    //is it old?
                    $expiresOn = (int)$rules["expires"];
                    if(time() > $expiresOn) {
                        //doin't use cache
                    }
                    else {
                        //should we add a content-type header?
                        if(isset($rules["content-type"])) {
                            header("Content-Type: ". $rules["content-type"]);
                        }
                        
                        //print the file and exit
                        print(file_get_contents($cacheFilePath));
                        exit();
                    }
                }
            }
        }
    }
}
