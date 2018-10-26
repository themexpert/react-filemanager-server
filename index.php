<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

use FileManager\FileManager;

require_once 'vendor/autoload.php';


if(authenticated()){
	(new FileManager(__DIR__ . '/config.php'));
	exit;
}
print "Not authenticated";

function authenticated() {
	return true;
}