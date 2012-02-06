<?php

require_once(dirname(__FILE__) ."/../lib/footprint/Config.php");
require_once(dirname(__FILE__) ."/../lib/footprint/Engine.php");
require_once(dirname(__FILE__) ."/lib/System.php");

/**
 * initialize config and system
 */
F::$config = new Config();
F::$system = new System();

/**
 * project settings
 */
F::$config->set("project-name", "MyProject");
F::$config->set("company-name", "Acme, Inc.");
F::$config->set("copyright-year", date("Y"));
F::$config->set("admin-email", "admin@localhost");

/**
 * directory settings
 */
F::$config->set("project-root", dirname(__FILE__) ."/../");
F::$config->set("project-root-cli", dirname(__FILE__) ."/../cli/");
F::$config->set("project-root-www", dirname(__FILE__) ."/../www/");
F::$config->set("project-root-cache", dirname(__FILE__) ."/../www-cache/");

/**
 * email and smtp credentials
 */
F::$config->set("email-from-address", "from@localhost");
F::$config->set("email-from-name", "From Name");
F::$config->set("email-host", "mail.localhost");
F::$config->set("email-username", "username");
F::$config->set("email-password", "password");
F::$config->set("email-port", "465");
F::$config->set("email-protocol", "ssl");

/**
 * set the execution mode (command line or web request)
 */
if(!isset($_SERVER["HTTP_HOST"])) {
    //command line
    F::$config->set("mode", "cli");
    F::$config->set("root-path", F::$config->get("project-root-cli"));
    F::$config->set("cli-request", $argv[1]);
}
else {
    //web requests
    F::$config->set("mode", "www");
    F::$config->set("host-name", "http://". $_SERVER["HTTP_HOST"]);
    F::$config->set("root-path", F::$config->get("project-root-www"));
    F::$config->set("root-url", "/");
    F::$config->set("base-href", F::$config->get("host-name") . F::$config->get("root-url"));
    F::$config->set("cookie-domain", $_SERVER["HTTP_HOST"]);
}

/**
 * enable/disable cache
 */
F::$config->set("cache-generation-enabled", false);
F::$config->set("cache-check-enabled", false);

/**
 * debug
 */
F::$config->set("debug-email-errors", true);
F::$config->set("debug-show-stack-trace", true);
F::$config->set("debug-enable-system-logs", false);

/**
 * mysql
 */
F::$config->set("mysql-host", "127.0.0.1");
F::$config->set("mysql-username", "root");
F::$config->set("mysql-password", "");
F::$config->set("mysql-schema", "footprint");

/**
 * router settings
 */
F::$config->set("router-route-file", ".route.php");
F::$config->set("router-index-file", "index");
F::$config->set("router-enforced-file-extension", "html");
F::$config->set("router-fallback-function", "System::defaultRouteFallback");
F::$config->set("router-enable-xff", false);
F::$config->set("router-acceptable-xff-ips", "");


/**
 * register our lib/class autoloader
 */
spl_autoload_register(array("F", "libAutoLoader"));
