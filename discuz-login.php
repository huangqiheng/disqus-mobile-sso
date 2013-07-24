<?php
require_once 'config.php';
require_once 'functions.php';

!extension_loaded('curl') && die('The curl extension is not loaded.');    

function is_cookie_valid($username)
{
	list($cookie_file, $account_file) = get_paths($username);

	if (!file_exists($cookie_file)) {
		return false;
	}

	$ch = curl_init(DISCUZ_CHECK_URL);    
	curl_setopt($ch, CURLOPT_HEADER, 0);    
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);    
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000);    
	curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
	$contents = curl_exec($ch);    
	$curl_errno = curl_errno($ch);
	curl_close($ch);    

	if ($curl_errno > 0) {
		return false;
	}

	if (preg_match(DISCUZ_CHECK_REGEX, $contents)) {
		unlink($cookie_file);
		return false;
	}

	return true;
}

function discuz_get($username, $url)
{
	list($cookie_file, $account_file) = get_paths($username);

	if (!file_exists($cookie_file)) {
		return false;
	}

	$ch = curl_init($url);    
	curl_setopt($ch, CURLOPT_HEADER, 0);    
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 3000);    
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000);    
	curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	$contents = curl_exec($ch);    
	curl_close($ch);    

	return $contents;
}

function discuz_login($username, $password)
{
	$post_fields = array();    
	$post_fields['loginfield'] = 'username';    
	$post_fields['loginsubmit'] = 'true';    
	$post_fields['username'] = $username;    
	$post_fields['password'] = $password;    
	$post_fields['questionid'] = 0;    
	$post_fields['answer'] = '';    

	//获取表单FORMHASH    
	$ch = curl_init(DISCUZ_LOGIN_URL);    
	curl_setopt($ch, CURLOPT_HEADER, 0);    
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 3000);    
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000);    
	curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	$contents = curl_exec($ch);    
	curl_close($ch);    

	preg_match('/<input\s+type="hidden"\s*name="formhash"\s*value="(.*?)"\s*\/>/i', $contents, $matches);    
	if(!empty($matches)) {    
		$post_fields['formhash'] = $matches[1];    
	} else {    
		return 1001;
	}    

	preg_match('/<input\s+type="hidden"\s*name="referer"\s*value="(.*?)"\s*\/>/i', $contents, $matches);    
	if(!empty($matches)) {    
		$post_fields['referer'] = $matches[1];    
	} else {    
		return 1002;
	}    


	preg_match('/<form\s+method="post"[\S\s]+?name="login"[\S\s]+?action="([\S]+?)">/i', $contents, $matches);    
	if(!empty($matches)) {    
		$login_url = preg_replace('/&amp;/i', '&', DISCUZ_URL.$matches[1]);   
	} else {    
		return 1003;
	}    

	list($cookie_file, $account_file) = get_paths($username);

	$ch = curl_init($login_url);    
	curl_setopt($ch, CURLOPT_HEADER, 0);    
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);    
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);    
	curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	curl_setopt($ch, CURLOPT_POST, 1);    
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);    
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);    
	$login_contents = curl_exec($ch);    
	curl_close($ch);    

	if (!preg_match(DISCUZ_LOGIN_REGEX, $login_contents)) {
		if (file_exists($cookie_file)) {
			unlink($cookie_file);
		}
		return 1004;
	}

	//echo htmlspecialchars($login_contents);
	return 0;

}

?> 
