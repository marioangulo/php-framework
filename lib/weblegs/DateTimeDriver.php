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

class DateTimeDriver {
    //major
    public $dateTime;
    public $year;
    public $month;
    public $day;
    public $hour;
    public $minute;
    public $second;
    
    //informational
    public $isLeapYear;
    public $dayOfWeek;
    public $dayOfYear;
    public $daysInYear;
    public $startDayOfMonth;
    public $endDayOfMonth;
    public $weekOfYear;
    public $daysInMonth;
    public $weeksInYear;
    public $dayName;
    public $dayNameAbbr;
    public $monthName;
    public $monthNameAbbr;
    
    //helpers
    public $dayNameAbbrs;
    public $dayNames;
    public $monthNameAbbrs;
    public $monthNames;
    public $minValue;
    public $maxValue;
    
    /**
     * construct the object
     * @overload #1 ()
     * @overload #2 ($valueToParse)
     * @overload #3 ($year, $month, $day)
     * @overload #4 ($year, $month, $day, $hour, $minute, $second)
     */
    public function __construct() {
        //build the date data
            //day name abbrs
            $this->dayNameAbbrs[1] = "Sun";
            $this->dayNameAbbrs[2] = "Mon";
            $this->dayNameAbbrs[3] = "Tue";
            $this->dayNameAbbrs[4] = "Wed";
            $this->dayNameAbbrs[5] = "Thu";
            $this->dayNameAbbrs[6] = "Fri";
            $this->dayNameAbbrs[7] = "Sat";
            
            //day names
            $this->dayNames[1] = "Sunday";
            $this->dayNames[2] = "Monday";
            $this->dayNames[3] = "Tuesday";
            $this->dayNames[4] = "Wednesday";
            $this->dayNames[5] = "Thursday";
            $this->dayNames[6] = "Friday";
            $this->dayNames[7] = "Saturday";
            
            //month name abbrs
            $this->monthNameAbbrs[1] = "Jan";
            $this->monthNameAbbrs[2] = "Feb";
            $this->monthNameAbbrs[3] = "Mar";
            $this->monthNameAbbrs[4] = "Apr";
            $this->monthNameAbbrs[5] = "May";
            $this->monthNameAbbrs[6] = "Jun";
            $this->monthNameAbbrs[7] = "Jul";
            $this->monthNameAbbrs[8] = "Aug";
            $this->monthNameAbbrs[9] = "Sep";
            $this->monthNameAbbrs[10] = "Oct";
            $this->monthNameAbbrs[11] = "Nov";
            $this->monthNameAbbrs[12] = "Dec";
            
            //month names
            $this->monthNames[1] = "January";
            $this->monthNames[2] = "February";
            $this->monthNames[3] = "March";
            $this->monthNames[4] = "April";
            $this->monthNames[5] = "May";
            $this->monthNames[6] = "June";
            $this->monthNames[7] = "July";
            $this->monthNames[8] = "August";
            $this->monthNames[9] = "September";
            $this->monthNames[10] = "October";
            $this->monthNames[11] = "November";
            $this->monthNames[12] = "December";
        //end build the date data
        
        //min/max
        $this->minValue = "1901-12-13 20:45:54";
        $this->maxValue = "2038-01-19 03:14:07";
        
        $argCount = func_num_args();
        $args = func_get_args();
        
        //set the default timezone
        date_default_timezone_set("UTC");
        
        //DateTimeDriver()
        if($argCount == 0){
            $this->dateTime = new DateTime();
            $this->refreshProperties();
        }
        //DateTimeDriver($value)
        else if($argCount == 1){
            $this->parse($args[0]);            
        }
        //DateTimeDriver($year, $month, $day)
        else if($argCount == 3){
            $this->dateTime = new DateTime();
            $this->set($args[0], $args[1], $args[2]);
        }
        //DateTimeDriver($year, $month, $day, $hour, $minute, $second)
        else if($argCount == 6){
            $this->dateTime = new DateTime();
            $this->set($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
        }
    }
    
    /**
     * set the date
     * @overload #1 ()
     * @overload #2 ($year, $month, $day)
     * @overload #3 ($year, $month, $day, $hour, $minute, $second)
     * @return this Object chaining
     */
    public function set() {
        $argCount = func_num_args();
        $args = func_get_args();
        
        if($argCount >= 3){
            date_date_set($this->dateTime, (int)$args[0], (int)$args[1], (int)$args[2]);
        }
        
        if($argCount == 6){
            date_time_set($this->dateTime, (int)$args[3], (int)$args[4], (int)$args[5]);
        }
        else{
            date_time_set($this->dateTime, 0, 0, 0);
        }
        
        //refresh
        $this->refreshProperties();
        
        return $this;
    }
    
    /**
     * refreshes the internal properties
     */
    function refreshProperties() {
        $dateTimeArray = explode("-", date_format($this->dateTime, "Y-m-d-H-i-s-L-z-w-W-t"));
        
        $this->year = $dateTimeArray[0];
        $this->month = (int)$dateTimeArray[1];
        $this->day = (int)$dateTimeArray[2];
        $this->hour = (int)$dateTimeArray[3];
        $this->minute = (int)$dateTimeArray[4];
        $this->second = (int)$dateTimeArray[5];
        
        $this->isLeapYear = ($dateTimeArray[6] == "0" ? false : true);
        $this->dayOfWeek = $dateTimeArray[8];
        $this->dayOfYear = $dateTimeArray[7];
        $this->daysInYear = 0;

        for($i = 1 ; $i < 12 ; $i++) {
            $this->daysInYear += cal_days_in_month(CAL_GREGORIAN, $i, (int)$this->year);
        }
        
        $tmpDateTime = new DateTime();
        $tmpDateTime->setDate($this->year, $this->month, 1);
        $this->startDayOfMonth = date_format($tmpDateTime, "w");
        
        $tmpDateTime->setDate($this->year, $this->month, 1);
        $tmpDateTime->modify("1 month");
        $tmpDateTime->modify("-1 day");
        $this->endDayOfMonth = date_format($tmpDateTime, "w");
        $this->weekOfYear = $dateTimeArray[9];
        $this->daysInMonth = $dateTimeArray[10];

        //get weeks in year
        $tmpDays = 0;
        for($i = 1; $i <= 12; $i++){
            $tmpDays += cal_days_in_month(CAL_GREGORIAN, (int)$i, $this->year);
        }
        $this->weeksInYear = ($tmpDays / 7);
        $this->dayName = $this->dayNames[(int)$this->dayOfWeek + 1];
        $this->dayNameAbbr = $this->dayNameAbbrs[(int)$this->dayOfWeek + 1];
        $this->monthName = $this->monthNames[(int)$this->month];
        $this->monthNameAbbr = $this->monthNameAbbrs[(int)$this->month];
    }
    
    /**
     * adds n number of seconds to the date
     * @param int|string $value
     * @return this Object chaining
     */
    function addSeconds($value) {
        date_modify($this->dateTime, $value." second");
        $this->refreshProperties();
        
        //return for method chaining
        return $this;
    }
    
    /**
     * adds n number of minutes to the date
     * @param int|string $value
     * @return this Object chaining
     */
    function addMinutes($value) {
        date_modify($this->dateTime, $value." minute");
        $this->refreshProperties();
        
        //return for method chaining
        return $this;
    }
    
    /**
     * adds n number of hours to the date
     * @param int|string $value
     * @return this Object chaining
     */
    function addHours($value) {
        date_modify($this->dateTime, $value." hour");
        $this->refreshProperties();
        
        //return for method chaining
        return $this;
    }
    
    /**
     * adds n number of days to the date
     * @param int|string $value
     * @return this Object chaining
     */
    function addDays($value) {
        date_modify($this->dateTime, $value." day");
        $this->refreshProperties();
        
        //return for method chaining
        return $this;
    }
    
    /**
     * adds n number of months to the date
     * @param int|string $value
     * @return this Object chaining
     */
    function addMonths($value) {
        date_modify($this->dateTime, $value." month");
        $this->refreshProperties();
        
        //return for method chaining
        return $this;
    }
    
    /**
     * adds n number of years to the date
     * @param int|string $value
     * @return this Object chaining
     */
    function addYears($value) {
        date_modify($this->dateTime, $value." year");
        $this->refreshProperties();
        
        //return for method chaining
        return $this;
    }
    
    /**
     * compares two DateTimeDriver objects and returns an array of data
     * @param DateTimeDriver $toCompare
     * @return array The difference between $toCompare and the internal $dateTime property
     */
    function diff(DateTimeDriver $toCompare) {
        $d1 = strtotime($this->toString());
        $d2 = strtotime($toCompare->toString());
        $diff_secs = abs($d1 - $d2);
        $base_year = min(date("Y", $d1), date("Y", $d2));
        $diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
        return array(
            "Milliseconds" => (int) date("s", $diff) * 1000,
            "Seconds" => (int) date("s", $diff),
            "Minutes" => (int) date("i", $diff),
            "Hours" => date("G", $diff),
            "Days" => date("j", $diff) - 1,
            "TotalMilliseconds" => $diff_secs * 1000,
            "TotalSeconds" => $diff_secs,
            "TotalMinutes" => floor($diff_secs / 60),
            "TotalHours" => floor($diff_secs / 3600),
            "TotalDays" => floor($diff_secs / (3600 * 24))
        );
    }
    
    /**
     * creates a new DateTimeDriver
     * @return DateTimeDriver A new object
     */
    function now() {
        return new DateTimeDriver();
    }
    
    /**
     * sets the internal date to the minimum value
     * @return this Object chaining
     */
    function setMinValue() {
        $this->parse($this->minValue);
        return $this;
    }
    
    /**
     * sets the internal date to the maximum value
     * @return this Object chaining
     */
    function setMaxValue() {
        $this->parse($this->maxValue);
        return $this;
    }
    
    /**
     * attempts to parse in a date 
     * @param string $value the text to try parsing
     * @return this Object chaining
     */
    function parse($value) {
        $this->dateTime = new DateTime();
        $arrTime = date_parse(trim($value));
        
        //if parse fails, try to fix
        if($arrTime["error_count"] > 0) {
            //try to fix a semi-common date format
            $value = preg_replace("/^(\d{2})-(\d{2})-(\d{4})/", "\\3-\\1-\\2", $value);
            
            //try again
            $this->dateTime = new DateTime();
            $arrTime = date_parse(trim($value));
        }
        
        //if this parse fails, set the min value
        if($arrTime["error_count"] > 0) {
            return $this->parse($this->minValue);
        }
        
        //set date
        if($arrTime["year"] != "" && $arrTime["month"] != "" && $arrTime["day"] != ""){
            date_date_set($this->dateTime, $arrTime["year"], $arrTime["month"], $arrTime["day"]);
        }
        else{
            //create temp date time
            $tmpDT = new DateTime();
            
            //set to todays date
            date_date_set($this->dateTime, date_format($tmpDT, "Y"), date_format($tmpDT, "n"), date_format($tmpDT, "j"));
        }
        
        //sanitize array values
        if($arrTime["hour"] == "") {
             $arrTime["hour"] = "00";
        }
        if($arrTime["minute"] == "") {
             $arrTime["minute"] = "00";
        }
        if($arrTime["second"] == "") {
             $arrTime["second"] = "00";
        }
        
        //set the time
        date_time_set($this->dateTime, $arrTime["hour"], $arrTime["minute"], $arrTime["second"]);
        
        
        $this->refreshProperties();
        
        return $this;
    }
    
    /**
     * generates the internal date in the supplied format
     * @param string $format the format the string should be in
     * @return string Transformed input
     */
    function toString($format = "yyyy-MM-dd HH:mm:ss") {
        //support for the [...content...] blocks
            $validKeyCharacters = array("a", "b", "c", "e", "f", "g", "i", "j", "k", "l", "p", "q", "r", "u", "v", "w", "x");
            $savedText = array();
            preg_match_all("(\[.*?\])", $format, $matches);
            
            //all matches reside in the first element
            $matches = $matches[0];
            
            for($i = 0 ; $i < count($matches); $i++) {
                //generate random key
                $thisKey = "";
                for($j = 0 ; $j < 20 ; $j++) {
                    $thisKey .= $validKeyCharacters[rand(0, count($validKeyCharacters) - 1)];
                }
                
                $thisMatch = $matches[$i];
                $savedText[$thisKey] =  str_replace(array("[", "]"), "", $matches[$i]);
                
                $format = str_replace($matches[$i], $thisKey, $format);
            }
        //end support for the [...content...] blocks
        
        //setup internal token translation
        $format = str_replace("dddd", "!!!!", $format);
        $format = str_replace("ddd", "!!!", $format);
        $format = str_replace("dd", "!!", $format);
        $format = str_replace("do", "!@", $format);
        $format = str_replace("d", "!", $format);
        $format = str_replace("hh", "##", $format);
        $format = str_replace("ho", "#@", $format);
        $format = str_replace("h", "#", $format);
        $format = str_replace("HH", "==", $format);
        $format = str_replace("HO", "=@", $format);
        $format = str_replace("H", "=", $format);
        $format = str_replace("mm", "%%", $format);
        $format = str_replace("mo", "%@", $format);
        $format = str_replace("m", "%", $format);
        $format = str_replace("MMMM", "^^^^", $format);
        $format = str_replace("MMM", "^^^", $format);
        $format = str_replace("MM", "^^", $format);
        $format = str_replace("MO", "^@", $format);
        $format = str_replace("M", "^", $format);
        $format = str_replace("ss", "&&", $format);
        $format = str_replace("so", "&@", $format);
        $format = str_replace("s", "&", $format);
        $format = str_replace("tzo", "***", $format);
        $format = str_replace("tt", "**", $format);
        $format = str_replace("t", "*", $format);
        $format = str_replace("TT", "__", $format);
        $format = str_replace("T", "_", $format);
        $format = str_replace("yyyyo", "~~~~@", $format);
        $format = str_replace("yyyy", "~~~~", $format);
        $format = str_replace("yy", "~~", $format);
        
        //translate internal tokens
        $format = str_replace("!!!!", date_format($this->dateTime, "l"), $format);
        $format = str_replace("!!!", date_format($this->dateTime, "D"), $format);
        $format = str_replace("!!", date_format($this->dateTime, "d"), $format);
        $format = str_replace("!@", $this->ordinalSuffix(date_format($this->dateTime, "j")), $format);
        $format = str_replace("!", date_format($this->dateTime, "j"), $format);
        $format = str_replace("##", date_format($this->dateTime, "h"), $format);
        $format = str_replace("#@", $this->ordinalSuffix((int)date_format($this->dateTime, "h")), $format);
        $format = str_replace("#", date_format($this->dateTime, "g"), $format);
        $format = str_replace("==", date_format($this->dateTime, "H"), $format);
        $format = str_replace("=@", $this->ordinalSuffix((int)date_format($this->dateTime, "G")), $format);
        $format = str_replace("=", (int)date_format($this->dateTime, "G"), $format);
        $format = str_replace("%%", date_format($this->dateTime, "i"), $format);
        $format = str_replace("%@", $this->ordinalSuffix(date_format($this->dateTime, "i")), $format);
        $format = str_replace("%", (int)date_format($this->dateTime, "i"), $format);
        $format = str_replace("^^^^", date_format($this->dateTime, "F"), $format);
        $format = str_replace("^^^", date_format($this->dateTime, "M"), $format);
        $format = str_replace("^^", date_format($this->dateTime, "m"), $format);
        $format = str_replace("^@", $this->ordinalSuffix(date_format($this->dateTime, "n")), $format);
        $format = str_replace("^", date_format($this->dateTime, "n"), $format);
        $format = str_replace("&&", date_format($this->dateTime, "s"), $format);
        $format = str_replace("&@", $this->ordinalSuffix(date_format($this->dateTime, "s")), $format);
        $format = str_replace("&", (int)date_format($this->dateTime, "s"), $format);
        $format = str_replace("***", date_format($this->dateTime, "P"), $format);
        $format = str_replace("**", date_format($this->dateTime, "a"), $format);
        $format = str_replace("*", str_replace("m", "", date_format($this->dateTime, "a")), $format);
        $format = str_replace("__", date_format($this->dateTime, "A"), $format);
        $format = str_replace("_", str_replace("M", "", date_format($this->dateTime, "A")), $format);
        $format = str_replace("~~~~@", $this->ordinalSuffix(date_format($this->dateTime, "Y")), $format);
        $format = str_replace("~~~~", date_format($this->dateTime, "Y"), $format);
        $format = str_replace("~~", date_format($this->dateTime, "y"), $format);
        
        //replace keys in string
        foreach($savedText as $key => $value) {
            $format = str_replace($key, $savedText[$key], $format);
        }
        
        return $format;
    }
    
    /**
     * retuns the suffix of a number
     * @param int $value the number to create a suffix for
     * @return string Transformed input
     */
    function ordinalSuffix($value) {
        //format numbers with 'st', 'nd', 'rd', 'th'
        $abr = "";
        $strNumber = $value;
        
        $strLastNumber = substr($strNumber, -1);
        $strLastTwoNumbers = substr($strNumber, -1);
        if(strlen($strNumber) >= 2) {
            $strLastTwoNumbers = substr($strNumber, strlen($strNumber) -2);
        }
        else {
            $strLastTwoNumbers = $strLastNumber;
        }
        
        switch($strLastNumber) {
            case "1":
                if($strLastTwoNumbers == "11") {$abr = "th";} else {$abr = "st";}
                break;
            case "2":
                if($strLastTwoNumbers == "12") {$abr = "th";} else {$abr = "nd";}
                break;
            case "3":
                if($strLastTwoNumbers == "13") {$abr = "th";} else {$abr = "rd";}
                break;
            case "4": case "5": case "6": case "7": case "8": case "9": case "0":
                $abr = "th";
                break;
            default:
                $abr = "";
                break;
        }
        
        return $strNumber . $abr;
    }
}
