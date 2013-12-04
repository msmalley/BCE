<?php

include(dirname(__FILE__).'/core.php');
include(dirname(__FILE__).'/mb.php');
include(dirname(__FILE__).'/jsonrpc.php');
include(dirname(__FILE__).'/btc.php');

class mongobase_btc_login extends mongobase_mb
{

	protected static $btc;
	protected static $cookie_name;
	protected static $btc_per_day;
	protected static $uid_life;
	protected static $txid_salt;
	protected static $user_salt;

	private function timed_cookies()
	{
		// Defaults
		$uid = false;
		$details = false;
		$address = false;
		$logged_in = false;

		// Find Hash
		if(is_array($_COOKIE))
		{
			foreach($_COOKIE as $key => $value)
			{
				$key_array = explode('_', $key);
				if(count($key_array) == 2 && $key_array[0] == $this::$cookie_name)
				{
					$temp_uid = $key_array[1];
					if(isset($_COOKIE[$this::$cookie_name.'_'.$temp_uid]))
					{
						$txid = $_COOKIE[$this::$cookie_name.'_'.$temp_uid];
						$transactions = $this::$btc->query(array('function'=>'listtransactions', 'options'=>$this::$cookie_name.'_'.$temp_uid));
						if(is_array($transactions))
						{
							foreach($transactions as $transaction)
							{
								if(isset($transaction['txid']))
								{
									$hashed_id = hash('sha256', $this::$txid_salt.$transaction['txid']);
									if($hashed_id == $txid)
									{
										$uid = $temp_uid;
										$address = $this::$btc->query(array('function'=>'getaddressesbyaccount', 'options'=>$this::$cookie_name.'_'.$uid));
										$logged_in = true;
									}
								}
							}
						}
					}
				}
			}
		}

		// Set Array
		$details = array(
			'uid'		=> $uid,
			'address'	=> $address,
			'logged_in'	=> $logged_in
		);
		return $details;
	}

	private function set_cookies()
	{
		$logged_in = false;
		$recent_transactions = $this::$btc->query(array('function'=>'listtransactions', 'options'=>$this::$cookie_name.'_'.$uid));
		if(is_array($recent_transactions) && isset($recent_transactions[0]['txid']) && isset($recent_transactions[0]['amount']) && isset($recent_transactions[0]['category']) && $recent_transactions[0]['category'] == 'receive')
		{
			$txid = $recent_transactions[0]['txid'];
			$amount = $recent_transactions[0]['amount'];

			if($amount > 0)
			{
				$logged_in = true;

				$number_of_days_bought = $amount / $this::$btc_per_day;
				$new_cookie_life = 86400 * $number_of_days_bought;

				// Manage Cookies
				setcookie($this::$cookie_name.'_'.$uid, hash('sha256',$this::$txid_salt.$txid), time() + $new_cookie_life);
				setcookie($this::$cookie_name, false, time() - 1);
			}
		}
		return $logged_in;
	}

    function __construct($options = array(), $key = 'btc')
	{
		// Include BTC Module
		$this::$btc = new mongobase_btc();

		// Get Configuration
		$config = $this->ini();

		// Set Options
		$this::$cookie_name = $config['app']['cookie_name'];
		$this::$btc_per_day = $config['app']['btc_per_day'];
		$this::$uid_life = $config['app']['uid_life'];
		$this::$txid_salt = $config['salts']['txid'];
		$this::$user_salt = $config['salts']['user'];
	}

	public function logged_in($uid = false)
	{
		$logged_in = false;

		// Check if got balance
		$uid_balance = $this::$btc->query(array('function'=>'getbalance', 'options'=>$this::$cookie_name.'_'.$uid));

		// Check if logged-in
		$details = $this->timed_cookies();
		$logged_in = $details['logged_in'];
		
		if(!$logged_in && $uid_balance > 0)
		{
			// Not logged-in but got transactions
			$logged_in = $this->set_cookies();
		}
		return $logged_in;
	}

	public function user()
	{
		$initial_cookie_life = 86400 * $this::$uid_life;
		
		// Check Timed Cookies
		$details = $this->timed_cookies();
		$logged_in = $details['logged_in'];

		// Check if address already exists
		if(!$logged_in && isset($_COOKIE[$this::$cookie_name]))
		{
			$uid = $_COOKIE[$this::$cookie_name];

			// Get existing BTC Address
			$addresses = $this::$btc->query(array('function'=>'getaddressesbyaccount','options'=>$this::$cookie_name.'_'.$uid));
			$address = $addresses[0];
		}
		elseif($logged_in)
		{
			// Confirm if logged-in
			$uid = $details['uid'];
			$address = $details['address'];
		}
		else
		{

			// Time to start fresh ...

			// Gather Server Settings
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
			$user_time = $_SERVER['REQUEST_TIME'];

			// Generate Unique ID
			$uid = hash('sha256',$this::$user_salt.$user_agent.$user_time);

			// Set UID Cookie
			setcookie($this::$cookie_name, $uid, time() + $initial_cookie_life);

			// Create new BTC Address
			$address = $this::$btc->query(array('function'=>'getnewaddress','options'=>$this::$cookie_name.'_'.$uid));
		}

		// User Array
		$user = array(
			'uid'		=> $uid,
			'address'	=> $address
		);
		return $user;
	}

	public function html($logged_in = false, $address = false)
	{		
		$html = false;
		$html.= '<!doctype html><html><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Block-Chain Embassy of Asia :: PHP Login Example</title><link id="css-less" href="assets/css/less.css" rel="stylesheet"><link id="css-styles" href="assets/css/styles.css" rel="stylesheet"></head><body>';
		$html.= '<div id="main-container" class="container">';
			if($logged_in)
			{
				$html.= '
					
					<div class="alert alert-success">
						<strong>Success</strong>: ACCESS GRANTED<br /><br />
						You may reset this demo by clearing your cookies or waiting for it to expire based upon the amount you contributed at '.$this::$btc_per_day.' per day.
					</div>

				';
			}
			else
			{
				$html.= '<div class="alert alert-warning">';
					$html.= '<p><strong>Warning</strong>: ACCESS DENIED</p>';
					$html.= '<p>
						It costs '.$this::$btc_per_day.' BTC per Day to access this site content.<br />
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