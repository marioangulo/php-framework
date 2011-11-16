<?php
//##########################################################################################

//--> Begin Class :: Page
	class P {
		//--> Begin Method :: Event_BeforeFinalize
			public static function Event_BeforeFinalize() {
				//rename ul's to ol's
				F::$Doc->GetNodesByTagName("ul")->RenameNodes("ol");
				
				//create new ul
				$NewULNode = F::$Doc->DOMDocument->createElement("ul");
				$NewULNode->setAttribute("class", "dropdown-menu");
				F::$Doc->GetNodesByTagName("body")->AppendChild($NewULNode);
				
				//find all the ol's with only 2 li's and remove (they are empty)
				$Lists = F::$Doc->GetNodesByTagName("ol")->GetNodes();
				foreach($Lists as $List) {
					$XID = uniqid();
					$List->setAttribute("data-bind-id", $XID);
					if(F::$Doc->Traverse("//*[@data-bind-id='". $XID ."']/li[1]/span")->GetInnerText() == "0") {
						F::$Doc->Remove($List);
					}
					else {
						$List->removeAttribute("data-bind-id");
					}
				}
				
				//find all the li's and move all into new ul
				$ListItems = F::$Doc->GetNodesByTagName("li")->GetNodes();
				foreach($ListItems as $ListItem) {
					F::$Doc->AppendChild($NewULNode, $ListItem);
				}
				if(count($ListItems) == 0) {
					$NewLINode = F::$Doc->DOMDocument->createElement("li");
					$Text = F::$Doc->DOMDocument->createTextNode('no results found');
					$NewLINode->appendChild($Text);
					F::$Doc->AppendChild($NewULNode, $NewLINode);
				}
				
				//remove all the ol's
				$ListItems = F::$Doc->GetNodesByTagName("ol")->Remove();
			}
		//<-- End Method :: Event_BeforeFinalize
	}
//<-- End Class :: Page

//##########################################################################################
?>