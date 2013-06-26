<?php
require_once 'config.php';

function get_paths($username)
{
	$name = md5($username);
	return array(COOKIES_PATH.'/'.$name.'.cookie', CACHES_PATH.'/'.$name.'.account');
}

?>
