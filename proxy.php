<?php
require_once 'proxy-do.php';

//默认缓存时间
define('DEFAULT_CACHE_TTL', 60*30);
//memcached服务器配置
define('API_MEMC_POOL', 'PROXY_MEMC_POOL');
define('MEMC_HOST', '127.0.0.1');
define('MEMC_PORT', 11211);
define('MEMC_PREFIX', 'PROXY_PREFIX:');

$param = get_param();

$proxy_url = @$param['purl'];
$cache_ttl = @$param['pttl'];
unset ($param['purl']);
unset ($param['pttl']);

if (empty($proxy_url)) {
	die('parameter error');
}

if (empty($cache_ttl)) {
	$cache_ttl = DEFAULT_CACHE_TTL;
} else {
	$cache_ttl = intval($cache_ttl);
}

if (count($param)) {
	$proxy_url .= '?' . http_build_query($param);
}


$mem = api_open_mmc();
$cache_rep = $mem->get(MEMC_PREFIX.$proxy_url);
if ($cache_rep) {
	if (is_array($cache_rep)) {
		set_response_headers($cache_rep[0]);
		header('Proxied: true');
		echo $cache_rep[1];
		exit;
	}
}

$proxy = new Proxy();
$reps = $proxy->forward($proxy_url);

$mem->set(MEMC_PREFIX.$proxy_url, $reps, $cache_ttl);

/************ memcached ************/

function api_open_mmc()
{
	$mem = new Memcached(API_MEMC_POOL);
	$ss = $mem->getServerList();
	if (empty($ss)) {
		$mem->addServer(MEMC_HOST, MEMC_PORT);
	}
	return $mem;
}


/********* set response headers **********/
function set_response_headers($response)
{
	// headers to strip
	$strip = array("Transfer-Encoding");
	$headers = explode("\n", $response);

	foreach ($headers as &$header)
	{
		if (!$header) continue;
		$pos = strpos($header, ":");
		$key = substr($header, 0, $pos);
		// set headers
		if (!in_array($key, $strip))
		{
			header($header, FALSE);
		}
	}
}

/******* get_param *************/

$g_union = null;

function get_param($name=null, $default='default')
{
	global $g_union;
	if ($g_union === null) {
		$g_union = array_merge($_GET, $_POST); 
	}

	if ($name === null) {
		return $g_union;
	}

	$value = @$g_union[$name];
	empty($value) && ($value=$default);

	return $value;
}

?>
