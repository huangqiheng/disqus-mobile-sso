<?php
require_once 'discuz-login.php';

$proxy_url = $_GET['url'];
$username = $_GET['user'];
$password = $_GET['pass'];

if (!is_cookie_valid($username)) {
	if (discuz_login($username, $password) != 0) {
		die('login failure');
	}
}


echo discuz_get($username, $proxy_url);

?>
