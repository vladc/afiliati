<?php
header('Access-Control-Allow-Origin: *');
$url = preg_replace("/^http:/i", "https:", file_get_contents('php://input'));

switch(1) {
	case (strpos($url, 'https://campanii.emag.ro/click.php') === 0):
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
	case (strpos($url, 'https://l.profitshare.ro/l/') === 0):
	case (strpos($url, 'https://profitshare.ro/l/') === 0):
		if(strpos($url, 'https://profitshare.ro/l/') === 0) {
			$url = str_replace('profitshare.ro', 'l.profitshare.ro', $url);
		}
		$do = get($url);
		if(preg_match('/window.location.replace\(\"(https:\/\/l.profitshare.ro([^\"\?]+))/', $do[1], $match)) {
			$do = get($match[1]);
			if(preg_match('/Profitshare.setup\("(.*)", "(.*)", "(.*)", "(.*)", \"([^\"\?]+)/', $do[1], $match)) {
				echo $match[5];
			} else {
				http_response_code(404);
			}
		} else {
			http_response_code(404);
		}
	break;
	case (strpos($url, 'https://event.2parale.ro/events/click') === 0):
	case (strpos($url, 'https://event.2performant.com/events/click') === 0):
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
		CURLOPT_URL => $url
		)
	);
	$data = curl_exec($curl);
	$info = curl_getinfo($curl);
	curl_close($curl); 
	return array($info, $data);
}
?>
