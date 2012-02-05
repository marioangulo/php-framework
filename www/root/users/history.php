<?php

class Page {
    /**
     * handles the before actions event
     */
    public static function eventBeforeActions() {
        //date_from/to
        F::$db->keyBinders["_date_from_"] = F::$dateTime->now()->parse(F::$request->input("date_from"))->toSQLString("yyyy-MM-dd");
        F::$db->keyBinders["_date_to_"] = F::$dateTime->now()->parse(F::$request->input("date_to"))->toSQLString("yyyy-MM-dd");
    }
    
    /**
     * custom row handling
     */
    public static function rowHandler($node, $data) {
        $id = uniqid();
        $node->setAttribute("data-bind-id", $id);
        $row = F::$doc->traverse("//*[@data-bind-id='". $id ."']")->getDOMChunk();
        
        $thisDT = F::$dateTime->now()->parse($data["timestamp_created"]);
        $row->getNodesByDataSet("label", "date")->setInnerText($thisDT->toString("MM/dd/yyyy"));
        $row->getNodesByDataSet("label", "time")->setInnerText($thisDT->toString("hh:mm:ss TT"));
        
        if($thisDT->toString("yyyy-MM-dd") == F::$dateTime->now()->toString("yyyy-MM-dd")) {
            $row->getNodesByDataSet("label", "date_time")->setAttribute("class", "time-today");
        }
        else if($thisDT->toString("yyyy-MM-dd") == F::$dateTime->now()->addDays(-1)->toString("yyyy-MM-dd")) {
            $row->getNodesByDataSet("label", "date_time")->setAttribute("class", "time-yesterday");
        }
        else {
            $row->getNodesByDataSet("label", "date_time")->setAttribute("class", "time-past");
        }
        
        //remove data-bind-id
        $node->removeAttribute("data-bind-id");
    }
    
    /**
     * handles the download action
     */
    public static function actionDownload() {
        F::$db->loadCommand("get-download-data", F::$engineArgs);
        $tmpCSV = F::$db->getDataString("\n", ",", "\"", 1);
        
        //close the db (we're not going back)
        F::$db->close();
        
        //lets change the output header so we download instead show
        F::$response->addHeader("Content-disposition", "attachment; filename=". F::$dateTime->now()->toString("yyyy-MM-dd") ."_user_history.csv");
        F::$response->addHeader("Content-type", "text/csv");
        
        //write the response
        F::$response->finalize($tmpCSV);
    }
    
    /**
     * handles the delete action
     */
    public static function actionDelete() {
        F::$db->loadCommand("delete-user-history", F::$engineArgs);
        F::$db->executeNonQuery();
        
        //redirect back here
        F::$warnings->add("Deleted filtered history.");
    }
}
