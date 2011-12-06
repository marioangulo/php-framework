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

class Imager {
    public $backgroundColor;
    public $outputImage;
    public $contentType;
    public $imageName;
    
    /**
     * construct the object
     * @param string $filePath the path of the file to load
     */
    public function __construct($filePath = "") {
        //get the num of args
        $argCount = func_num_args();
    
        //set default background color
        $this->backgroundColor = "#000000";
    
        if($argCount > 0){
            $this->load($filePath);
        }
    }
    
    /**
     * loads the image from the supplied path
     * @param string $filePath the path of the file to load
     * @return this Object chaining
     */
    public function load($filePath) {
        if(!file_exists($filePath)){
            throw new Exception("Weblegs.Imager.load(): File not found or not able to access.");
        }
        
        $this->outputImage = $this->loadImageFromFile($filePath);
        $fileInfo = pathinfo($filePath);
        switch($fileInfo["extension"]) {
            case "png":
                $this->contentType = "image/png";
                break;
            case "jpg":
                $this->contentType = "image/jpeg";
                break;
            case "gif":
                $this->contentType = "image/gif";
                break;
            default:
                $this->contentType = "image/jpeg";
                break;    
        }
        
        //get filename with out extension
        $this->imageName = $fileInfo["filename"];
        
        //retrun this
        return $this;
    }
    
    /**
     * constrains the image to the specified height and width
     * @param int $height the height the image should constrain to
     * @param int $width the width the image should constrain to
     * @return this Object chaining
     */
    public function constrain($height, $width) {
        //original height and width
        $sourceWidth = $this->getWidth();
        $sourceHeight = $this->getHeight();
        
        //shrink calculations
            //calculation containers
            $shrinkPercentage = 0;
            $shrinkPercentageW = 0;
            $shrinkPercentageH = 0;
            
            //calculate height and width percentages
            $shrinkPercentageH = ($height / $sourceHeight);
            $shrinkPercentageW = ($width / $sourceWidth);
            
            //if we have to pad the image, pad evenly on top/bottom or left/right
            if($shrinkPercentageH < $shrinkPercentageW) {
                $shrinkPercentage = $shrinkPercentageH;
            }
            else {
                $shrinkPercentage = $shrinkPercentageW;
            }
        //end shrink percentages
        
        //output height and width
        $outputWidth  = ($sourceWidth * $shrinkPercentage);
        $outputHeight = ($sourceHeight * $shrinkPercentage);
        
        //adjust dimensions so that one dimension always matches the dimensions passed in
        $differencePercent = 0;
        $differenceWidth = $width - $outputWidth;
        $differenceHeight = $height - $outputHeight;
        
        //use the dimension that needs to be asdjusted by the least pixels
        if($differenceHeight < $differenceWidth){
            $differencePercent = $height / $outputHeight;
        }
        else{
            $differencePercent = $width / $outputWidth;
        }
       
        //adjust both dimensions by the same percentage
        $outputWidth = ($outputWidth * $differencePercent);
        $outputHeight = ($outputHeight * $differencePercent);
        
        //create new image and draw on new image
        $newImage = imagecreatetruecolor($outputWidth, $outputHeight);
        imagecopyresampled($newImage, $this->outputImage, 0, 0, 0, 0, $outputWidth, $outputHeight, $sourceWidth, $sourceHeight);
        
        //write over the outputImage memory
        $this->outputImage = $newImage;
        
        //retrun this
        return $this;
    }
    
