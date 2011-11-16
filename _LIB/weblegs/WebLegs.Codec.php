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

//--> Begin Class :: Codec
	class Codec {
		//--> Begin :: Properties
			//no properties
		//<-- End :: Properties
		
		//##################################################################################
		
		//--> Begin :: Constructor
			//no constructor
		//<-- End :: Constructor
		
		//##################################################################################
		
		//--> Begin Method :: URLEncode
			public static function URLEncode($Input){
				return urlencode($Input);
			}
		//<-- End Method :: URLEncode
		
		//##################################################################################
		
		//--> Begin Method :: URLDecode
			public static function URLDecode($Input) {
				return urldecode($Input);
			}
		//<-- End Method :: URLDecode
		
		//##################################################################################
		
		//--> Begin Method :: HTMLEncode
			public static function HTMLEncode($Input) {
				return htmlentities($Input);
			}
		//<-- End Method :: HTMLEncode
		
		//##################################################################################
		
		//--> Begin Method :: HTMLDecode
			public static function HTMLDecode($Input){
				return html_entity_decode($Input);
			}
		//<-- End Method :: HTMLDecode
		
		//##################################################################################
		
		//--> Begin Method :: XMLEncode
			public static function XMLEncode($Input) {
				$Input = str_replace("&", "&amp;", $Input);
				$Input = str_replace("<", "&lt;", $Input);
				$Input = str_replace(">", "&gt;", $Input);
				$Input = str_replace("\"", "&quot;", $Input);
				$Input = str_replace("'", "&apos;", $Input);
				return $Input;
			}
		//<-- End Method :: XMLEncode
		
		//##################################################################################
		
		//--> Begin Method :: XMLDecode
			public static function XMLDecode($Input){
				$Input = str_replace("&amp;", "&", $Input);
				$Input = str_replace("&lt;", "<", $Input);
				$Input = str_replace("&gt;", ">", $Input);
				$Input = str_replace("&quot;", "\"", $Input);
				$Input = str_replace("&apos;", "'", $Input);
				return $Input;
			}
		//<-- End Method :: XMLDecode
		
		//##################################################################################
		
		//--> Begin Method :: XHTMLCleanText
			public static function XHTMLCleanText($Input){
				//this method is very strict. it will replace raw entities and it also translates
				//html entities to unicode entities. if you're taking in HTML data via a WYSIWYG
				//editor that already translates raw entities into html|unicode entities you 
				//should use XHTMLCleanHTML instead.
				
				//convert to single byte
				$Input = utf8_decode($Input);
				
				//encode &s
				$Input = str_replace("&", "&amp;", $Input);
				
				//build our translation table
				$html_entity_hash = get_html_translation_table(HTML_ENTITIES); //, ENT_QUOTES
				$html_entity_hash[chr(130)] = "&sbquo;";    // Single Low-9 Quotation Mark
				$html_entity_hash[chr(131)] = "&fnof;";    // Latin Small Letter F With Hook
				$html_entity_hash[chr(132)] = "&bdquo;";    // Double Low-9 Quotation Mark
				$html_entity_hash[chr(133)] = "&hellip;";    // Horizontal Ellipsis
				$html_entity_hash[chr(134)] = "&dagger;";    // Dagger
				$html_entity_hash[chr(135)] = "&Dagger;";    // Double Dagger
				$html_entity_hash[chr(136)] = "&circ;";    // Modifier Letter Circumflex Accent
				$html_entity_hash[chr(137)] = "&permil;";    // Per Mille Sign
				$html_entity_hash[chr(138)] = "&Scaron;";    // Latin Capital Letter S With Caron
				$html_entity_hash[chr(139)] = "&lsaquo;";    // Single Left-Pointing Angle Quotation Mark
				$html_entity_hash[chr(140)] = "&OElig;";    // Latin Capital Ligature OE
				$html_entity_hash[chr(145)] = "&lsquo;";    // Left Single Quotation Mark
				$html_entity_hash[chr(146)] = "&rsquo;";    // Right Single Quotation Mark
				$html_entity_hash[chr(147)] = "&ldquo;";    // Left Double Quotation Mark
				$html_entity_hash[chr(148)] = "&rdquo;";    // Right Double Quotation Mark
				$html_entity_hash[chr(149)] = "&bull;";    // Bullet
				$html_entity_hash[chr(150)] = "&ndash;";    // En Dash
				$html_entity_hash[chr(151)] = "&mdash;";    // Em Dash
				$html_entity_hash[chr(152)] = "&tilde;";    // Small Tilde
				$html_entity_hash[chr(153)] = "&trade;";    // Trade Mark Sign
				$html_entity_hash[chr(154)] = "&scaron;";    // Latin Small Letter S With Caron
				$html_entity_hash[chr(155)] = "&rsaquo;";    // Single Right-Pointing Angle Quotation Mark
				$html_entity_hash[chr(156)] = "&oelig;";    // Latin Small Ligature OE
				$html_entity_hash[chr(159)] = "&Yuml;";    // Latin Capital Letter Y With Diaeresis
				
				//remove &s (we took care of this already)
				unset($html_entity_hash[chr(38)]);
				
				//create replacement arrays
				$utf8Entities = array();
				$htmlEntities = array_values($html_entity_hash); 
				$entitiesDecoded = array_keys($html_entity_hash);
				
				//build the unicode entities array
				$num = count($entitiesDecoded); 
				for($i = 0 ; $i < $num ; $i++) { 
					$utf8Entities[$i] = "&#". ord($entitiesDecoded[$i]) .";"; 
				} 
				
				//replace raw entities with html entities
				$Input = str_replace($entitiesDecoded, $htmlEntities, $Input); 
				
				//replace html entities with unicode entities
				$Input = str_replace($htmlEntities, $utf8Entities, $Input); 
				
				//give it back
				return $Input;
			}
		//<-- End Method :: XHTMLCleanText
		
		//##################################################################################
		
		//--> Begin Method :: XHTMLCleanEntities
			public static function XHTMLCleanEntities($Input){
				//convert to single byte
				$Input = utf8_decode($Input);
				
				//build our translation table
				$html_entity_hash = get_html_translation_table(HTML_ENTITIES); //, ENT_QUOTES
				$html_entity_hash[chr(130)] = "&sbquo;";    // Single Low-9 Quotation Mark
				$html_entity_hash[chr(131)] = "&fnof;";    // Latin Small Letter F With Hook
				$html_entity_hash[chr(132)] = "&bdquo;";    // Double Low-9 Quotation Mark
				$html_entity_hash[chr(133)] = "&hellip;";    // Horizontal Ellipsis
				$html_entity_hash[chr(134)] = "&dagger;";    // Dagger
				$html_entity_hash[chr(135)] = "&Dagger;";    // Double Dagger
				$html_entity_hash[chr(136)] = "&circ;";    // Modifier Letter Circumflex Accent
				$html_entity_hash[chr(137)] = "&permil;";    // Per Mille Sign
				$html_entity_hash[chr(138)] = "&Scaron;";    // Latin Capital Letter S With Caron
				$html_entity_hash[chr(139)] = "&lsaquo;";    // Single Left-Pointing Angle Quotation Mark
				$html_entity_hash[chr(140)] = "&OElig;";    // Latin Capital Ligature OE
				$html_entity_hash[chr(145)] = "&lsquo;";    // Left Single Quotation Mark
				$html_entity_hash[chr(146)] = "&rsquo;";    // Right Single Quotation Mark
				$html_entity_hash[chr(147)] = "&ldquo;";    // Left Double Quotation Mark
				$html_entity_hash[chr(148)] = "&rdquo;";    // Right Double Quotation Mark
				$html_entity_hash[chr(149)] = "&bull;";    // Bullet
				$html_entity_hash[chr(150)] = "&ndash;";    // En Dash
				$html_entity_hash[chr(151)] = "&mdash;";    // Em Dash
				$html_entity_hash[chr(152)] = "&tilde;";    // Small Tilde
				$html_entity_hash[chr(153)] = "&trade;";    // Trade Mark Sign
				$html_entity_hash[chr(154)] = "&scaron;";    // Latin Small Letter S With Caron
				$html_entity_hash[chr(155)] = "&rsaquo;";    // Single Right-Pointing Angle Quotation Mark
				$html_entity_hash[chr(156)] = "&oelig;";    // Latin Small Ligature OE
				$html_entity_hash[chr(159)] = "&Yuml;";    // Latin Capital Letter Y With Diaeresis
				
				//create replacement arrays
				$utf8Entities = array();
				$htmlEntities = array_values($html_entity_hash); 
				$entitiesDecoded = array_keys($html_entity_hash);
				
				//build the unicode entities array
				$num = count($entitiesDecoded); 
				for($i = 0 ; $i < $num ; $i++) { 
					$utf8Entities[$i] = "&#". ord($entitiesDecoded[$i]) .";"; 
				} 
				
				//replace html entities with unicode entities
				$Input = str_replace($htmlEntities, $utf8Entities, $Input); 
				
				//give it back
				return $Input;
			}
		//<-- End Method :: XHTMLCleanEntities
		
		//##################################################################################
		
		//--> Begin Method :: Base64Encode
			public static function Base64Encode($Input){
				return chunk_split(base64_encode($Input), 76, "\n");
			}
		//<-- End Method :: Base64Encode
		
		//##################################################################################
		
		//--> Begin Method :: Base64Decode
			public static function Base64Decode($Input){
				return base64_decode($Input);
			}
		//<-- End Method :: Base64Decode
		
		//##################################################################################
		
		//--> Begin Method :: QuotedPrintableEncode
			public static function QuotedPrintableEncode($Input){
				//container for our final string
				$tmpQPString = "";
				
				//container for our current line length
				$CurrentLineLength = 0;
				
				//loop over bytes and build new string
				for($i = 0 ; $i < strlen($Input) ; $i++) {
					//Get one character at a time
					$Current = $Input[$i];
					
					//Keep track of the next character too... if its the last character
					//present return a CR character
					$Next = (($i + 1) != strlen($Input)) ? $Input[$i + 1] : chr(0x0D);
					
					//make hex-style string out of the current byte
					$CurrentEncoded = "";
					
					//if this is the '=' character just encode it and return
					if($Current == '=') {
						$CurrentEncoded = sprintf('=%02X', ord($Current));
					}
					//if this is any of these characters, just encode them
					else if($Current == '!' || $Current == '"' || $Current == '#' || $Current == '$' || $Current == '@' || $Current == '[' || $Current == '\\' || $Current == ']' || $Current == '^' || $Current == '`' || $Current == '{' || $Current == '|' || $Current == '}' || $Current == '~' || $Current == '\'') {
						$CurrentEncoded = sprintf('=%02X', ord($Current));
					}
					//if we come across a tab or a space AND the next byte
					//represents CR or LF, we need to encode it too
					else if(($Current == chr(0x09) || $Current == chr(0x20)) && ($Next == chr(0x0A) || $Next == chr(0x0D))) {
						$CurrentEncoded = sprintf('=%02X', ord($Current));
					}
					//is this character ok as is?
					else if((ord($Current) >= 33 && ord($Current) <= 126) || $Current == chr(0x0D) || $Current == chr(0x0A) || $Current == chr(0x09) || $Current == chr(0x20)) {
						$CurrentEncoded = $Current;
					}
					else {
						//if we get here, we've fell from above, ecode anything that gets here
						$CurrentEncoded = sprintf('=%02X', ord($Current));
					}
					
					//let's make sure that we keep track of line length while
					//we append characters together for the final output
					
					//check for CR and LF to get away from double lines
					if($Current == chr(0x0D) || $Current == chr(0x0A)) {
						//if we got here that means that we are at the end of the
						//line and we need to reset our line length tracking variable
						if($Current == chr(0x0A)) {
							$CurrentLineLength = 0;
						}
					}
					
					//check to see if this pushes us past 76 characters
					//if so lets add a soft line break
					if(strlen($CurrentEncoded) + $CurrentLineLength > 74) {
						$tmpQPString .= "=\n";
						$CurrentLineLength = 0;
					}
					
					//append this character and increase line length
					$tmpQPString .= $CurrentEncoded;
					$CurrentLineLength += strlen($CurrentEncoded);
				}
				
				//return our completed string
				return $tmpQPString;
			}
		//<-- End Method :: QuotedPrintableEncode

		//##################################################################################
		
		//--> Begin Method :: QuotedPrintableDecode
			public static function QuotedPrintableDecode($Input){
				return quoted_printable_decode($Input);
			}
		//<-- End Method :: QuotedPrintableDecode
		
		//##################################################################################
		
		//--> Begin Method :: MD5Encrypt
			public static function MD5Encrypt($Input){
				return md5($Input);
			}
		//<-- End Method :: MD5Encrypt

		//##################################################################################
		
		//--> Begin Method :: HMACMD5Encrypt
			public static function HMACMD5Encrypt($Key, $Input){
				if(strlen($Key) > 64) {
					$Key = pack('H32', md5($Key));
				}
				elseif(strlen($Key) < 64) {
					$Key = str_pad($Key, 64, chr(0));
				}
				
				$Kipad = substr($Key, 0, 64) ^ str_repeat(chr(0x36), 64);
				$Kopad = substr($Key, 0, 64) ^ str_repeat(chr(0x5C), 64);
				$Inner = pack('H32', md5($Kipad . $Input));
				return md5($Kopad . $Inner);
			}
		//<-- End Method :: HMACMD5Encrypt
		
		//##################################################################################
		
		//--> Begin Method :: Encrypt
			public static function Encrypt($Key, $Input){
				return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($Key), $Input, MCRYPT_MODE_CBC, md5(md5($Key))));
			}
		//<-- End Method :: Encrypt
		
		//##################################################################################
		
		//--> Begin Method :: Decrypt
			public static function Decrypt($Key, $Input){
				return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($Key), base64_decode($Input), MCRYPT_MODE_CBC, md5(md5($Key))), "\0");
			}
		//<-- End Method :: Decrypt
	}
//<-- End Class :: Alert

//##########################################################################################
?>