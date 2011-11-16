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

//--> Begin Class :: DOMChunk
	class DOMChunk extends DOMTemplate {
		//--> Begin :: Properties
			public $Blank;
			public $All;
			public $Current;
			public $Original;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function DOMChunk($ThisDOMTemplate) {
				//set default property values
				$this->Blank = null;
				$this->All = null;
				$this->Current = null;
		
				//call constructor
				parent::__construct();
				
				//set basepath
				$this->BasePath = $ThisDOMTemplate->XPathQuery;
				
				//use references here - we do NOT want to make copies
				$this->DOMXPath = $ThisDOMTemplate->DOMXPath;
				$this->DOMDocument = $ThisDOMTemplate->DOMDocument;
				$this->Original = $this->GetNode();
				$this->Blank = $this->Original->cloneNode(true);
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Root
			public function Root(){
				//clear out results nodes
				$this->ResultNodes = null;
				
				//clear out xpath query
				$this->XPathQuery = "";
				
				return $this;
			}
		//--> Begin Method :: Root
		
		//##################################################################################
		
		//--> Begin Method :: Begin
			public function Begin() {
				//make a copy of blank
				$this->Current = $this->Blank->cloneNode(true);

				//put current in the tree
				$this->Original->parentNode->replaceChild($this->Current, $this->Original); 
				
				//current is the new original
				$this->Original = $this->Current;
			}
		//<-- End Method :: Begin
		
		//##################################################################################
		
		//--> Begin Method :: End
			public function End() {
				//save a copy of current now that its been edited
				$this->All[] = $this->Current->cloneNode(true);
			}
		//<-- End Method :: End
		
		//##################################################################################
		
		//--> Begin Method :: Render
			public function Render() {
				for($i = 0; $i < count($this->All); $i++) {
					$this->Original->parentNode->insertBefore($this->All[$i], $this->Original);
				}
				$this->Original->parentNode->removeChild($this->Original);
			}
		//<-- End Method :: Render
	}
//<-- End Class :: DOMChunk

//##########################################################################################
?>