    /**
     * constrains the image to the specified height
     * @param int $height the height the image should constrain to
     * @return this Object chaining
     */
    public function constrainHeight($height) {
        //original height and width
        $sourceWidth =  $this->getWidth();
        $sourceHeight = $this->getHeight();
        
        //output height and width
            $shrinkPercentage = ($height / $sourceHeight);
            $outputWidth = ($sourceWidth * $shrinkPercentage);
            $outputHeight = ($sourceHeight * $shrinkPercentage);
            
            //make the width exactly what we passed in
            if($outputHeight < $height) {
                $offset = $height - $outputHeight;
                $outputWidth += $offset;
                $outputHeight += $offset;
            }
        //end output height and width
        
        //create new image and draw
        $newImage = imagecreatetruecolor($outputWidth, $outputHeight);
        imagecopyresampled($newImage, $this->outputImage, 0, 0, 0, 0, $outputWidth, $outputHeight, $sourceWidth, $sourceHeight);
        
        //write over the outputImage memory
        $this->outputImage = $newImage;
        
        //retrun this
        return $this;
    }
    
    /**
     * constrains the image to the specified width
     * @param int $width the width the image should constrain to
     * @return this Object chaining
     */
    public function constrainWidth($width) {
        //original height and width
        $sourceWidth = $this->getWidth();
        $sourceHeight = $this->getHeight();
        
        //output height and width
            $shrinkPercentage = ($width / $sourceWidth);
            $outputWidth = ($sourceWidth * $shrinkPercentage);
            $outputHeight = ($sourceHeight * $shrinkPercentage);
            
            //make the width exactly what we passed in
            if($outputWidth < $width) {
                $offset = $width - $outputWidth;
                $outputWidth += $offset;
                $outputHeight += $offset;
            }
        //end output height and width
        
        //create new image and draw
        $newImage = imagecreatetruecolor($outputWidth, $outputHeight);
        imagecopyresampled($newImage, $this->outputImage, 0, 0, 0, 0, $outputWidth, $outputHeight, $sourceWidth, $sourceHeight);
        
        //write over the outputImage memory
        $this->outputImage = $newImage;
        
        //retrun this
        return $this;
    }
    
    /**
     * scales the image by percentage
     * @param int $percent [1-100] the desired scaling percent
     * @return this Object chaining
     */
    function scaleByPercent($percent){
        //original height and width
        $sourceWidth = $this->getWidth();
        $sourceHeight = $this->getHeight();
        
        //output height and width
        $shrinkPercentage = ($percent/100);
        $outputWidth = ($sourceWidth * $shrinkPercentage);
        $outputHeight = ($sourceHeight * $shrinkPercentage);
        
        //create new image
        $newImage = imagecreatetruecolor($outputWidth, $outputHeight);
        
        //draw on new image
        imagecopyresampled($newImage, $this->outputImage, 0, 0, 0, 0, $outputWidth, $outputHeight, $sourceWidth, $sourceHeight);
        
        //write over the outputImage memory
        $this->outputImage = $newImage;
        
        //retrun this
        return $this;
    }
    
