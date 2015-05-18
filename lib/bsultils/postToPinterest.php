<?php

// ## Code
if (! function_exists ( 'prr' )) {
	function prr($str) {
		echo "<pre>";
		print_r ( $str );
		echo "</pre>\r\n";
	}
}
if (! function_exists ( "CutFromTo" )) {
	function CutFromTo($string, $from, $to) {
		$fstart = stripos ( $string, $from );
		$tmp = substr ( $string, $fstart + strlen ( $from ) );
		$flen = stripos ( $tmp, $to );
		return substr ( $tmp, 0, $flen );
	}
}
if (! function_exists ( "getUqID" )) {
	function getUqID() {
		return mt_rand ( 0, 9999999 );
	}
}
if (! function_exists ( "build_http_query" )) {
	function build_http_query($query) {
		$query_array = array ();
		foreach ( $query as $key => $key_value ) {
			$query_array [] = $key . '=' . urlencode ( $key_value );
		}
		return implode ( '&', $query_array );
	}
}
if (! function_exists ( "rndString" )) {
	function rndString($lngth) {
		$str = '';
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$size = strlen ( $chars );
		for($i = 0; $i < $lngth; $i ++) {
			$str .= $chars [rand ( 0, $size - 1 )];
		}
		return $str;
	}
}

if (! function_exists ( "prcGSON" )) {
	function prcGSON($gson) {
		$json = substr ( $gson, 5 );
		$json = str_replace ( ',{', ',{"', $json );
		$json = str_replace ( ':[', '":[', $json );
		$json = str_replace ( ',{""', ',{"', $json );
		$json = str_replace ( '"":[', '":[', $json );
		$json = str_replace ( '[,', '["",', $json );
		$json = str_replace ( ',,', ',"",', $json );
		$json = str_replace ( ',,', ',"",', $json );
		return $json;
	}
}
if (! function_exists ( "nxsCheckSSLCurl" )) {
	function nxsCheckSSLCurl($url) {
		$ch = curl_init ( $url );
		$headers = array ();
		$headers [] = 'Accept: text/html, application/xhtml+xml, */*';
		$headers [] = 'Cache-Control: no-cache';
		$headers [] = 'Connection: Keep-Alive';
		$headers [] = 'Accept-Language: en-us';
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)" );
		$content = curl_exec ( $ch );
		$err = curl_errno ( $ch );
		$errmsg = curl_error ( $ch );
		if ($err != 0)
			return array (
					'errNo' => $err,
					'errMsg' => $errmsg 
			);
		else
			return false;
	}
}

