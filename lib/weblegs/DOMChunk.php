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

class DOMChunk extends DOMTemplate {
    public $blank;
    public $all;
    public $current;
    public $original;
    
    /**
     * construct the object
     * @param DOMTemplate $parentDOMTemplate what object chunk is connectted to
     */
    public function __construct($parentDOMTemplate) {
        //set default property values
        $this->blank = null;
        $this->all = null;
        $this->current = null;
        
        //call parent constructor
        parent::__construct();
        
        //set basepath
        $this->basePath = $parentDOMTemplate->xpathQuery;
        
        //use references here - we do NOT want to make copies
        $this->domXpath = $parentDOMTemplate->domXpath;
        $this->domDocument = $parentDOMTemplate->domDocument;
        $this->original = $this->getNode();
        $this->blank = $this->original->cloneNode(true);
    }
    
    /**
     * sets the xpath to the root of the chunk
     * @return this Object chaining
     */
    public function root(){
        //clear out results nodes
        $this->resultNodes = null;
        
        //clear out xpath query
        $this->xpathQuery = "";
        
        return $this;
    }
    
    /**
     * starts a new chunk
     */
    public function begin() {
        //make a copy of blank
        $this->current = $this->blank->cloneNode(true);
        
        //put current in the tree
        $this->original->parentNode->replaceChild($this->current, $this->original); 
        
        //current is the new original
        $this->original = $this->current;
    }
    
    /**
     * ends the current chunk
     */
    public function end() {
        //save a copy of current now that its been edited
        $this->all[] = $this->current->cloneNode(true);
    }
    
    /**
     * renders into the parent document
     */
    public function render() {
        for($i = 0; $i < count($this->all); $i++) {
            $this->original->parentNode->insertBefore($this->all[$i], $this->original);
        }
        $this->original->parentNode->removeChild($this->original);
    }
}
