<?php

/*
File Name: PHP Twitter API Class
Author: Matt Harzewski (redwall_hp)
Author URL: http://www.webmaster-source.com
License: LGPL
*/

//if ( !class_exists('tmhOAuth') ) {
	require_once 'tmhOAuth.php';
	require_once 'tmhUtilities.php';
//}

class Twitter_API {




function __construct($consumer_key='', $consumer_secret='') {

	if ($consumer_key != '' && $consumer_secret != '') {
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->oauth_on = TRUE;
	} else {
		$this->oauth_on = FALSE;
	}

}



//Get the 20 most recent tweets from a user.
public function user_timeline($user, $auth_user='', $auth_pass='') {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), array(
		'include_rts' => '1',
		'screen_name' => $user
	));

	if ($code == 200) {
		$timeline = json_decode($tmhOAuth->response['response'], true);
	}

	return $timeline;

}



//Get the latest status update from a user.
public function latest_tweet($user, $auth_user='', $auth_pass='') {

	$timeline = $this->user_timeline($user, $auth_user, $auth_pass);
	return $timeline[0];

}



//Get the recent activity of a users' friends. #Auth
public function friends_timeline($auth_user, $auth_pass, $count='10') {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/home_timeline'));

	if ($code == 200) {
		$timeline = json_decode($tmhOAuth->response['response'], true);
	}

	return $timeline;

}



//Get the newest replies/mentions of a user. #Auth
public function mentions($auth_user, $auth_pass) {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/mentions_timeline'));

	if ($code == 200) {
		$timeline = json_decode($tmhOAuth->response['response'], true);
	}

	return $timeline;

}



//Show a single status update by its ID.
public function show_single($id, $auth_user='', $auth_pass='') {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/show'), array(
		'id' => $id
	));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Update a user's status. #Auth #NoLimit
public function update_status($status, $auth_user, $auth_pass, $in_reply_to_status_id='') {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update'), array(
		'status' => $status,
		'in_reply_to_status_id' => $in_reply_to_status_id
	));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Delete a status message (by ID). #Auth #NoLimit
public function destroy_status($id, $auth_user, $auth_pass) {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/destroy'), array(
		'id' => $id
	));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Returns extended user data via the users/show API method.
public function user_info($user, $auth_user='', $auth_pass='') {

	$url = "http://twitter.com/users/show.xml?screen_name={$user}";
	$response = $this->send_request($url, 'GET', '', $auth_user, $auth_pass);
	$xml = new SimpleXmlElement($response);
	return $xml;

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/users/show'), array(
		'screen_name' => $user
	));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Send a direct message. The recipient must be following you. #Auth #NoLimit
public function send_direct_message($recipient, $message, $auth_user, $auth_pass) {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/direct_messages/new'), array(
		'text' => $message,
		'user' => $recipient
	));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Follow a user. #Auth #NoLimit
public function follow_user($user, $auth_user, $auth_pass) {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/friendships/create'), array(
		'screen_name ' => $user
	));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//UnFollow a user. #Auth #NoLimit
public function unfollow_user($user, $auth_user, $auth_pass) {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/friendships/destroy'), array(
		'screen_name ' => $user
	));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Returns a list of a users' friends' ids.
public function friends_ids($user, $auth_user='', $auth_pass='') {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/friends/ids'), array(
		'screen_name ' => $user
	));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Returns a list of a users' followers' ids.
public function followers_ids($user, $auth_user='', $auth_pass='') {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/followers/ids'), array(
		'screen_name ' => $user
	));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Verify a user's credentials #Auth
