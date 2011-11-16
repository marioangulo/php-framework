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

//--> Begin Overload :: DOMTemplate
	class DOMTemplate_Ext extends DOMTemplate {
		//--> Begin Method :: BindResources
			public function BindResources($Chunk = null) {
				if(!isset($Chunk)) {
					$Chunk = $this;
				}
				
				//bind data results
				$Nodes = $Chunk->GetNodesByDataSet("bind-results")->GetNodes();
				for($i = 0 ; $i < count($Nodes) ; $i++) {
					//get the resource name
					$Name = $this->GetAttribute($Nodes[$i], "data-bind-results");
					
					//bind data table
					F::Log("<data-bind-results name=\"". $Name ."\">");
					$this->BindResults($Nodes[$i], $Name);
					F::Log("</data-bind-results>");
				}
				
				//bind data rows
				$Nodes = $Chunk->GetNodesByDataSet("bind-rows")->GetNodes();
				for($i = 0 ; $i < count($Nodes) ; $i++) {
					//get the resource name
					$Name = $this->GetAttribute($Nodes[$i], "data-bind-rows");
					
					//bind data table
					F::Log("<data-bind-rows name=\"". $Name ."\">");
					$this->BindRows($Nodes[$i], $Name);
					F::Log("</data-bind-rows>");
				}
				
				//bind data form
				$Nodes = $Chunk->GetNodesByDataSet("bind-form")->GetNodes();
				for($i = 0 ; $i < count($Nodes) ; $i++) {
					//get the resource name
					$Name = $this->GetAttribute($Nodes[$i], "data-bind-form");
					
					//bind data form
					F::Log("<data-bind-form name=\"". $Name ."\">");
					$this->BindForm($Nodes[$i], $Name);
					F::Log("</data-bind-form>");
				}
			}
		//<-- End Method :: BindResources
		
		//##################################################################################
		
		//--> Begin Method :: BindResults
			public function BindResults($Node, $ResourceID) {
				//stay in our boundries
				$ID = uniqid();
				$Node->setAttribute("data-bind-id", $ID);
				$Table = $this->Traverse("//*[@data-bind-id='". $ID ."']")->GetDOMChunk();
				
				//column variables
				$totalColumns = (int)$Table->Root()->GetAttribute("data-bind-results-columns");
				if($totalColumns == 0) { $totalColumns = 1; }
				$tmpItemsPerColumn = 0;
				if($totalColumns > 1) {
					//create our columns
					$Column = $this->Traverse("//*[@data-bind-id='". $ID ."']")->GetNodesByDataSet("label", "blank-column")->GetDOMChunk();
					for($i = 1 ; $i <= $totalColumns ; $i++) {
						$Column->Begin();
						$Column->Root()->SetAttribute("data-bind-id", $ID ."-". $i);
						$Column->End();
					}
					$Column->Render();
				}
				
				//setup data pager?
				if($Table->Root()->GetAttribute("data-bind-paging") != "") {
					F::$Timer->Start();
					F::$DataPager->LinkLoopOffset = 3; //(for paging)
					F::$DataPager->RecordsPerPage = isset(F::$PageInput[$ResourceID ."-show_rows"]) ? F::$PageInput[$ResourceID ."-show_rows"] : F::$Request->Input($ResourceID ."-show_rows", F::$Request->Input("show_rows", "20"));
					F::$DataPager->CurrentPage = isset(F::$PageInput[$ResourceID ."-page"]) ? F::$PageInput[$ResourceID ."-page"] : F::$Request->Input($ResourceID ."-page", F::$Request->Input("page", "1"));
					
					//sql paging data
					F::$PageInput["_limit_"] = F::$DataPager->RecordsPerPage * (F::$DataPager->CurrentPage - 1) .", ". F::$DataPager->RecordsPerPage;
				}
				
				//get data
				$Data = array();
				if(!isset(F::$ResultsCache[$ResourceID])) {
					F::$DB->LoadCommand($ResourceID, F::$PageInput);
					F::$ResultsCache[$ResourceID] = F::$DB->GetDataTable();
				}
				else {
					F::Log("<!--used cached result data-->");
				}
				$Data = F::$ResultsCache[$ResourceID];
				
				//if columns, items per column
				if($totalColumns > 1) {
					$tmpItemsPerColumn = (int)(count($Data) / $totalColumns);
					if(($tmpItemsPerColumn * $totalColumns) < count($Data)) {
						$tmpItemsPerColumn++;
					}
				}
				$tmpItemsPerColumn_Original = $tmpItemsPerColumn;
				
				//build results
				$columnIndex = 1;
				if($totalColumns == 1) {
					//$Row = $Table->GetNodesByDataSet("label", "blank-row")->GetDOMChunk();
					$Row = $this->Traverse("//*[@data-bind-id='". $ID ."']//*[@data-label='blank-row']")->GetDOMChunk();
				}
				else {
					$Row = $Table->GetNodesByDataSet("bind-id", $ID ."-". $columnIndex)->GetNodesByDataSet("label", "blank-row")->GetDOMChunk();
				}
				for($i = 0 ; $i < count($Data) ; $i++) {
					//if columns, split the rows up
					if($totalColumns > 1) {
						//have the columns been adjusted?
						if($tmpItemsPerColumn != $tmpItemsPerColumn_Original) {
							$Newi = (($i - 1) - $tmpItemsPerColumn_Original) + 1;
							if($Newi == ($tmpItemsPerColumn * ($columnIndex - 1))) {
								$Row->Render();
								$columnIndex++;
								$Row = $Table->GetNodesByDataSet("bind-id", $ID ."-". $columnIndex)->GetNodesByDataSet("label", "blank-row")->GetDOMChunk();
							}
						}
						//nope
						else if($i == ($tmpItemsPerColumn * $columnIndex)) {
							$Row->Render();
							$columnIndex++;
							$Row = $Table->GetNodesByDataSet("bind-id", $ID ."-". $columnIndex)->GetNodesByDataSet("label", "blank-row")->GetDOMChunk();
							
							//try to fix un-even column division after we've filled the first column
							if($i == $tmpItemsPerColumn_Original) {
								$DataCountLeft = count($Data) - $i;
								$ColumnsLeft = $totalColumns - 1;
								if($DataCountLeft % $ColumnsLeft == 0) {
									$tmpItemsPerColumn = $DataCountLeft / $ColumnsLeft;
								}
							}
						}
					}
					
					$Row->Begin();
					F::Log("<data-row index=\"". $i ."\">");
					F::Log("<data>");
					F::Log(print_r($Data[$i], true));
					F::Log("</data>");
					$this->DataBinder($Data[$i], $Row);
					F::Log("</data-row>");
					$Row->End();
				}
				if(count($Data) > 0) {
					//render chunk
					$Row->Render();
					
					//remove no-results-row
					$Table->GetNodesByDataSet("label", "no-results-row")->Remove();
				}
				else{
					//remove blank-row
					$Row->Remove();
				}
				
				//get found rows
				$tmpFoundRows = F::$DB->GetFoundRows();
				
				//apply data paging?
				if($Table->Root()->GetAttribute("data-bind-paging") != "") {
					F::$Timer->Stop();
					F::$DataPager->TotalRecords = $tmpFoundRows;
					$this->BindDataPaging($ResourceID, $Table->Root()->GetAttribute("data-bind-paging"));
				}
				
				//setup the extra data binder features
				F::$DOMBinders[$ResourceID ."-found-rows"] = number_format($tmpFoundRows);
				F::$DOMBinders[$ResourceID ."-count"] = count($Data);
				
				//cleanup empty columns
				if($totalColumns > 1) {
					if($columnIndex < $totalColumns) {
						for($i = ($columnIndex + 1) ; $i <= $totalColumns ; $i++) {
							$this->Traverse("//*[@data-bind-id='". $ID ."']")->GetNodesByDataSet("bind-id", $ID ."-". $i)->Remove();
						}
					}
				}
				
				//remove binders
				$Table->GetNodesByAttribute("data-bind-id")->RemoveAttribute("data-bind-id");
				$Table->GetNodesByAttribute("data-label", "blank-row")->RemoveAttribute("data-label");
				$Table->GetNodesByAttribute("data-label", "blank-column")->RemoveAttribute("data-label");
				$Table->GetNodesByAttribute("data-label", "no-results-row")->RemoveAttribute("data-label");
				$Table->Root()->RemoveAttribute("data-bind-results");
				$Table->Root()->RemoveAttribute("data-bind-results-columns");
				$Table->Root()->RemoveAttribute("data-bind-paging");
				$Table->Root()->RemoveAttribute("data-bind-id");
			}
		//<-- End Method :: BindResults
		
		//####################################################################################
		
		//--> Begin Method :: FinalBind
			public function FinalBind() {
				//we merge input and dom-binders for a final binding
				F::$Doc->DataBinder(array_merge((array)F::$PageInput, (array)F::$DOMBinders));
			}
		//<-- End Method :: FinalBind
		
		//####################################################################################
		
		//--> Begin Method :: BindRows
			public function BindRows($Node, $ResourceID) {
				//stay in our boundries
				$ID = uniqid();
				$Node->setAttribute("data-bind-id", $ID);
				$Table = $this->Traverse("//*[@data-bind-id='". $ID ."']")->GetDOMChunk();
				$Row = $this->Traverse("//*[@data-bind-id='". $ID ."']")->GetNodesByDataSet("label", "blank-row")->GetDOMChunk();
				
				//get the data
				$Data = array();
				if(isset(F::$CustomRows[$ResourceID])) {
					$Data = F::$CustomRows[$ResourceID];
				}
				
				//build results
				for($i = 0 ; $i < count($Data); $i++) {
					$Row->Begin();
					F::Log("<data-row index=\"". $i ."\">");
					F::Log("<data>");
					F::Log(print_r($Data[$i], true));
					F::Log("</data>");
					$this->DataBinder($Data[$i], $Row);
					F::Log("</data-row>");
					$Row->End();
				}
				if(count($Data) > 0) {
					//render chunk
					$Row->Render();
				}
				else{
					//remove blank-row
					$Row->Remove();
				}
				
				//remove binders
				F::$Doc->RemoveAttribute($Node, "data-bind-id");
				F::$Doc->RemoveAttribute($Node, "data-bind-rows");
			}
		//<-- End Method :: BindRows
		
		//####################################################################################
		
		//--> Begin Method :: BindForm
			public function BindForm($Node, $ResourceID) {
				//stay in our boundries
				$ID = uniqid();
				$Node->setAttribute("data-bind-id", $ID);
				$Form = $this->Traverse("//*[@data-bind-id='". $ID ."']")->GetDOMChunk();
				
				//get data
				$Data = array();
				if(!isset(F::$FormCache[$ResourceID])) {
					F::$DB->LoadCommand($ResourceID, F::$PageInput);
					F::$FormCache[$ResourceID] = F::$DB->GetDataRow();
				}
				else {
					F::Log("<!--used cached form data-->");
				}
				$Data = F::$FormCache[$ResourceID];
				
				//log data
				F::Log("<record-data>");
				F::Log(print_r($Data, true));
				F::Log("</record-data>");
				
				//setup binders
				$MyBinders = F::$PageInput;
				if($Form->Root()->GetAttribute("data-bind-form-postback") == "false") {
					$MyBinders = array_merge($MyBinders, $Data);
				}
				else {
					//only merge binders when there is an action and there is a result (postback support)
					if(F::$Request->Input("action") == "" && F::$DB->GetFoundRows() > 0) {
						$MyBinders = array_merge($MyBinders, $Data);
					}
				}
				
				//contextual
				if($Form->Root()->GetAttribute("data-bind-form-context") == "add-update-delete") {
					//show and hide form buttons
					if(F::$Request->Input("id") == "") {
						$this->GetNodeByID("button_update")->Remove();
						$this->GetNodeByID("button_delete")->Remove();
					}
					else {
						$this->GetNodeByID("button_new")->Remove();
					}
				}
				
				//bind the data
				F::$Doc->DataBinder($MyBinders, $Form);
				
				//remove binders
				F::$Doc->RemoveAttribute($Node, "data-bind-id");
				F::$Doc->RemoveAttribute($Node, "data-bind-form");
			}
		//<-- End Method :: BindForm
		
		//####################################################################################
		
		//--> Begin Method :: DataBinder
			public function DataBinder($Data, $Chunk = null) {
				if(!isset($Chunk)) {
					$Chunk = $this;
				}
				
				//functions
				$arrFunctions = $Chunk->GetNodesByDataSet("bind-function")->GetNodes();
				if(method_exists($Chunk, "Root")) {
					if($Chunk->Root()->GetNode()->getAttribute("data-bind-function") != "") {
						$arrFunctions[] = $Chunk->Root()->GetNode();
					}
				}
				for($i = 0 ; $i < count($arrFunctions) ; $i++) {
					//get the resource name
					$Name = $this->GetAttribute($arrFunctions[$i], "data-bind-function");
					
					//was a class provided?
					if(strpos($Name, "::") > -1) {
						$Signature = explode("::", $Name);
						
						F::Log("<!--func lookup (". $Signature[0] ."::". $Signature[1] .")-->");
						if(method_exists($Signature[0], $Signature[1])) {
							F::Log("<Fire|". $Signature[0] ."::". $Signature[1] .">");
							call_user_func($Signature[0] ."::". $Signature[1], $arrFunctions[$i], $Data);
							F::Log("</Fire|". $Signature[0] ."::". $Signature[1] .">");
						}
					}
					//no class. try to fire like an event.
					else {
						//////////////////////////////////////////
						F::FireEvents($Name);
						/////////////////////////////////////////
					}
					
					//remove binder
					$this->RemoveAttribute($arrFunctions[$i], "data-bind-function");
				}
				
				//attributes
				$arrAttributes = $Chunk->GetNodesByDataSet("bind-attr")->GetNodes();
				for($i = 0 ; $i < count($arrAttributes) ; $i++) {
					$attrCommands = explode(",", $this->GetAttribute($arrAttributes[$i], "data-bind-attr"));
					
					for($j = 0 ; $j < count($attrCommands) ; $j++) {
						$attrDetails = explode("=", $attrCommands[$j]);
						
						$BindValue = F::GetBindDataValue($attrDetails[1], $Data);
						if(isset($BindValue)) {
							$this->SetAttribute($arrAttributes[$i], $attrDetails[0], $BindValue);
							F::Log("<!--bound attribute (". $attrDetails[0] .")-->");
							F::Log($this->ToString($arrAttributes[$i]));
						}
					}
					
					//remove binder
					$this->RemoveAttribute($arrAttributes[$i], "data-bind-attr");
				}
				
				//text
				$arrText = $Chunk->GetNodesByDataSet("bind-text")->GetNodes();
				for($i = 0 ; $i < count($arrText) ; $i++) {
					$DataIndex = $this->GetAttribute($arrText[$i], "data-bind-text");
					
					$BindValue = F::GetBindDataValue($DataIndex, $Data);
					if(isset($BindValue)) {
						$this->SetInnerText($arrText[$i], Codec::HTMLEncode($BindValue));
						F::Log("<!--bound inner text-->");
						F::Log($this->ToString($arrText[$i]));
					}
					
					//remove binder
					$this->RemoveAttribute($arrText[$i], "data-bind-text");
				}
				
				//html
				$arrHTML = $Chunk->GetNodesByDataSet("bind-html")->GetNodes();
				for($i = 0 ; $i < count($arrHTML) ; $i++) {
					$DataIndex = $this->GetAttribute($arrHTML[$i], "data-bind-html");
					
					$BindValue = F::GetBindDataValue($DataIndex, $Data);
					if(isset($BindValue)) {
						$this->SetInnerHTML($arrHTML[$i], $BindValue);
						F::Log("<!--bound inner html-->");
						F::Log($this->ToString($arrHTML[$i]));
					}
					
					//remove binder
					$this->RemoveAttribute($arrHTML[$i], "data-bind-html");
				}
				
				//input
				$arrInput = $Chunk->GetNodesByDataSet("bind-input")->GetNodes();
				for($i = 0 ; $i < count($arrInput) ; $i++) {
					//remember data index
					$DataIndex = $this->GetAttribute($arrInput[$i], "data-bind-input");
					
					//text
					if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "text") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							$this->SetAttribute($arrInput[$i], "value", $BindValue);
						}
					}
					
					//textarea
					if($arrInput[$i]->nodeName == "textarea") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							$this->SetInnerHTML($arrInput[$i], Codec::HTMLEncode($BindValue));
						}
					}
					
					//select
					if($arrInput[$i]->nodeName == "select") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							$arrInput[$i]->setAttribute("data-bind-id", $i);
							$Chunk->Traverse("//*[@data-bind-id='". $i ."']")->GetNodesByAttribute("value", $BindValue)->SetAttribute("selected", "selected");
							
							//remove data-bind-id
							$arrInput[$i]->removeAttribute("data-bind-id");
						}
					}
					
					//checkbox
					if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "checkbox") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							if($this->GetAttribute($arrInput[$i], "value") == $BindValue) {
								$this->SetAttribute($arrInput[$i], "checked", "checked");
							}
						}
					}
					
					//radio
					if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "radio") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							if($this->GetAttribute($arrInput[$i], "value") == $BindValue) {
								$this->SetAttribute($arrInput[$i], "checked", "checked");
							}
						}
					}
					
					//hidden
					if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "hidden") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							$this->SetAttribute($arrInput[$i], "value", $BindValue);
						}
					}
					
					//password
					if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "password") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							$this->SetAttribute($arrInput[$i], "value", $BindValue);
						}
					}
					
					//submit
					if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "submit") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							$this->SetAttribute($arrInput[$i], "value", $BindValue);
						}
					}
					
					//button
					if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "button") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							$this->SetAttribute($arrInput[$i], "value", $BindValue);
						}
					}
					
					//reset
					if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "reset") {
						$BindValue = F::GetBindDataValue($DataIndex, $Data);
						if(isset($BindValue)) {
							$this->SetAttribute($arrInput[$i], "value", $BindValue);
						}
					}
					
					//log
					F::Log("<!--bound input-->");
					F::Log($this->ToString($arrInput[$i]));
					
					//remove binder
					$this->RemoveAttribute($arrInput[$i], "data-bind-input");
				}
				
				//errors
				$Chunk->GetNodesByAttribute("class", "footprint_msg")->SetInnerHTML(F::GetAlerts());
			}
		//<-- End Method :: DataBinder
		
		//##################################################################################
		
		//--> Begin Method :: ProcessAlerts
			public function ProcessAlerts($Node = null) {
				//stay in our boundries
				$ID = uniqid();
				$Chunk = null;
				
				//get the chunk
				if(!isset($Node)) {
					$Chunk = $this;
				}
				else {
					$Node->setAttribute("data-bind-id", $ID);
					$Chunk = $this->Traverse("//*[@data-bind-id='". $ID ."']")->GetDOMChunk();
				}
				
				$ErrorNodes = (array)$Chunk->Traverse("//*[@class='footprint_msg']//*[@class='alert-message error']//p[@for]")->GetNodes();
				foreach($ErrorNodes as $Error) {
					$ResourceID = $Error->getAttribute("for");
					$Chunk->Traverse("//*[@name='". $ResourceID ."']")->GetNode()->parentNode->parentNode->setAttribute("class", "clearfix error");
					$Chunk->Traverse("//*[@name='". $ResourceID ."']/parent::*//*[@class='help-inline']")->SetInnerText($Error->textContent);
					$Chunk->Remove($Error);
				}
				
				
				//if there are no more messages remove the alerts
				if(F::$Errors->Count() > 0) {
					$ErrorNodes = $Chunk->Traverse("//*[@class='footprint_msg']//*[@class='alert-message error']//p")->GetNodes();
					if(count($ErrorNodes) == 0) {
						$ErrorNodes = $Chunk->Traverse("//*[@class='alert-message error']")->Remove();
					}
				}
			}
		//<-- End Method :: ProcessAlerts
		
		//##################################################################################
		
		//--> Begin Method :: BindDataPaging
			public function BindDataPaging($ResourceID, $Type = "") {
				if($Type == "ajax"){
					$this->BindDataPagingAjax($ResourceID);
					return;
				}
				
				//get this chunk
				$Chunk = $this->GetNodesByDataSet("bind-results", $ResourceID)->GetDOMChunk();
				
				
				if(F::$DataPager->GetTotalPages() > 1) {
					//show the data			
					$Chunk->Traverse("//*[@data-label='pager_current_page']")->SetInnerText(F::$DataPager->CurrentPage);
					$Chunk->Traverse("//*[@data-label='pager_total_pages']")->SetInnerText(F::$DataPager->GetTotalPages());
					$Chunk->Traverse("//*[@data-label='pager_record_start']")->SetInnerText((F::$DataPager->GetRecordToStart() + (F::$DataPager->TotalRecords == 0 ? 0 : 1)));
					$Chunk->Traverse("//*[@data-label='pager_record_end']")->SetInnerText(F::$DataPager->GetRecordToStop());
					$Chunk->Traverse("//*[@data-label='pager_total_records']")->SetInnerText(F::$DataPager->TotalRecords);
					
					//get the base query and remove paging args
					$BaseQuery = F::$Request->QueryString();
					$BaseQuery = preg_replace("/^". $ResourceID ."-page=\\d+|&". $ResourceID ."-page=\\d+/i", "", $BaseQuery);
					$BaseQuery = preg_replace("/^page=\\d+|&page=\\d+/i", "", $BaseQuery);
					
					//replace prev/next
						if(F::$DataPager->HasPreviousPage()) {
							$PreviousLinkURL = F::$PageNamespace .".html?". Codec::HTMLEncode($BaseQuery . ($BaseQuery == "" ? "" : "&") . $ResourceID ."-page=". F::$DataPager->GetPreviousPage());
							$Chunk->Traverse("//*[@data-label='pager_prev']/a")->SetAttribute("href", htmlspecialchars_decode($PreviousLinkURL));
						}
						else {
							$Chunk->Traverse("//*[@data-label='pager_prev']")->SetAttribute("class", "prev disabled");
						}
						
						if(F::$DataPager->HasNextPage()) {
							$NextLinkURL = F::$PageNamespace .".html?". Codec::HTMLEncode($BaseQuery . ($BaseQuery == "" ? "" : "&") . $ResourceID ."-page=". F::$DataPager->GetNextPage());
							$Chunk->Traverse("//*[@data-label='pager_next']/a")->SetAttribute("href", htmlspecialchars_decode($NextLinkURL));
						}
						else {
							$Chunk->Traverse("//*[@data-label='pager_next']")->SetAttribute("class", "next disabled");
						}
					//end replace prev/next
					
					//replace first/last
						if(F::$DataPager->GetTotalPages() <= 10) {
							//just remove first/last pages buttons
							$Chunk->Traverse("//*[@data-label='pager_first']")->Remove();
							$Chunk->Traverse("//*[@data-label='pager_last']")->Remove();
						}
						else {
							if(F::$DataPager->GetLinkLoopStart() != "1") {
								$FirstLinkURL = F::$PageNamespace .".html?". Codec::HTMLEncode($BaseQuery . ($BaseQuery == "" ? "" : "&") . $ResourceID ."-page=1");
								$Chunk->Traverse("//*[@data-label='pager_first']/a")->SetAttribute("href", htmlspecialchars_decode($FirstLinkURL));
							}
							else {
								$Chunk->Traverse("//*[@data-label='pager_first']")->Remove();
							}
							
							if(F::$DataPager->GetLinkLoopStop() != F::$DataPager->GetTotalPages()) {
								$LastLinkURL = F::$PageNamespace .".html?". Codec::HTMLEncode($BaseQuery . ($BaseQuery == "" ? "" : "&") . $ResourceID ."-page=". F::$DataPager->GetTotalPages());
								$Chunk->Traverse("//*[@data-label='pager_last']/a")->SetAttribute("href", htmlspecialchars_decode($LastLinkURL));
							}
							else {
								$Chunk->Traverse("//*[@data-label='pager_last']")->Remove();
							}
						}
					//end replace first/last
					
					//replace paging links
					$cnkPagingLinks = $Chunk->Traverse("//*[@data-label='pager_blank']")->GetDOMChunk();
					for($i = F::$DataPager->GetLinkLoopStart() ; $i <= F::$DataPager->GetLinkLoopStop() ; $i++) {
						$cnkPagingLinks->Begin();
						
						if($i == F::$DataPager->CurrentPage) {
							$cnkPagingLinks->Root()->SetAttribute("class", "active");
							$cnkPagingLinks->Root()->GetNodesByTagName("a")->SetInnerText($i);
						}
						else {
							$PageLinkURL = F::$PageNamespace .".html?". Codec::HTMLEncode($BaseQuery . ($BaseQuery == "" ? "" : "&") . $ResourceID ."-page=". $i);
							$cnkPagingLinks->Root()->GetNodesByTagName("a")->SetInnerText($i);
							$cnkPagingLinks->Root()->GetNodesByTagName("a")->SetAttribute("href", htmlspecialchars_decode($PageLinkURL));
						}
						
						$cnkPagingLinks->End();
					}
					$cnkPagingLinks->Render();
					
					//replace the timer
					if(F::$Timer->TimeSpent == 0) {
						$Chunk->Traverse("//*[@data-label='timer_data']")->Remove();
					}
					else {
						$Chunk->Traverse("//*[@data-label='query_time']")->SetInnerText(F::$Timer->TimeSpent);
					}
				}
				else{
					$Chunk->Traverse("//*[@data-label='paging']")->Remove();
				}
			}
		//<-- End Method :: BindDataPaging
		
		//##################################################################################
		
		//--> Begin Method :: BindDataPagingAjax
			public function BindDataPagingAjax($ResourceID) {
				$Chunk = $this->GetNodesByDataSet("bind-results", $ResourceID)->GetDOMChunk();
				
				//is there paging to replace?
				if(F::$DataPager->GetTotalPages() <= 1){
					$Chunk->GetNodesByDataSet("label", "paging")->Remove();
				}
				else {
					//show the data
					$Chunk->Traverse("//*[@data-label='pager_record_start']")->SetInnerText((F::$DataPager->GetRecordToStart() + (F::$DataPager->TotalRecords == 0 ? 0 : 1)));
					$Chunk->Traverse("//*[@data-label='pager_record_end']")->SetInnerText(F::$DataPager->GetRecordToStop());
					$Chunk->Traverse("//*[@data-label='pager_total_records']")->SetInnerText(F::$DataPager->TotalRecords);
					
					//set storage data
					$Chunk->Traverse("//*[@data-label='paging']")->SetAttribute("data-current-page", F::$DataPager->CurrentPage);
					
					//replace prev/next
					if(F::$DataPager->HasPreviousPage() == false) {
						$Chunk->Traverse("//*[@data-label='pager_prev']")->SetAttribute("class", "disabled");
					}
					else {
						$Chunk->Traverse("//*[@data-label='pager_prev']/a")->SetAttribute("data-page", $ResourceID ."-page=". F::$DataPager->GetPreviousPage());
					}
					
					if(F::$DataPager->HasNextPage() == false) {
						$Chunk->Traverse("//*[@data-label='pager_next']")->SetAttribute("class", "disabled");
					}
					else {
						$Chunk->Traverse("//*[@data-label='pager_next']/a")->SetAttribute("data-page", $ResourceID ."-page=". F::$DataPager->GetNextPage());
					}
					
					//replace paging options
					$DropDown = new WebFormMenu("tmp", 1, 0);
					$DropDown->AddSelectedValue($ResourceID ."-page=". F::$Request->Input($ResourceID ."-page"));
					
					F::$DataPager->LinkLoopOffset = 25;
					for($i = F::$DataPager->GetLinkLoopStart() ; $i <= F::$DataPager->GetLinkLoopStop() ; $i++) {
						$DropDown->AddOption("Page ". $i ."/". F::$DataPager->GetTotalPages(), $ResourceID ."-page=". $i);
					}
					$Chunk->Traverse("//*[@data-label='page_jump']")->SetInnerHTML($DropDown->GetOptionTags());
					
					//replace the timer
					if(F::$Timer->TimeSpent == 0) {
						//remove entire row
						$Chunk->Traverse("//*[@data-label='timer_data']")->Remove();
					}
					else {
						//replace query time
						$Chunk->Traverse("//*[@data-label='query_time']")->SetInnerText(number_format(F::$Timer->TimeSpent, 5));
					}
				}
			}
		//<-- End Method :: BindDataPagingAjax
		
		//####################################################################################
		//####################################################################################
		//####################################################################################
	}
//<-- End Class :: DOMTemplate

//##########################################################################################
?>