<?php

class Helpers {
    /**
     * generates the timezone dropdown
     */
    public static function timezoneDD($node) {
        //populate the options
        F::$doc->setInnerHTML($node, F::$system->timeZoneDD(false, false));
    }
}
