<?php
$url = file_get_contents('php://input');
switch(1) {
	case (strpos($url, 'campanii.emag.ro/click.php') === 0):
		$do = get($url);
		if(!empty($do[0]['redirect_url'])) {
			$raw = parse_url($do[0]['redirect_url'], PHP_URL_QUERY);
			parse_str($raw, $vars);
			if(isset($vars['redirect'])) {
				echo $vars['redirect'];
			} else {
				http_response_code(404);
			}
		} else {
			http_response_code(404);
		}
	break;
	case (strpos($url, 'profitshare.ro/l/') === 0):
	case (strpos($url, 'profitshare.ro/cl/') === 0):
		$do = get($url, true);
		if(preg_match('/location.replace\(\'([^\'\?]+)/', $do[1], $match)) {
			echo $match[1];
		} else {
			http_response_code(404);
		}
	break;
	case (strpos($url, 'event.2parale.ro/events/click') === 0):
		$raw = parse_url($url, PHP_URL_QUERY);
		parse_str($raw, $vars);
		if(isset($vars['redirect_to']) && !empty($vars['redirect_to'])) {
			echo rawurldecode($vars['redirect_to']);
		} else {
			http_response_code(404);
		}
	break;
	default:
		http_response_code(404);
	break;
}

function get($url, $follow=false) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)", 
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FOLLOWLOCATION => $follow,
		CURLOPT_URL => 'http://'.$url)
		);
	$data = curl_exec($curl);
	$info = curl_getinfo($curl);
	curl_close($curl); 
	return array($info, $data);
}
?>
