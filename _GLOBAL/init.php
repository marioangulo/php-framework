<?php
require_once("includes.php");
//##########################################################################################

//--> Begin Setup :: Footprint
	//setup the config object
	F::$Config = new Config();
	
	//branding
	F::$Config->Set("project-name", "MyProject");
	F::$Config->Set("company-name", "Your Company, Inc.");
	F::$Config->Set("copyright-year", date("Y"));
	
	//main
	F::$Config->Set("admin-email", "admin@localhost");
	F::$Config->Set("root-path", $_SERVER["DOCUMENT_ROOT"] ."/");
	F::$Config->Set("host-name", "http://localhost");
	F::$Config->Set("root-url", "/");
	F::$Config->Set("cookie-domain", ".localhost");
	F::$Config->Set("base-href", F::$Config->Get("host-name") . F::$Config->Get("root-url"));
	
	//debug
	F::$Config->Set("log-errors", true);
	F::$Config->Set("email-errors", true);
	F::$Config->Set("show-stack-trace", false);
	
	//mysql
	F::$Config->Set("mysql-host", "localhost");
	F::$Config->Set("mysql-username", "username");
	F::$Config->Set("mysql-password", "password");
	F::$Config->Set("mysql-schema", "schema");
	
	//smtp
	F::$Config->Set("email-from-address", "from@localhost");
	F::$Config->Set("email-from-name", "From Name");
	F::$Config->Set("email-host", "mail.localhost");
	F::$Config->Set("email-username", "username");
	F::$Config->Set("email-password", "password");
//<-- End Setup :: Footprint

//##########################################################################################
?>