<?php

require_once(dirname(__FILE__) ."/../app/config.php");
require_once(dirname(__FILE__) ."/../lib/footprint/Router.php");

/**
 * first check the cache
 */
Router::cacheCheck();

/**
 * initialize the router
 */
Router::init();
