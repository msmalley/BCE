<?php

// Include & Activate Demo Class
include(dirname(dirname(__FILE__)).'/mongobase/classes/btc-login.php');
$login = new mongobase_btc_login();

// Get User Information
$user = $login->user();
$uid = $user['uid'];
$address = $user['address'];

// Check if user is logged-in or not...?
$logged_in = $login->logged_in($uid);

// Create and display relevant HTML
$html = $login->html($logged_in, $address);
echo $html;