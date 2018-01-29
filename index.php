<?php

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