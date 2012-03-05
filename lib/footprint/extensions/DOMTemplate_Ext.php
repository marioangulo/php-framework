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

class DOMTemplate_Ext extends DOMTemplate {
    public $domBinders;
    public $resultsCache;
    
    /**
     * construct the object
     */
    public function __construct() { 
        parent::__construct(); 
        
        $this->domBinders = array();
        $this->resultsCache = array();
    }
    
    /**
     * executes the major data binding logic
     * @param DOMChunk|DOMTemplate $chunk
     * @return string Transformed input
     */
    public function bindResources($chunk = null) {
        if(!isset($chunk)) {
            $chunk = $this;
        }
        
        //bind data results
        $nodes = $chunk->getNodesByDataSet("bind-results")->getNodes();
        for($i = 0 ; $i < count($nodes) ; $i++) {
            //get the resource name
            $name = $this->getAttribute($nodes[$i], "data-bind-results");
            
            //bind data table
            F::sysLog("<data-bind-results name=\"". $name ."\">");
            $this->bindResults($nodes[$i], $name);
            F::sysLog("</data-bind-results>");
        }
        
        //bind data rows
        $nodes = $chunk->getNodesByDataSet("bind-rows")->getNodes();
        for($i = 0 ; $i < count($nodes) ; $i++) {
            //get the resource name
            $name = $this->getAttribute($nodes[$i], "data-bind-rows");
            
            //bind data table
            F::sysLog("<data-bind-rows name=\"". $name ."\">");
            $this->bindRows($nodes[$i], $name);
            F::sysLog("</data-bind-rows>");
        }
        
        //bind data form
        $nodes = $chunk->getNodesByDataSet("bind-form")->getNodes();
        for($i = 0 ; $i < count($nodes) ; $i++) {
            //get the resource name
            $name = $this->getAttribute($nodes[$i], "data-bind-form");
            
            //bind data form
            F::sysLog("<data-bind-form name=\"". $name ."\">");
            $this->bindForm($nodes[$i], $name);
            F::sysLog("</data-bind-form>");
        }
    }
    
