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

//--> Begin Class :: Imager
	class Imager {
		//--> Begin :: Properties
			public $BackgroundColor;
			public $OutputImage;
			public $ContentType;
			public $ImageName;
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			public function Imager($FilePath = "") {
				//get the num of args
				$ArgCount = func_num_args();
			
				//set default background color
				$this->BackgroundColor = "#000000";
			
				if($ArgCount > 0){
					$this->Load($FilePath);
				}
			}
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin :: Load
			public function Load($FilePath) {
				if(!file_exists($FilePath)){
					throw new Exception("WebLegs.Imager.Load(): File not found or not able to access.");
				}
				
				$this->OutputImage = $this->LoadImageFromFile($FilePath);
				$FileInfo = pathinfo($FilePath);
				switch($FileInfo["extension"]) {
					case "png":
						$this->ContentType = "image/png";
						break;
					case "jpg":
						$this->ContentType = "image/jpeg";
						break;
					case "gif":
						$this->ContentType = "image/gif";
						break;
					default:
						$this->ContentType = "image/jpeg";
						break;	
				}
				
				//get filename with out extension
				$this->ImageName = $FileInfo["filename"];
				
				//retrun this
				return $this;
			}
		//<-- End :: Load
		
		//##################################################################################
		
		//--> Begin Method :: Constrain
			public function Constrain($Height, $Width) {
				//original height and width
				$SourceWidth = $this->GetWidth();
				$SourceHeight = $this->GetHeight();
				
				//shrink calculations
					//calculation containers
					$ShrinkPercentage = 0;
					$ShrinkPercentageW = 0;
					$ShrinkPercentageH = 0;
					
					//calculate height and width percentages
					$ShrinkPercentageH = ($Height / $SourceHeight);
					$ShrinkPercentageW = ($Width / $SourceWidth);
					
					//if we have to pad the image, pad evenly on top/bottom or left/right
					if($ShrinkPercentageH < $ShrinkPercentageW) {
						$ShrinkPercentage = $ShrinkPercentageH;
					}
					else {
						$ShrinkPercentage = $ShrinkPercentageW;
					}
				//end shrink percentages
				
				//output height and width
				$OutputWidth  = ($SourceWidth * $ShrinkPercentage);
				$OutputHeight = ($SourceHeight * $ShrinkPercentage);
				
				//adjust dimensions so that one dimension always matches the dimensions passed in
				$DifferencePercent = 0;
				$DifferenceWidth = $Width - $OutputWidth;
				$DifferenceHeight = $Height - $OutputHeight;

				//use the dimension that needs to be asdjusted by the least pixels
				if($DifferenceHeight < $DifferenceWidth){
					$DifferencePercent = $Height / $OutputHeight;
				}
				else{
					$DifferencePercent = $Width / $OutputWidth;
				}
			   
				//adjust both dimensions by the same percentage
				$OutputWidth = ($OutputWidth * $DifferencePercent);
				$OutputHeight = ($OutputHeight * $DifferencePercent);
				
				//create new image and draw on new image
				$NewImage = imagecreatetruecolor($OutputWidth, $OutputHeight);
				imagecopyresampled($NewImage, $this->OutputImage, 0, 0, 0, 0, $OutputWidth, $OutputHeight, $SourceWidth, $SourceHeight);
				
				//write over the OutputImage memory
				$this->OutputImage = $NewImage;
				
				//retrun this
				return $this;
			}
		//<-- End Method :: Constrain
		
		//##################################################################################
		
		//--> Begin Method :: ConstrainHeight
			public function ConstrainHeight($Height) {
				//original height and width
				$SourceWidth =  $this->GetWidth();
				$SourceHeight = $this->GetHeight();
				
				//output height and width
					$ShrinkPercentage = ($Height / $SourceHeight);
					$OutputWidth = ($SourceWidth * $ShrinkPercentage);
					$OutputHeight = ($SourceHeight * $ShrinkPercentage);
					
					//make the width exactly what we passed in
					if($OutputHeight < $Height) {
						$Offset = $Height - $OutputHeight;
						$OutputWidth += $Offset;
						$OutputHeight += $Offset;
					}
				//end output height and width
				
				//create new image and draw
				$NewImage = imagecreatetruecolor($OutputWidth, $OutputHeight);
				imagecopyresampled($NewImage, $this->OutputImage, 0, 0, 0, 0, $OutputWidth, $OutputHeight, $SourceWidth, $SourceHeight);
				
				//write over the OutputImage memory
				$this->OutputImage = $NewImage;
				
				//retrun this
				return $this;
			}
		//<-- End Method :: ConstrainHeight
		
		//##################################################################################
		
		//--> Begin Method :: ConstrainWidth
			public function ConstrainWidth($Width) {
				//original height and width
				$SourceWidth = $this->GetWidth();
				$SourceHeight = $this->GetHeight();
				
				//output height and width
					$ShrinkPercentage = ($Width / $SourceWidth);
					$OutputWidth = ($SourceWidth * $ShrinkPercentage);
					$OutputHeight = ($SourceHeight * $ShrinkPercentage);
					
					//make the width exactly what we passed in
					if($OutputWidth < $Width) {
						$Offset = $Width - $OutputWidth;
						$OutputWidth += $Offset;
						$OutputHeight += $Offset;
					}
				//end output height and width
				
				//create new image and draw
				$NewImage = imagecreatetruecolor($OutputWidth, $OutputHeight);
				imagecopyresampled($NewImage, $this->OutputImage, 0, 0, 0, 0, $OutputWidth, $OutputHeight, $SourceWidth, $SourceHeight);
				
				//write over the OutputImage memory
				$this->OutputImage = $NewImage;
				
				//retrun this
				return $this;
			}
		//<-- End Method :: ConstrainWidth
		
		//##################################################################################

		//--> Begin Method :: ScaleByPercent
			function ScaleByPercent($Percent){
				//original height and width
				$SourceWidth = $this->GetWidth();
				$SourceHeight = $this->GetHeight();
				
				//output height and width
				$ShrinkPercentage = ($Percent/100);
				$OutputWidth = ($SourceWidth * $ShrinkPercentage);
				$OutputHeight = ($SourceHeight * $ShrinkPercentage);
				
				//create new image
				$NewImage = imagecreatetruecolor($OutputWidth, $OutputHeight);
				
				//draw on new image
				imagecopyresampled($NewImage, $this->OutputImage, 0, 0, 0, 0, $OutputWidth, $OutputHeight, $SourceWidth, $SourceHeight);
				
				//write over the OutputImage memory
				$this->OutputImage = $NewImage;
				
				//retrun this
				return $this;
			}
		//<-- End Method :: ScaleByPercent
		
		//##################################################################################
		
		//--> Begin Method :: FixedSize
			public function FixedSize($Height, $Width) {
				//original height and width
				$SourceWidth = $this->GetWidth();
				$SourceHeight = $this->GetHeight();
				
				//shrink calculations
					//output x and y coords (for padding)
					$OutputX = 0;
					$OutputY = 0; 
					
					//calculation containers
					$ShrinkPercentage = 0;
					$ShrinkPercentageW = 0;
					$ShrinkPercentageH = 0;
					
					//calculate height and width percentages
					$ShrinkPercentageH = ($Height / $SourceHeight);
					$ShrinkPercentageW = ($Width / $SourceWidth);
					
					//if we have to pad the image, pad evenly on top/bottom or left/right
					if($ShrinkPercentageH < $ShrinkPercentageW) {
						$ShrinkPercentage = $ShrinkPercentageH;
						$OutputX = (($Width - ($SourceWidth * $ShrinkPercentage)) / 2);
					}
					else {
						$ShrinkPercentage = $ShrinkPercentageW;
						$OutputY = (($Height - ($SourceHeight * $ShrinkPercentage)) / 2);
					}
				//end shrink percentages
				
				//output height and width
				$OutputWidth  = ($SourceWidth * $ShrinkPercentage);
				$OutputHeight = ($SourceHeight * $ShrinkPercentage);
				
				//create new image
				$NewImage = imagecreatetruecolor($Width, $Height);
				
				//set background color
				$RGB = sscanf($this->BackgroundColor, '#%2x%2x%2x');
				$NewBackgroundColor = imagecolorallocate($NewImage, $RGB[0], $RGB[1], $RGB[2]);
				imagefill($NewImage, 0, 0, $NewBackgroundColor);
				
				//draw on new image
				imagecopyresampled($NewImage, $this->OutputImage, $OutputX, $OutputY, 0, 0, $OutputWidth, $OutputHeight, $SourceWidth, $SourceHeight);
				
				//write over the OutputImage memory
				$this->OutputImage = $NewImage;
				
				//retrun this
				return $this;
			}
		//<-- End Method :: FixedSize
		
		//##################################################################################
		
		//--> Begin Method :: Crop
			public function Crop() {
				//emulate overloading with these argument count and vars
				$NumberOfArgs = func_num_args();
				$Args = func_get_args();			
				
				//argument variables
				$Height = 0;
				$Width = 0;
				$Anchor = "";
				$SourceX = 0;
				$SourceY = 0;
				
				//public void Crop(int Height, int Width, string Anchor) {
				if($NumberOfArgs == 3){
					$Height = $Args[0];
					$Width = $Args[1];
					$Anchor = $Args[2];
				}
				//public void Crop(int Height, int Width, int SourceX, int SourceY)
				else if($NumberOfArgs == 4){
					$Height = $Args[0];
					$Width = $Args[1];
					$SourceX = $Args[2];
					$SourceY = $Args[3];
				}
				//neither
				else{
					return;
				}
				
				//set default x y values for output image
				$OutputX = 0;
				$OutputY = 0;
				
				//calculate general points
				$Center = ($this->GetWidth() / 2);
				$Middle = ($this->GetHeight() / 2);
				
				//set x/y positions
				switch($Anchor) {
					//- - - - - - - - - - - - - - - - - -//
					case "top-left":
						$OutputX = 0;
						$OutputY = 0;
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "top-center":
						$OutputX = ($Center - ($Width / 2));
						$OutputY = 0;
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "top-right":
						$OutputX = ($this->GetWidth() - $Width);
						$OutputY = 0;
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "middle-left":
						$OutputX = 0;
						$OutputY = ($Middle - ($Height / 2));
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "middle":
						$OutputX = ($Center - ($Width / 2));
						$OutputY = ($Middle - ($Height / 2));
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "middle-right":
						$OutputX = ($this->GetWidth() - $Width);
						$OutputY = ($Middle - ($Height / 2));
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "bottom-left":
						$OutputX = 0;
						$OutputY = ($this->GetHeight() - $Height);
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "bottom-center":
						$OutputX = ($Center - ($Width / 2));
						$OutputY = ($this->GetHeight() - $Height);
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "bottom-right":
						$OutputX = ($this->GetWidth() - $Width);
						$OutputY = ($this->GetHeight() - $Height);
						break;
					//- - - - - - - - - - - - - - - - - -//
					default: 
						//do nothing
						break;
				}
				
				//create new image
				$NewImage = imagecreatetruecolor($Width, $Height);
				
				//set background color
				$RGB = sscanf($this->BackgroundColor, "#%2x%2x%2x");
				$NewBackgroundColor = imagecolorallocate($NewImage, $RGB[0], $RGB[1], $RGB[2]);
				imagefill($NewImage , 0, 0, $NewBackgroundColor);
				
				//draw on new image
				imagecopyresampled($NewImage, $this->OutputImage, -$SourceX, -$SourceY, $OutputX, $OutputY, $this->GetWidth(), $this->GetHeight(), $this->GetWidth(), $this->GetHeight());
				
				//write over the OutputImage memory
				$this->OutputImage = $NewImage;
				
				//retrun this
				return $this;
			}
		//<-- End Method :: Crop
		
		//##################################################################################
		
		//--> Begin Method :: GetHeight
			public function GetHeight(){
				return imagesy($this->OutputImage);
			}
		//<-- End Method :: GetHeight
		
		//##################################################################################
		
		//--> Begin Method :: GetWidth
			public function GetWidth(){
				return imagesx($this->OutputImage);
			}
		//<-- End Method :: GetWidth
		
		//##################################################################################
		
		//--> Begin Method :: SaveAs
			public function SaveAs($FileName) {
				switch($this->ContentType){
					//- - - - - - - - - - - - - - - - - -//
					case "image/gif":
						if(!imagegif($this->OutputImage, $FileName, 100)){
							throw new Exception("WebLegs.Imager.SaveAs(): Unable to save file.");
						}
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "image/jpeg":
						if(!imagejpeg($this->OutputImage, $FileName, 100)){
							throw new Exception("WebLegs.Imager.SaveAs(): Unable to save file.");
						}
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "image/png":
						if(!imagepng($this->OutputImage, $FileName, 9)){
							throw new Exception("WebLegs.Imager.SaveAs(): Unable to save file.");
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
		//<-- End Method :: SaveAs
		
		//##################################################################################
		
		//--> Begin Method :: SaveHTTP
			public function SaveHTTP($Filename = null) {
				if(is_null($Filename)){
					$Filename = $this->ImageName;
				}
				
				//get file name without excetion
				$tmpFilename = pathinfo($Filename);
				$Filename = $tmpFilename["filename"];
				
				header("Content-type: ". $this->ContentType);
				switch($this->ContentType){
					//- - - - - - - - - - - - - - - - - -//
					case "image/gif":
						header("Content-Disposition: filename=". $Filename .".gif;");
						imagegif($this->OutputImage, "", 100);
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "image/jpeg":
						header("Content-Disposition: filename=". $Filename .".jpg;");
						imagejpeg($this->OutputImage, "", 100);
						break;
					//- - - - - - - - - - - - - - - - - -//
					case "image/png":
						header("Content-Disposition: filename=". $Filename .".png;");
						imagepng($this->OutputImage, "", 9);
						break;
					//- - - - - - - - - - - - - - - - - -//
					default:
						//none
						break;
				}
				exit();
			}
		//<-- End Method :: SaveHTTP
		
		
		//##################################################################################
		
		//--> Begin Method :: LoadImageFromFile
			public function LoadImageFromFile($FilePath) {
				//set image name
				$FileInfo = pathinfo($FilePath);
				$this->ImageName = $FileInfo["basename"];
				$VirtualImage = null;
				switch($FileInfo["extension"]){
					//-------------------------------------------------//
					case "jpg":
						//is this a jpeg
						$VirtualImage = @imagecreatefromjpeg($FilePath);
						if($VirtualImage == false) {
							return; 
						}
						break;
					//-------------------------------------------------//
					case "gif":
						//is this a gif
						$VirtualImage = @imagecreatefromgif($FilePath);
						if($VirtualImage == false) {
							return; 
						}
						break;
					//-------------------------------------------------//
					case "png":
						//is this a png
						$VirtualImage = @imagecreatefrompng($FilePath);
						if($VirtualImage == false) {
							return; 
						}
						break;
					//-------------------------------------------------//
					case "wbmp":
						//is this a wbmp
						$VirtualImage = @imagecreatefromwbmp($FilePath);
						if($VirtualImage == false) {
							return; 
						}
						break;
					//-------------------------------------------------//
					case "xbm":
						//is this a xbm
						$VirtualImage = @imagecreatefromxbm($FilePath);
						if($VirtualImage == false) {
							return; 
						}
						break;
					//-------------------------------------------------//
					case "xpm":
						//is this a xpm
						$VirtualImage = @imagecreatefromxpm($FilePath);
						if($VirtualImage == false) {
							return; 
						}
						break;
					//-------------------------------------------------//
					default:
						//is this a gd
						$VirtualImage = @imagecreatefromgd($FilePath);
						if($VirtualImage == false) {
							return; 
						}
						
						//is this a gd2
						$VirtualImage = @imagecreatefromgd2($FilePath);
						if($VirtualImage == false) {
							return; 
						}
					
						//try and load from string
						$VirtualImage = @imagecreatefromstring(file_get_contents($FilePath));
						if($VirtualImage == false) {
							return; 
						}
						break;
					//-------------------------------------------------//
				}
				
				return $VirtualImage;
			}
		//<-- End Method :: LoadImageFromFile
	}
//<-- End Class :: Imager

//##########################################################################################
?>