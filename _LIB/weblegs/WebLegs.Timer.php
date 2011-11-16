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

//--> Begin Class :: Timer
	class Timer {
		//--> Begin :: Properties
			public $TimeStart;
			public $TimeStop;
			public $TimeSpent;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function Timer() {
				$this->TimeStart = 0;
				$this->TimeStop = 0;
				$this->TimeSpent = 0;
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: Start
			public function Start() {
				$StartTime = microtime();
				$StartTime = explode(' ', $StartTime);
				$StartTime = $StartTime[1] + $StartTime[0];
				$this->TimeStart = $StartTime;
				$this->TimeSpent = 0;
			}
		//<-- End Method :: Start
		
		//##################################################################################
		
		//--> Begin Method :: Stop
			public function Stop() {
				$EndTime = microtime();
				$EndTime = explode(' ', $EndTime);
				$EndTime = $EndTime[1] + $EndTime[0];
				$this->TimeStop = $EndTime;
				$this->TimeSpent = ($this->TimeStop - $this->TimeStart);
			}
		//<-- End Method :: Stop
	}
//<-- End Class :: Timer

//##########################################################################################
?>