if (! function_exists ( "cookArrToStr" )) {
	function cookArrToStr($cArr) {
		$cs = '';
		foreach ( $cArr as $cName => $cVal ) {
			$cs .= $cName . '=' . $cVal . '; ';
		}
		return $cs;
	}
}
if (! function_exists ( "getCurlPageMC" )) {
	function getCurlPageMC($ch, $ref = '', $ctOnly = false, $fields = '', $dbg = false, $advSettings = '') {
		$ccURL = curl_getinfo ( $ch, CURLINFO_EFFECTIVE_URL );
		if ($dbg)
			echo '<br/><b style="font-size:16px;color:green;">#### START CURL:' . $ccURL . '</b><br/>';
		static $curl_loops = 0;
		static $curl_max_loops = 20;
		global $nxs_gCookiesArr;
		$cookies = cookArrToStr ( $nxs_gCookiesArr );
		if ($dbg) {
			echo '<br/><b style="color:#005800;">## Request Cookies:</b><br/>';
			prr ( $cookies );
		}
		if ($curl_loops ++ >= $curl_max_loops) {
			$curl_loops = 0;
			return false;
		}
		$headers = array ();
		$headers [] = 'Accept: text/html, application/xhtml+xml, */*';
		$headers [] = 'Cache-Control: no-cache';
		$headers [] = 'Connection: Keep-Alive';
		$headers [] = 'Accept-Language: en-us'; // $headers[] = 'Accept-Encoding:
		                                        // gzip, deflate';
		
		if ($fields != '') {
			if ((stripos ( $ccURL, 'http://www.blogger.com/blogger_rpc' ) !== false))
				$headers [] = 'Content-Type: application/json; charset=utf-8';
			else
				$headers [] = 'Content-Type: application/x-www-form-urlencoded;charset=utf-8';
		}
		
		if (stripos ( $ccURL, 'http://www.blogger.com/blogger_rpc' ) !== false) {
			$headers [] = 'X-GWT-Permutation: 767A98E1C0A5F1D22D727BB9E37360B2';
			$headers [] = 'X-GWT-Module-Base: http://www.blogger.com/static/v1/gwt/';
		}
		if (isset ( $advSettings ['liXMLHttpRequest'] )) {
			$headers [] = 'X-Requested-With: XMLHttpRequest';
		}
		if (isset ( $advSettings ['Origin'] )) {
			$headers [] = 'Origin: ' . $advSettings ['Origin'];
		}
		
		if (stripos ( $ccURL, 'blogger.com' ) !== false && (isset ( $advSettings ['cdomain'] ) && $advSettings ['cdomain'] == 'google.com'))
			$advSettings ['cdomain'] = 'blogger.com';
		if (isset ( $advSettings ['noSSLSec'] )) {
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		}
		curl_setopt ( $ch, CURLOPT_HEADER, true );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_COOKIE, $cookies );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLINFO_HEADER_OUT, true );
		if ($ref != '')
			curl_setopt ( $ch, CURLOPT_REFERER, $ref );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)" );
		if ($fields != '') {
			curl_setopt ( $ch, CURLOPT_POST, true );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
		} else {
			curl_setopt ( $ch, CURLOPT_POST, false );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, '' );
			curl_setopt ( $ch, CURLOPT_HTTPGET, true );
		}
		$content = curl_exec ( $ch ); // prr($content);
		$errmsg = curl_error ( $ch );
		if (isset ( $errmsg ) && stripos ( $errmsg, 'SSL' ) !== false) {
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$content = curl_exec ( $ch );
		}
		$ndel = strpos ( $content, "\n\n" );
		$rndel = strpos ( $content, "\r\n\r\n" );
		if ($ndel == false)
			$ndel = 100000;
		if ($rndel == false)
			$rndel = 100000;
		$rrDel = $rndel < $ndel ? "\r\n\r\n" : "\n\n";
		list ( $header, $content ) = explode ( $rrDel, $content, 2 );
		if ($ctOnly !== true) {
			$nsheader = curl_getinfo ( $ch );
			$err = curl_errno ( $ch );
			$errmsg = curl_error ( $ch );
			$nsheader ['errno'] = $err;
			$nsheader ['errmsg'] = $errmsg;
			$nsheader ['headers'] = $header;
			$nsheader ['content'] = $content;
		}
		$http_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		$headers = curl_getinfo ( $ch );
		if ($dbg) {
			echo '<br/><b style="color:#005800;">## Headers:</b><br/>';
			prr ( $headers );
			prr ( $header );
		}
		$results = array ();
		$cookies = '';
		preg_match_all ( '|Set-Cookie: (.*);|U', $header, $results );
		$carTmp = $results [1]; // $nxs_gCookiesArr
		                        // =
		                        // array_merge($nxs_gCookiesArr,
		                        // $ret['cookies']);
		preg_match_all ( '/Set-Cookie: (.*)\b/', $header, $xck );
		$xck = $xck [1];
		if ($dbg) {
			echo "Full Resp Cookies";
			prr ( $xck );
			echo "Plain Resp Cookies";
			prr ( $carTmp );
		}
		
		if (isset ( $advSettings ['cdomain'] ) && $advSettings ['cdomain'] != '') {
			foreach ( $carTmp as $iii => $cTmp )
				if (stripos ( $xck [$iii], 'Domain=' ) === false || stripos ( $xck [$iii], 'Domain=.' . $advSettings ['cdomain'] . ';' ) !== false) {
					$ttt = explode ( '=', $cTmp, 2 );
					$nxs_gCookiesArr [$ttt [0]] = $ttt [1];
				}
		} else {
			foreach ( $carTmp as $cTmp ) {
				$ttt = explode ( '=', $cTmp, 2 );
				$nxs_gCookiesArr [$ttt [0]] = $ttt [1];
			}
		}
		if ($dbg) {
			echo '<br/><b style="color:#005800;">## Response/Common Cookies:</b><br/>';
			prr ( $nxs_gCookiesArr );
		}
		if ($dbg && $http_code == 200) {
			$contentH = htmlentities ( $content );
			prr ( $contentH );
		}
		$rURL = '';
		
		if ($http_code == 200 && stripos ( $content, 'http-equiv="refresh" content="0; url=&#39;' ) !== false) {
			$http_code = 301;
			$rURL = CutFromTo ( $content, 'http-equiv="refresh" content="0; url=&#39;', '&#39;"' );
			$nxs_gCookiesArr = array ();
		} elseif ($http_code == 200 && stripos ( $content, 'location.replace' ) !== false) {
			$http_code = 301;
			$rURL = CutFromTo ( $content, 'location.replace("', '"' );
		} // echo
		  // "~~~~~~~~~~~~~~~~~~~~~~".$rURL."|".$http_code;
		if ($http_code == 301 || $http_code == 302) {
			if ($rURL != '') {
				$rURL = str_replace ( '\x3d', '=', $rURL );
				$rURL = str_replace ( '\x26', '&', $rURL );
				$url = @parse_url ( $rURL );
			} else {
				$matches = array ();
				preg_match ( '/Location:(.*?)\n/', $header, $matches );
				$url = @parse_url ( trim ( array_pop ( $matches ) ) );
			}
			$rURL = ''; // echo
			            // "#######";
			            // prr($url);
			if (! $url) {
				$curl_loops = 0;
				return ($ctOnly === true) ? $content : $nsheader;
			}
			$last_url = parse_url ( curl_getinfo ( $ch, CURLINFO_EFFECTIVE_URL ) );
			if (! $url ['scheme'])
				$url ['scheme'] = $last_url ['scheme'];
			if (! $url ['host'])
				$url ['host'] = $last_url ['host'];
			if (! $url ['path'])
				$url ['path'] = $last_url ['path'];
			if (! isset ( $url ['query'] ))
				$url ['query'] = '';
			$new_url = $url ['scheme'] . '://' . $url ['host'] . $url ['path'] . ($url ['query'] ? '?' . $url ['query'] : '');
			curl_setopt ( $ch, CURLOPT_URL, $new_url );
			if ($dbg)
				echo '<br/><b style="color:#005800;">Redirecting to:</b>' . $new_url . "<br/>";
			return getCurlPageMC ( $ch, $last_url, $ctOnly, '', $dbg, $advSettings );
		} else {
			$curl_loops = 0;
			return ($ctOnly === true) ? $content : $nsheader;
		}
	}
}
if (! function_exists ( "getCurlPageX" )) {
	function getCurlPageX($url, $ref = '', $ctOnly = false, $fields = '', $dbg = false, $advSettings = '') {
		if ($dbg)
			echo '<br/><b style="font-size:16px;color:green;">#### GSTART URL:' . $url . '</b><br/>';
		$ch = curl_init ( $url );
		$contents = getCurlPageMC ( $ch, $ref, $ctOnly, $fields, $dbg, $advSettings );
		curl_close ( $ch );
		return $contents;
	}
}