    /**
     * resizes the image to a fixed size
     * @param int $height the height the image should size to
     * @param int $width the width the image should size to
     * @return this Object chaining
     */
    public function fixedSize($height, $width) {
        //original height and width
        $sourceWidth = $this->getWidth();
        $sourceHeight = $this->getHeight();
        
        //shrink calculations
            //output x and y coords (for padding)
            $outputX = 0;
            $outputY = 0; 
            
            //calculation containers
            $shrinkPercentage = 0;
            $shrinkPercentageW = 0;
            $shrinkPercentageH = 0;
            
            //calculate height and width percentages
            $shrinkPercentageH = ($height / $sourceHeight);
            $shrinkPercentageW = ($width / $sourceWidth);
            
            //if we have to pad the image, pad evenly on top/bottom or left/right
            if($shrinkPercentageH < $shrinkPercentageW) {
                $shrinkPercentage = $shrinkPercentageH;
                $outputX = (($width - ($sourceWidth * $shrinkPercentage)) / 2);
            }
            else {
                $shrinkPercentage = $shrinkPercentageW;
                $outputY = (($height - ($sourceHeight * $shrinkPercentage)) / 2);
            }
        //end shrink percentages
        
        //output height and width
        $outputWidth  = ($sourceWidth * $shrinkPercentage);
        $outputHeight = ($sourceHeight * $shrinkPercentage);
        
        //create new image
        $newImage = imagecreatetruecolor($width, $height);
        
        //set background color
        $rgb = sscanf($this->backgroundColor, '#%2x%2x%2x');
        $newBackgroundColor = imagecolorallocate($newImage, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($newImage, 0, 0, $newBackgroundColor);
        
        //draw on new image
        imagecopyresampled($newImage, $this->outputImage, $outputX, $outputY, 0, 0, $outputWidth, $outputHeight, $sourceWidth, $sourceHeight);
        
        //write over the outputImage memory
        $this->outputImage = $newImage;
        
        //retrun this
        return $this;
    }
    
    /**
     * crops the image
     * @overload #1 (int $height, int $width, string anchor)
     * @overload #2 (int $height, int $width, int sourceX, int sourceY)
     * @return this Object chaining
     */
    public function crop() {
        //emulate overloading with these argument count and vars
        $argCount = func_num_args();
        $args = func_get_args();            
        
        //argument variables
        $height = 0;
        $width = 0;
        $anchor = "";
        $sourceX = 0;
        $sourceY = 0;
        
        //public void Crop(int Height, int Width, string Anchor) {
        if($argCount == 3){
            $height = $args[0];
            $width = $args[1];
            $anchor = $args[2];
        }
        //public void Crop(int Height, int Width, int SourceX, int SourceY)
        else if($argCount == 4){
            $height = $args[0];
            $width = $args[1];
            $sourceX = $args[2];
            $sourceY = $args[3];
        }
        //neither
        else{
            return;
        }
        
        //set default x y values for output image
        $outputX = 0;
        $outputY = 0;
        
        //calculate general points
        $center = ($this->getWidth() / 2);
        $middle = ($this->getHeight() / 2);
        
        //set x/y positions
        switch($anchor) {
            //- - - - - - - - - - - - - - - - - -//
            case "top-left":
                $outputX = 0;
                $outputY = 0;
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "top-center":
                $outputX = ($center - ($width / 2));
                $outputY = 0;
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "top-right":
                $outputX = ($this->getWidth() - $width);
                $outputY = 0;
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "middle-left":
                $outputX = 0;
                $outputY = ($middle - ($height / 2));
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "middle":
                $outputX = ($center - ($width / 2));
                $outputY = ($middle - ($height / 2));
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "middle-right":
                $outputX = ($this->getWidth() - $width);
                $outputY = ($middle - ($height / 2));
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "bottom-left":
                $outputX = 0;
                $outputY = ($this->getHeight() - $height);
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "bottom-center":
                $outputX = ($center - ($width / 2));
                $outputY = ($this->getHeight() - $height);
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "bottom-right":
                $outputX = ($this->getWidth() - $width);
                $outputY = ($this->getHeight() - $height);
                break;
            //- - - - - - - - - - - - - - - - - -//
            default: 
                //do nothing
                break;
        }
        
        //create new image
        $newImage = imagecreatetruecolor($width, $height);
        
        //set background color
        $rgb = sscanf($this->backgroundColor, "#%2x%2x%2x");
        $newBackgroundColor = imagecolorallocate($newImage, $rgb[0], $rgb[1], $rgb[2]);
        imagefill($newImage , 0, 0, $newBackgroundColor);
        
        //draw on new image
        imagecopyresampled($newImage, $this->outputImage, -$sourceX, -$sourceY, $outputX, $outputY, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
        
        //write over the outputImage memory
        $this->outputImage = $newImage;
        
        //retrun this
        return $this;
    }
    
    /**
     * gets the hight of the image
     * @return int The image height
     */
    public function getHeight(){
        return imagesy($this->outputImage);
    }
    
    /**
     * gets the width of the image
     * @return int The image width
     */
    public function getWidth(){
        return imagesx($this->outputImage);
    }
    
    /**
     * saves the image to a file
     * @param string $path the path to save to
     * @return int The image width
     */
    public function saveAs($fileName) {
        switch($this->contentType){
            //- - - - - - - - - - - - - - - - - -//
            case "image/gif":
                if(!imagegif($this->outputImage, $fileName, 100)){
                    throw new Exception("Weblegs.Imager.saveAs(): Unable to save file.");
                }
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "image/jpeg":
                if(!imagejpeg($this->outputImage, $fileName, 100)){
                    throw new Exception("Weblegs.Imager.saveAs(): Unable to save file.");
                }
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "image/png":
                if(!imagepng($this->outputImage, $fileName, 9)){
                    throw new Exception("Weblegs.Imager.saveAs(): Unable to save file.");
                }
                break;
            //- - - - - - - - - - - - - - - - - -//
            default:
                //none
                break;
        }
        
        //retrun this
        return $this;
    }
    
    /**
     * saves the image to the http stream
     * @param string $fileName the name of the output file
     */
    public function saveHTTP($fileName = null) {
        if(is_null($fileName)){
            $fileName = $this->imageName;
        }
        
        //get file name without excetion
        $tmpFilename = pathinfo($fileName);
        $fileName = $tmpFilename["filename"];
        
        header("Content-type: ". $this->contentType);
        switch($this->contentType){
            //- - - - - - - - - - - - - - - - - -//
            case "image/gif":
                header("Content-Disposition: filename=". $fileName .".gif;");
                imagegif($this->outputImage, "", 100);
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "image/jpeg":
                header("Content-Disposition: filename=". $fileName .".jpg;");
                imagejpeg($this->outputImage, "", 100);
                break;
            //- - - - - - - - - - - - - - - - - -//
            case "image/png":
                header("Content-Disposition: filename=". $fileName .".png;");
                imagepng($this->outputImage, "", 9);
                break;
            //- - - - - - - - - - - - - - - - - -//
            default:
                //none
                break;
        }
        exit();
    }
    
    /**
     * loads an image from file
     * @param string $filePath that path to the file
     * @return binary The image loaded from the supplied path
     */
    public function loadImageFromFile($filePath) {
        //set image name
        $fileInfo = pathinfo($filePath);
        $this->imageName = $fileInfo["basename"];
        $virtualImage = null;
        switch($fileInfo["extension"]){
            //-------------------------------------------------//
            case "jpg":
                //is this a jpeg
                $virtualImage = @imagecreatefromjpeg($filePath);
                if($virtualImage == false) {
                    return; 
                }
                break;
            //-------------------------------------------------//
            case "gif":
                //is this a gif
                $virtualImage = @imagecreatefromgif($filePath);
                if($virtualImage == false) {
                    return; 
                }
                break;
            //-------------------------------------------------//
            case "png":
                //is this a png
                $virtualImage = @imagecreatefrompng($filePath);
                if($virtualImage == false) {
                    return; 
                }
                break;
            //-------------------------------------------------//
            case "wbmp":
                //is this a wbmp
                $virtualImage = @imagecreatefromwbmp($filePath);
                if($virtualImage == false) {
                    return; 
                }
                break;
            //-------------------------------------------------//
            case "xbm":
                //is this a xbm
                $virtualImage = @imagecreatefromxbm($filePath);
                if($virtualImage == false) {
                    return; 
                }
                break;
            //-------------------------------------------------//
            case "xpm":
                //is this a xpm
                $virtualImage = @imagecreatefromxpm($filePath);
                if($virtualImage == false) {
                    return; 
                }
                break;
            //-------------------------------------------------//
            default:
                //is this a gd
                $virtualImage = @imagecreatefromgd($filePath);
                if($virtualImage == false) {
                    return; 
                }
                
                //is this a gd2
                $virtualImage = @imagecreatefromgd2($filePath);
                if($virtualImage == false) {
                    return; 
                }
            
                //try and load from string
                $virtualImage = @imagecreatefromstring(file_get_contents($filePath));
                if($virtualImage == false) {
                    return; 
                }
                break;
            //-------------------------------------------------//
        }
        
        return $virtualImage;
    }
}
