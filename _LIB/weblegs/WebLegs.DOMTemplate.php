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

//--> Begin Class :: DOMTemplate
	class DOMTemplate {
		//--> Begin :: Properties
			public $XPathQuery;
			public $DOMDocument;
			public $DOMXPath;
			public $ResultNodes;
			public $BasePath;
			public $DTD;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function DOMTemplate() {
				$this->XPathQuery = "";
				$this->DOMXPath = null;
				$this->DOMDocument = new DOMDocument();
				$this->DOMDocument->substituteEntities = true;
				$this->DOMDocument->strictErrorChecking = false;
				$this->ResultNodes = array();
				$this->BasePath = "";
				$this->DTD = "";
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//<-- End Method :: Traverse
			public function Traverse($Value) {
				//clear out results nodes
				$this->ResultNodes = null;
		
				//set the xpath query
				$this->XPathQuery .= $Value;
				
				return $this;
			}
		//<-- End Method :: Traverse
		
		//##################################################################################
		
		//--> Begin Method :: GetDOMChunk
			public function GetDOMChunk() {
				$ReturnData = new DOMChunk($this);
				$this->XPathQuery = "";
				return $ReturnData;
			}
		//<-- End Method :: GetDOMChunk
		
		//##################################################################################
		
		//--> Begin Method :: LoadFile
			public function LoadFile($Path, $RootPath = null) {
				//make sure file exists
				if(!file_exists($Path)){
					throw new Exception("WebLegs.DOMTemplate.LoadFile(): File not found or not able to access. (". $Path .")");
				}
			
				//load up file
				$Source = file_get_contents($Path);
				$this->Load($Source, $RootPath);
				
				//return this reference
				return $this;
			}
		//<-- End Method :: LoadFile
		
		//##################################################################################
		
		//--> Begin Method :: Load
			public function Load($Source, $RootPath = null) {
				$Source = str_replace("&", "&amp;", $Source);  // disguise &s going IN to loadXML() 
				
				if(is_null($RootPath)) {
					//load up our dom object
					$this->DOMDocument->loadXML($Source);
					
					//setup the xpath object
					$this->DOMXPath = new DOMXPath($this->DOMDocument);
					
					return;
				}
				//see if there is any stylesheets
				else if(strpos($Source, "xml-stylesheet") == false) {
					//load up our dom object
					$this->DOMDocument->loadXML($Source);
					
					//setup the xpath object
					$this->DOMXPath = new DOMXPath($this->DOMDocument);
					return;
				}
		
				//find the xsl style sheet path in our document
				preg_match("/xml-stylesheet.*?href=[\"|'](.*?)[\"|']/", $Source, $Matches);
				
				$XSLTPath = $Matches[1];
				
				//get dtd
				preg_match("/(<!DOCTYPE.*?>)/", $Source, $Matches);
				if(count($Matches) > 0){
					$this->DTD = $Matches[1];
					
					//strip out dtd
					$Source = str_replace($this->DTD, "", $Source);
				}
				
				//loat xml source
				$XMLDoc = new DOMDocument();
				$XMLDoc->substituteEntities = true;
				$XMLDoc->loadXML($Source);
				
				//create a xslt document
				$XSLTDoc = new DOMDocument();
				$XSLTDoc->substituteEntities = true;
				$XSLTSource = file_get_contents($RootPath . $XSLTPath);
				$XSLTDoc->loadXML($XSLTSource);
				
				//create an xslt processor and load style sheet
				$XProc = new XSLTProcessor();
				$XProc->importStylesheet($XSLTDoc);
		
				//transform the xml and load up our dom object
				$this->DOMDocument = $XProc->transformToDoc($XMLDoc);
				
				//setup the xpath object
				$this->DOMXPath = new DOMXPath($this->DOMDocument);
				
				//clear XPathQuery
				$this->XPathQuery = "";
				
				//clear node results
				$this->ResultNodes = null;
				
				//return this reference
				return $this;
			}
		//<-- End Method :: Load
		
		//##################################################################################
		
		//--> Begin Method :: ExecuteQuery
			public function ExecuteQuery($XPathQuery = "") {
				//check for empty document
				//DOMXPath is only instantiated when we LoadFile() or Load()
				//these functions were never called if this is the case
				if(is_null($this->DOMXPath)) {
					return $this;
				}
				
				//this is the overload
				if($XPathQuery != "") {
					$Nodes = $this->DOMXPath->query($this->BasePath . $XPathQuery, $this->DOMDocument);
					$ReturnNodes = array();
					for($i = 0; $i < $Nodes->length; $i++) {
						$ReturnNodes[] = $Nodes->item($i);
					}
					return $ReturnNodes;
				}
				
				//if its blank default to whole document
				if($this->BasePath == "" && $this->XPathQuery == "") {
					$this->XPathQuery = "//*";
				}
				//this accomodates for the duplicate queries in both the basepath and XPathquery
				//this can happen when attempting to access the parent node in a DOMChunk
				else if($this->BasePath == $this->XPathQuery){
					$this->XPathQuery = "";
				}
				
				$ReturnNodes = array();
				$Nodes = $this->DOMXPath->query($this->BasePath . $this->XPathQuery, $this->DOMDocument);
				for($i = 0; $i < $Nodes->length; $i++) {
					$ReturnNodes[] = $Nodes->item($i);
				}
				
				//clear XPathQuery
				$this->XPathQuery = "";
				
				//set node results
				$this->ResultNodes = $ReturnNodes;
				
				return $this;
			}
		//<-- End Method :: ExecuteQuery
		
		//##################################################################################
		
		//--> Begin Method :: ToString
			public function ToString() {
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//ToString entire document - ToString()
				if($NumberOfArgs == 0){
					//check for empty document
					//DOMXPath is only instantiated when we LoadFile() or Load()
					//these functions were never called if this is the case
					if(is_null($this->DOMXPath)) {
						return "";
					}
					
					//get dtd
					$OutputSource = $this->DOMDocument->saveXML(null, LIBXML_NOEMPTYTAG);
					preg_match("/(<!DOCTYPE.*?>)/", $OutputSource, $Matches);
					if(count($Matches) > 0){
						$WrongDTD = $Matches[1];
						
						//strip out dtd
						$OutputSource = str_replace($WrongDTD, "", $OutputSource);
					}
					
					//strip out xml declaration
					$OutputSource = str_replace("<?xml version=\"1.0\"?>", "", $OutputSource);
					
					//unhide entities
					$OutputSource = str_replace("&amp;", "&", $OutputSource);  // undisguise &s
					
			        //fix singleton tag issues
					$OutputSource = preg_replace("/><\/(area|base|br|col|command|embed|hr|img|input|link|meta|param|source)>/", " />", $OutputSource);
					
					return $this->DTD . $OutputSource;
				}
				//ToString an array of Nodes - ToString(NodeList ThisNodeList)
				else if($Args[0] instanceof DOMNodeList){
					$ReturnData = "";
					$ThisNodeList = $Args[0];
					for($i = 0; $i < $ThisNodeList->length; $i++) {
						$ReturnData .= $this->ToString($ThisNodeList->item($i));
					}
					return $ReturnData;
				}
				//ToString single Nodes - ToString(Node ThisNode)
				else if($Args[0] instanceof DomNode){
					$ImportNode;
					if(get_class($Args[0]) == "DOMDocument"){
						$ImportNode = $Args[0]->documentElement;
					}
					else{
						$ImportNode = $Args[0];
					}
					
					$ReturnData = "";
					$TmpDoc = new DOMDocument();
					$TmpDoc->substituteEntities = true;
			        $TmpDoc->appendChild($TmpDoc->importNode($ImportNode, true));
					$OutputSource = $TmpDoc->saveXML(null, LIBXML_NOEMPTYTAG);
					
					//unhide entities
					$OutputSource = str_replace("&amp;", "&", $OutputSource);  // undisguise &s
			        
					//strip out xml declaration
					$OutputSource = str_replace("<?xml version=\"1.0\"?>", "", $OutputSource);
			        
			        //fix singleton tag issues
					$OutputSource = preg_replace("/><\/(area|base|br|col|command|embed|hr|img|input|link|meta|param|source)>/", " />", $OutputSource);
			        
			        return $OutputSource;
				}
			}
		//<-- End Method :: ToString
		
		//##################################################################################
		
		//--> Begin Method :: GetNodesByTagName
			public function GetNodesByTagName($TagName) {
				//clear out results nodes
				$this->ResultNodes = null;
			
				//set the xpath query
				$this->XPathQuery .= "//". $TagName;
		
				return $this;
			}
		//<-- End Method :: GetNodesByTagName
		
		//##################################################################################
		
		//--> Begin Method :: GetNodeByID
			public function GetNodeByID($Value) {
				//clear out results nodes
				$this->ResultNodes = null;
				
				//set the xpath query
				$this->XPathQuery .= "//*[@id='". $Value ."']";
		
				return $this;
			}
		//<-- End Method :: GetNodeByID
		
		//##################################################################################
		
		//--> Begin Method :: GetNodesByAttribute
			public function GetNodesByAttribute($Attribute, $Value = null) {
				//clear out results nodes
				$this->ResultNodes = null;
				
				//GetNodesByAttribute($Value)
				if(is_null($Value)) {
					//set the xpath query
					$this->XPathQuery .= "//*[@". $Attribute ."]";
				}
				//GetNodesByAttribute($Attribute, $Value)
				else{
					//set the xpath query
					$this->XPathQuery .= "//*[@". $Attribute ."='". $Value ."']";
				}
		
				return $this;
			}
		//<-- End Method :: GetNodesByAttribute
		
		//##################################################################################
		
		//--> Begin Method :: GetNodesByDataSet
			public function GetNodesByDataSet($Attribute, $Value = null) {
				//clear out results nodes
				$this->ResultNodes = null;
				
				//use GetNodesByAttribute
				$this->GetNodesByAttribute("data-". $Attribute, $Value);
				
				return $this;
			}
		//<-- End Method :: GetNodesByDataSet
		
		//##################################################################################
		
		//--> Begin Method :: GetNodesByAttributes
			public function GetNodesByAttributes($Attributes) {
				//clear out results nodes
				$this->ResultNodes = null;
				
				$Query = "";
				$Counter = 0;
				$Count = count($Attributes);
				foreach($Attributes as $Key => $Value) {
					$Query .= "@". $Key ."='". $Value ."'";
					if(($Counter + 1) != $Count) {
						$Query .= " and ";
					}
					$Counter++;
				}
				
				//set the xpath query
				$this->XPathQuery .= "//*[". $Query ."]";
				
				//execute query
				return $this;
			}
		//<-- End Method :: GetNodesByAttributes
		
		//##################################################################################
		
		//--> Begin Method :: SetAttribute
			public function SetAttribute() {
				//emulate overloading with these argument count and vars
				$Nodes = null;
				$Attribute = "";
				$Value = "";
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//SetAttribute(string $Attribute, string $Value)
				if($NumberOfArgs == 2) {		
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
				
					//set argument vars
					$Attribute = $Args[0];
					$Value = $Args[1];
					
					for($i = 0; $i < count($this->ResultNodes); $i++) {
						$this->ResultNodes[$i]->setAttribute($Attribute, $Value);
					}
				
				}
				else if($NumberOfArgs == 3) {
					//set argument vars
					$Nodes = $Args[0];
					$Attribute = $Args[1];
					$Value = $Args[2];
					
					//SetAttribute(array $Nodes, string $Attribute, string $Value)
					if(gettype($Nodes) == "array") {
						for($i = 0; $i < count($Nodes); $i++) {
							$Nodes[$i]->setAttribute($Attribute, $Value);
						}
					}
					//SetAttribute(node $Nodes, string $Attribute, string $Value)
					else{
						$Nodes->setAttribute($Attribute, $Value);
					}
				}
				
				return $this;
			}
		//<-- End Method :: SetAttribute
		
		//##################################################################################
		
		//--> Begin Method :: GetAttribute
			public function GetAttribute() {
				//emulate overloading with these argument count and vars
				$Nodes = null;
				$Attribute = "";
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				$ReturnValue = "";
				
				//GetAttribute(string $Attribute)
				if($NumberOfArgs == 1) {
				
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
				
					$Attribute = $Args[0];
					$ReturnValue = $this->ResultNodes[0]->getAttribute($Attribute);
				}
				//GetAttribute(node $Node, string $Attribute)
				else if($NumberOfArgs == 2) {
					$Node = $Args[0];
					$Attribute = $Args[1];
					$ReturnValue = $Node->getAttribute($Attribute);
				}
				
				//this is a termination method clear out properties
				$this->XPathQuery = "";
				$this->ResultNodes = null;
				
				return $ReturnValue;
			}
		//<-- End Method :: GetAttribute
		
		//##################################################################################
		
		//--> Begin Method :: SetInnerHTML
			public function SetInnerHTML() {
				//emulate overloading with these argument count and vars
				$Nodes = null;
				$Value = "";
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				$ReturnValue = "";
				
				//SetInnerHTML(string $Value)
				if($NumberOfArgs == 1) {
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					
					//get value arg
					$Value = $Args[0];
					for($i = 0; $i < count($this->ResultNodes); $i++) {
						$this->SetInnerHTML($this->ResultNodes[$i], $Value);
					}
				}
				else if($NumberOfArgs == 2) {
					$Nodes = $Args[0];
					$Value = $Args[1];
					
					//SetInnerHTML(array $Nodes, string $Value)
					if(gettype($Nodes) == "array") {
						for($i = 0; $i < count($Nodes); $i++) {
							$this->SetInnerHTML($Nodes[$i], $Value);
						}
					}
					//SetInnerHTML(node $Node, string $Value)
					else{
						$Node = $Args[0];
						$Node->nodeValue = null;
						
						//load source
						$tmpDOMDocument = new DOMDocument();
						$tmpDOMDocument->validateOnParse = false;
						
						if($Node->nodeName == "html"){
							@$tmpDOMDocument->loadHTML($Value);
						}
						else{
							@$tmpDOMDocument->loadXML("<container-root>". $Value ."</container-root>");
						}
						
						$NewNode = $this->DOMDocument->importNode($tmpDOMDocument->documentElement, true);
						
						for($i = 0; $i < $NewNode->childNodes->length; $i++) {
							//accomodate for textnodes
							if($Node instanceof stdClass){
								$Node->nodeValue .= $NewNode->childNodes->item($i)->nodeValue;
							}
							else{
								$Node->appendChild($NewNode->childNodes->item($i)->cloneNode(true));
							}
						}
					}
				}
				return $this;
			}
		//<-- End Method :: SetInnerHTML
		
		//##################################################################################
		
		//--> Begin Method :: GetOuterHTML
			public function GetOuterHTML() {
				//emulate overloading with these argument count and vars
				$Node = null;
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				$ReturnValue = "";
				
				//GetOuterHTML()
				if($NumberOfArgs == 0) {
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					
					$ReturnValue = $this->GetOuterHTML($this->ResultNodes[0]);
				}
				//GetOuterHTML(node $Node)
				else if($NumberOfArgs == 1) {
					$ReturnValue = $this->ToString($Args[0]);
				}
				
				//this is a termination method clear out properties
				$this->XPathQuery = "";
				$this->ResultNodes = null;
				
				return $ReturnValue;
			}
		//<-- End Method :: GetOuterHTML
				
		//##################################################################################
		
		//--> Begin Method :: GetInnerHTML
			public function GetInnerHTML() {
				//emulate overloading with these argument count and vars
				$Node = null;
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				$ReturnValue = "";
				
				//GetInnerHTML()
				if($NumberOfArgs == 0) {
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					
					if(array_key_exists(0, $this->ResultNodes)){
						$ReturnValue = $this->GetInnerHTML($this->ResultNodes[0]);
					}
					else{
						$ReturnValue = "";
					}
				}
				//GetInnerHTML(node $Node)
				else if($NumberOfArgs == 1) {
					$ReturnValue = $this->ToString($Args[0]->childNodes);
				}
				
				//this is a termination method clear out properties
				$this->XPathQuery = "";
				$this->ResultNodes = null;
				
				return $ReturnValue;
			}
		//<-- End Method :: GetInnerHTML
		
		//##################################################################################
		
		//--> Begin Method :: SetInnerText
			public function SetInnerText() {
				//emulate overloading with these argument count and vars
				$Nodes = null;
				$Value = "";
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//SetInnerText($Value)
				if($NumberOfArgs == 1) {
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					
					//set argument
					$Value = $Args[0];
					
					for($i = 0; $i < count($this->ResultNodes); $i++) {
						$this->ResultNodes[$i]->nodeValue = $Value;
					}
				}
				else if($NumberOfArgs == 2) {
					$Nodes = $Args[0];
					$Value = $Args[1];
					
					//SetInnerText(array $Nodes, string $Value)
					if(gettype($Nodes) == "array") {
						for($i = 0; $i < count($Nodes); $i++) {
							$Nodes[$i]->nodeValue = $Value;
						}
					}
					//SetInnerText(node $Nodes, string $Value)
					else{
						$Nodes->nodeValue = $Value;
					}
				}
				
				return $this;
			}
		//<-- End Method :: SetInnerText
		
		//##################################################################################
		
		//--> Begin Method :: GetInnerText
			public function GetInnerText() {
				//emulate overloading with these argument count and vars
				$Node = null;
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				$ReturnValue = "";
				
				//GetInnerText()
				if($NumberOfArgs == 0) {
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					
					$ReturnValue = $this->ResultNodes[0]->nodeValue;
				}
				//GetInnerText(node $Node)
				else if($NumberOfArgs == 1) {
					$Node = $Args[0];
					$ReturnValue = $Node->nodeValue;
				}
				
				//this is a termination method clear out properties
				$this->XPathQuery = "";
				$this->ResultNodes = null;
				
				return $ReturnValue;
			}
		//<-- End Method :: GetInnerText
		
		//##################################################################################
		
		//--> Begin Method :: GetNodeName
			public function GetNodeName() {
				//emulate overloading with these argument count and vars
				$Node = null;
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				$ReturnValue = "";
				
				//GetInnerText()
				if($NumberOfArgs == 0) {
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					
					$ReturnValue = $this->ResultNodes[0]->nodeName;
				}
				//GetInnerText(node $Node)
				else if($NumberOfArgs == 1) {
					$Node = $Args[0];
					$ReturnValue = $Node->nodeName;
				}
				
				//this is a termination method clear out properties
				$this->XPathQuery = "";
				$this->ResultNodes = null;
				
				return $ReturnValue;
			}
		//<-- End Method :: GetNodeName
		
		//##################################################################################
		
		//--> Begin Method :: Remove
			public function Remove() {
				//emulate overloading with these argument count and vars
				$Nodes = null;
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//Remove()
				if($NumberOfArgs == 0) {
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					
					for($i = 0; $i < count($this->ResultNodes); $i++) {
						if(is_object($this->ResultNodes[$i]->parentNode)){
							$this->ResultNodes[$i]->parentNode->removeChild($this->ResultNodes[$i]);
						}
					}
				}
				else if($NumberOfArgs == 1) {
					$Nodes = $Args[0];
					
					//Remove(array $Nodes)
					if(gettype($Nodes) == "array") {
						for($i = 0; $i < count($Nodes); $i++) {
							$Nodes[$i]->parentNode->removeChild($Nodes[$i]);
						}
					}
					//Remove(node $Nodes)
					else{
						$Nodes->parentNode->removeChild($Nodes);
					}
				}
				
				//this is a termination method clear out properties
				$this->XPathQuery = "";
				$this->ResultNodes = null;
				
				//return this reference
				return $this;
			}
		//<-- End Method :: Remove
		
		//##################################################################################
		
		//--> Begin Method :: RemoveAttribute
			public function RemoveAttribute() {
				//emulate overloading with these argument count and vars
				$Nodes = null;
				$Attribute = "";
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//RemoveAttribute($Attribute)
				if($NumberOfArgs == 1) {
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					
					$Attribute = $Args[0];
		
					for($i = 0; $i < count($this->ResultNodes); $i++) {
						$this->ResultNodes[$i]->removeAttribute($Attribute);
					}
				}
				else if($NumberOfArgs == 2) {
					$Nodes = $Args[0];
					$Attribute = $Args[1];
					
					//RemoveAttribute(array $Nodes, string $Attribute)
					if(gettype($Nodes) == "array") {
						for($i = 0; $i < count($Nodes); $i++) {
							$Nodes[$i]->removeAttribute($Attribute);
						}
					}
					//RemoveAttribute(node $Nodes, string $Attribute)
					else{
						$Nodes->removeAttribute($Attribute);
					}
				}
				
				return $this;
			}
		//<-- End Method :: RemoveAttribute
		
		//##################################################################################
		
		//--> Begin Method :: RemoveAllAttributes
			public function RemoveAllAttributes() {
				//emulate overloading with these argument count and vars
				$Nodes = null;
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//RemoveAllAttributes()
				if($NumberOfArgs == 0) {
					//execute query if is_null(ResultNodes)
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					
					for($i = 0; $i < count($this->ResultNodes); $i++) {
						while($this->ResultNodes[$i]->attributes->length != 0) {
							$this->ResultNodes[$i]->removeAttribute($this->ResultNodes[$i]->attributes->item(0)->nodeName);
						}
					}
				}
				else if($NumberOfArgs == 1) {
					$Nodes = $Args[0];
					
					//RemoveAllAttributes(array $Nodes)
					if(gettype($Nodes) == "array") {
						for($i = 0; $i < count($Nodes); $i++) {
							while($Nodes[$i]->attributes->length != 0) {
								$Nodes[$i]->removeAttribute($Nodes[$i]->attributes->item(0)->nodeName);
							}
						}
					}
					//RemoveAllAttributes(node $Nodes)
					else{
						while($Nodes->attributes->length != 0) {
							$Nodes->removeAttribute($Nodes->attributes->item(0)->nodeName);
						}
					}
				}
				
				return $this;
			}
		//<-- End Method :: RemoveAllAttributes
		
		//##################################################################################
		
		//--> Begin Method :: GetNodes
			public function GetNodes() {
				//execute query
				$this->ExecuteQuery();
				
				$ReturnValue = @$this->ResultNodes;
				
				//this is a termination method clear out properties
				$this->XPathQuery = "";
				$this->ResultNodes = null;
				
				return $ReturnValue;
			}
		//<-- End Method :: GetNodes
		
		//##################################################################################
		
		//--> Begin Method :: GetNode
			public function GetNode() {
				//execute query
				$this->ExecuteQuery();
				
				$ReturnValue = null;
				if(count($this->ResultNodes) > 0){
					$ReturnValue = $this->ResultNodes[0];
				}
		
				//this is a termination method clear out properties
				$this->XPathQuery = "";
				$this->ResultNodes = null;
				
				return $ReturnValue;
			}
		//<-- End Method :: GetNode
		
		//##################################################################################
		
		//--> Begin Method :: GetNodesAsString
			public function GetNodesAsString() {
				//execute query
				$this->ExecuteQuery();
				
				//get the node array
				$XMLNodes = $this->ResultNodes;
				
				//this is a termination method clear out properties
				$this->XPathQuery = "";
				$this->ResultNodes = null;
				
				//output container
				$ReturnValue = "";
				
				//loop over items and build string
				for($i = 0 ; $i < count($XMLNodes); $i++) {
					$ReturnValue .= $this->ToString($XMLNodes[$i]);
				}
				
				return $ReturnValue;
			}
		//<-- End Method :: GetNodesAsString
		
		//##################################################################################
		
		//--> Begin Method :: ReplaceNode
			public function ReplaceNode() {
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				if($NumberOfArgs == 2){
					$Args[0]->parentNode->replaceChild($Args[1], $Args[0]);
				}
				else if($NumberOfArgs == 1){
					//execute query if ResultNodes == null
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					$this->ReplaceNode($this->ResultNodes[0], $Args[0]);
				}
				
				return $this;
			}
		//<-- End Method :: ReplaceNode
		
		//##################################################################################
		
		//--> Begin Method :: RenameNode
			public function RenameNode() {
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//RenameNode(NodeType)
				if($NumberOfArgs == 1){
					
					//execute query if ResultNodes == null
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					$this->RenameNodes($this->ResultNodes[0], $Args[0]);
				}
				//RenameNodes(Node, NodeType)
				else if($NumberOfArgs == 2){
					$this->RenameNodes($Args[0], $Args[1]);
				}
				return $this;
			}
		//<-- End Method :: RenameNode
				
		//##################################################################################
		
		//--> Begin Method :: RenameNodes
			public function RenameNodes() {
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//RenameNodes(NodeType)
				if($NumberOfArgs == 1){
					
					//execute query if ResultNodes == null
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					 
					for($i = 0; $i < count($this->ResultNodes); $i++) {
						$this->RenameNodes($this->ResultNodes[$i], $Args[0]);
					}
				}
				else if($NumberOfArgs == 2){
					//RenameNodes(Nodes[], NodeType)
					if(gettype($Args[0]) == "array"){
						for($i = 0; $i < count($Args[0]); $i++){
							$this->RenameNodes($Args[0], $Args[1]);
						}
					}
					//RenameNodes(Nodes, NodeType)
					else{
						$ThisNode = $Args[0];
						$NewNode = $this->DOMDocument->createElement($Args[1], "");
						
						//set attributes
						foreach ($ThisNode->attributes as $attrName => $attrNode) {
							$NewNode->setAttribute($attrNode->name, $attrNode->value);
						}
						
						//set children
						for($i = 0; $i < $ThisNode->childNodes->length; $i++) {
							//accomodate for textnodes
							if($NewNode InstanceOf stdClass){
								$NewNode->nodeValue .= $ThisNode->childNodes->item($i)->nodeValue;
							}
							else{
								$NewNode->appendChild($ThisNode->childNodes->item($i)->cloneNode(true));
							}
						}
						
						//replace nodes
						$this->ReplaceNode($ThisNode, $NewNode);
					}
				}
				return $this;
			}
		//<-- End Method :: RenameNodes
		
		//##################################################################################
		
		//--> Begin Method :: ReplaceInnerString
			public function ReplaceInnerString($This, $WithThat) {
				//default to html
				if($this->XPathQuery == ""){
					$this->XPathQuery = "/html";
				}
				
				//execute query if ResultNodes == null
				if(is_null($this->ResultNodes)) {
					//execute query
					$this->ExecuteQuery();
				}
				
				$ThisNode = $this->ResultNodes[0];
				$Source = $this->GetInnerHTML($ThisNode);
				$Source = str_replace($This, $WithThat, $Source);
				$this->SetInnerHTML($ThisNode, $Source);
				
				//return this reference
				return $this;
			}
		//<-- End Method :: ReplaceInnerString
		
		//##################################################################################
		
		//--> Begin Method :: GetInnerSubString
			public function GetInnerSubString($Start, $End) {
				//execute query if ResultNodes == null
				if(is_null($this->ResultNodes)) {
					//execute query
					$this->ExecuteQuery();
				}
				$Source = $this->GetInnerHTML($this->ResultNodes[0]);
				
				$MyStart = 0;
				$MyEnd = 0;
				
				if(stripos($Source, $Start) != false && strripos($Source, $End) != false) {
					$MyStart = (stripos($Source, $Start)) + strlen($Start);
					$MyEnd = strripos($Source, $End);
					try {
						return substr($Source, $MyStart, $MyEnd - $MyStart);
					}
					catch(Exception $e) {
						throw new Exception("WebLegs.DOMTemplate.GetInnerSubString: Boundry string mismatch.");
					}
				}
				else {
					throw new Exception("WebLegs.DOMTemplate.GetInnerSubString: Boundry strings not present in source string.");
				} 
				
			}
		//<-- End Method :: GetInnerSubString
		
		//##################################################################################
		
		//--> Begin Method :: RemoveInnerSubString
			public function RemoveInnerSubString($Start, $End, $RemoveKeys = false) {
				//default to html
				if($this->XPathQuery == ""){
					$this->XPathQuery = "/html";
				}
				
				//execute query if ResultNodes == null
				if(is_null($this->ResultNodes)) {
					//execute query
					$this->ExecuteQuery();
				}
				
				$ThisNode = $this->ResultNodes[0];
				$Source = $this->GetInnerHTML($ThisNode);
				$SubString = "";
				
				//try to get the sub string and remove
				try {
					$SubString = $this->GetInnerSubString($Start, $End);
					$Source = str_replace($SubString, "", $Source);
				}
				catch(Exception $e) {
					throw new Exception("WebLegs.DOMTemplate.RemoveInnerSubString(): Boundry string mismatch.");
				}
				
				//should we remove the keys too?
				if($RemoveKeys) {
					$Source = str_replace($Start, "", $Source);
					$Source = str_replace($End, "", $Source);
				}
				//load this back into the dom
				$this->SetInnerHTML($ThisNode, $Source);

				//return this reference
				return $this;
			}
		//<-- End Method :: RemoveInnerSubString
		
		//##################################################################################
		
		//--> Begin Method :: SaveAs
			public function SaveAs($FilePath) {
				if(file_put_contents($FilePath, $this->ToString()) == false){
					 throw new Exception("WebLegs.DOMTemplate.SaveAs(): Unable to save file.");
				}
			} 
		//<-- End Method :: ToString
		
		//##################################################################################
		
		//--> Begin Method :: AppendChild
			public function AppendChild(){
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//AppendChild(Node ParentNode, Node ThisNode)
				if($NumberOfArgs == 2){
					$Args[0]->appendChild($Args[1]);
				}
				//AppendChild(Node ThisNode)
				else{
					//execute query if ResultNodes == null
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					$this->AppendChild($this->ResultNodes[0], $Args[0]);
				}
				return $this;
			}
		//<-- End Method :: AppendChild
		
		//##################################################################################
		
		//--> Begin Method :: PrependChild
			public function PrependChild() {
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//PrependChild(Node ParentNode, Node ThisNode)
				if($NumberOfArgs == 2){
					$Args[0]->insertBefore($Args[1], $Args[0]->firstChild);
				}
				//PrependChild(Node ThisNode)
				else{
					//execute query if ResultNodes == null
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					$this->PrependChild($this->ResultNodes[0], $Args[0]);
				}
				
				return $this;
			}
		//<-- End Method :: PrependChild
		
		//##################################################################################
		
		//--> Begin Method :: InsertAfter
			public function InsertAfter() {
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//InsertAfter(Node RefNode, Node ThisNode)
				if($NumberOfArgs == 2){
					//determine if the ref node is the last node
					if($Args[0]->parentNode->lastChild === $Args[0]){
						$Args[0]->parentNode->appendChild($Args[1]);
					}
					//its not the last node
					else{
						$Args[0]->parentNode->insertBefore($Args[1], $Args[0]->nextSibling);
					}
				}
				//InsertAfter(Node ThisNode)
				else{
					//execute query if ResultNodes == null
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					$this->InsertAfter($this->ResultNodes[0], $Args[0]);
				}
				
				return $this;
			}
		//<-- End Method :: InsertAfter
		
		//##################################################################################
		
		//--> Begin Method :: InsertBefore
			public function InsertBefore() {
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();
				
				//InsertBefore(Node RefNode, Node ThisNode)
				if($NumberOfArgs == 2){
					$Args[0]->parentNode->insertBefore($Args[1], $Args[0]);
				}
				//InsertBefore(Node ThisNode)
				else{
					//execute query if ResultNodes == null
					if(is_null($this->ResultNodes)) {
						//execute query
						$this->ExecuteQuery();
					}
					$this->InsertBefore($this->ResultNodes[0], $Args[0]);
				}
				return $this;
			}
		//<-- End Method :: InsertBefore
	}
//<-- End Class :: DOMTemplate

//##########################################################################################
?>