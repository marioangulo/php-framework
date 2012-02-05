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

class WebResponse {
    public $redirectURL;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->redirectURL = null;
    }
    
    /**
     * writes data to the http response and terminates script execution
     * @param string $data
     */
    public function finalize($data = "") {
        //is there a redirect url?
        if(!is_null($this->redirectURL) && $this->redirectURL != "") {
            $this->redirect($this->redirectURL);
        }
        else {
            //write final data and end
            $this->write($data);
            $this->end();
        }
    }
    
    /**
     * writes data to the http response
     * @param string $value
     */
    public function write($value) {
        print($value);
    }
    
    /**
     * writes the redirection instruction to the http response
     * @param string $url
     */
    public function redirect($url) {
        //set redirect header
        header("Location: ". $url);
        $this->end();
    }
    
    /**
     * ends script exection
     * @param string $url
     */
    public function end() {
        //stop the execution of php
        exit();
    }
    
    /**
     * writes an http response header
     * @param string $url
     */
    public function addHeader($name, $value = null) {
        //set http header
        if(!isset($value)) {
            header($name);
        }
        else {
            header($name .": ". $value);
        }
    }
    
    /**
     * sets server side client data
     * @param string $name
     * @param string $value
     */
    public function session($name, $value) {
        $_SESSION[$name] = $value;
        return;
    }
    
    /**
     * sets data at the client
     * @param string $name
     * @param string $value
     * @param int $minuts
     * @param string $path
     * @param string $domain
     * @param bool $secure
     */
    public function cookies($key, $value = "", $minutes = 0, $path = "/", $domain = null, $secure = false) {
        //calculate minutes
        if($minutes != 0) {
            //unix timestamp X 60 seconds X $minutes
            $expires = time() + 60 * $minutes;        
        }
        else{
            $expires = 0;
        }
        
        //set domain
        if(is_null($domain)) {
            $domain = $_SERVER['HTTP_HOST'];
        }
        
        //if we are ssl make cookies require ssl
        if($secure == false && $_SERVER['SERVER_PORT'] == "443") {
            $secure = true;
        }
        if($secure == false && isset($_SERVER['HTTP_X_FORWARDED_PORT'])) {
            if($_SERVER['HTTP_X_FORWARDED_PORT'] == "443") {
                $secure = true;
            }
        }
        
        //lets set the cookie
        setcookie($key, $value, $expires, $path, $domain, $secure);
    }
    
    /**
     * destroys all cookie data 
     * @param string $path
     * @param string $domain
     */
    public function clearCookies($path = "/", $domain = "") {
        //set default value
        if($domain == ""){
            $domain = $_SERVER["SERVER_NAME"];
        }
        
        //loop through each cookie and expire
        foreach($_COOKIE as $key => $value) {
            //set cookie to expire yesterday
            setcookie($key, null, time() - 1440, $path, $domain);
        }
    }
    
    /**
     * destroys all session data 
     */
    public function clearSession() {
        //accomodate for incosistant behaviour
        session_unset();
        session_destroy();
        $_SESSION = array();
    }
}
