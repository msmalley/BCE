<?php

/*

Application Settings

 * 86400 Seconds Equals 1 Day
 * Nov 2014 - 0.001 BTC Equals US$1

*/

$config = parse_ini_file(dirname(dirname(__FILE__)).'/config.ini', true);
$cookie_name = $config['app']['cookie_name'];
$btc_per_day = $config['app']['btc_per_day'];
$uid_life = $config['app']['uid_life'];
$initial_cookie_life = 86400 * $uid_life;


/*

External Vendor / Includes / Classes

 * JSON-RPC
 * MongoBase
 * MB Core
 * MB BTC

*/
$is_active = false;
include(dirname(dirname(__FILE__)).'/mongobase/classes/core.php');
include(dirname(dirname(__FILE__)).'/mongobase/classes/mb.php');
include(dirname(dirname(__FILE__)).'/mongobase/classes/jsonrpc.php');
include(dirname(dirname(__FILE__)).'/mongobase/classes/btc.php');
$btc = new mongobase_btc();

// Defaults
$btc_address = false;
$activity = '<p>Warning: BTC Server is not Connected';
$is_active = $btc->query(array('function'=>'getbalance'));
if($is_active > 0) $activity = '<p>BTC Server is Connected';

// Check if BTCUID Cookie Exists
if(isset($_COOKIE[$cookie_name]))
{
	$uid = $_COOKIE[$cookie_name];

	// Get existing BTC Address
	$addresses = $btc->query(array('function'=>'getaddressesbyaccount','options'=>$cookie_name.'_'.$uid));
	$address = $addresses[0];
}
else
{

	/*

	GENERATE UID

	*/

	// Gather Server Settings
	$user_salt = md5('you-should-change-this-to-something-unique-too');
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$user_time = $_SERVER['REQUEST_TIME'];

	// Generate Unique ID
	$uid = hash('sha256',$user_salt.$user_agent.$user_time);

	// Set UID Cookie
	setcookie($cookie_name, $uid, time() + $initial_cookie_life);

	// Create new BTC Address
	$address = $btc->query(array('function'=>'getnewaddress','options'=>$cookie_name.'_'.$uid));
}

// Check Balance
$logged_in = false;
$uid_balance = $btc->query(array('function'=>'getbalance', 'options'=>$cookie_name.'_'.$uid));
if($uid_balance > 0) $logged_in = true;

// Construct HTML
$html = false;
$html.= '<!doctype html><html><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Block-Chain Embassy of Asia :: PHP Login Example</title><link id="css-less" href="assets/css/less.css" rel="stylesheet"><link id="css-styles" href="assets/css/styles.css" rel="stylesheet"></head><body>';
$html.= '<div id="main-container" class="container">';
	if($logged_in)
	{
		$html.= '<div class="alert alert-success"><strong>Success</strong>: ACCESS GRANTED</div>';
	}
	else
	{
		$html.= '<div class="alert alert-warning">';
			$html.= '<p><strong>Warning</strong>: ACCESS DENIED</p>';
			$html.= '<p>Please send '.$btc_per_day.' BTC to <code>'.$address.'</code></p>';
		$html.= '</div>';
	}
$html.= '</div>';
$html.= '</body></html>';

echo $html;