    /**
     * binds the results of sql queries to dom nodes
     * @param Node $node
     * @param string $resourceID
     */
    public function bindResults($node, $resourceID) {
        //stay in our boundries
        $id = uniqid();
        $node->setAttribute("data-bind-id", $id);
        $table = $this->traverse("//*[@data-bind-id='". $id ."']")->getDOMChunk();
        
        //column variables
        $totalColumns = (int)$table->root()->getAttribute("data-bind-results-columns");
        if($totalColumns == 0) { $totalColumns = 1; }
        $tmpItemsPerColumn = 0;
        if($totalColumns > 1) {
            //create our columns
            $column = $this->traverse("//*[@data-bind-id='". $id ."']")->getNodesByDataSet("label", "blank-column")->getDOMChunk();
            for($i = 1 ; $i <= $totalColumns ; $i++) {
                $column->begin();
                $column->root()->setAttribute("data-bind-id", $id ."-". $i);
                $column->end();
            }
            $column->render();
        }
        
        //setup data pager?
        if($table->root()->getAttribute("data-bind-paging") != "") {
            F::$timer->start();
            F::$dataPager->linkLoopOffset = 3; //(for paging)
            F::$dataPager->recordsPerPage = isset(F::$engineArgs[$resourceID ."-show_rows"]) ? F::$engineArgs[$resourceID ."-show_rows"] : F::$request->input($resourceID ."-show_rows", F::$request->input("show_rows", "20"));
            F::$dataPager->currentPage = isset(F::$engineArgs[$resourceID ."-page"]) ? F::$engineArgs[$resourceID ."-page"] : F::$request->input($resourceID ."-page", F::$request->input("page", "1"));
            
            //sql paging data
            F::$db->keyBinders["_limit_"] = F::$dataPager->recordsPerPage * (F::$dataPager->currentPage - 1) .", ". F::$dataPager->recordsPerPage;
        }
        
        //get data
            $data = array();
            if(!isset($this->resultsCache[$resourceID])) {
                F::$db->loadCommand($resourceID, F::$engineArgs);
                $this->resultsCache[$resourceID] = F::$db->getDataTable();
            }
            else {
                F::sysLog("<!--used cached result data-->");
            }
            $data = $this->resultsCache[$resourceID];
            
            //get found rows
            $tmpFoundRows = F::$db->getFoundRows();
            
            //apply data paging?
            if($table->root()->getAttribute("data-bind-paging") != "") {
                F::$timer->stop();
                F::$dataPager->totalRecords = $tmpFoundRows;
                $this->bindDataPaging($resourceID, $table->root()->getAttribute("data-bind-paging"));
            }
            
            //setup the extra data binder features
            $this->domBinders[$resourceID ."-found-rows"] = number_format($tmpFoundRows);
            $this->domBinders[$resourceID ."-count"] = count($data);
        //end get data
        
        //if columns, items per column
        if($totalColumns > 1) {
            $tmpItemsPerColumn = (int)(count($data) / $totalColumns);
            if(($tmpItemsPerColumn * $totalColumns) < count($data)) {
                $tmpItemsPerColumn++;
            }
        }
        $tmpItemsPerColumn_Original = $tmpItemsPerColumn;
        
        //build results
        $columnIndex = 1;
        if($totalColumns == 1) {
            //$row = $table->getNodesByDataSet("label", "blank-row")->getDOMChunk();
            $row = $this->traverse("//*[@data-bind-id='". $id ."']//*[@data-label='blank-row']")->getDOMChunk();
        }
        else {
            $row = $table->getNodesByDataSet("bind-id", $id ."-". $columnIndex)->getNodesByDataSet("label", "blank-row")->getDOMChunk();
        }
        for($i = 0 ; $i < count($data) ; $i++) {
            //if columns, split the rows up
            if($totalColumns > 1) {
                //have the columns been adjusted?
                if($tmpItemsPerColumn != $tmpItemsPerColumn_Original) {
                    $newi = (($i - 1) - $tmpItemsPerColumn_Original) + 1;
                    if($newi == ($tmpItemsPerColumn * ($columnIndex - 1))) {
                        $row->render();
                        $columnIndex++;
                        $row = $table->getNodesByDataSet("bind-id", $id ."-". $columnIndex)->getNodesByDataSet("label", "blank-row")->getDOMChunk();
                    }
                }
                //nope
                else if($i == ($tmpItemsPerColumn * $columnIndex)) {
                    $row->render();
                    $columnIndex++;
                    $row = $table->getNodesByDataSet("bind-id", $id ."-". $columnIndex)->getNodesByDataSet("label", "blank-row")->getDOMChunk();
                    
                    //try to fix un-even column division after we've filled the first column
                    if($i == $tmpItemsPerColumn_Original) {
                        $dataCountLeft = count($data) - $i;
                        $columnsLeft = $totalColumns - 1;
                        if($dataCountLeft % $columnsLeft == 0) {
                            $tmpItemsPerColumn = $dataCountLeft / $columnsLeft;
                        }
                    }
                }
            }
            
            $row->begin();
            F::sysLog("<data-row index=\"". $i ."\">");
            F::sysLog("<data>");
            F::sysLog(print_r($data[$i], true));
            F::sysLog("</data>");
            $this->dataBinder($data[$i], $row);
            F::sysLog("</data-row>");
            $row->end();
        }
        if(count($data) > 0) {
            //render chunk
            $row->render();
            
            //remove no-results-row
            $table->getNodesByDataSet("label", "no-results-row")->remove();
        }
        else{
            //remove blank-row
            $row->remove();
        }
        
        //cleanup empty columns
        if($totalColumns > 1) {
            if($columnIndex < $totalColumns) {
                for($i = ($columnIndex + 1) ; $i <= $totalColumns ; $i++) {
                    $this->traverse("//*[@data-bind-id='". $id ."']")->getNodesByDataSet("bind-id", $id ."-". $i)->remove();
                }
            }
        }
        
        //remove binders
        $table->getNodesByAttribute("data-bind-id")->removeAttribute("data-bind-id");
        $table->getNodesByAttribute("data-label", "blank-row")->removeAttribute("data-label");
        $table->getNodesByAttribute("data-label", "blank-column")->removeAttribute("data-label");
        $table->getNodesByAttribute("data-label", "no-results-row")->removeAttribute("data-label");
        $table->root()->removeAttribute("data-bind-results");
        $table->root()->removeAttribute("data-bind-results-columns");
        $table->root()->removeAttribute("data-bind-paging");
        $table->root()->removeAttribute("data-bind-id");
    }
    
