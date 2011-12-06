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

class WebForm {
    /**
     * generates an html radio input field
     * @param string $name
     * @param string $value
     * @param string $checked
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function radioButton($name, $value, $checked, $disabled, $custom = "") {
        return "<input type=\"radio\" name=\"". $name ."\" value=\"". Codec::htmlEncode($value) ."\" ". ($checked == true ? " checked=\"checked\" " : "") ." ". ($disabled == true ? " disabled=\"disabled\" " : "") ." ". $custom ."/>";
    }
    
    /**
     * generates an html checkbox field
     * @param string $name
     * @param string $value
     * @param string $checked
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function checkBox($name, $value, $checked, $disabled, $custom = "") {
        return "<input type=\"checkbox\" name=\"". $name ."\" value=\"". Codec::htmlEncode($value) ."\" ". ($checked == true ? " checked=\"checked\" " : "") ." ". ($disabled == true ? " disabled=\"disabled\" " : "") ." ". $custom ."/>";
    }
    
    /**
     * generates an html hidden field
     * @param string $name
     * @param string $value
     * @param string $custom
     * @return string The generated html
     */
    public static function hiddenField($name, $value, $custom = "") {
        return "<input type=\"hidden\" name=\"". $name ."\" value=\"". Codec::htmlEncode($value) ."\" ". $custom ."/>";
    }
    
    /**
     * generates an html text input field
     * @param string $name
     * @param string $value
     * @param string|int $size
     * @param string|int $maxLength
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function textBox($name, $value, $size, $maxLength, $disabled, $custom = "") {
        return "<input type=\"text\" name=\"". $name ."\" value=\"". Codec::htmlEncode($value) ."\" size=\"". $size ."\" maxlength=\"". $maxLength ."\" ". ($disabled == true ? "disabled=\"disabled\" " : "") ." ". $custom ."/>";
    }
    
    /**
     * generates an html password input field
     * @param string $name
     * @param string $value
     * @param string|int $size
     * @param string|int $maxLength
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function passwordBox($name, $value, $size, $maxLength, $disabled, $custom = "") {
        return "<input type=\"password\" name=\"". $name ."\" value=\"". Codec::htmlEncode($value) ."\" size=\"". $size ."\" maxlength=\"". $maxLength ."\" ". ($disabled == true ? "disabled=\"disabled\" " : "") ." ". $custom ."/>";
    }
    
    /**
     * generates an html text area field
     * @param string $name
     * @param string $value
     * @param string|int $numCols
     * @param string|int $numRows
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function textArea($name, $value, $numCols, $numRows, $disabled, $custom = "") {
        return "<textarea name=\"". $name ."\" cols=\"". $numCols ."\" rows=\"". $numRows ."\" ". ($disabled == true ? " disabled=\"disabled\" " : "") ." ". $custom .">". Codec::htmlEncode($value) ."</textarea>";
    }
    
    /**
     * generates an html file field
     * @param string $name
     * @param string|int $size
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function fileField($name, $size, $disabled, $custom = "") {
        return "<input type=\"file\" name=\"". $name ."\" size=\"". $size ."\" ". ($disabled == true ? " disabled=\"disabled\" " : "") . ($custom != "" ? $custom : "") ." />";
    }
    
    /**
     * generates an html button field
     * @param string $name
     * @param string $value
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function button($name, $value, $disabled, $custom = "") {
        return "<input type=\"button\" name=\"". $name ."\" value=\"". Codec::htmlEncode($value) ."\" ". ($disabled == true ? "disabled=\"disabled\" " : "") ." ". $custom ."/>";
    }
    
    /**
     * generates an html submit button field
     * @param string $name
     * @param string $value
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function submitButton($name, $value, $disabled, $custom = "") {
        return "<input type=\"submit\" name=\"". $name ."\" value=\"". Codec::htmlEncode($value) ."\" ". ($disabled == true ? "disabled=\"disabled\" " : "") ." ". $custom ."/>";
    }
    
    /**
     * generates an html reset button field
     * @param string $name
     * @param string $value
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function resetButton($name, $value, $disabled, $custom = "") {
        return "<input type=\"reset\" name=\"". $name ."\" value=\"". Codec::htmlEncode($value) ."\" ". ($disabled == true ? "disabled=\"disabled\" " : "") ." ". $custom ."/>";
    }
    
    /**
     * generates an html list menu (aka drop down) field
     * @param string $name
     * @param string $currentValue
     * @param int|string $size
     * @param string $options seperated by a '|' character
     * @param string $values seperated by a '|' character
     * @param string $disabled
     * @param string $custom
     * @return string The generated html
     */
    public static function dropDown($name, $currentValue, $size, $options, $values, $disabled, $custom = "") {
        $mydd = "";
        $mydd .= "<select name=\"". $name ."\" size=\"". $size ."\" ". ($disabled == true ? " disabled=\"disabled\" " : "") ." ". $custom .">";
            //check for options
            if(strlen($values) == 0) {
                $values = $options;
            }
            //explode strings into arrays (split)
            $option_array = explode("|", $options);
            $value_array = explode("|", $values);
            //count array items
            $option_count = count($option_array);
            $value_count = count($value_array);
            //check if option/vlaue count match
            if($option_count != $value_count) {
                throw new Exception("Weblegs.WebForm.dropDown(): Option count is different than value count.");
            }
            else {
                //loop through arrays and build options
                for($i = 0 ; $i < $option_count ; $i++) {
                    $mydd .= "<option value=\"". Codec::htmlEncode($value_array[$i]) ."\" ". ($value_array[$i] == $currentValue ? " selected=\"selected\" " : "") .">". Codec::htmlEncode($option_array[$i]) ."</option>";
                }
            }
        $mydd .= "</select>";
        return $mydd;
    }
}
