<?php

class Page {
    /**
     * handles the before binding event
     */
    public static function eventBeforeBinding() {
        F::$doc->domBinders["button_new"] = "/root/security/add-edit.html?dk_id_parent=". F::$request->input("dk_id_parent");
    }
}
