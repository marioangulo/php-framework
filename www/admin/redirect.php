<?php

class Page {
    /**
     * handles the on load event
     */
    public static function eventOnLoad() {
        F::$response->redirectURL = Codec::urlDecode(F::$request->input("url"));
        F::$response->finalize();
    }
}