// ================================Pinterest===========================================

// ## Check current Pinterest session
if (! function_exists ( "doCheckPinterest" )) {
	function doCheckPinterest() {
		global $nxs_gCookiesArr, $nxs_gTkn, $nxs_gPNBoards;
		$advSettings = array ();
		$contents = getCurlPageX ( 'http://pinterest.com/', 'http://pinterest.com', true, '', false, $advSettings );
		$nxs_gTkn = trim ( CutFromTo ( $contents, "name='csrfmiddlewaretoken' value='", "'" ) );
		if (stripos ( $contents, 'UserNav' ) !== false) { /*echo "You are IN"; */ return false;
		} else
			return 'No Saved Login';
		return false;
	}
}
// ## Login to Pinterest+
if (! function_exists ( "doConnectToPinterest" )) {
	function doConnectToPinterest($email, $pass) {
		global $nxs_gCookiesArr, $nxs_gTkn, $nxs_gPNBoards;
		$nxs_gCookiesArr = array ();
		$advSettings = array ();
		$err = nxsCheckSSLCurl ( 'https://www.pinterest.com' );
		if ($err !== false && $err ['errNo'] == '60')
			$advSettings ['noSSLSec'] = true;
		$contents = getCurlPageX ( 'https://pinterest.com/login/?next=%2F', 'https://pinterest.com', true, '', false, $advSettings );
		// ## GET HIDDEN FIELDS
		$md = array ();
		$mids = '';
		while ( stripos ( $contents, "'hidden'" ) !== false ) {
			$contents = substr ( $contents, stripos ( $contents, "'hidden'" ) + 8 );
			$name = trim ( CutFromTo ( $contents, "name='", "'" ) );
			if (! in_array ( $name, $md )) {
				$md [] = $name;
				$val = trim ( CutFromTo ( $contents, "value='", "'" ) );
				$flds [$name] = $val;
				if ($name == 'csrfmiddlewaretoken')
					$nxs_gTkn = $val;
				$mids .= "&" . $name . "=" . $val;
			}
		}
		$flds ['email'] = $email;
		$flds ['password'] = $pass;
		$fldsTxt = build_http_query ( $flds );
		$advSettings ['Origin'] = 'https://pinterest.com';
		// ## ACTUAL LOGIN
		$contents = getCurlPageX ( 'https://pinterest.com/login/?next=%2Flogin%2F', 'https://pinterest.com/login/?next=%2F', true, $fldsTxt, false, $advSettings ); // echo
		                                                                                                                                                            // $fldsTxt;
		                                                                                                                                                            // prr($contents);
		                                                                                                                                                            // die();
		if (stripos ( $contents, 'UserNav' ) !== false) { // echo "You are IN";
			$txt = CutFromTo ( $contents, 'class="BoardList"', '</ul>' );
			$txta = explode ( '<li', $txt ); // prr($txta);
			$items = array();
			foreach ( $txta as $txti )
				if (stripos ( $txti, 'data=' ) !== false) {
					$val = CutFromTo ( $txti, 'data="', '"' );
					$name = CutFromTo ( $txti, '<span>', '</span>' );
					// $items .= '{"optionValue": '.$val.', "optionDisplay":
					// "'.$name.'"},'; } $nxs_gPNBoards = '['.substr($items, 0 ,
					// -1).']'; // JSON, But WHY?
					//$items .= '<option value="' . $val . '">' . $name . '</option>';
					$items[] = array('value'=>$val, 'label'=>$name);
				}
			$nxs_gPNBoards = $items;
			return false;
		} else if (stripos ( 'IP because of suspicious activity', $contents ) !== false)
			return 'Pinterest blocked logins from this IP because of suspicious activity';
		else
			return 'Incorrect Username/Password ';
		return false;
	}
}
if (! function_exists ( "doGetBoardsFromPinterest" )) {
	function doGetBoardsFromPinterest() {
		global $nxs_gPNBoards;
		return $nxs_gPNBoards;
	}
}
// ## Post $msg to Google Plus pass $pageID to post to the Google+ Page or leave
// it empty to post to the profile
if (! function_exists ( "doPostToPinterest" )) {
	function doPostToPinterest($msg, $imgURL, $lnk, $boardID) {
		global $nxs_gTkn, $nxs_gCookiesArr;
		$mgs = urlencode ( $msg );
		$lnk = urlencode ( $lnk );
		$fldsTxt = 'caption=' . $msg . '&board=' . $boardID . '&tags=&replies=&buyable=&title=&media_url=' . $imgURL . '&url=' . $lnk . '&via=&csrfmiddlewaretoken=' . $nxs_gTkn . '&form_url=';
		if (trim ( $boardID ) == '')
			return "Board is not Set";
		if (trim ( $imgURL ) == '')
			return "Image is not Set";
		$contents = getCurlPageX ( 'http://pinterest.com/pin/create/button/', '', true, $fldsTxt, false, '' ); // prr($contents);
		
		
		if (stripos ( $contents, 'blocked this source' ) !== false)
			return "Pinterest ERROR: 'The Source is blocked'. Please see https://support.pinterest.com/entries/21436306-why-is-my-pin-or-site-blocked-for-spam-or-inappropriate-content/ for more info";
		if (stripos ( $contents, 'Oops' ) !== false && stripos ( $contents, '<body>' ) !== false)
			return 'Pinterest ERROR MESSAGE : ' . strip_tags ( CutFromTo ( $contents, '<body>', '</body>' ) );
		if (stripos ( $contents, 'pinSuccess' ) !== false)
			return "OK";
		else
			return $contents;
	}
}

?>