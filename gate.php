<?php
require_once 'discuz-login.php';

$proxy_url = $_GET['url'];
$username = $_GET['user'];
$password = $_GET['pass'];

if (!is_cookie_valid($username)) {
	$err_no = discuz_login($username, $password);
	if ($err_no !== 0) {
		die(json_encode(array('status'=>'error', 'errno'=>$err_no, 'error'=>errstr($err_no))));
	}

	list($cookie_file, $account_file) = get_paths($username);
	file_put_contents($account_file, json_encode(array('username'=>$username, 'password'=>$password)));
}

$content = discuz_get($username, $proxy_url);
echo package_result($content);

?>
