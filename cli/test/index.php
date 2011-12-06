<?php

class Task {
    /**
     * handles the on load event
     */
    public static function eventOnLoad() {
        //F::$emailDebugLog = true;
        F::$doc->domBinders["args"] = print_r(F::$engineArgs, true);
    }
    
    /**
     * handles the final event
     */
    public static function eventFinal() {
        F::$doc->finalBind();
        print(F::$doc->toString());
    }
}
