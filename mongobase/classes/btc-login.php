<?php

include(dirname(__FILE__).'/core.php');
include(dirname(__FILE__).'/mb.php');
include(dirname(__FILE__).'/jsonrpc.php');
include(dirname(__FILE__).'/btc.php');

class mongobase_btc_login extends mongobase_mb
{

	protected static $btc;

    function __construct($options = array(), $key = 'btc')
	{
		$this::$btc = new mongobase_btc();
	}

	public function logged_in($uid = false)
	{
		$config = $this->ini();
		$cookie_name = $config['app']['cookie_name'];
		$btc_per_day = $config['app']['btc_per_day'];
		$uid_life = $config['app']['uid_life'];
		$initial_cookie_life = 86400 * $uid_life;

		$logged_in = false;
		$uid_balance = $this::$btc->query(array('function'=>'getbalance', 'options'=>$cookie_name.'_'.$uid));
		if($uid_balance > 0) $logged_in = true;
		
		return $logged_in;
	}

	public function user()
	{
		$config = $this->ini();
		$cookie_name = $config['app']['cookie_name'];
		$btc_per_day = $config['app']['btc_per_day'];
		$uid_life = $config['app']['uid_life'];
		$initial_cookie_life = 86400 * $uid_life;
		
		// Check if BTCUID Cookie Exists
		if(isset($_COOKIE[$cookie_name]))
		{
			$uid = $_COOKIE[$cookie_name];

			// Get existing BTC Address
			$addresses = $this::$btc->query(array('function'=>'getaddressesbyaccount','options'=>$cookie_name.'_'.$uid));
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
			$address = $this::$btc->query(array('function'=>'getnewaddress','options'=>$cookie_name.'_'.$uid));
		}
		$user = array(
			'uid'		=> $uid,
			'address'	=> $address
		);
		return $user;
	}

	public function html($logged_in = false, $address = false)
	{
		$config = $this->ini();
		$cookie_name = $config['app']['cookie_name'];
		$btc_per_day = $config['app']['btc_per_day'];
		$uid_life = $config['app']['uid_life'];
		$initial_cookie_life = 86400 * $uid_life;
		
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
					$html.= '<p>
						It costs '.$btc_per_day.' BTC per Day to access this site content.<br />
						If you pay 0.002 BTC you get two days access, if you pay 0.0025 you get 6 hours access, and so on and so forth...<br />
						(so long as you do not clear your cookies, which also resets everything)<br /><br />
						Please send your desired amount to: <code>'.$address.'</code></p>';
				$html.= '</div>';
			}
		$html.= '</div>';
		$html.= '</body></html>';
		return $html;
	}
    
}