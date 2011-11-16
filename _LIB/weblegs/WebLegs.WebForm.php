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

//--> Begin Class :: WebForm
	class WebForm {
		//--> Begin :: Properties
			//no properties
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			//no constructor
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: RadioButton
			public static function RadioButton($Name, $Value, $Checked, $Disabled, $Custom = "") {
				return "<input type=\"radio\" name=\"". $Name ."\" value=\"". Codec::HTMLEncode($Value) ."\" ". ($Checked == true ? " checked=\"checked\" " : "") ." ". ($Disabled == true ? " disabled=\"disabled\" " : "") ." ". $Custom ."/>";
			}
		//<-- End Method :: RadioButton
		
		//##################################################################################
		
		//--> Begin Method :: CheckBox
			public static function CheckBox($Name, $Value, $Checked, $Disabled, $Custom = "") {
				return "<input type=\"checkbox\" name=\"". $Name ."\" value=\"". Codec::HTMLEncode($Value) ."\" ". ($Checked == true ? " checked=\"checked\" " : "") ." ". ($Disabled == true ? " disabled=\"disabled\" " : "") ." ". $Custom ."/>";
			}
		//<-- End Method :: CheckBox
		
		//##################################################################################
		
		//--> Begin Method :: HiddenField
			public static function HiddenField($Name, $Value, $Custom = "") {
				return "<input type=\"hidden\" name=\"". $Name ."\" value=\"". Codec::HTMLEncode($Value) ."\" ". $Custom ."/>";
			}
		//<-- End Method :: HiddenField
		
		//##################################################################################
		
		//--> Begin Method :: TextBox
			public static function TextBox($Name, $Value, $Size, $MaxLength, $Disabled, $Custom = "") {
				return "<input type=\"text\" name=\"". $Name ."\" value=\"". Codec::HTMLEncode($Value) ."\" size=\"". $Size ."\" maxlength=\"". $MaxLength ."\" ". ($Disabled == true ? "disabled=\"disabled\" " : "") ." ". $Custom ."/>";
			}
		//<-- End Method :: TextBox
		
		//##################################################################################
		
		//--> Begin Method :: PasswordBox
			public static function PasswordBox($Name, $Value, $Size, $MaxLength, $Disabled, $Custom = "") {
				return "<input type=\"password\" name=\"". $Name ."\" value=\"". Codec::HTMLEncode($Value) ."\" size=\"". $Size ."\" maxlength=\"". $MaxLength ."\" ". ($Disabled == true ? "disabled=\"disabled\" " : "") ." ". $Custom ."/>";
			}
		//<-- End Method :: PasswordBox
		
		//##################################################################################
		
		//--> Begin Method :: TextArea
			public static function TextArea($Name, $Value, $NumCols, $NumRows, $Disabled, $Custom = "") {
				return "<textarea name=\"". $Name ."\" cols=\"". $NumCols ."\" rows=\"". $NumRows ."\" ". ($Disabled == true ? " disabled=\"disabled\" " : "") ." ". $Custom .">". Codec::HTMLEncode($Value) ."</textarea>";
			}
		//<-- End Method :: TextArea
		
		//##################################################################################
		
		//--> Begin Method :: FileField
			public static function FileField($Name, $Size, $Disabled, $Custom = "") {
				return "<input type=\"file\" name=\"". $Name ."\" size=\"". $Size ."\" ". ($Disabled == true ? " disabled=\"disabled\" " : "") . ($Custom != "" ? $Custom : "") ." />";
			}
		//<-- End Method :: FileField
		
		//##################################################################################
		
		//--> Begin Method :: Button
			public static function Button($Name, $Value, $Disabled, $Custom = "") {
				return "<input type=\"button\" name=\"". $Name ."\" value=\"". Codec::HTMLEncode($Value) ."\" ". ($Disabled == true ? "disabled=\"disabled\" " : "") ." ". $Custom ."/>";
			}
		//<-- End Method :: Button
		
		//##################################################################################
		
		//--> Begin Method :: SubmitButton
			public static function SubmitButton($Name, $Value, $Disabled, $Custom = "") {
				return "<input type=\"submit\" name=\"". $Name ."\" value=\"". Codec::HTMLEncode($Value) ."\" ". ($Disabled == true ? "disabled=\"disabled\" " : "") ." ". $Custom ."/>";
			}
		//<-- End Method :: SubmitButton
		
		//##################################################################################
		
		//--> Begin Method :: ResetButton
			public static function ResetButton($Name, $Value, $Disabled, $Custom = "") {
				return "<input type=\"reset\" name=\"". $Name ."\" value=\"". Codec::HTMLEncode($Value) ."\" ". ($Disabled == true ? "disabled=\"disabled\" " : "") ." ". $Custom ."/>";
			}
		//<-- End Method :: ResetButton
		
		//##################################################################################
		
		//--> Begin Method :: DropDown
			public static function DropDown($Name, $CurrentValue, $Size, $Options, $Values, $Disabled, $Custom = "") {
				$mydd = "";
				$mydd .= "<select name=\"". $Name ."\" size=\"". $Size ."\" ". ($Disabled == true ? " disabled=\"disabled\" " : "") ." ". $Custom .">";
					//check for options
					if(strlen($Values) == 0) {
						$Values = $Options;
					}
					//explode strings into arrays (split)
					$option_array = explode("|", $Options);
					$value_array = explode("|", $Values);
					//count array items
					$option_count = count($option_array);
					$value_count = count($value_array);
					//check if option/vlaue count match
					if($option_count != $value_count) {
						throw new Exception("WebLegs.WebForm.DropDown(): Option count is different than value count.");
					}
					else {
						//loop through arrays and build options
						for($i = 0 ; $i < $option_count ; $i++) {
							$mydd .= "<option value=\"". Codec::HTMLEncode($value_array[$i]) ."\" ". ($value_array[$i] == $CurrentValue ? " selected=\"selected\" " : "") .">". Codec::HTMLEncode($option_array[$i]) ."</option>";
						}
					}
				$mydd .= "</select>";
				return $mydd;
			}
		//<-- End Method :: DropDown
	}
//<-- End Class :: WebForm

//##########################################################################################
?>