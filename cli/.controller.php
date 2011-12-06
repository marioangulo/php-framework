<?php

require_once(dirname(__FILE__) ."/../app/config.php");
require_once(dirname(__FILE__) ."/../cli/". $argv[1]);

/**
 * start the engine
 */
F::init();
