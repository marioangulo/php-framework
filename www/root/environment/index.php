<?php

class Page {
    /**
     * handles the before binding event
     */
    public static function eventBeforeBinding() {
        //footprint data
        $footprint = array();
        foreach(F::$config->get() AS $key => $value) {
            if(is_bool($value)) {
                $value = $value ? "true" : "false";
            }
            $footprint[] = array("property" => $key, "value" => $value);
        }
        F::$customRows["footprint-data"] = $footprint;
        
        //environment variables
        function mapKeyValues($k, $v) { return(array("property" => $k, "value" => print_r($v, true))); }
        F::$customRows["env-data"] = array_map("MapKeyValues", array_keys($_SERVER), $_SERVER);
    }
}
