<?php

global $mb;

/**
 *
 * Version 0.6.2
 *
 * This class sets the globals whilst locating config, core and app files.
 *
 * Please note that a unit test is available for this class at:
 * root/mb_tests/MongobaseBootstrapTest.php
 *
 */
class mongobase_core
{

	protected function ini()
	{
		return parse_ini_file(dirname(dirname(dirname(__FILE__))).'/config.ini', true);
	}

	function __construct(){
		
	}

}
