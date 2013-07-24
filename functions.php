<?php
require_once 'config.php';

function errstr($errno)
{
	switch ($errno) {
		case 1001: return 'discuz_login formhash error';
		case 1002: return 'discuz_login referer error';
		case 1003: return 'discuz_login get action url error';
		case 1004: return 'discuz_login username or password error';
		default: return 'no error';
	}
}

function get_paths($username)
{
	$name = md5($username);
	return array(COOKIES_PATH.'/'.$name.'.cookie', CACHES_PATH.'/'.$name.'.account');
}

function package_result($content)
{
	$json_arr = json_decode($content, true);

	if (json_last_error() === JSON_ERROR_NONE) { 
		return json_encode(array('status'=>'ok', 'content'=>$json_arr));
	} else { 
		return json_encode(array('status'=>'ok', 'content'=>$content));
	} 
}

?>
