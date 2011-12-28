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

class WebRequestFile {
    public $formName;
    public $fileName;
    public $filePath;
    public $contentType;
    public $contentLength;
    
    /**
     * construct the object
     */
    public function __construct() {}
    
    /**
     * saves the file to the specified path
     * @param string $filePath
     */
    public function saveAs($filePath) {
        move_uploaded_file($_FILES[$this->formName]["tmp_name"], $filePath);
    }
}
