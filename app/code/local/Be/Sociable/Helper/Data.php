<?php
class Be_Sociable_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected function sendRequest($url, $method='GET', $data='', $auth_user='', $auth_pass='') {
	
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
		return $response;
	
	}
	
	public function shortenLink($link, $shortener='is.gd', $user='', $apiKey=''){
		if (($shortener=="bit.ly" || $shortener=="j.mp") && isset($apiKey) && isset($user)) {
			$url = "http://api.bitly.com/v3/shorten?longUrl={$link}&domain={$shortener}&login={$user}&apiKey={$apiKey}&format=xml";
			$response = $this->sendRequest($url, 'GET');
			$the_results = new SimpleXmlElement($response);
	
			if ($the_results->status_code == '200') {
				$response = $the_results->data->url;
			} else {
				$response = "";
			}
	
		} elseif ($shortener=="su.pr") {
			$url = "http://su.pr/api/simpleshorten?url={$link}";
			$response = $this->sendRequest($url, 'GET');
		} elseif ($shortener=="tr.im") {
			$url = "http://api.tr.im/api/trim_simple?url={$link}";
			$response = $this->sendRequest($url, 'GET');
		} elseif ($shortener=="3.ly") {
			$url = "http://3.ly/?api=mh4829510392&u={$link}";
			$response = $this->sendRequest($url, 'GET');
		} elseif ($shortener=="tinyurl") {
			$url = "http://tinyurl.com/api-create.php?url={$link}";
			$response = $this->sendRequest($url, 'GET');
		} elseif ($shortener=="yourls" && isset($apiKey) && isset($user)) {
			//Pass a string in the form of "user@domain.com" as the username, and the password as the API key
			$yourls = explode('@', $user);
			$url = "http://{$yourls[1]}/yourls-api.php?username={$yourls[0]}&password={$apiKey}&format=simple&action=shorturl&url={$link}";
			$response = $this->sendRequest($url, 'GET');
		} else {
			$url = "http://is.gd/api.php?longurl={$link}";
			$response = $this->sendRequest($url, 'GET');
		}
		return trim($response);
	}
	
	public function fitTweetAuto($message, $url) {
	
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
	public function fitTweet($message, $url) {
	
		$message = $message." ";
		$message = substr($message, 0, 110);
		$message = substr($message, 0, strrpos($message,' '));
		if (strlen($message) > 110) { $message = $message."..."; }
		return $message." ".$url;
	
	}
}
	 