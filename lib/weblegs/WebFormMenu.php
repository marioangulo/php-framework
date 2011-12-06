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

class WebFormMenu {
    public $name;
    public $size;
    public $selectMultiple;
    public $attributes;
    public $selectedValues;
    public $options;
    
    /**
     * construct the object
     * @param string $name
     * @param int $size
     * @param bool $selectMultiple
     */
    public function __construct($name = "", $size = 1, $selectMultiple = false) {
        $this->name = $name;
        $this->size = $size;
        $this->selectMultiple = $selectMultiple;
        $this->attributes = array();
        $this->selectedValues = array();
        $this->options = array();
    }
    
    /**
     * adds an item to the attribute array
     * @param string $name
     * @param int $value
     */
    public function addAttribute($name, $value) {
        $this->attributes[$name] = $value;
    }
    
    /**
     * adds an item to the options array
     * @param string $name
     * @param int $value
     * @param int $custom
     */
    public function addOption($label, $value, $custom = "") {
        $this->options[] = array(
            "label"     => $label,
            "value"     => $value,
            "custom"     => $custom,
            "groupflag" => false
        );
    }
    
    /**
     * adds an item to the options array with an exta group flag
     * @param string $label
     * @param int $custom
     */
    public function addOptionGroup($label, $custom = "") {
        $this->options[] = array(
            "label"     => $label,
            "value"     => "",
            "custom"     => $custom,
            "groupflag" => true
        );
    }
    
    /**
     * adds an item to the selected values array
     * @param string $value
     */
    public function addSelectedValue($value) {
        $this->selectedValues[] = $value;
    }
    
    /**
     * gets the drop down option tags
     * @return string The tags
     */
    public function getOptionTags() {
        //main container
        $tmpOptionTags = "";
        
        //last group reference
        $lastGroupReference = null;
        
        //build options
        for($i = 0 ; $i < count($this->options); $i++) {
            //check for groups
            if($this->options[$i]["groupflag"] == true) {
                //was there a group before this
                if(is_null($lastGroupReference)) {
                    $lastGroupReference = $i;
                }
                else {
                    $tmpOptionTags .= "</optgroup>";
                    $lastGroupReference = $i;
                }
                $tmpOptionTags .= "<optgroup label=\"". Codec::htmlEncode($this->options[$i]["label"]) ."\" ". $this->options[$i]["custom"] .">";
            }
            //normal option
            else {
                $isSelected = in_array($this->options[$i]["value"], $this->selectedValues);
                $tmpOptionTags .= "<option value=\"". Codec::htmlEncode($this->options[$i]["value"]) ."\"". ($isSelected == true ? " selected=\"selected\"" : "") ." ". $this->options[$i]["custom"] .">". Codec::htmlEncode($this->options[$i]["label"]) ."</option>";
            }
        }
        //should end a group
        if(!is_null($lastGroupReference)) {
            $tmpOptionTags .= "</optgroup>";
        }
        return $tmpOptionTags;
    }
    
    /**
     * generates an html list menu (aka drop down) field
     * @return string The generated html
     */
    public function toString() {
        $tmpDropDown = "";
        
        //start the beginning select tag
        $tmpDropDown .= "<select name=\"". $this->name ."\" size=\"". $this->size ."\"". ($this->selectMultiple ? " multiple=\"multiple\"" : "");
        
        //add any custom attributes
        foreach($this->attributes as $key => $value) {
            $tmpDropDown .= " ". $key ."=\"". $value ."\"";
        }
        
        //finish the begining select tag
        $tmpDropDown .= ">";
        
        //add the options
        $tmpDropDown .= $this->getOptionTags();
        
        //finish building the select tag
        $tmpDropDown .= "</select>";
        
        return $tmpDropDown;
    }
}
