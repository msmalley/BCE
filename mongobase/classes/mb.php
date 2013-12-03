<?php

// Show errors
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

// Global MongoBase functions required :: v0.8
require_once(dirname(dirname(__FILE__)).'/functions/mb.php');

class mongobase_mb extends mongobase_core
{

	function __construct($options = array(), $key = 'mb')
	{

	}
}
