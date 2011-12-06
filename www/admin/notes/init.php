<?php

class Page {
    /**
     * handles the before binding event
     */
    public static function eventBeforeBinding() {
        if(F::$request->input("fk_pivot_id") == "" || F::$request->input("fk_pivot_id") == "0") {
            F::$doc->getNodeByID("save_button")->remove();
            F::$doc->getNodesByTagName("textarea")->setAttribute("placeholder", "*notes are disabled*");
        }
    }
    
    /**
     * custom row handling
     */
    public static function rowHandler($node, $data) {
        $id = uniqid();
        $node->setAttribute("data-bind-id", $id);
        $row = F::$doc->traverse("//*[@data-bind-id='". $id ."']")->getDOMChunk();
        
        //new formatted notes
        $outputNote = $data["data"];
        $linksMap = array();
        
        //find URLs
            //get matches
            preg_match_all("/http\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/\S*)?/is", $outputNote, $arrURLs, PREG_PATTERN_ORDER | PCRE_MULTILINE);
            
            //fix the results
            if(is_array($arrURLs)) {
                for($i = 0; $i < count($arrURLs[0]); $i++) {
                    $tmpAnchor = "";
                    $tmpURLParts = parse_url($arrURLs[0][$i]);
                    if($tmpURLParts["path"] != "" && $tmpURLParts["path"] != "/") {
                        $tmpAnchor = "[". $tmpURLParts["host"] ."/...]";
                    }
                    else {
                        $tmpAnchor = "[". $tmpURLParts["host"] ."]";
                    }
                    $linkID = "[". uniqid() ."]";
                    $linksMap[$linkID] = "<a href=\"admin/redirect.html?url=". Codec::urlEncode($arrURLs[0][$i]) ."\" title=\"". Codec::htmlEncode($arrURLs[0][$i]) ."\" target=\"_blank\">". $tmpAnchor ."</a>";
                    $outputNote = str_replace($arrURLs[0][$i], $linkID, $outputNote);
                }
            }
        //end find URLs
        
        //replace any remaining left over &s
        $outputNote = Codec::xhtmlCleanText($outputNote);
        
        //now we can put our html back in
            //replace line breaks
            $outputNote = nl2br($outputNote);
            
            //put our links back
            $outputNote = str_replace(array_keys($linksMap), array_values($linksMap), $outputNote);
        //end now we can put our html back in
        
        //set value
        $row->getNodesByDataSet("bind-html", "data")->setInnerHTML($outputNote);
        $row->getNodesByDataSet("bind-html", "data")->removeAttribute("data-bind-html");
        
        //remove data-bind-id
        $node->removeAttribute("data-bind-id");
    }
    
    /**
     * handles the add note action
     */
    public static function actionAddNote() {
        //add user_id input binders
        F::$db->keyBinders["fk_user_id"] = F::$request->session("user_id");
        
        //validate input data
        if(trim(F::$request->input("data")) == "") {
            F::$errors->add("You forgot to enter some notes.");
        }
        if(F::$request->input("fk_pivot_table") == "") {
            F::$errors->add("Missing table reference.");
        }
        if(F::$request->input("fk_pivot_id") == "") {
            F::$errors->add("Missing record ID.");
        }
        if(F::$request->input("data") == "") {
            F::$errors->add("You didn't enter any notes.");
        }
        
        //take action
        if(F::$errors->count() == 0) {
            F::$db->loadCommand("add-note", F::$engineArgs);
            F::$db->executeNonQuery();
        }

    }
}