    /**
     * runs the final bind on the document
     */
    public function finalBind($data = null) {
        if(!isset($data)) {
            $data = array();
        }
        //we merge input and dom-binders for a final binding
        $this->dataBinder(array_merge($data, $this->domBinders));
    }
    
    /**
     * binds the results of arrays to dom nodes
     * @param Node $node
     * @param string $resourceID
     */
    public function bindRows($node, $resourceID) {
        //stay in our boundries
        $id = uniqid();
        $node->setAttribute("data-bind-id", $id);
        $table = $this->traverse("//*[@data-bind-id='". $id ."']")->getDOMChunk();
        $row = $this->traverse("//*[@data-bind-id='". $id ."']")->getNodesByDataSet("label", "blank-row")->getDOMChunk();
        
        //get the data
        $data = array();
        if(isset(F::$customRows[$resourceID])) {
            $data = F::$customRows[$resourceID];
        }
        
        //build results
        for($i = 0 ; $i < count($data); $i++) {
            $row->begin();
            F::sysLog("<data-row index=\"". $i ."\">");
            F::sysLog("<data>");
            F::sysLog(print_r($data[$i], true));
            F::sysLog("</data>");
            $this->dataBinder($data[$i], $row);
            F::sysLog("</data-row>");
            $row->end();
        }
        if(count($data) > 0) {
            //render chunk
            $row->render();
        }
        else{
            //remove blank-row
            $row->remove();
        }
        
        //remove binders
        $this->removeAttribute($node, "data-bind-id");
        $this->removeAttribute($node, "data-bind-rows");
    }
    
    /**
     * binds the results of an sql query to dom nodes (usually web forms)
     * @param Node $node
     * @param string $resourceID
     */
    public function bindForm($node, $resourceID) {
        //stay in our boundries
        $id = uniqid();
        $node->setAttribute("data-bind-id", $id);
        $form = $this->traverse("//*[@data-bind-id='". $id ."']")->getDOMChunk();
        
        //get data
        $data = array();
        if(!isset(F::$formCache[$resourceID])) {
            F::$db->loadCommand($resourceID, F::$engineArgs);
            F::$formCache[$resourceID] = F::$db->getDataRow();
        }
        else {
            F::sysLog("<!--used cached form data-->");
        }
        $data = F::$formCache[$resourceID];
        
        //log data
        F::sysLog("<record-data>");
        F::sysLog(print_r($data, true));
        F::sysLog("</record-data>");
        
        //setup binders
        $myBinders = F::$engineArgs;
        if($form->root()->getAttribute("data-bind-form-postback") == "false") {
            $myBinders = array_merge($myBinders, $data);
        }
        else {
            //only merge binders when there is an action and there is a result (postback support)
            if(F::$request->input("action") == "" && F::$db->getFoundRows() > 0) {
                $myBinders = array_merge($myBinders, $data);
            }
        }
        
        //contextual
        if($form->root()->getAttribute("data-bind-form-context") == "add-update-delete") {
            //show and hide form buttons
            if(F::$request->input("id") == "") {
                $this->getNodeByID("button_update")->remove();
                $this->getNodeByID("button_delete")->remove();
            }
            else {
                $this->getNodeByID("button_new")->remove();
            }
        }
        
        //bind the data
        $this->dataBinder($myBinders, $form);
        
        //remove binders
        $this->removeAttribute($node, "data-bind-id");
        $this->removeAttribute($node, "data-bind-form");
    }
    
