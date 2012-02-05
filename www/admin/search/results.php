<?php

class Page {
    /**
     * handles the before finalize event
     */
    public static function eventBeforeFinalize() {
        //rename ul's to ol's
        F::$doc->getNodesByTagName("ul")->renameNodes("ol");
        
        //create new ul
        $newULNode = F::$doc->domDocument->createElement("ul");
        $newULNode->setAttribute("class", "dropdown-menu");
        F::$doc->getNodesByTagName("body")->appendChild($newULNode);
        
        //find all the ol's with only 2 li's and remove (they are empty)
        $lists = F::$doc->getNodesByTagName("ol")->getNodes();
        foreach($lists as $list) {
            $xID = uniqid();
            $list->setAttribute("data-bind-id", $xID);
            if(F::$doc->traverse("//*[@data-bind-id='". $xID ."']/li[1]/span")->getInnerText() == "0") {
                F::$doc->remove($list);
            }
            else {
                $list->removeAttribute("data-bind-id");
            }
        }
        
        //find all the li's and move all into new ul
        $listItems = F::$doc->getNodesByTagName("li")->getNodes();
        foreach($listItems as $listItem) {
            F::$doc->appendChild($newULNode, $listItem);
        }
        if(count($listItems) == 0) {
            $newLINode = F::$doc->domDocument->createElement("li");
            $newAnchorNode = F::$doc->domDocument->createElement("a");
            $text = F::$doc->domDocument->createTextNode('no results found');
            $newAnchorNode->appendChild($text);
            $newLINode->appendChild($newAnchorNode);
            F::$doc->appendChild($newULNode, $newLINode);
        }
        
        //remove all the ol's
        $listItems = F::$doc->getNodesByTagName("ol")->remove();
    }
}
