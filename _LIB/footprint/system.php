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

//--> Begin Class :: System
	class System {
		//--> Begin :: Properties
			//no properties
		//<-- End :: Properties

		//##################################################################################



		//--> Begin :: Constructor
			public function System() {
				//do nothing
			}
		//<-- End :: Constructor

		//##################################################################################

		//--> Begin Method :: ConvertTZIn
			public function ConvertTZIn($DateTime, $Format = null) {
				//create a temporary date time object
				$tmpDateTime = null;
				$newDateTime = null;

				//make sure this is an object
				if(is_object($DateTime)){
					//was this a datetime driver?
					if(get_class($DateTime) == "DateTimeDriver" || get_class($DateTime) == "DateTimeDriver_Ext"){
						$tmpDateTime = $DateTime->ToString();
					}
					else {
						$tmpDateTime = $DateTime;
					}
				}
				else{
					$tmpDateTime = $DateTime;
				}

				//does the user have a timezone preference?
				if(F::$Request->Session("timezone") != ""){
					//set the user's timezone
					$tmpTimeZone = new DateTimeZone(F::$Request->Session("timezone"));
					$newDateTime = new DateTime($tmpDateTime, $tmpTimeZone);

					//now convert to UTC
					$tmpTimeZone = new DateTimeZone("UTC");
					$newDateTime->setTimezone($tmpTimeZone);
				}
				else {
					//just recreate a datetime object
					$newDateTime = new DateTime($tmpDateTime);
				}

				//create a new DateTimeDriver
				$outDateTime = new DateTimeDriver_Ext();
				$outDateTime->DateTime = $newDateTime;
				$outDateTime->RefreshProperties();

				//return the new DateTimeDriver
				if(is_null($Format)) {
					return $outDateTime;
				}
				//otherwise return the formatted string
				else if($Format == "") {
					return $outDateTime->ToString();
				}
				else {
					return $outDateTime->ToString($Format);
				}
			}
		//<-- End Method :: ConvertTZIn

		//##################################################################################

		//--> Begin Method :: ConvertTZOut
			public function ConvertTZOut($DateTime, $Format = null) {
				//create a temporary date time object
				$tmpDateTime = null;
				$newDateTime = null;

				//make sure this is an object
				if(is_object($DateTime)){
					//was this a datetime driver?
					if(get_class($DateTime) == "DateTimeDriver" || get_class($DateTime) == "DateTimeDriver_Ext"){
						$tmpDateTime = $DateTime->ToString();
					}
					else {
						$tmpDateTime = $DateTime;
					}
				}
				else{
					$tmpDateTime = $DateTime;
				}

				//does the user have a timezone preference?
				if(F::$Request->Session("timezone") != ""){
					//set UTC as the timezone
					$tmpTimeZone = new DateTimeZone("UTC");
					$newDateTime = new DateTime($tmpDateTime, $tmpTimeZone);

					//set convert to the user's timezone
					$tmpTimeZone = new DateTimeZone(F::$Request->Session("timezone"));
					$newDateTime->setTimezone($tmpTimeZone);
				}
				else {
					//just recreate a datetime object
					$newDateTime = new DateTime($tmpDateTime);
				}

				//create a new DateTimeDriver
				$outDateTime = new DateTimeDriver_Ext();
				$outDateTime->DateTime = $newDateTime;
				$outDateTime->RefreshProperties();

				//return the new DateTimeDriver
				if(is_null($Format)) {
					return $outDateTime;
				}
				//otherwise return the formatted string
				else if($Format == "") {
					return $outDateTime->ToString();
				}
				else {
					return $outDateTime->ToString($Format);
				}
			}
		//<-- End Method :: ConvertTZOut

		//##################################################################################

		//--> Begin Method :: TimezoneDD
			public function TimezoneDD($AnyOption = false, $NoOption = false) {
				//create a list menu
				$DropDown = new WebFormMenu("tmp", 1, 0);

				//add default option(s)
				if($AnyOption) {
					$DropDown->AddOption("--- any ---", "");
				}
				if($NoOption) {
					$DropDown->AddOption("--- none ---", "");
				}
				else {
					$DropDown->AddOption("--- Select A Timezone ---", "");
				}

				//build options
					//tmp array container
					$tmpTimeZones = Array();
					
					//rackspace cloud isn't accepting DTZ::listIdentifiers
					//they get all of them no-matter the filter argument
					$tmpTimeZones = DateTimeZone::listIdentifiers();
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}
					
					/*
					//UTC [DateTimeZone::UTC = 1024]
					$DropDown->AddOptionGroup("UTC");
					$tmpTimeZones = DateTimeZone::listIdentifiers(1024);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//America [DateTimeZone::AMERICA = 2]
					$DropDown->AddOptionGroup("America");
					$tmpTimeZones = DateTimeZone::listIdentifiers(2);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//Africa [DateTimeZone::AFRICA = 1]
					$DropDown->AddOptionGroup("Africa");
					$tmpTimeZones = DateTimeZone::listIdentifiers(1);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//Antarctica [DateTimeZone::ANTARCTICA = 4]
					$DropDown->AddOptionGroup("Antarctica");
					$tmpTimeZones = DateTimeZone::listIdentifiers(4);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//Arctic [DateTimeZone::ARCTIC = 8]
					$DropDown->AddOptionGroup("Arctic");
					$tmpTimeZones = DateTimeZone::listIdentifiers(8);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//Asia [DateTimeZone::ASIA = 16]
					$DropDown->AddOptionGroup("Asia");
					$tmpTimeZones = DateTimeZone::listIdentifiers(16);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//Atlantic [DateTimeZone::ATLANTIC = 32]
					$DropDown->AddOptionGroup("Atlantic");
					$tmpTimeZones = DateTimeZone::listIdentifiers(32);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//Australia [DateTimeZone::AUSTRALIA = 64]
					$DropDown->AddOptionGroup("Australia");
					$tmpTimeZones = DateTimeZone::listIdentifiers(64);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//Europe [DateTimeZone::EUROPE = 128]
					$DropDown->AddOptionGroup("Europe");
					$tmpTimeZones = DateTimeZone::listIdentifiers(128);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//Indian [DateTimeZone::INDIAN = 256]
					$DropDown->AddOptionGroup("Indian");
					$tmpTimeZones = DateTimeZone::listIdentifiers(256);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}

					//Pacific [DateTimeZone::PACIFIC = 512]
					$DropDown->AddOptionGroup("Pacific");
					$tmpTimeZones = DateTimeZone::listIdentifiers(512);
					for($i = 0 ; $i < count($tmpTimeZones) ; $i++) {
						$DropDown->AddOption($tmpTimeZones[$i], $tmpTimeZones[$i]);
					}
					*/
				//end build options

				//return the option tags
				return $DropDown->GetOptionTags();
			}
		//<-- End Method :: TimezoneDD

		//##################################################################################
	}
//<-- End Class :: System

//##########################################################################################
?>