public function verify_credentials($auth_user, $auth_pass) {

	$url = "http://twitter.com/account/verify_credentials.xml";
	$response = $this->send_request($url, 'GET', '', $auth_user, $auth_pass);
	$xml = new SimpleXmlElement($response);
	return $xml;

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/account/verify_credentials'));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Check the rate limit status of a user or the current IP. #Auth #NoLimit
public function rate_limit_status($auth_user, $auth_pass) {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $auth_user,
		'user_secret' => $auth_pass,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/application/rate_limit_status'));

	if ($code == 200) {
		return json_decode($tmhOAuth->response['response']);
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Search API. No authentication whatsoever, and nobody knows the limits...
public function search($query, $lang='en', $results_per_page='15', $page='1', $since_id='') {

	$query = urlencode($query);
	$url = "http://search.twitter.com/search.atom?q={$query}&lang={$lang}&rpp={$results_per_page}&page={$page}&since_id={$since_id}";
	$response = $this->send_request($url, 'GET');
	$xml = new SimpleXmlElement($response);
	return $xml;

}



//Sends HTTP requests for other functions.
private function send_request($url, $method='GET', $data='', $auth_user='', $auth_pass='') {

	if ($this->oauth_on && $auth_user != '') {
		$response = $this->oauth_request($url, $method, $auth_user, $auth_pass, $data);
	}
	else {
		$ch = curl_init($url);
		if (strtoupper($method)=="POST") {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off'){
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		}
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($auth_user != '' && $auth_pass != '') {
			curl_setopt($ch, CURLOPT_USERPWD, "{$auth_user}:{$auth_pass}");
		}
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($httpcode != 200) {
			return $httpcode;
		}
	}
	return $response;

}



//Get OAuth authorization link
public function oauth_authorize_link() {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
	));

	$params = array(
		'x_auth_access_type' => 'write'
	);
	$code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/request_token', ''), $params);

	if ($code == 200) {
		$response = $tmhOAuth->extract_params($tmhOAuth->response['response']);
    	$_SESSION["authtoken"] = $response["oauth_token"];
        $_SESSION["authsecret"] = $response["oauth_token_secret"];
        $_SESSION["authstate"] = 1;
    	$authurl = $tmhOAuth->url("oauth/authorize", "") . '?oauth_token=' . $response["oauth_token"];
    	$data = array(
    		"request_link" => $authurl,
    		"request_token" => $response["oauth_token"],
    		"request_token_secret" => $response["oauth_token_secret"]
    	);
		return $data;
	}

}



//Acquire OAuth user token
public function oauth_get_user_token($request_token, $request_token_secret) {

	$tmhOAuth->config['user_token'] = $request_token;
  	$tmhOAuth->config['user_secret'] = $request_token_secret;

  	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
	));

  	$tmhOAuth->request("POST", $tmhOAuth->url("oauth/access_token", ""), array( 
        'oauth_verifier' => $_GET["oauth_verifier"]  
    ));

    echo 'Resp: <pre>'; print_r($tmhOAuth->response); echo '</pre>';

  	if ($tmhOAuth->response["code"] == 200) {
  		$response = $tmhOAuth->extract_params($tmhOAuth->response['response']);
    	$_SESSION["authstate"] = 2;
    	$user_token = array(
    		"access_token" => $response["oauth_token"],
    		"access_token_secret" => $response["oauth_token_secret"]
    	);
		return $user_token;
  	}

}



//Send an API request via OAuth
public function oauth_request($url, $method='POST', $user_access_key, $user_access_secret, $data) {

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => $this->consumer_key,
		'consumer_secret' => $this->consumer_secret,
		'user_token' => $user_access_key,
		'user_secret' => $user_access_secret,
	));

	$code = $tmhOAuth->request($method, $tmhOAuth->url($url), $data);

	if ($code == 200) {
		tmhUtilities::pr(json_decode($tmhOAuth->response['response']));
	} else {
		tmhUtilities::pr($tmhOAuth->response['response']);
	}

}



//Shorten long URLs with is.gd or bit.ly.
public function shorten_url($the_url, $shortener='is.gd', $api_key='', $user='') {

	if (($shortener=="bit.ly" || $shortener=="j.mp") && isset($api_key) && isset($user)) {
		$url = "http://api.bitly.com/v3/shorten?longUrl={$the_url}&domain={$shortener}&login={$user}&apiKey={$api_key}&format=xml";
		$response = $this->send_request($url, 'GET');
		$the_results = new SimpleXmlElement($response);

		if ($the_results->status_code == '200') {
			$response = $the_results->data->url;
		} else {
			$response = "";
		}

	} elseif ($shortener=="su.pr") {
		$url = "http://su.pr/api/simpleshorten?url={$the_url}";
		$response = $this->send_request($url, 'GET');
	} elseif ($shortener=="tr.im") {
		$url = "http://api.tr.im/api/trim_simple?url={$the_url}";
		$response = $this->send_request($url, 'GET');
	} elseif ($shortener=="3.ly") {
		$url = "http://3.ly/?api=mh4829510392&u={$the_url}";
		$response = $this->send_request($url, 'GET');
	} elseif ($shortener=="tinyurl") {
		$url = "http://tinyurl.com/api-create.php?url={$the_url}";
		$response = $this->send_request($url, 'GET');
	} elseif ($shortener=="yourls" && isset($api_key) && isset($user)) {
		//Pass a string in the form of "user@domain.com" as the username, and the password as the API key
		$yourls = explode('@', $user);
		$url = "http://{$yourls[1]}/yourls-api.php?username={$yourls[0]}&password={$api_key}&format=simple&action=shorturl&url={$the_url}";
		$response = $this->send_request($url, 'GET');
	} else {
		$url = "http://is.gd/api.php?longurl={$the_url}";
		$response = $this->send_request($url, 'GET');
	}
	return trim($response);

}



//Shrink a tweet and accompanying URL down to 140 chars.
public function fit_tweet_auto($message, $url) {

	$message_length = strlen($message);
	$url_length = strlen($url);
	if ($message_length + $url_length > 140) {
		$shorten_message_to = $message_length - $url_length;
		$shorten_message_to = $shorten_message_to - 4;
		$message = $message." ";
		$message = substr($message, 0, $shorten_message_to);
		$message = substr($message, 0, strrpos($message,' '));
		$message = $message."...";
	}
	return $message." ".$url;

}



//Shrink a tweet and accompanying URL down to fit in 140 chars.
public function fit_tweet($message, $url) {

	$message = $message." ";
	$message = substr($message, 0, 100);
	$message = substr($message, 0, strrpos($message,' '));
	if (strlen($message) > 100) { $message = $message."..."; }
	return $message." ".$url;

}




}

?>
