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

class DataPager {
    public $recordsPerPage;
    public $totalRecords;
    public $currentPage;
    public $linkLoopOffset;
    
    /**
     * construct the object
     */
    public function __construct() {
        $this->recordsPerPage = 0;
        $this->totalRecords = 0;
        $this->currentPage = 0;
        $this->linkLoopOffset = 0;
    }
    
    /**
     * @return int Total number of pages
     */
    function getTotalPages() {
        $totalPages = floor($this->totalRecords / $this->recordsPerPage);
        if($this->totalRecords % $this->recordsPerPage > 0) {
            $totalPages++;
        }
        if($totalPages <= 0) $totalPages = 1;
        return $totalPages;
    }
    
    /**
     * @return int Next record to start
     */
    function getRecordToStart() {
        $recordToStart = 0;
        if($this->currentPage > $this->getTotalPages()) {
            $this->currentPage = $this->getTotalPages();
        }
        $recordToStart = ($this->currentPage - 1) * $this->recordsPerPage;
        if($recordToStart < 0) {
            $recordToStart = 0;
        }
        return $recordToStart;
    }
    
    /**
     * @return int Last record to process
     */
    function getRecordToStop() {
        $recordToStop = $this->getRecordToStart() + $this->recordsPerPage;
        if($recordToStop > $this->totalRecords) {
            $recordToStop = $this->totalRecords;
        }
        return $recordToStop;
    }
    
    /**
     * @return int Previous page
     */
    function getPreviousPage() {
        if($this->currentPage - 1 <= 0) {
            return 1;
        }
        else {
            return $this->currentPage - 1;
        }
    }
    
    /**
     * @return int Next page
     */
    function getNextPage() {
        if($this->currentPage + 1 > $this->getTotalPages()) {
            return $this->getTotalPages();
        }
        else {
            return $this->currentPage + 1;
        }
    }
    
    /**
     * @return bool If there is a previous page
     */
    function hasPreviousPage() {
        if($this->currentPage - 1 <= 0) {
            return false;
        }
        else {
            return true;
        }
    }
    
    /**
     * @return bool If there is a next page
     */
    function hasNextPage() {
        if($this->currentPage + 1 > $this->getTotalPages()) {
            return false;
        }
        else {
            return true;
        }
    }
    
    /**
     * @return int Link loop starting index
     */
    function getLinkLoopStart() {
        if($this->currentPage - $this->linkLoopOffset < 1) {
            return 1;
        }
        else {
            return $this->currentPage - $this->linkLoopOffset;
        }
    }
    
    /**
     * @return int Link loop ending index
     */
    function getLinkLoopStop() {
        if($this->currentPage + $this->linkLoopOffset > $this->getTotalPages()) {
            return $this->getTotalPages();
        }
        else {
            return $this->currentPage + $this->linkLoopOffset;
        }
    }
}
