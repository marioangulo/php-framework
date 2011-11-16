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

//--> Begin Class :: TextChunk
	class TextChunk {
		//--> Begin :: Properties
			public $Blank;
			public $Current;
			public $All;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
		public function TextChunk(&$Source = "", $Start = "", $End = "") {
			$this->Blank = "";
			$this->Current = "";
			$this->All = "";
			
			//get arg count
			$ArgCount = func_num_args();
			
			//how many args?
			if($ArgCount == 0) {
				//do nothing
			}
			else if($ArgCount == 3) {
				$MyStart = 0;
				$MyEnd = 0;
				
				if(strpos($Source, $Start) != false && strpos($Source, $End) != false) {
					$MyStart = (strpos($Source, $Start)) + strlen($Start);
					$MyEnd = strpos($Source, $End);
					
					try {
						$this->Blank = substr($Source, $MyStart, $MyEnd - $MyStart);
					}
					catch(Exception $e) {
						throw new Exception("WebLegs.TextChunk.Constructor(): Boundry string mismatch.");
					}
				}
				else {
					throw new Exception("WebLegs.TextChunk.Constructor(): Boundry strings not present in source string.");
				}
			}
		}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Begin
			public function Begin() {
				$this->Current = $this->Blank;
			}
		//<-- End Method :: Begin
		
		//##################################################################################
		
		//--> Begin Method :: End
			public function End() {
				$this->All .= $this->Current;
			}
		//<-- End Method :: End
		
		//##################################################################################
		
		//--> Begin Method :: Replace
			public function Replace($This, $WithThis) {
				$this->Current = str_replace($This, $WithThis, $this->Current);
				return $this;
			}
		//<-- End Method :: Replace
		
		//##################################################################################
		
		//--> Begin Method :: ToString
			public function ToString() {
				return $this->All;
			}
		//<-- End Method :: ToString
	}
//<-- End Class :: TextChunk

//##########################################################################################
?>