    /**
     * performs a data binding routine on a dom chunk or dom template with an array name/value hash 
     * @param array $node
     * @param DOMChunk|DOMTemplate $chunk
     */
    public function dataBinder($data, $chunk = null) {
        if(!isset($chunk)) {
            $chunk = $this;
        }
        
        //functions
        $arrFunctions = $chunk->getNodesByDataSet("bind-function")->getNodes();
        if(method_exists($chunk, "Root")) {
            if($chunk->root()->getNode()->getAttribute("data-bind-function") != "") {
                $arrFunctions[] = $chunk->root()->getNode();
            }
        }
        for($i = 0 ; $i < count($arrFunctions) ; $i++) {
            //get the resource name
            $name = $this->getAttribute($arrFunctions[$i], "data-bind-function");
            
            //was a class provided?
            if(strpos($name, "::") > -1) {
                $signature = explode("::", $name);
                
                F::sysLog("<!--func lookup (". $signature[0] ."::". $signature[1] .")-->");
                if(method_exists($signature[0], $signature[1])) {
                    F::sysLog("<Fire|". $signature[0] ."::". $signature[1] .">");
                    call_user_func($signature[0] ."::". $signature[1], $arrFunctions[$i], $data);
                    F::sysLog("</Fire|". $signature[0] ."::". $signature[1] .">");
                }
            }
            //no class. try to fire like an event.
            else {
                //////////////////////////////////////////
                F::fireEvents($name);
                /////////////////////////////////////////
            }
            
            //remove binder
            $this->removeAttribute($arrFunctions[$i], "data-bind-function");
        }
        
        //attributes
        $arrAttributes = $chunk->getNodesByDataSet("bind-attr")->getNodes();
        for($i = 0 ; $i < count($arrAttributes) ; $i++) {
            $attrCommands = explode(",", $this->getAttribute($arrAttributes[$i], "data-bind-attr"));
            
            for($j = 0 ; $j < count($attrCommands) ; $j++) {
                $attrDetails = explode("=", $attrCommands[$j]);
                
                $bindValue = F::getBindDataValue($attrDetails[1], $data);
                if(isset($bindValue)) {
                    $this->setAttribute($arrAttributes[$i], $attrDetails[0], $bindValue);
                    F::sysLog("<!--bound attribute (". $attrDetails[0] .")-->");
                    F::sysLog($this->toString($arrAttributes[$i]));
                }
            }
            
            //remove binder
            $this->removeAttribute($arrAttributes[$i], "data-bind-attr");
        }
        
        //text
        $arrText = $chunk->getNodesByDataSet("bind-text")->getNodes();
        for($i = 0 ; $i < count($arrText) ; $i++) {
            $dataIndex = $this->getAttribute($arrText[$i], "data-bind-text");
            
            $bindValue = F::getBindDataValue($dataIndex, $data);
            if(isset($bindValue)) {
                $this->setInnerText($arrText[$i], Codec::htmlEncode($bindValue));
                F::sysLog("<!--bound inner text-->");
                F::sysLog($this->toString($arrText[$i]));
            }
            
            //remove binder
            $this->removeAttribute($arrText[$i], "data-bind-text");
        }
        
        //html
        $arrHTML = $chunk->getNodesByDataSet("bind-html")->getNodes();
        for($i = 0 ; $i < count($arrHTML) ; $i++) {
            $dataIndex = $this->getAttribute($arrHTML[$i], "data-bind-html");
            
            $bindValue = F::getBindDataValue($dataIndex, $data);
            if(isset($bindValue)) {
                $this->setInnerHTML($arrHTML[$i], $bindValue);
                F::sysLog("<!--bound inner html-->");
                F::sysLog($this->toString($arrHTML[$i]));
            }
            
            //remove binder
            $this->removeAttribute($arrHTML[$i], "data-bind-html");
        }
        
        //input
        $arrInput = $chunk->getNodesByDataSet("bind-input")->getNodes();
        for($i = 0 ; $i < count($arrInput) ; $i++) {
            //remember data index
            $dataIndex = $this->getAttribute($arrInput[$i], "data-bind-input");
            
            //text
            if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "text") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    $this->setAttribute($arrInput[$i], "value", $bindValue);
                }
            }
            
            //textarea
            if($arrInput[$i]->nodeName == "textarea") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    $this->setInnerHTML($arrInput[$i], Codec::htmlEncode($bindValue));
                }
            }
            
            //select
            if($arrInput[$i]->nodeName == "select") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    $arrInput[$i]->setAttribute("data-bind-id", $i);
                    $chunk->traverse("//*[@data-bind-id='". $i ."']")->getNodesByAttribute("value", $bindValue)->setAttribute("selected", "selected");
                    
                    //remove data-bind-id
                    $arrInput[$i]->removeAttribute("data-bind-id");
                }
            }
            
            //checkbox
            if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "checkbox") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    if($this->getAttribute($arrInput[$i], "value") == $bindValue) {
                        $this->setAttribute($arrInput[$i], "checked", "checked");
                    }
                }
            }
            
            //radio
            if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "radio") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    if($this->getAttribute($arrInput[$i], "value") == $bindValue) {
                        $this->setAttribute($arrInput[$i], "checked", "checked");
                    }
                }
            }
            
            //hidden
            if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "hidden") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    $this->setAttribute($arrInput[$i], "value", $bindValue);
                }
            }
            
            //password
            if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "password") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    $this->setAttribute($arrInput[$i], "value", $bindValue);
                }
            }
            
            //submit
            if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "submit") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    $this->setAttribute($arrInput[$i], "value", $bindValue);
                }
            }
            
            //button
            if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "button") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    $this->setAttribute($arrInput[$i], "value", $bindValue);
                }
            }
            
            //reset
            if($arrInput[$i]->nodeName == "input" && $arrInput[$i]->getAttribute("type") == "reset") {
                $bindValue = F::getBindDataValue($dataIndex, $data);
                if(isset($bindValue)) {
                    $this->setAttribute($arrInput[$i], "value", $bindValue);
                }
            }
            
            //log
            F::sysLog("<!--bound input-->");
            F::sysLog($this->toString($arrInput[$i]));
            
            //remove binder
            $this->removeAttribute($arrInput[$i], "data-bind-input");
        }
        
        //errors
        $chunk->getNodesByAttribute("class", "app-alerts")->setInnerHTML(F::getWebAlerts());
    }
    
    /**
     * processes the web alerts and binds them to the dom template
     * @param DOMChunk|DOMTemplate $node
     */
    public function processWebAlerts($node = null) {
        //stay in our boundries
        $id = uniqid();
        $chunk = null;
        
        //get the chunk
        if(!isset($node)) {
            $chunk = $this;
        }
        else {
            $node->setAttribute("data-bind-id", $id);
            $chunk = $this->traverse("//*[@data-bind-id='". $id ."']")->getDOMChunk();
        }
        
        $errorNodes = (array)$chunk->traverse("//*[@class='app-alerts']//*[@class='alert alert-block alert-error']//p[@for]")->getNodes();
        foreach($errorNodes as $error) {
            $resourceID = $error->getAttribute("for");
            $forNode = $chunk->traverse("//*[@name='". $resourceID ."']")->getNode();
            if($forNode) {
                $forNode->parentNode->parentNode->setAttribute("class", "control-group error");
                $chunk->traverse("//*[@name='". $resourceID ."']/parent::*//*[@class='help-inline']")->setInnerText($error->textContent);
                $chunk->remove($error);
            }
        }
        
        //if there are no more messages remove the alerts
        if(F::$errors->count() > 0) {
            $errorNodes = $chunk->traverse("//*[@class='app-alerts']//*[@class='alert-message error']//p")->getNodes();
            if(count($errorNodes) == 0) {
                $errorNodes = $chunk->traverse("//*[@class='alert-message error']")->remove();
            }
        }
    }
    
    /**
     * binds data paging to dom (usuall a dom chunk)
     * @param string $resourceID
     * @param string $type
     */
    public function bindDataPaging($resourceID, $type = "") {
        if($type == "ajax"){
            $this->bindDataPagingAjax($resourceID);
            return;
        }
        
        //get this chunk
        $chunk = $this->getNodesByDataSet("bind-results", $resourceID)->getDOMChunk();
        
        
        if(F::$dataPager->getTotalPages() > 1) {
            //show the data            
            $chunk->traverse("//*[@data-label='pager_current_page']")->setInnerText(F::$dataPager->currentPage);
            $chunk->traverse("//*[@data-label='pager_total_pages']")->setInnerText(F::$dataPager->getTotalPages());
            $chunk->traverse("//*[@data-label='pager_record_start']")->setInnerText((F::$dataPager->getRecordToStart() + (F::$dataPager->totalRecords == 0 ? 0 : 1)));
            $chunk->traverse("//*[@data-label='pager_record_end']")->setInnerText(F::$dataPager->getRecordToStop());
            $chunk->traverse("//*[@data-label='pager_total_records']")->setInnerText(F::$dataPager->totalRecords);
            
            //get the base query and remove paging args
            $baseQuery = F::$request->queryString();
            $baseQuery = preg_replace("/^". $resourceID ."-page=\\d+|&". $resourceID ."-page=\\d+/i", "", $baseQuery);
            $baseQuery = preg_replace("/^page=\\d+|&page=\\d+/i", "", $baseQuery);
            
            //replace prev/next
                if(F::$dataPager->hasPreviousPage()) {
                    $previousLinkURL = F::$engineNamespace .".html?". Codec::htmlEncode($baseQuery . ($baseQuery == "" ? "" : "&") . $resourceID ."-page=". F::$dataPager->getPreviousPage());
                    $chunk->traverse("//*[@data-label='pager_prev']/a")->setAttribute("href", htmlspecialchars_decode($previousLinkURL));
                }
                else {
                    $chunk->traverse("//*[@data-label='pager_prev']")->setAttribute("class", "prev disabled");
                }
                
                if(F::$dataPager->hasNextPage()) {
                    $nextLinkURL = F::$engineNamespace .".html?". Codec::htmlEncode($baseQuery . ($baseQuery == "" ? "" : "&") . $resourceID ."-page=". F::$dataPager->getNextPage());
                    $chunk->traverse("//*[@data-label='pager_next']/a")->setAttribute("href", htmlspecialchars_decode($nextLinkURL));
                }
                else {
                    $chunk->traverse("//*[@data-label='pager_next']")->setAttribute("class", "next disabled");
                }
            //end replace prev/next
            
            //replace first/last
                if(F::$dataPager->getTotalPages() <= 10) {
                    //just remove first/last pages buttons
                    $chunk->traverse("//*[@data-label='pager_first']")->remove();
                    $chunk->traverse("//*[@data-label='pager_last']")->remove();
                }
                else {
                    if(F::$dataPager->getLinkLoopStart() != "1") {
                        $firstLinkURL = F::$engineNamespace .".html?". Codec::htmlEncode($baseQuery . ($baseQuery == "" ? "" : "&") . $resourceID ."-page=1");
                        $chunk->traverse("//*[@data-label='pager_first']/a")->setAttribute("href", htmlspecialchars_decode($firstLinkURL));
                    }
                    else {
                        $chunk->traverse("//*[@data-label='pager_first']")->remove();
                    }
                    
                    if(F::$dataPager->getLinkLoopStop() != F::$dataPager->getTotalPages()) {
                        $lastLinkURL = F::$engineNamespace .".html?". Codec::htmlEncode($baseQuery . ($baseQuery == "" ? "" : "&") . $resourceID ."-page=". F::$dataPager->getTotalPages());
                        $chunk->traverse("//*[@data-label='pager_last']/a")->setAttribute("href", htmlspecialchars_decode($lastLinkURL));
                    }
                    else {
                        $chunk->traverse("//*[@data-label='pager_last']")->remove();
                    }
                }
            //end replace first/last
            
            //replace paging links
            $cnkPagingLinks = $chunk->traverse("//*[@data-label='pager_blank']")->getDOMChunk();
            for($i = F::$dataPager->getLinkLoopStart() ; $i <= F::$dataPager->getLinkLoopStop() ; $i++) {
                $cnkPagingLinks->begin();
                
                if($i == F::$dataPager->currentPage) {
                    $cnkPagingLinks->root()->setAttribute("class", "active");
                    $cnkPagingLinks->root()->getNodesByTagName("a")->setInnerText($i);
                }
                else {
                    $pageLinkURL = F::$engineNamespace .".html?". Codec::htmlEncode($baseQuery . ($baseQuery == "" ? "" : "&") . $resourceID ."-page=". $i);
                    $cnkPagingLinks->root()->getNodesByTagName("a")->setInnerText($i);
                    $cnkPagingLinks->root()->getNodesByTagName("a")->setAttribute("href", htmlspecialchars_decode($pageLinkURL));
                }
                
                $cnkPagingLinks->end();
            }
            $cnkPagingLinks->render();
            
            //replace the timer
            if(F::$timer->timeSpent == 0) {
                $chunk->traverse("//*[@data-label='timer_data']")->remove();
            }
            else {
                $chunk->traverse("//*[@data-label='query_time']")->setInnerText(F::$timer->timeSpent);
            }
        }
        else{
            $chunk->traverse("//*[@data-label='paging']")->remove();
        }
    }
    
    /**
     * binds data ajax paging to dom (usuall a dom chunk)
     * @param string $resourceID
     */
    public function bindDataPagingAjax($resourceID) {
        $chunk = $this->getNodesByDataSet("bind-results", $resourceID)->getDOMChunk();
        
        //is there paging to replace?
        if(F::$dataPager->getTotalPages() <= 1){
            $chunk->getNodesByDataSet("label", "paging")->remove();
        }
        else {
            //set storage data
            $chunk->traverse("//*[@data-label='paging']")->setAttribute("data-current-page", F::$dataPager->currentPage);
            
            //show the data
            $chunk->traverse("//*[@data-label='pager_current_page']")->setInnerText(F::$dataPager->currentPage);
            $chunk->traverse("//*[@data-label='pager_total_pages']")->setInnerText(F::$dataPager->getTotalPages());
            $chunk->traverse("//*[@data-label='pager_record_start']")->setInnerText((F::$dataPager->getRecordToStart() + (F::$dataPager->totalRecords == 0 ? 0 : 1)));
            $chunk->traverse("//*[@data-label='pager_record_end']")->setInnerText(F::$dataPager->getRecordToStop());
            $chunk->traverse("//*[@data-label='pager_total_records']")->setInnerText(F::$dataPager->totalRecords);
            
            //replace prev/next
            if(F::$dataPager->hasPreviousPage() == false) {
                $chunk->traverse("//*[@data-label='pager_prev']")->setAttribute("class", "disabled");
            }
            else {
                $chunk->traverse("//*[@data-label='pager_prev']/a")->setAttribute("data-page", $resourceID ."-page=". F::$dataPager->getPreviousPage());
            }
            if(F::$dataPager->hasNextPage() == false) {
                $chunk->traverse("//*[@data-label='pager_next']")->setAttribute("class", "disabled");
            }
            else {
                $chunk->traverse("//*[@data-label='pager_next']/a")->setAttribute("data-page", $resourceID ."-page=". F::$dataPager->getNextPage());
            }
            
            //replace paging options
            $jumpChunk = $chunk->traverse("//*[@data-label='pager_blank']")->getDOMChunk();
            F::$dataPager->linkLoopOffset = 25;
            for($i = F::$dataPager->getLinkLoopStart() ; $i <= F::$dataPager->getLinkLoopStop() ; $i++) {
                $jumpChunk->begin();
                if($i == F::$dataPager->currentPage) {
                    $jumpChunk->root()->setAttribute("class", "active");
                }
                $jumpChunk->getNodesByTagName("a")->setAttribute("data-page", $resourceID ."-page=". $i);
                $jumpChunk->getNodesByTagName("a")->setInnerText("Page ". $i ."/". F::$dataPager->getTotalPages());
                $jumpChunk->root()->removeAttribute("data-label");
                $jumpChunk->end();
            }
            $jumpChunk->render();
        }
    }
}
