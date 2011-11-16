<?php
//##########################################################################################

/*
Copyright (C) 2005-2011 WebLegs, Inc.
This program is free software: you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation, either
version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.
If not, see <http://www.gnu.org/licenses/>.
*/

//##########################################################################################

//--> Begin Class :: DateTimeDriver
	class DateTimeDriver{
		//--> Begin :: Properties
		
			public $DateTime;
			public $Year;
			public $Month;
			public $Day;
			public $Hour;
			public $Minute;
			public $Second;
			
			//informational
			public $IsLeapYear;
			public $DayOfWeek;
			public $DayOfYear;
			public $DaysInYear;
			public $StartDayOfMonth;
			public $EndDayOfMonth;
			public $WeekOfYear;
			public $DaysInMonth;
			public $WeeksInYear;
			public $DayName;
			public $DayNameAbbr;
			public $MonthName;
			public $MonthNameAbbr;
			
			//helpers
			public $DayNameAbbrs;
			public $DayNames;
			public $MonthNameAbbrs;
			public $MonthNames;
			public $MinValue;
			public $MaxValue;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			function DateTimeDriver() {
				//build the date data
					//day name abbrs
					$this->DayNameAbbrs[1] = "Sun";
					$this->DayNameAbbrs[2] = "Mon";
					$this->DayNameAbbrs[3] = "Tue";
					$this->DayNameAbbrs[4] = "Wed";
					$this->DayNameAbbrs[5] = "Thu";
					$this->DayNameAbbrs[6] = "Fri";
					$this->DayNameAbbrs[7] = "Sat";
					
					//day names
					$this->DayNames[1] = "Sunday";
					$this->DayNames[2] = "Monday";
					$this->DayNames[3] = "Tuesday";
					$this->DayNames[4] = "Wednesday";
					$this->DayNames[5] = "Thursday";
					$this->DayNames[6] = "Friday";
					$this->DayNames[7] = "Saturday";
					
					//month name abbrs
					$this->MonthNameAbbrs[1] = "Jan";
					$this->MonthNameAbbrs[2] = "Feb";
					$this->MonthNameAbbrs[3] = "Mar";
					$this->MonthNameAbbrs[4] = "Apr";
					$this->MonthNameAbbrs[5] = "May";
					$this->MonthNameAbbrs[6] = "Jun";
					$this->MonthNameAbbrs[7] = "Jul";
					$this->MonthNameAbbrs[8] = "Aug";
					$this->MonthNameAbbrs[9] = "Sep";
					$this->MonthNameAbbrs[10] = "Oct";
					$this->MonthNameAbbrs[11] = "Nov";
					$this->MonthNameAbbrs[12] = "Dec";
					
					//month names
					$this->MonthNames[1] = "January";
					$this->MonthNames[2] = "February";
					$this->MonthNames[3] = "March";
					$this->MonthNames[4] = "April";
					$this->MonthNames[5] = "May";
					$this->MonthNames[6] = "June";
					$this->MonthNames[7] = "July";
					$this->MonthNames[8] = "August";
					$this->MonthNames[9] = "September";
					$this->MonthNames[10] = "October";
					$this->MonthNames[11] = "November";
					$this->MonthNames[12] = "December";
				//end build the date data
				
				//min/max
				$this->MinValue = "1901-12-13 20:45:54";
				$this->MaxValue = "2038-01-19 03:14:07";
				
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//set the default timezone
				date_default_timezone_set("UTC");
				
				//DateTimeDriver()
				if($NumberOfArgs == 0){
					$this->DateTime = new DateTime();
					$this->RefreshProperties();
				}
				//DateTimeDriver(Value)
				else if($NumberOfArgs == 1){
					$this->Parse($Args[0]);			
				}
				//DateTimeDriver($Year, $Month, $Day)
				else if($NumberOfArgs == 3){
					$this->DateTime = new DateTime();
					$this->Set($Args[0], $Args[1], $Args[2]);
				}
				//DateTimeDriver($Year, $Month, $Day, $Hour, $Minute, $Second)
				else if($NumberOfArgs == 6){
					$this->DateTime = new DateTime();
					$this->Set($Args[0], $Args[1], $Args[2], $Args[3], $Args[4], $Args[5]);
				}
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Set
			public function Set() {
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				if($NumberOfArgs >= 3){
					date_date_set($this->DateTime, (int)$Args[0], (int)$Args[1], (int)$Args[2]);
				}
				
				if($NumberOfArgs == 6){
					date_time_set($this->DateTime, (int)$Args[3], (int)$Args[4], (int)$Args[5]);
				}
				else{
					date_time_set($this->DateTime, 0, 0, 0);
				}
				
				//refresh
				$this->RefreshProperties();
				
				return $this;
			}
		//<-- End Method :: Set
		
		//##################################################################################
		
		//--> Begin Method :: RefreshProperties
			function RefreshProperties() {
				$DateTimeArray = explode("-", date_format($this->DateTime, "Y-m-d-H-i-s-L-z-w-W-t"));
				
				$this->Year = $DateTimeArray[0];
				$this->Month = (int)$DateTimeArray[1];
				$this->Day = (int)$DateTimeArray[2];
				$this->Hour = (int)$DateTimeArray[3];
				$this->Minute = (int)$DateTimeArray[4];
				$this->Second = (int)$DateTimeArray[5];
				
				//informational
					$this->IsLeapYear = ($DateTimeArray[6] == "0" ? false : true);
					$this->DayOfWeek = $DateTimeArray[8];
					$this->DayOfYear = $DateTimeArray[7];
					$this->DaysInYear = 0;

					for($i = 1 ; $i < 12 ; $i++) {
						$this->DaysInYear += cal_days_in_month(CAL_GREGORIAN, $i, (int)$this->Year);
					}
					
					$TmpDateTime = new DateTime();
					$TmpDateTime->setDate($this->Year, $this->Month, 1);
					$this->StartDayOfMonth = date_format($TmpDateTime, "w");
					
					$TmpDateTime->setDate($this->Year, $this->Month, 1);
					$TmpDateTime->modify("1 month");
					$TmpDateTime->modify("-1 day");
					$this->EndDayOfMonth = date_format($TmpDateTime, "w");
					$this->WeekOfYear = $DateTimeArray[9];
					$this->DaysInMonth = $DateTimeArray[10];

					//get weeks in year
					$TmpDays = 0;
					for($i = 1; $i <= 12; $i++){
						$TmpDays += cal_days_in_month(CAL_GREGORIAN, (int)$i, $this->Year);
					}
					$this->WeeksInYear = ($TmpDays / 7);
					$this->DayName = $this->DayNames[(int)$this->DayOfWeek + 1];
					$this->DayNameAbbr = $this->DayNameAbbrs[(int)$this->DayOfWeek + 1];
					$this->MonthName = $this->MonthNames[(int)$this->Month];
					$this->MonthNameAbbr = $this->MonthNameAbbrs[(int)$this->Month];
				//end informational
				
			}
		//<-- End Method :: RefreshProperties
		
		//##################################################################################
		
		//--> Begin Method :: AddSeconds
			function AddSeconds($Value) {
				date_modify($this->DateTime, $Value." second");
				$this->RefreshProperties();
				
				//return for method chaining
				return $this;
			}
		//<-- End Method :: AddSeconds
		
		//##################################################################################
		
		//--> Begin Method :: AddMinutes
			function AddMinutes($Value) {
				date_modify($this->DateTime, $Value." minute");
				$this->RefreshProperties();
				
				//return for method chaining
				return $this;
			}
		//<-- End Method :: AddMinutes
		
		//##################################################################################
		
		//--> Begin Method :: AddHours
			function AddHours($Value) {
				date_modify($this->DateTime, $Value." hour");
				$this->RefreshProperties();
				
				//return for method chaining
				return $this;
			}
		//<-- End Method :: AddHours
		
		//##################################################################################
		
		//--> Begin Method :: AddDays
			function AddDays($Value) {
				date_modify($this->DateTime, $Value." day");
				$this->RefreshProperties();
				
				//return for method chaining
				return $this;
			}
		//<-- End Method :: AddDays
		
		//##################################################################################
		
		//--> Begin Method :: AddMonths
			function AddMonths($Value) {
				date_modify($this->DateTime, $Value." month");
				$this->RefreshProperties();
				
				//return for method chaining
				return $this;
			}
		//<-- End Method :: AddMonths
		
		//##################################################################################
		
		//--> Begin Method :: AddYears
			function AddYears($Value) {
				date_modify($this->DateTime, $Value." year");
				$this->RefreshProperties();
				
				//return for method chaining
				return $this;
			}
		//<-- End Method :: AddYears
		
		//##################################################################################
		
		//--> Begin Method :: Diff
			function Diff(DateTimeDriver $ToCompare) {
				$d1 = strtotime($this->ToString());
				$d2 = strtotime($ToCompare->ToString());
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
		//<-- End Method :: Diff
		
		//##################################################################################
		
		//--> Begin Method :: Now
			function Now() {
				return new DateTimeDriver();
			}
		//<-- End Method :: Now
		
		//##################################################################################
		
		//--> Begin Method :: SetMinValue
			function SetMinValue() {
				$this->Parse($this->MinValue);
				return $this;
			}
		//<-- End Method :: SetMinValue
		
		//##################################################################################
		
		//--> Begin Method :: SetMaxValue
			function SetMaxValue() {
				$this->Parse($this->MaxValue);
				return $this;
			}
		//<-- End Method :: SetMaxValue
		
		//##################################################################################
		
		//--> Begin Method :: Parse
			function Parse($Value) {
				$this->DateTime = new DateTime();
				$arrTime = date_parse(trim($Value));
				
				//if parse fails, try to fix
				if($arrTime["error_count"] > 0) {
					//try to fix a semi-common date format
					$Value = preg_replace("/^(\d{2})-(\d{2})-(\d{4})/", "\\3-\\1-\\2", $Value);
					
					//try again
					$this->DateTime = new DateTime();
					$arrTime = date_parse(trim($Value));
				}
				
				//if this parse fails, set the min value
				if($arrTime["error_count"] > 0) {
					return $this->Parse($this->MinValue);
				}
				
				//set date
				if($arrTime["year"] != "" && $arrTime["month"] != "" && $arrTime["day"] != ""){
					date_date_set($this->DateTime, $arrTime["year"], $arrTime["month"], $arrTime["day"]);
				}
				else{
					//create temp date time
					$TmpDT = new DateTime();
					
					//set to todays date
					date_date_set($this->DateTime, date_format($TmpDT, "Y"), date_format($TmpDT, "n"), date_format($TmpDT, "j"));
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
				date_time_set($this->DateTime, $arrTime["hour"], $arrTime["minute"], $arrTime["second"]);
				
				
				$this->RefreshProperties();
				
				return $this;
			}
		//<-- End Method :: Parse
		
		//##################################################################################
		
		//--> Begin Method :: ToString
			function ToString($Format = "yyyy-MM-dd HH:mm:ss") {
				//support for the [...content...] blocks
					$ValidKeyCharacters = array("a", "b", "c", "e", "f", "g", "i", "j", "k", "l", "p", "q", "r", "u", "v", "w", "x");
					$SavedText = array();
					preg_match_all("(\[.*?\])", $Format, $Matches);
					
					//all matches reside in the first element
					$Matches = $Matches[0];
					
					for($i = 0 ; $i < count($Matches); $i++) {
						//generate random key
						$ThisKey = "";
						for($j = 0 ; $j < 20 ; $j++) {
							$ThisKey .= $ValidKeyCharacters[rand(0, count($ValidKeyCharacters) - 1)];
						}
						
						$ThisMatch = $Matches[$i];
						$SavedText[$ThisKey] =  str_replace(array("[", "]"), "", $Matches[$i]);
						
						$Format = str_replace($Matches[$i], $ThisKey, $Format);
					}
				//end support for the [...content...] blocks
				
				//setup internal token translation
				$Format = str_replace("dddd", "!!!!", $Format);
				$Format = str_replace("ddd", "!!!", $Format);
				$Format = str_replace("dd", "!!", $Format);
				$Format = str_replace("do", "!@", $Format);
				$Format = str_replace("d", "!", $Format);
				$Format = str_replace("hh", "##", $Format);
				$Format = str_replace("ho", "#@", $Format);
				$Format = str_replace("h", "#", $Format);
				$Format = str_replace("HH", "==", $Format);
				$Format = str_replace("HO", "=@", $Format);
				$Format = str_replace("H", "=", $Format);
				$Format = str_replace("mm", "%%", $Format);
				$Format = str_replace("mo", "%@", $Format);
				$Format = str_replace("m", "%", $Format);
				$Format = str_replace("MMMM", "^^^^", $Format);
				$Format = str_replace("MMM", "^^^", $Format);
				$Format = str_replace("MM", "^^", $Format);
				$Format = str_replace("MO", "^@", $Format);
				$Format = str_replace("M", "^", $Format);
				$Format = str_replace("ss", "&&", $Format);
				$Format = str_replace("so", "&@", $Format);
				$Format = str_replace("s", "&", $Format);
				$Format = str_replace("tzo", "***", $Format);
				$Format = str_replace("tt", "**", $Format);
				$Format = str_replace("t", "*", $Format);
				$Format = str_replace("TT", "__", $Format);
				$Format = str_replace("T", "_", $Format);
				$Format = str_replace("yyyyo", "~~~~@", $Format);
				$Format = str_replace("yyyy", "~~~~", $Format);
				$Format = str_replace("yy", "~~", $Format);
				
				//translate internal tokens
				$Format = str_replace("!!!!", date_format($this->DateTime, "l"), $Format);
				$Format = str_replace("!!!", date_format($this->DateTime, "D"), $Format);
				$Format = str_replace("!!", date_format($this->DateTime, "d"), $Format);
				$Format = str_replace("!@", $this->OrdinalSuffix(date_format($this->DateTime, "j")), $Format);
				$Format = str_replace("!", date_format($this->DateTime, "j"), $Format);
				$Format = str_replace("##", date_format($this->DateTime, "h"), $Format);
				$Format = str_replace("#@", $this->OrdinalSuffix((int)date_format($this->DateTime, "h")), $Format);
				$Format = str_replace("#", date_format($this->DateTime, "g"), $Format);
				$Format = str_replace("==", date_format($this->DateTime, "H"), $Format);
				$Format = str_replace("=@", $this->OrdinalSuffix((int)date_format($this->DateTime, "G")), $Format);
				$Format = str_replace("=", (int)date_format($this->DateTime, "G"), $Format);
				$Format = str_replace("%%", date_format($this->DateTime, "i"), $Format);
				$Format = str_replace("%@", $this->OrdinalSuffix(date_format($this->DateTime, "i")), $Format);
				$Format = str_replace("%", (int)date_format($this->DateTime, "i"), $Format);
				$Format = str_replace("^^^^", date_format($this->DateTime, "F"), $Format);
				$Format = str_replace("^^^", date_format($this->DateTime, "M"), $Format);
				$Format = str_replace("^^", date_format($this->DateTime, "m"), $Format);
				$Format = str_replace("^@", $this->OrdinalSuffix(date_format($this->DateTime, "n")), $Format);
				$Format = str_replace("^", date_format($this->DateTime, "n"), $Format);
				$Format = str_replace("&&", date_format($this->DateTime, "s"), $Format);
				$Format = str_replace("&@", $this->OrdinalSuffix(date_format($this->DateTime, "s")), $Format);
				$Format = str_replace("&", (int)date_format($this->DateTime, "s"), $Format);
				$Format = str_replace("***", date_format($this->DateTime, "P"), $Format);
				$Format = str_replace("**", date_format($this->DateTime, "a"), $Format);
				$Format = str_replace("*", str_replace("m", "", date_format($this->DateTime, "a")), $Format);
				$Format = str_replace("__", date_format($this->DateTime, "A"), $Format);
				$Format = str_replace("_", str_replace("M", "", date_format($this->DateTime, "A")), $Format);
				$Format = str_replace("~~~~@", $this->OrdinalSuffix(date_format($this->DateTime, "Y")), $Format);
				$Format = str_replace("~~~~", date_format($this->DateTime, "Y"), $Format);
				$Format = str_replace("~~", date_format($this->DateTime, "y"), $Format);
				
				//replace keys in string
				foreach($SavedText as $Key => $Value) {
					$Format = str_replace($Key, $SavedText[$Key], $Format);
				}
				
				return $Format;
			}
		//<-- End Method :: ToString
		
		//##################################################################################
		//##################################################################################
		//##################################################################################
		
		//--> Begin Method :: OrdinalSuffix
			function OrdinalSuffix($Value) {
				//format numbers with 'st', 'nd', 'rd', 'th'
				$Abr = "";
				$strNumber = $Value;
				
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
						if($strLastTwoNumbers == "11") {$Abr = "th";} else {$Abr = "st";}
						break;
					case "2":
						if($strLastTwoNumbers == "12") {$Abr = "th";} else {$Abr = "nd";}
						break;
					case "3":
						if($strLastTwoNumbers == "13") {$Abr = "th";} else {$Abr = "rd";}
						break;
					case "4": case "5": case "6": case "7": case "8": case "9": case "0":
						$Abr = "th";
						break;
					default:
						$Abr = "";
						break;
				}
				
				return $strNumber . $Abr;
			}
		//<-- End Method :: OrdinalSuffix
	}
//<-- End Class :: DateTimeDriver

//##########################################################################################
?>