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

//--> Begin Class :: DataValidator
	class DataValidator {
		//--> Begin :: Properties
			//no properties
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			//no constructor
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: IsValidEmail
			public static function IsValidEmail($EmailAddress) {
				$MatchesFound = preg_match("/^[a-zA-Z0-9+._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/", $EmailAddress, $Matches);
				if($MatchesFound > 0) {
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: IsValidEmail
		
		//##################################################################################
		
		//--> Begin Method :: IsValidURL
			public static function IsValidURL($URL) {
				$MatchesFound = preg_match("/^https?:\/\/[a-zA-Z0-9._%-]+\.[a-zA-Z0-9.-]+(\/.*)*$/", $URL, $Matches);
				if($MatchesFound > 0) {
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: IsValidURL
		
		//##################################################################################
		
		//--> Begin Method :: IsPhone
			public static function IsPhone($Phone, $CountryCode = "us") {
				switch(strtoupper($CountryCode)) {
					case "US":
						$MatchesFound = preg_match("/^([01][\s\.-]?)?(\(\d{3}\)|\d{3})[\s\.-]?\d{3}[\s\.-]?\d{4}$/", $Phone, $Matches);
						if($MatchesFound > 0) {
							return true;
						}
						else {
							return false;
						}
					default:
						//do nothing
						return false;
				}
			}
		//<-- End Method :: IsPhone
		
		//##################################################################################
		
		//--> Begin Method :: IsPostalCode
			public static function IsPostalCode($PostalCode, $CountryCode = "us") {
				switch(strtoupper($CountryCode)) {
					case "US":
						$MatchesFound = preg_match("/^\d{5}(-?\d{4})?$/", $PostalCode, $Matches);
						if($MatchesFound > 0) {
							return true;
						}
						else {
							return false;
						}
					default:
						//do nothing
						return false;
				}
			}
		//<-- End Method :: IsPostalCode
		
		//##################################################################################
		
		//--> Begin Method :: IsAlpha
			public static function IsAlpha($Input) {
				$MatchesFound = preg_match("/^[a-zA-Z]+$/", $Input, $Matches);
				if($MatchesFound > 0) {
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: IsAlpha
		
		//##################################################################################
		
		//--> Begin Method :: IsIPv4
			public static function IsIPv4($Input) {
				$MatchesFound = preg_match("/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.) {3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/", $Input, $Matches);
				if($MatchesFound > 0) {
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: IsIPv4
		
		//##################################################################################
		
		//--> Begin Method :: IsInt
			public static function IsInt($Input) {
				if((int)$Input != 0 && $Input != "0"){
					return is_int((int)$Input);
				}
				else{
					return false;
				}
			}
		//<-- End Method :: IsInt
		
		//##################################################################################
		
		//--> Begin Method :: IsDouble
			public static function IsDouble($Input) {
				return is_double($Input);
			}
		//<-- End Method :: IsDouble
		
		//##################################################################################
		
		//--> Begin Method :: IsNumeric
			public static function IsNumeric($Input) {
				if(is_double($Input)){
					return true;
				}
				else if(is_int((int)$Input)){
					return true;
				}
				return false;
			}
		//<-- End Method :: IsNumeric
		
		//##################################################################################
		
		//--> Begin Method :: IsDateTime
			public static function IsDateTime($Input) {
				$ParseReply = date_parse($Input);
				if($ParseReply["error_count"] == "0") {
					//return true
					return true;
				}
				else {
					return false;
				}
			}
		//<-- End Method :: IsDateTime
		
		//##################################################################################
		
		//--> Begin Method :: MinLength
			public static function MinLength($Value, $Length) {
				if(strlen($Value) >= $Length){
					return true;
				}
				else{
					return false;
				}
			}
		//<-- End Method :: MinLength
		
		//##################################################################################
	
		//--> Begin Method :: MaxLength
			public static function MaxLength($Value, $Length) {
				if(strlen($Value) <= $Length){
					return true;
				}
				else{
					return false;
				}
			}
		//<-- End Method :: MaxLength
		
		//##################################################################################
		
		//--> Begin Method :: Length
			public static function Length($Value, $Length) {
				if(strlen($Value) == $Length){
					return true;
				}
				else{
					return false;
				}
			}
		//<-- End Method :: Length
	}
//<-- End Class :: DataValidator

//##########################################################################################
?>