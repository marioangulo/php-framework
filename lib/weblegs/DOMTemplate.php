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

class DOMTemplate {
    public $xpathQuery;
    public $domDocument;
    public $domXpath;
    public $resultNodes;
    public $basePath;
    public $dtd;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->xpathQuery = "";
        $this->domXpath = null;
        $this->domDocument = new DOMDocument();
        $this->domDocument->substituteEntities = true;
        $this->domDocument->strictErrorChecking = false;
        $this->resultNodes = array();
        $this->basePath = "";
        $this->dtd = "";
    }
    
    /**
     * set the xpath manually
     * @param string $value The xpath to set
     * @return this Object chaining
     */
    public function traverse($value) {
        //clear out results nodes
        $this->resultNodes = null;
        
        //set the xpath query
        $this->xpathQuery .= $value;
        
        return $this;
    }
    
    /**
     * get's a dom chunk using the current xpath query
     * @param string $value The xpath to set
     * @return this Object chaining
     */
    public function getDOMChunk() {
        $returnData = new DOMChunk($this);
        $this->xpathQuery = "";
        return $returnData;
    }
    
    /**
     * loads a file into the document
     * @param string $path The file path
     * @param string $rootPath The path where we can find xsl
     * @return this Object chaining
     */
    public function loadFile($path, $rootPath = null) {
        //make sure file exists
        if(!file_exists($path)){
            throw new Exception("Weblegs.DOMTemplate.loadFile(): File not found or not able to access. (". $path .")");
        }
    
        //load up file
        $source = file_get_contents($path);
        $this->load($source, $rootPath);
        
        //return this reference
        return $this;
    }
    
    /**
     * loads a string into the document
     * @param string $source The string to load
     * @param string $rootPath The path where we can find XSL
     * @return this Object chaining
     */
    public function load($source, $rootPath = null) {
        $source = str_replace("&", "&amp;", $source);  // disguise &s going IN to loadXML() 
        
        if(is_null($rootPath)) {
            //load up our dom object
            $this->domDocument->loadXML($source);
            
            //setup the xpath object
            $this->domXpath = new DOMXPath($this->domDocument);
            
            return;
        }
        //see if there is any stylesheets
        else if(strpos($source, "xml-stylesheet") == false) {
            //load up our dom object
            $this->domDocument->loadXML($source);
            
            //setup the xpath object
            $this->domXpath = new DOMXPath($this->domDocument);
            return;
        }
        
        //find the xsl style sheet path in our document
        preg_match("/xml-stylesheet.*?href=[\"|'](.*?)[\"|']/", $source, $matches);
        
        $xsltPath = $matches[1];
        
        //get dtd
        preg_match("/(<!DOCTYPE.*?>)/", $source, $matches);
        if(count($matches) > 0){
            $this->dtd = $matches[1];
            
            //strip out dtd
            $source = str_replace($this->dtd, "", $source);
        }
        
        //loat xml source
        $xmlDoc = new DOMDocument();
        $xmlDoc->substituteEntities = true;
        $xmlDoc->loadXML($source);
        
        //create a xslt document
        $xsltDoc = new DOMDocument();
        $xsltDoc->substituteEntities = true;
        $xsltSource = file_get_contents($rootPath . $xsltPath);
        $xsltDoc->loadXML($xsltSource);
        
        //create an xslt processor and load style sheet
        $xsltProcess = new XSLTProcessor();
        $xsltProcess->setParameter("", "root_path", $rootPath);
        $xsltProcess->importStylesheet($xsltDoc);
        
        //transform the xml and load up our dom object
        $this->domDocument = $xsltProcess->transformToDoc($xmlDoc);
        
        //setup the xpath object
        $this->domXpath = new DOMXPath($this->domDocument);
        
        //clear XPathQuery
        $this->xpathQuery = "";
        
        //clear node results
        $this->resultNodes = null;
        
        //return this reference
        return $this;
    }
    
    /**
     * executes an xpath query
     * @param string $xpathQuery The xpath query to run
     * @return this Object chaining
     */
    public function executeQuery($xpathQuery = "") {
        //check for empty document
        //DOMXPath is only instantiated when we loadFile() or load()
        //these functions were never called if this is the case
        if(is_null($this->domXpath)) {
            return $this;
        }
        
        //this is the overload
        if($xpathQuery != "") {
            $nodes = $this->domXpath->query($this->basePath . $xpathQuery, $this->domDocument);
            $returnNodes = array();
            for($i = 0; $i < $nodes->length; $i++) {
                $returnNodes[] = $nodes->item($i);
            }
            return $returnNodes;
        }
        
        //if its blank default to whole document
        if($this->basePath == "" && $this->xpathQuery == "") {
            $this->xpathQuery = "//*";
        }
        //this accomodates for the duplicate queries in both the basepath and XPathquery
        //this can happen when attempting to access the parent node in a DOMChunk
        else if($this->basePath == $this->xpathQuery){
            $this->xpathQuery = "";
        }
        
        $returnNodes = array();
        $nodes = $this->domXpath->query($this->basePath . $this->xpathQuery, $this->domDocument);
        for($i = 0; $i < $nodes->length; $i++) {
            $returnNodes[] = $nodes->item($i);
        }
        
        //clear XPathQuery
        $this->xpathQuery = "";
        
        //set node results
        $this->resultNodes = $returnNodes;
        
        return $this;
    }
    
    /**
     * returns a string representation of the document
     * @return string Transformed document
     */
    public function toString() {
        $argCount = func_num_args();
        $args = func_get_args();
        
        //ToString entire document - ToString()
        if($argCount == 0){
            //check for empty document
            //DOMXPath is only instantiated when we loadFile() or load()
            //these functions were never called if this is the case
            if(is_null($this->domXpath)) {
                return "";
            }
            
            //get dtd
            $outputSource = $this->domDocument->saveXML(null, LIBXML_NOEMPTYTAG);
            preg_match("/(<!DOCTYPE.*?>)/", $outputSource, $matches);
            if(count($matches) > 0){
                $wrongDTD = $matches[1];
                
                //strip out dtd
                $outputSource = str_replace($wrongDTD, "", $outputSource);
            }
            
            //strip out xml declaration
            $outputSource = str_replace("<?xml version=\"1.0\"?>", "", $outputSource);
            
            //unhide entities
            $outputSource = str_replace("&amp;", "&", $outputSource);  // undisguise &s
            
            //fix singleton tag issues
            $outputSource = preg_replace("/><\/(area|base|br|col|command|embed|hr|img|input|link|meta|param|source)>/", " />", $outputSource);
            
            return $this->dtd . $outputSource;
        }
        //ToString an array of Nodes - ToString(NodeList ThisNodeList)
        else if($args[0] instanceof DOMNodeList){
            $returnData = "";
            $thisNodeList = $args[0];
            for($i = 0; $i < $thisNodeList->length; $i++) {
                $returnData .= $this->toString($thisNodeList->item($i));
            }
            return $returnData;
        }
        //ToString single Nodes - ToString(Node ThisNode)
        else if($args[0] instanceof DomNode){
            $importNode;
            if(get_class($args[0]) == "DOMDocument"){
                $importNode = $args[0]->documentElement;
            }
            else{
                $importNode = $args[0];
            }
            
            $returnData = "";
            $tmpDoc = new DOMDocument();
            $tmpDoc->substituteEntities = true;
            $tmpDoc->appendChild($tmpDoc->importNode($importNode, true));
            $outputSource = $tmpDoc->saveXML(null, LIBXML_NOEMPTYTAG);
            
            //unhide entities
            $outputSource = str_replace("&amp;", "&", $outputSource);  // undisguise &s
            
            //strip out xml declaration
            $outputSource = str_replace("<?xml version=\"1.0\"?>", "", $outputSource);
            
            //fix singleton tag issues
            $outputSource = preg_replace("/><\/(area|base|br|col|command|embed|hr|img|input|link|meta|param|source)>/", " />", $outputSource);
            
            return $outputSource;
        }
    }
    
    /**
     * helps build an xpath query 
     * @param string $tagName the xhtml tag name
     * @return this Object chaining
     */
    public function getNodesByTagName($tagName) {
        //clear out results nodes
        $this->resultNodes = null;
        
        //set the xpath query
        $this->xpathQuery .= "//". $tagName;
        
        return $this;
    }
    
    /**
     * helps build an xpath query 
     * @param string $value the value of the id attribute
     * @return this Object chaining
     */
    public function getNodeByID($value) {
        //clear out results nodes
        $this->resultNodes = null;
        
        //set the xpath query
        $this->xpathQuery .= "//*[@id='". $value ."']";
        
        return $this;
    }
    
    /**
     * helps build an xpath query 
     * @param string $attribute the attribute name
     * @param string $value the attribute value
     * @return this Object chaining
     */
    public function getNodesByAttribute($attribute, $value = null) {
        //clear out results nodes
        $this->resultNodes = null;
        
        //GetNodesByAttribute($value)
        if(is_null($value)) {
            //set the xpath query
            $this->xpathQuery .= "//*[@". $attribute ."]";
        }
        //GetNodesByAttribute($attribute, $value)
        else{
            //set the xpath query
            $this->xpathQuery .= "//*[@". $attribute ."='". $value ."']";
        }
        
        return $this;
    }
    
    /**
     * helps build an xpath query 
     * @param string $attribute the data- attribute name
     * @param string $value the attribute value
     * @return this Object chaining
     */
    public function getNodesByDataSet($attribute, $value = null) {
        //clear out results nodes
        $this->resultNodes = null;
        
        //use GetNodesByAttribute
        $this->getNodesByAttribute("data-". $attribute, $value);
        
        return $this;
    }
    
    /**
     * helps build an xpath query 
     * @param array $attributes an array of attributes to look for
     * @return this Object chaining
     */
    public function getNodesByAttributes($attributes) {
        //clear out results nodes
        $this->resultNodes = null;
        
        $query = "";
        $counter = 0;
        $count = count($attributes);
        foreach($attributes as $key => $value) {
            $query .= "@". $key ."='". $value ."'";
            if(($counter + 1) != $count) {
                $query .= " and ";
            }
            $counter++;
        }
        
        //set the xpath query
        $this->xpathQuery .= "//*[". $query ."]";
        
        //execute query
        return $this;
    }
    
    /**
     * sets the attribute of a node
     * @overload #1 (string $attribute, string $value)
     * @overload #2 (array $nodes, string $attribute, string $value)
     * @return this Object chaining
     */
    public function setAttribute() {
        //emulate overloading with these argument count and vars
        $nodes = null;
        $attribute = "";
        $value = "";
        $argCount = func_num_args();
        $args = func_get_args();
        
        //SetAttribute(string $attribute, string $value)
        if($argCount == 2) {        
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
        
            //set argument vars
            $attribute = $args[0];
            $value = $args[1];
            
            for($i = 0; $i < count($this->resultNodes); $i++) {
                $this->resultNodes[$i]->setAttribute($attribute, $value);
            }
        
        }
        //SetAttribute(array $nodes, string $attribute, string $value)
        else if($argCount == 3) {
            //set argument vars
            $nodes = $args[0];
            $attribute = $args[1];
            $value = $args[2];
            
            //SetAttribute(array $nodes, string $attribute, string $value)
            if(gettype($nodes) == "array") {
                for($i = 0; $i < count($nodes); $i++) {
                    $nodes[$i]->setAttribute($attribute, $value);
                }
            }
            //SetAttribute(node $nodes, string $attribute, string $value)
            else{
                $nodes->setAttribute($attribute, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * gets the attribute of a node
     * @overload #1 (string $attribute)
     * @overload #2 (Node $node, string $attribute)
     * @return this Object chaining
     */
    public function getAttribute() {
        //emulate overloading with these argument count and vars
        $nodes = null;
        $attribute = "";
        $argCount = func_num_args();
        $args = func_get_args();
        $returnValue = "";
        
        //GetAttribute(string $attribute)
        if($argCount == 1) {
        
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
        
            $attribute = $args[0];
            $returnValue = $this->resultNodes[0]->getAttribute($attribute);
        }
        //GetAttribute(node $node, string $attribute)
        else if($argCount == 2) {
            $node = $args[0];
            $attribute = $args[1];
            $returnValue = $node->getAttribute($attribute);
        }
        
        //this is a termination method clear out properties
        $this->xpathQuery = "";
        $this->resultNodes = null;
        
        return $returnValue;
    }
    
    /**
     * sets the inner html of a node
     * @overload #1 (string $value)
     * @overload #2 (array $nodes, string $value)
     * @overload #3 (Node $node, string $value)
     * @return this Object chaining
     */
    public function setInnerHTML() {
        //emulate overloading with these argument count and vars
        $nodes = null;
        $value = "";
        $argCount = func_num_args();
        $args = func_get_args();
        $returnValue = "";
        
        //SetInnerHTML(string $value)
        if($argCount == 1) {
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            
            //get value arg
            $value = $args[0];
            for($i = 0; $i < count($this->resultNodes); $i++) {
                $this->setInnerHTML($this->resultNodes[$i], $value);
            }
        }
        else if($argCount == 2) {
            $nodes = $args[0];
            $value = $args[1];
            
            //SetInnerHTML(array $nodes, string $value)
            if(gettype($nodes) == "array") {
                for($i = 0; $i < count($nodes); $i++) {
                    $this->setInnerHTML($nodes[$i], $value);
                }
            }
            //SetInnerHTML(node $node, string $value)
            else{
                $node = $args[0];
                $node->nodeValue = null;
                
                //load source
                $tmpDOMDocument = new DOMDocument();
                $tmpDOMDocument->validateOnParse = false;
                
                if($node->nodeName == "html"){
                    @$tmpDOMDocument->loadHTML($value);
                }
                else{
                    @$tmpDOMDocument->loadXML("<container-root>". $value ."</container-root>");
                }
                
                $newNode = $this->domDocument->importNode($tmpDOMDocument->documentElement, true);
                
                for($i = 0; $i < $newNode->childNodes->length; $i++) {
                    //accomodate for textnodes
                    if($node instanceof stdClass){
                        $node->nodeValue .= $newNode->childNodes->item($i)->nodeValue;
                    }
                    else{
                        $node->appendChild($newNode->childNodes->item($i)->cloneNode(true));
                    }
                }
            }
        }
        return $this;
    }
    
    /**
     * gets the outer html of a node
     * @overload #1 ()
     * @overload #2 (Node $node)
     * @return this Object chaining
     */
    public function getOuterHTML() {
        //emulate overloading with these argument count and vars
        $node = null;
        $argCount = func_num_args();
        $args = func_get_args();
        $returnValue = "";
        
        //GetOuterHTML()
        if($argCount == 0) {
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            
            $returnValue = $this->getOuterHTML($this->resultNodes[0]);
        }
        //GetOuterHTML(node $node)
        else if($argCount == 1) {
            $returnValue = $this->toString($args[0]);
        }
        
        //this is a termination method clear out properties
        $this->xpathQuery = "";
        $this->resultNodes = null;
        
        return $returnValue;
    }
    
    /**
     * gets the inner html of a node
     * @overload #1 ()
     * @overload #2 (Node $node)
     * @return this Object chaining
     */
    public function getInnerHTML() {
        //emulate overloading with these argument count and vars
        $node = null;
        $argCount = func_num_args();
        $args = func_get_args();
        $returnValue = "";
        
        //GetInnerHTML()
        if($argCount == 0) {
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            
            if(array_key_exists(0, $this->resultNodes)){
                $returnValue = $this->getInnerHTML($this->resultNodes[0]);
            }
            else{
                $returnValue = "";
            }
        }
        //GetInnerHTML(node $node)
        else if($argCount == 1) {
            $returnValue = $this->toString($args[0]->childNodes);
        }
        
        //this is a termination method clear out properties
        $this->xpathQuery = "";
        $this->resultNodes = null;
        
        return $returnValue;
    }
    
    /**
     * sets the inner text of a node
     * @overload #1 (string $value)
     * @overload #2 (array $nodes, $value)
     * @overload #3 (Node $node, $value)
     * @return this Object chaining
     */
    public function setInnerText() {
        //emulate overloading with these argument count and vars
        $nodes = null;
        $value = "";
        $argCount = func_num_args();
        $args = func_get_args();
        
        //SetInnerText($value)
        if($argCount == 1) {
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            
            //set argument
            $value = $args[0];
            
            for($i = 0; $i < count($this->resultNodes); $i++) {
                $this->resultNodes[$i]->nodeValue = $value;
            }
        }
        else if($argCount == 2) {
            $nodes = $args[0];
            $value = $args[1];
            
            //SetInnerText(array $nodes, string $value)
            if(gettype($nodes) == "array") {
                for($i = 0; $i < count($nodes); $i++) {
                    $nodes[$i]->nodeValue = $value;
                }
            }
            //SetInnerText(node $nodes, string $value)
            else{
                $nodes->nodeValue = $value;
            }
        }
        
        return $this;
    }
    
    /**
     * gets the inner text of a node
     * @overload #1 ()
     * @overload #2 (Node $node, $value)
     * @return this Object chaining
     */
    public function getInnerText() {
        //emulate overloading with these argument count and vars
        $node = null;
        $argCount = func_num_args();
        $args = func_get_args();
        $returnValue = "";
        
        //GetInnerText()
        if($argCount == 0) {
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            
            $returnValue = $this->resultNodes[0]->nodeValue;
        }
        //GetInnerText(node $node)
        else if($argCount == 1) {
            $node = $args[0];
            $returnValue = $node->nodeValue;
        }
        
        //this is a termination method clear out properties
        $this->xpathQuery = "";
        $this->resultNodes = null;
        
        return $returnValue;
    }
    
    /**
     * gets the name of the node
     * @overload #1 ()
     * @overload #2 (Node $node, $value)
     * @return this Object chaining
     */
    public function getNodeName() {
        //emulate overloading with these argument count and vars
        $node = null;
        $argCount = func_num_args();
        $args = func_get_args();
        $returnValue = "";
        
        //GetInnerText()
        if($argCount == 0) {
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            
            $returnValue = $this->resultNodes[0]->nodeName;
        }
        //GetInnerText(node $node)
        else if($argCount == 1) {
            $node = $args[0];
            $returnValue = $node->nodeName;
        }
        
        //this is a termination method clear out properties
        $this->xpathQuery = "";
        $this->resultNodes = null;
        
        return $returnValue;
    }
    
    /**
     * removes a node
     * @overload #1 ()
     * @overload #2 (array $nodes, $value)
     * @overload #3 (Node $node, $value)
     * @return this Object chaining
     */
    public function remove() {
        //emulate overloading with these argument count and vars
        $nodes = null;
        $argCount = func_num_args();
        $args = func_get_args();
        
        //Remove()
        if($argCount == 0) {
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            
            for($i = 0; $i < count($this->resultNodes); $i++) {
                if(is_object($this->resultNodes[$i]->parentNode)){
                    $this->resultNodes[$i]->parentNode->removeChild($this->resultNodes[$i]);
                }
            }
        }
        else if($argCount == 1) {
            $nodes = $args[0];
            
            //Remove(array $nodes)
            if(gettype($nodes) == "array") {
                for($i = 0; $i < count($nodes); $i++) {
                    $nodes[$i]->parentNode->removeChild($nodes[$i]);
                }
            }
            //Remove(node $nodes)
            else{
                $nodes->parentNode->removeChild($nodes);
            }
        }
        
        //this is a termination method clear out properties
        $this->xpathQuery = "";
        $this->resultNodes = null;
        
        //return this reference
        return $this;
    }
    
    /**
     * removes an attribute from a node
     * @overload #1 ($attribute)
     * @overload #2 (array $nodes, $attribute)
     * @overload #3 (Node $node, $attribute)
     * @return this Object chaining
     */
    public function removeAttribute() {
        //emulate overloading with these argument count and vars
        $nodes = null;
        $attribute = "";
        $argCount = func_num_args();
        $args = func_get_args();
        
        //RemoveAttribute($attribute)
        if($argCount == 1) {
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            
            $attribute = $args[0];
            
            for($i = 0; $i < count($this->resultNodes); $i++) {
                $this->resultNodes[$i]->removeAttribute($attribute);
            }
        }
        else if($argCount == 2) {
            $nodes = $args[0];
            $attribute = $args[1];
            
            //RemoveAttribute(array $nodes, string $attribute)
            if(gettype($nodes) == "array") {
                for($i = 0; $i < count($nodes); $i++) {
                    $nodes[$i]->removeAttribute($attribute);
                }
            }
            //RemoveAttribute(node $nodes, string $attribute)
            else{
                $nodes->removeAttribute($attribute);
            }
        }
        
        return $this;
    }
    
    /**
     * removes all the attribute from a node
     * @overload #1 ($attribute)
     * @overload #2 (array $nodes, $attribute)
     * @overload #3 (Node $node, $attribute)
     * @return this Object chaining
     */
    public function removeAllAttributes() {
        //emulate overloading with these argument count and vars
        $nodes = null;
        $argCount = func_num_args();
        $args = func_get_args();
        
        //RemoveAllAttributes()
        if($argCount == 0) {
            //execute query if is_null(ResultNodes)
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            
            for($i = 0; $i < count($this->resultNodes); $i++) {
                while($this->resultNodes[$i]->attributes->length != 0) {
                    $this->resultNodes[$i]->removeAttribute($this->resultNodes[$i]->attributes->item(0)->nodeName);
                }
            }
        }
        else if($argCount == 1) {
            $nodes = $args[0];
            
            //RemoveAllAttributes(array $nodes)
            if(gettype($nodes) == "array") {
                for($i = 0; $i < count($nodes); $i++) {
                    while($nodes[$i]->attributes->length != 0) {
                        $nodes[$i]->removeAttribute($nodes[$i]->attributes->item(0)->nodeName);
                    }
                }
            }
            //RemoveAllAttributes(node $nodes)
            else{
                while($nodes->attributes->length != 0) {
                    $nodes->removeAttribute($nodes->attributes->item(0)->nodeName);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * gets the result nodes from the executed xpath query
     * @return array Result nodes
     */
    public function getNodes() {
        //execute query
        $this->executeQuery();
        
        $returnValue = @$this->resultNodes;
        
        //this is a termination method clear out properties
        $this->xpathQuery = "";
        $this->resultNodes = null;
        
        return $returnValue;
    }
    
    /**
     * gets the first node from the executed xpath query
     * @return Node First result node
     */
    public function getNode() {
        //execute query
        $this->executeQuery();
        
        $returnValue = null;
        if(count($this->resultNodes) > 0){
            $returnValue = $this->resultNodes[0];
        }
        
        //this is a termination method clear out properties
        $this->xpathQuery = "";
        $this->resultNodes = null;
        
        return $returnValue;
    }
    
    /**
     * gets the nodes from the executed xpath query as a string
     * @return string Nodes as string
     */
    public function getNodesAsString() {
        //execute query
        $this->executeQuery();
        
        //get the node array
        $xmlNodes = $this->resultNodes;
        
        //this is a termination method clear out properties
        $this->xpathQuery = "";
        $this->resultNodes = null;
        
        //output container
        $returnValue = "";
        
        //loop over items and build string
        for($i = 0 ; $i < count($xmlNodes); $i++) {
            $returnValue .= $this->toString($xmlNodes[$i]);
        }
        
        return $returnValue;
    }
    
    /**
     * replaces a node with another node
     * @param Node $oldNode
     * @param Node $newNode
     * @return this Object chaining
     */
    public function replaceNode() {
        $argCount = func_num_args();
        $args = func_get_args();
        
        if($argCount == 2){
            $args[0]->parentNode->replaceChild($args[1], $args[0]);
        }
        else if($argCount == 1){
            //execute query if ResultNodes == null
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            $this->replaceNode($this->resultNodes[0], $args[0]);
        }
        
        return $this;
    }
    
    /**
     * renames a node
     * @overload #1 (NodeType $type)
     * @overload #2 (Node $node, NodeType $type)
     * @return this Object chaining
     */
    public function renameNode() {
        $argCount = func_num_args();
        $args = func_get_args();
        
        //RenameNode(NodeType)
        if($argCount == 1){
            //execute query if ResultNodes == null
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            $this->renameNodes($this->resultNodes[0], $args[0]);
        }
        //RenameNodes(Node, NodeType)
        else if($argCount == 2){
            $this->renameNodes($args[0], $args[1]);
        }
        return $this;
    }
    
    /**
     * renames nodes
     * @overload #1 (NodeType $type)
     * @overload #2 (array $nodes, NodeType $type)
     * @overload #3 (Node $node, NodeType $type)
     * @return this Object chaining
     */
    public function renameNodes() {
        $argCount = func_num_args();
        $args = func_get_args();
        
        //RenameNodes(NodeType)
        if($argCount == 1){
            //execute query if ResultNodes == null
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
             
            for($i = 0; $i < count($this->resultNodes); $i++) {
                $this->renameNodes($this->resultNodes[$i], $args[0]);
            }
        }
        else if($argCount == 2){
            //RenameNodes(Nodes[], NodeType)
            if(gettype($args[0]) == "array"){
                for($i = 0; $i < count($args[0]); $i++){
                    $this->renameNodes($args[0], $args[1]);
                }
            }
            //RenameNodes(Node, NodeType)
            else{
                $thisNode = $args[0];
                $newNode = $this->domDocument->createElement($args[1], "");
                
                //set attributes
                foreach ($thisNode->attributes as $attrName => $attrNode) {
                    $newNode->setAttribute($attrNode->name, $attrNode->value);
                }
                
                //set children
                for($i = 0; $i < $thisNode->childNodes->length; $i++) {
                    //accomodate for textnodes
                    if($newNode InstanceOf stdClass){
                        $newNode->nodeValue .= $thisNode->childNodes->item($i)->nodeValue;
                    }
                    else{
                        $newNode->appendChild($thisNode->childNodes->item($i)->cloneNode(true));
                    }
                }
                
                //replace nodes
                $this->replaceNode($thisNode, $newNode);
            }
        }
        return $this;
    }
    
    /**
     * does a string replace on a node
     * @param string $replaceThis the string to replace
     * @param string $withThat the string to replace with
     * @return this Object chaining
     */
    public function replaceInnerString($replaceThis, $withThat) {
        //default to html
        if($this->xpathQuery == ""){
            $this->xpathQuery = "/html";
        }
        
        //execute query if ResultNodes == null
        if(is_null($this->resultNodes)) {
            //execute query
            $this->executeQuery();
        }
        
        $thisNode = $this->resultNodes[0];
        $source = $this->getInnerHTML($thisNode);
        $source = str_replace($replaceThis, $withThat, $source);
        $this->setInnerHTML($thisNode, $source);
        
        //return this reference
        return $this;
    }
    
    /**
     * gets a substring from the inner text of a node
     * @param string $start the starting text
     * @param end $end the ending text
     * @return string The sub string
     */
    public function getInnerSubString($start, $end) {
        //execute query if ResultNodes == null
        if(is_null($this->resultNodes)) {
            //execute query
            $this->executeQuery();
        }
        $source = $this->getInnerHTML($this->resultNodes[0]);
        
        $myStart = 0;
        $myEnd = 0;
        
        if(stripos($source, $start) != false && strripos($source, $end) != false) {
            $myStart = (stripos($source, $start)) + strlen($start);
            $myEnd = strripos($source, $end);
            try {
                return substr($source, $myStart, $myEnd - $myStart);
            }
            catch(Exception $e) {
                throw new Exception("Weblegs.DOMTemplate.getInnerSubString: Boundry string mismatch.");
            }
        }
        else {
            throw new Exception("Weblegs.DOMTemplate.getInnerSubString: Boundry strings not present in source string.");
        } 
        
    }
    
    /**
     * removes a substring from the inner text of a node
     * @param int $start the starting index
     * @param int $end the starting index
     * @param bool $removeKeys flags if we should remove the start and end of the substring too
     * @return this Object chaining
     */
    public function removeInnerSubString($start, $end, $removeKeys = false) {
        //default to html
        if($this->xpathQuery == ""){
            $this->xpathQuery = "/html";
        }
        
        //execute query if ResultNodes == null
        if(is_null($this->resultNodes)) {
            //execute query
            $this->executeQuery();
        }
        
        $thisNode = $this->resultNodes[0];
        $source = $this->getInnerHTML($thisNode);
        $subString = "";
        
        //try to get the sub string and remove
        try {
            $subString = $this->setInnerSubString($start, $end);
            $source = str_replace($subString, "", $source);
        }
        catch(Exception $e) {
            throw new Exception("Weblegs.DOMTemplate.removeInnerSubString(): Boundry string mismatch.");
        }
        
        //should we remove the keys too?
        if($removeKeys) {
            $source = str_replace($start, "", $source);
            $source = str_replace($end, "", $source);
        }
        //load this back into the dom
        $this->setInnerHTML($thisNode, $source);
        
        //return this reference
        return $this;
    }
    
    /**
     * saves the toString result to a path
     * @param string $path the path to save to
     */
    public function saveAs($path) {
        if(file_put_contents($path, $this->toString()) == false){
             throw new Exception("Weblegs.DOMTemplate.saveAs(): Unable to save file.");
        }
    }
    
    /**
     * appends one node to another
     * @overload #1 (Node $parentNode, Node $childNode)
     * @overload #2 (Node $childNode)
     * @return this Object chaining
     */
    public function appendChild(){
        $argCount = func_num_args();
        $args = func_get_args();
        
        //AppendChild(Node ParentNode, Node ThisNode)
        if($argCount == 2){
            $args[0]->appendChild($args[1]);
        }
        //AppendChild(Node ThisNode)
        else{
            //execute query if ResultNodes == null
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            $this->appendChild($this->resultNodes[0], $args[0]);
        }
        return $this;
    }
    
    /**
     * prepends one node to another
     * @overload #1 (Node $parentNode, Node $childNode)
     * @overload #2 (Node $childNode)
     * @return this Object chaining
     */
    public function prependChild() {
        $argCount = func_num_args();
        $args = func_get_args();
        
        //PrependChild(Node ParentNode, Node ThisNode)
        if($argCount == 2){
            $args[0]->insertBefore($args[1], $args[0]->firstChild);
        }
        //PrependChild(Node ThisNode)
        else{
            //execute query if ResultNodes == null
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            $this->prependChild($this->resultNodes[0], $args[0]);
        }
        
        return $this;
    }
    
    /**
     * inserts a node after another node
     * @overload #1 (Node $refNode, Node $newNode)
     * @overload #2 (Node $newNode)
     * @return this Object chaining
     */
    public function insertAfter() {
        $argCount = func_num_args();
        $args = func_get_args();
        
        //InsertAfter(Node RefNode, Node ThisNode)
        if($argCount == 2){
            //determine if the ref node is the last node
            if($args[0]->parentNode->lastChild === $args[0]){
                $args[0]->parentNode->appendChild($args[1]);
            }
            //its not the last node
            else{
                $args[0]->parentNode->insertBefore($args[1], $args[0]->nextSibling);
            }
        }
        //InsertAfter(Node ThisNode)
        else{
            //execute query if ResultNodes == null
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            $this->insertAfter($this->resultNodes[0], $args[0]);
        }
        
        return $this;
    }
    
    /**
     * inserts a node before another node
     * @overload #1 (Node $refNode, Node $newNode)
     * @overload #2 (Node $newNode)
     * @return this Object chaining
     */
    public function insertBefore() {
        $argCount = func_num_args();
        $args = func_get_args();
        
        //InsertBefore(Node RefNode, Node ThisNode)
        if($argCount == 2){
            $args[0]->parentNode->insertBefore($args[1], $args[0]);
        }
        //InsertBefore(Node ThisNode)
        else{
            //execute query if ResultNodes == null
            if(is_null($this->resultNodes)) {
                //execute query
                $this->executeQuery();
            }
            $this->insertBefore($this->resultNodes[0], $args[0]);
        }
        return $this;
    }
}
