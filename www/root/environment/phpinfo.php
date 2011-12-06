<?php

class Page {
    /**
     * handles the before actions event
     */
    public static function eventBeforeActions() {
        phpinfo();
        exit();
    }
}
