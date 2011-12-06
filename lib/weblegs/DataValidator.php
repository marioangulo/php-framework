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

class DataValidator {
    /**
     * @param string $input the data to valiate
     * @return bool If input is a valid email
     */
    public static function isValidEmail($input) {
        $matchesFound = preg_match("/^[a-zA-Z0-9+._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/", $input, $matches);
        if($matchesFound > 0) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @return bool If input is a valid url
     */
    public static function isValidURL($input) {
        $matchesFound = preg_match("/^https?:\/\/[a-zA-Z0-9._%-]+\.[a-zA-Z0-9.-]+(\/.*)*$/", $input, $matches);
        if($matchesFound > 0) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @param string $countryCode the country code
     * @return bool If input is a valid phone
     */
    public static function isPhone($input, $countryCode = "us") {
        if(strtoupper($countryCode) == "US") {
            $matchesFound = preg_match("/^([01][\s\.-]?)?(\(\d{3}\)|\d{3})[\s\.-]?\d{3}[\s\.-]?\d{4}$/", $input, $matches);
            if($matchesFound > 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
          return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @param string $countryCode the country code
     * @return bool If input is a valid postal code
     */
    public static function isPostalCode($input, $countryCode = "us") {
        if(strtoupper($countryCode) == "US") {
            $matchesFound = preg_match("/^\d{5}(-?\d{4})?$/", $input, $matches);
            if($matchesFound > 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @return bool If input is only alpha numeric
     */
    public static function isAlpha($input) {
        $matchesFound = preg_match("/^[a-zA-Z]+$/", $input, $matches);
        if($matchesFound > 0) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @return bool If input matches the IPv4 pattern
     */
    public static function isIPv4($input) {
        $matchesFound = preg_match("/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.) {3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/", $input, $matches);
        if($matchesFound > 0) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @return bool If input is an integer
     */
    public static function isInt($input) {
        if((int)$input != 0 && $input != "0"){
            return is_int((int)$input);
        }
        else{
            return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @return bool If input is a double (floatig point number)
     */
    public static function isDouble($input) {
        return is_double($input);
    }
    
    /**
     * @param string $input the data to valiate
     * @return bool If input is only numeric
     */
    public static function isNumeric($input) {
        if(is_double($input)){
            return true;
        }
        else if(is_int((int)$input)){
            return true;
        }
        return false;
    }
    
    /**
     * @param string $input the data to valiate
     * @return bool If input is a date (highly subjective)
     */
    public static function isDateTime($input) {
        $parseReply = date_parse($input);
        if($parseReply["error_count"] == "0") {
            //return true
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @param string $length the minimum length $input must be 
     * @return bool If input is greater than or equal to the supplied $length
     */
    public static function minLength($value, $length) {
        if(strlen($value) >= $length){
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @param string $length the maximum length $input can be 
     * @return bool If input is less than or equal to the supplied $length
     */
    public static function maxLength($value, $length) {
        if(strlen($value) <= $length){
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * @param string $input the data to valiate
     * @param string $length the length $input must be 
     * @return bool If input is equal to the supplied $length
     */
    public static function length($value, $length) {
        if(strlen($value) == $length){
            return true;
        }
        else{
            return false;
        }
    }
}
