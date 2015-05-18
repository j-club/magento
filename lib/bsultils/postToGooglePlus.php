<?php
// ## Google Verification email. - Fill it if you get message "Login Verification is required. Please Enter your Google backup/recovery email ot the postToGooglePlus.php"
$gPlusRecoveryEmail = '';
$gPlusRecoveryPhone = '';

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
		$headers [] = 'Accept-Language: en-us'; // $headers[] = 'Accept-Encoding: gzip, deflate';
		
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
		$carTmp = $results [1]; // $nxs_gCookiesArr = array_merge($nxs_gCookiesArr, $ret['cookies']);
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
		} // echo "~~~~~~~~~~~~~~~~~~~~~~".$rURL."|".$http_code;
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
			$rURL = ''; // echo "#######"; prr($url);
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

// ================================GOOGLE===========================================
// Back Version 1.x Compartibility
if (! function_exists ( "doConnectToGooglePlus" )) {
	function doConnectToGooglePlus($connectID, $email, $pass) {
		return doConnectToGooglePlus2 ( $email, $pass );
	}
}
if (! function_exists ( "doGetGoogleUrlInfo" )) {
	function doGetGoogleUrlInfo($connectID, $url) {
		return doGetGoogleUrlInfo2 ( $url );
	}
}
if (! function_exists ( "doPostToGooglePlus" )) {
	function doPostToGooglePlus($connectID, $msg, $lnk = '', $pageID = '') {
		return doPostToGooglePlus2 ( $msg, $lnk, $pageID );
	}
}
// New 2.X Functions
if (! function_exists ( "doConnectToGooglePlus2" )) {
	function doConnectToGooglePlus2($email, $pass) {
		global $nxs_gCookiesArr, $gPlusRecoveryEmail, $gPlusRecoveryPhone;
		$nxs_gCookiesArr = array ();
		$advSettings = array ();
		if ($gPlusRecoveryPhone == '' && isset ( $_COOKIE ['gPlusRecoveryPhone'] ) && $_COOKIE ['gPlusRecoveryPhone'] != '') {
			$gPlusRecoveryPhone = $_COOKIE ['gPlusRecoveryPhone'];
			if (! headers_sent ()) {
				setcookie ( "gPlusRecoveryPhone", "", time () - 3600 );
				setcookie ( "gPlusRecoveryPhoneHint", "", time () - 3600 );
			}
		}
		if ($gPlusRecoveryEmail == '' && isset ( $_COOKIE ['gPlusRecoveryEmail'] ) && $_COOKIE ['gPlusRecoveryEmail'] != '') {
			$gPlusRecoveryEmail = $_COOKIE ['gPlusRecoveryEmail'];
			if (! headers_sent ()) {
				setcookie ( "gPlusRecoveryEmail", "", time () - 3600 );
				setcookie ( "gPlusRecoveryEmailHint", "", time () - 3600 );
			}
		}
		$err = nxsCheckSSLCurl ( 'https://accounts.google.com/ServiceLogin' );
		if ($err !== false && $err ['errNo'] == '60')
			$advSettings ['noSSLSec'] = true;
		$contents = getCurlPageX ( 'https://accounts.google.com/ServiceLogin?service=oz&continue=https://plus.google.com/?gpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3Dc7578f19&hl=en-US', '', true, '', false, $advSettings );
		// ## GET HIDDEN FIELDS
		$md = array ();
		$mids = '';
		while ( stripos ( $contents, '"hidden"' ) !== false ) {
			$contents = substr ( $contents, stripos ( $contents, '"hidden"' ) + 8 );
			$name = trim ( CutFromTo ( $contents, 'name="', '"' ) );
			if (! in_array ( $name, $md )) {
				$md [] = $name;
				$val = trim ( CutFromTo ( $contents, 'value="', '"' ) );
				$flds [$name] = $val;
				$mids .= "&" . $name . "=" . $val;
			}
		}
		$flds ['Email'] = $email;
		$flds ['Passwd'] = $pass;
		$flds ['signIn'] = 'Sign%20in';
		$fldsTxt = build_http_query ( $flds );
		$advSettings ['cdomain'] = 'google.com';
		// ## ACTUAL LOGIN
		$contents = getCurlPageX ( 'https://accounts.google.com/ServiceLoginAuth', '', false, $fldsTxt, false, $advSettings ); // prr($contents);
		
		if (stripos ( $contents ['url'], 'https://accounts.google.com/ServiceLoginAuth' ) !== false && stripos ( $contents ['content'], '<span color="red">' ) !== false)
			return CutFromTo ( $contents ['content'], '<span color="red">', '</span>' );
		
		if (stripos ( $contents ['url'], 'NewPrivacyPolicy' ) !== false)
			return 'Please login to your account and accept new "New Privacy Policy"';
		
		if (stripos ( $contents ['content'], 'captcha-box' ) !== false || stripos ( $contents ['content'], 'CaptchaChallengeOptionContent' ) !== false)
			return 'Captcha is "On" for your account. Please login to your account from the bworser and try�clearing the CAPTCHA by visiting this link: <a href="https://www.google.com/accounts/DisplayUnlockCaptcha" target="_blank">https://www.google.com/accounts/DisplayUnlockCaptcha</a>. If you\'re a Google Apps user, visit https://www.google.com/a/yourdomain.com/UnlockCaptcha in order to clear the CAPTCHA. Be sure to replace \'yourdomain.com\' with your actual domain�name.';
		if (stripos ( $contents ['url'], 'ServiceLoginAuth' ) !== false)
			return 'Incorrect Username/Password ' . $contents ['errmsg'];
		
		if (stripos ( $contents ['url'], 'google.com/SmsAuth' ) !== false)
			return '<b style="color:#800000;">2-step verification in on.</b> <br/><br/> 2-step verification is not compatible with auto-posting. <br/><br/>Please see more here:<br/> <a href="http://www.nextscripts.com/blog/google-2-step-verification-and-auto-posting" target="_blank">Google+, 2-step verification and auto-posting</a><br/>';
		
		if (stripos ( $contents ['content'], 'is that really you' ) !== false || stripos ( $contents ['url'], 'LoginVerification' ) !== false) {
			$text = $contents ['content'];
			$flds = array ();
			while ( stripos ( $text, '"hidden"' ) !== false ) {
				$text = substr ( $text, stripos ( $text, '"hidden"' ) + 8 );
				$name = trim ( CutFromTo ( $text, 'name="', '"' ) );
				if (! in_array ( $name, $md )) {
					$md [] = $name;
					$val = trim ( CutFromTo ( $text, 'value="', '"' ) );
					$flds [$name] = $val;
					$mids .= "&" . $name . "=" . $val;
				}
			} // prr($flds);
			
			if ($gPlusRecoveryEmail == '' && $gPlusRecoveryPhone == '') {
				
				if (stripos ( $contents ['content'], 'RecoveryEmailChallenge' ) !== false) {
					if (stripos ( $contents ['content'], "Confirm my recovery email address:" ) !== false)
						$recEm = trim ( CutFromTo ( $contents ['content'], "Confirm my recovery email address:", "</label>" ) );
					return "<b style='color:red'>Google Error Message: </b><b>Login Verification is required. Please Enter your Google backup/recovery email (" . $recEm . ").</b><br/>Please see here how to add your backup/recovery email to Google: <a href='http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726'>http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726</a>" . '        
        Enter full recovery email address: <input type="tel" name="recoveryEmail" onchange="document.cookie = \'gPlusRecoveryEmail=\'+this.value;document.cookie = \'gPlusRecoveryEmailHint=' . $recEm . '\';" id="recoveryEmail" size="30" placeholder="Enter full recovery email address"><br/>
        Please click "OK", then click "Submit Test Post to Google+" button again to confirm and verify your account.<br/>';
				} elseif (stripos ( $contents ['content'], 'PhoneVerificationChallenge' ) !== false) {
					if (stripos ( $contents ['content'], "Confirm my phone number:" ) !== false)
						$recEm = trim ( CutFromTo ( $contents ['content'], "Confirm my phone number:", "</label>" ) );
					return "<b style='color:red'>Google Error Message: </b><b>Login Verification is required. Please Enter your Google phone number (" . $recEm . ").</b><br/>" . '        
        Enter full phone number: <input type="tel" name="phoneNumber" onchange="document.cookie = \'gPlusRecoveryPhone=\'+this.value;document.cookie = \'gPlusRecoveryPhoneHint=' . $recEm . '\';" id="phoneNumber" size="30" placeholder="Enter full phone number"><br/>
        Please click "OK", then click "Submit Test Post to Google+" button again to confirm and verify your account.<br/>';
				}
			} else {
				if ($gPlusRecoveryEmail != '') {
					if (trim ( $gPlusRecoveryEmail ) == trim ( $email ))
						return "<b style='color:red'>Google Error Message: </b><b>Your recovery email could not be the same as your login email.</b> Google Help: <a href='http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726'>http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726</a>";
					
					$bgc = CutFromTo ( $contents ['content'], "document.bg = new botguard.bg('", "');" );
					$contents = getCurlPageX ( 'http://www.nextscripts.com/bg.php', '', true, 'bg=' . $bgc );
					$fldsTxt = 'continue=https%3A%2F%2Fplus.google.com%2F%3Fgpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3D38f4feed&_utf8=%E2%98%83&bgresponse=' . $contents . '&phoneNumber=&challengetype=RecoveryEmailChallenge&emailAnswer=' . urlencode ( $gPlusRecoveryEmail ) . '&answer=&challengestate=' . $flds ['challengestate'];
					$contents = getCurlPageX ( 'https://accounts.google.com/LoginVerification?Email=' . urlencode ( $email ) . '&continue=https%3A%2F%2Fplus.google.com%2F%3Fgpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3D38f4feed&service=oz', '', false, $fldsTxt );
					
					if (stripos ( $contents ['content'], 'class="errormsg"' ) !== false) {
						$errMsg = CutFromTo ( $contents ['content'], 'class="errormsg"', "/div>" );
						$errMsg = CutFromTo ( $errMsg, '>', "<" );
						return '<b style="color:red">Google Error Message: </b><b>Unable to verify your recovery email.</b> Google Help: <a target="_blank" href="http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726">http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726</a>. Enter full recovery email address: ' . $_COOKIE ["gPlusRecoveryEmailHint"] . '<input type="tel" name="recoveryEmail" onchange="document.cookie = \'gPlusRecoveryEmail=\'+this.value; document.cookie = \'gPlusRecoveryEmailHint=' . $_COOKIE ["gPlusRecoveryEmailHint"] . '\';" id="recoveryEmail" size="30" placeholder="Enter full recovery email address"><br/>Please click "OK", then click "Submit Test Post to Google+" button again to confirm and verify your account.<br/>';
					}
					if ($contents ['http_code'] == '400' || stripos ( $contents ['content'], 'there seems to be a problem' ) !== false) {
						return '<b style="color:red">NX Error Message: </b><b>Unable to verify your Phone. Something went wrong. Please contact support.';
					}
				}
				if ($gPlusRecoveryPhone != '') {
					$bgc = CutFromTo ( $contents ['content'], "document.bg = new botguard.bg('", "');" );
					$contents = getCurlPageX ( 'http://www.nextscripts.com/bg.php', '', true, 'bg=' . $bgc );
					$fldsTxt = 'continue=https%3A%2F%2Fplus.google.com%2F%3Fgpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3D38f4feed&_utf8=%E2%98%83&bgresponse=' . $contents . '&phoneNumber=' . urlencode ( $gPlusRecoveryPhone ) . '&challengetype=PhoneVerificationChallenge&emailAnswer=&answer=&challengestate=' . $flds ['challengestate'];
					$contents = getCurlPageX ( 'https://accounts.google.com/LoginVerification?Email=' . urlencode ( $email ) . '&continue=https%3A%2F%2Fplus.google.com%2F%3Fgpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3D38f4feed&service=oz', '', false, $fldsTxt );
					if (stripos ( $contents ['content'], 'class="errormsg"' ) !== false) {
						$errMsg = CutFromTo ( $contents ['content'], 'class="errormsg"', "/div>" );
						$errMsg = CutFromTo ( $errMsg, '>', "<" );
						return '<b style="color:red">Google Error Message: </b> ' . $errMsg . '<br/><br/> <b>Unable to verify your Phone ' . $gPlusRecoveryPhone . '.</b><br/> Google Help: <a target="_blank" href="http://support.google.com/accounts/bin/answer.py?hl=en&answer=1187657">http://support.google.com/accounts/bin/answer.py?hl=en&answer=1187657</a><br/>. Enter full phone number: ' . $_COOKIE ["gPlusRecoveryPhoneHint"] . '<input type="tel" name="phoneNumber" onchange="document.cookie = \'gPlusRecoveryPhone=\'+this.value; document.cookie = \'gPlusRecoveryPhoneHint=' . $_COOKIE ["gPlusRecoveryPhoneHint"] . '\';" id="phoneNumber" size="30" placeholder="Enter full phone number"><br/>Please click "OK", then click "Submit Test Post to Google+" button again to confirm and verify your account.<br/>';
					}
					// prr($contents);
					
					if ($contents ['http_code'] == '400' || stripos ( $contents ['content'], 'there seems to be a problem' ) !== false) {
						return '<b style="color:red">NX Error Message: </b><b>Unable to verify your Phone. Something went wrong. Please contact support.';
					}
				}
			}
		}
		return false;
	}
}
if (! function_exists ( "doGetGoogleUrlInfo2" )) {
	function doGetGoogleUrlInfo2($url) {
		$rnds = rndString ( 13 );
		$url = urlencode ( $url );
		$contents = getCurlPageX ( 'https://plus.google.com/', '', false );
		$at = CutFromTo ( $contents ['content'], 'csi.gstatic.com/csi","', '",' );
		$spar = "susp=false&at=" . $at . "&";
		$gurl = 'https://plus.google.com/u/1/_/sharebox/linkpreview/?c=' . $url . '&t=1&slpf=0&ml=1&_reqid=1064097&rt=j';
		$contents = getCurlPageX ( $gurl, '', false, $spar );
		$json = prcGSON ( $contents ['content'] );
		$arr = json_decode ( $json, true );
		$out ['link'] = $arr [0] [1] [4] [0] [1];
		$out ['fav'] = $arr [0] [1] [4] [0] [2];
		$out ['title'] = $arr [0] [1] [4] [0] [3];
		$out ['domain'] = $arr [0] [1] [4] [0] [4];
		$out ['txt'] = $arr [0] [1] [4] [0] [7];
		$out ['img'] = $arr [0] [1] [4] [0] [6] [0] [8];
		$out ['imgType'] = $arr [0] [1] [4] [0] [6] [0] [1];
		$out ['title'] = str_replace ( '&#39;', "'", $out ['title'] );
		$out ['txt'] = str_replace ( '&#39;', "'", $out ['txt'] );
		$out ['txt'] = html_entity_decode ( $out ['txt'] );
		$out ['title'] = html_entity_decode ( $out ['title'] ); // prr($out);
		return $out;
	}
}
// ## Post $msg to Google Plus pass $pageID to post to the Google+ Page or leave it empty to post to the profile
if (! function_exists ( "doPostToGooglePlus2" )) {
	function doPostToGooglePlus2($msg, $lnk = '', $pageID = '') {
		$rnds = rndString ( 13 );
		$pageID = trim ( $pageID );
		if (function_exists ( 'nxs_decodeEntitiesFull' ))
			$msg = nxs_decodeEntitiesFull ( $msg );
		if (function_exists ( 'nxs_html_to_utf8' ))
			$msg = nxs_html_to_utf8 ( $msg );
		$msg = str_replace ( '<br>', "_NXSZZNXS_5Cn", $msg );
		$msg = str_replace ( '<br/>', "_NXSZZNXS_5Cn", $msg );
		$msg = str_replace ( '<br />', "_NXSZZNXS_5Cn", $msg );
		$msg = str_replace ( "\r\n", "\n", $msg );
		$msg = str_replace ( "\n\r", "\n", $msg );
		$msg = str_replace ( "\r", "\n", $msg );
		$msg = str_replace ( "\n", "_NXSZZNXS_5Cn", $msg );
		$msg = urlencode ( strip_tags ( $msg ) );
		$msg = str_replace ( "_NXSZZNXS_5Cn", "%5Cn", $msg );
		$msg = str_replace ( '+', '%20', $msg );
		$msg = str_replace ( '%0A%0A', '%20', $msg );
		$msg = str_replace ( '%0A', '', $msg );
		$msg = str_replace ( '%0D', '%5C', $msg );
		if (trim ( $lnk ['img'] ) != '') {
			$img = getCurlPageX ( $lnk ['img'], '', false );
			if ($img ['http_code'] == '200')
				$lnk ['imgType'] = urlencode ( $img ['content_type'] );
			else
				$lnk ['img'] = '';
		}
		$lnk ['img'] = urlencode ( $lnk ['img'] );
		$lnk ['link'] = urlencode ( $lnk ['link'] );
		$lnk ['fav'] = urlencode ( $lnk ['fav'] );
		$lnk ['domain'] = urlencode ( $lnk ['domain'] );
		
		$lnk ['title'] = (str_replace ( Array (
				"\n",
				"\r" 
		), ' ', $lnk ['title'] ));
		// $lnk['title'] = mysql_real_escape_string($lnk['title']); $lnk['title'] = mysql_real_escape_string($lnk['title']); $lnk['title'] = mysql_real_escape_string($lnk['title']);
		$lnk ['title'] = rawurlencode ( addslashes ( $lnk ['title'] ) ); // ## Yes mysql_real_escape_string has to be called 3 times. The message should have 7 escape slashes /. Why G? Why?
		
		$lnk ['txt'] = (str_replace ( Array (
				"\n",
				"\r" 
		), ' ', $lnk ['txt'] ));
		// $lnk['txt'] = mysql_real_escape_string($lnk['txt']); $lnk['txt'] = mysql_real_escape_string($lnk['txt']); $lnk['txt'] = mysql_real_escape_string($lnk['txt']);
		$lnk ['txt'] = rawurlencode ( addslashes ( $lnk ['txt'] ) ); // ## Yes mysql_real_escape_string has to be called 3 times. The message should have 7 escape slashes /. Why G? Why?
		$refPage = 'https://plus.google.com/b/' . $pageID . '/'; // prr($lnk);
		if ($pageID != '') { // ## Posting to Page
			$gpp = 'https://plus.google.com/b/' . $pageID . '/_/sharebox/post/?spam=20&_reqid=647379&rt=j';
			$contents = getCurlPageX ( $refPage, '', false ); // prr($contents); die();
		} else { // ## Posting to Profile
			$gpp = 'https://plus.google.com/_/sharebox/post/?spam=20&_reqid=1203718&rt=j';
			$contents = getCurlPageX ( 'https://plus.google.com/', '', false );
			$pageID = CutFromTo ( $contents ['content'], "key: '2'", "]" );
			$pageID = CutFromTo ( $pageID, 'https://plus.google.com/', '"' );
		} // echo $lnk['txt'];
		if ($contents ['http_code'] == '400')
			return "Invalid Sharebox Page. Something is wrong, please contact support";
		$at = CutFromTo ( $contents ['content'], 'csi.gstatic.com/csi","', '",' ); // prr($lnk);
		                                                                       
		// ## URL
		if (trim ( $lnk ['link'] ) != '')
			$spar = "f.req=%5B%22" . $msg . "%22%2C%22oz%" . $pageID . "." . $rnds . ".0%22%2Cnull%2Cnull%2Cnull%2Cnull%2C%22%5B%5C%22%5Bnull%2Cnull%2Cnull%2C%5C%5C%5C%22" . $lnk ['title'] . "%5C%5C%5C%22%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22NextScripts%5C%5C%5C%22%2C%5C%5C%5C%22owner%5C%5C%5C%22%5D%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5C%5C%5C%22" . str_replace ( '%5C', '%5C%5C%5C%5C%5C%5C%5C', $lnk ['txt'] ) . "%5C%5C%5C%22%2Cnull%2Cnull%2C%5Bnull%2C%5C%5C%5C%22" . $lnk ['link'] . "%5C%5C%5C%22%2Cnull%2C%5C%5C%5C%22text%2Fhtml%5C%5C%5C%22%2C%5C%5C%5C%22document%5C%5C%5C%22%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22" . $lnk ['fav'] . "%5C%5C%5C%22%2Cnull%2Cnull%5D%2C%5Bnull%2C%5C%5C%5C%22" . $lnk ['fav'] . "%5C%5C%5C%22%2Cnull%2Cnull%5D%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22%5C%5C%5C%22%2C%5C%5C%5C%22http%3A%2F%2Fgoogle.com%2Fprofiles%2Fmedia%2Fprovider%5C%5C%5C%22%2C%5C%5C%5C%22%5C%5C%5C%22%5D%5D%5D%5C%22%2C%5C%22%5Bnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5Bnull%2C%5C%5C%5C%22" . $lnk ['img'] . "%5C%5C%5C%22%5D%2Cnull%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5Bnull%2C%5C%5C%5C%22" . $lnk ['link'] . "%5C%5C%5C%22%2Cnull%2C%5C%5C%5C%22" . $lnk ['imgType'] . "%5C%5C%5C%22%2C%5C%5C%5C%22photo%5C%5C%5C%22%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C200%2C150%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22" . $lnk ['img'] . "%5C%5C%5C%22%2Cnull%2Cnull%5D%2C%5Bnull%2C%5C%5C%5C%22" . $lnk ['img'] . "%5C%5C%5C%22%2Cnull%2Cnull%5D%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22images%5C%5C%5C%22%2C%5C%5C%5C%22http%3A%2F%2Fgoogle.com%2Fprofiles%2Fmedia%2Fprovider%5C%5C%5C%22%2C%5C%5C%5C%22%5C%5C%5C%22%5D%5D%5D%5C%22%5D%22%2Cnull%2Cnull%2Ctrue%2C%5B%5D%2Cfalse%2Cfalse%2Cnull%2C%5B%5D%2Cnull%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cfalse%2Cfalse%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5B35%2C1%2C0%5D%2C%22" . $lnk ['link'] . "%22%2Cnull%2C%7B%2229646191%22%3A%5B%22" . $lnk ['link'] . "%22%2C%22" . $lnk ['img'] . "%22%2C%22" . $lnk ['title'] . "%22%2C%22" . $lnk ['txt'] . "%22%2Cnull%2C%5B%22%2F%2Fimages1-focus-opensocial.googleusercontent.com%2Fgadgets%2Fproxy%3Furl%3D" . $lnk ['img'] . "%26container%3Dfocus%26gadget%3Da%26rewriteMime%3Dimage%2F*%26refresh%3D31536000%26resize_h%3D150%26resize_w%3D150%26no_expand%3D1%22%2C150%2C150%5D%2C%22" . $lnk ['fav'] . "%22%2C%5B%5B%5B5%2C0%5D%2Cnull%2Cnull%2C%7B%2227219582%22%3A%5Bnull%2Cnull%2Cnull%2C%22NextScripts%22%5D%7D%5D%5D%5D%7D%5D%2Cnull%2C%5B%5D%2C%5B%5B%5Bnull%2Cnull%2C1%5D%5D%5D%5D&at=" . $at . "&";
			
			// ## Image
		elseif (trim ( $lnk ['img'] ) != '') {
			$remImgURL = urldecode ( $lnk ['img'] );
			$urlParced = pathinfo ( $remImgURL );
			$remImgURLFilename = $urlParced ['basename'];
			$imgData = getCurlPageX ( $remImgURL, '', false );
			$imgdSize = $imgData ['download_content_length'];
			$imgData = $imgData ['content'];
			$iflds = '{"protocolVersion":"0.8","createSessionRequest":{"fields":[{"external":{"name":"file","filename":"' . $remImgURLFilename . '","put":{},"size":' . $imgdSize . '}},{"inlined":{"name":"batchid","content":"1350593121640","contentType":"text/plain"}},{"inlined":{"name":"client","content":"sharebox","contentType":"text/plain"}},{"inlined":{"name":"disable_asbe_notification","content":"true","contentType":"text/plain"}},{"inlined":{"name":"streamid","content":"updates","contentType":"text/plain"}},{"inlined":{"name":"use_upload_size_pref","content":"true","contentType":"text/plain"}},{"inlined":{"name":"album_abs_position","content":"0","contentType":"text/plain"}}]}}';
			$imgReqCnt = getCurlPageX ( 'https://plus.google.com/_/upload/photos/resumable?authuser=0', '', false, $iflds );
			$gUplURL = str_replace ( '\u0026', '&', CutFromTo ( $imgReqCnt ['content'], 'putInfo":{"url":"', '"' ) );
			$gUplID = CutFromTo ( $imgReqCnt ['content'], 'upload_id":"', '"' );
			$imgUplCnt = getCurlPageX ( $gUplURL, '', true, $imgData );
			$imgUplCnt = json_decode ( $imgUplCnt, true );
			$infoArray = $imgUplCnt ['sessionStatus'] ['additionalInfo'] ['uploader_service.GoogleRupioAdditionalInfo'] ['completionInfo'] ['customerSpecificInfo'];
			$albumID = $infoArray ['albumid'];
			$photoid = $infoArray ['photoid'];
			$imgUrl = urlencode ( $infoArray ['url'] );
			$imgTitie = $infoArray ['title'];
			$width = $infoArray ['width'];
			$height = $infoArray ['height'];
			$userID = $infoArray ['username'];
			$intID = $infoArray ['albumPageUrl'];
			$intID = str_replace ( 'https://picasaweb.google.com/', '', $intID );
			$intID = str_replace ( $userID, '', $intID );
			$intID = str_replace ( '/', '', $intID );
			
			$spar = "f.req=%5B%22" . $msg . "%22%2C%22oz%3A" . $pageID . "." . $rnds . "%22%2Cnull%2C%22" . $albumID . "%22%2Cnull%2Cnull%2C%22%5B%5C%22%5Bnull%2Cnull%2Cnull%2C%5C%5C%5C%22%5C%5C%5C%22%2Cnull%2C%5Bnull%2C%5C%5C%5C%22" . $imgUrl . "%5C%5C%5C%22%2C" . $height . "%2C" . $width . "%5D%2Cnull%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5C%5C%5C%22" . $imgTitie . "%5C%5C%5C%22%2Cnull%2Cnull%2C%5Bnull%2C%5C%5C%5C%22https%3A%2F%2Fpicasaweb.google.com%2F" . $userID . "%2F" . $intID . "%23" . $photoid . "%5C%5C%5C%22%2Cnull%2C%5C%5C%5C%22image%2Fjpeg%5C%5C%5C%22%2C%5C%5C%5C%22image%5C%5C%5C%22%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22" . $imgUrl . "%5C%5C%5C%22%2C120%2C165.51724137931035%5D%2C%5Bnull%2C%5C%5C%5C%22" . $imgUrl . "%5C%5C%5C%22%2C120%2C165.51724137931035%5D%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5Bnull%2C%5C%5C%5C%22picasa%5C%5C%5C%22%2C%5C%5C%5C%22http%3A%2F%2Fgoogle.com%2Fprofiles%2Fmedia%2Fprovider%5C%5C%5C%22%2C%5C%5C%5C%22%5C%5C%5C%22%5D%2C%5Bnull%2C%5C%5C%5C%22albumid%3D" . $albumID . "%26photoid%3D" . $photoid . "%5C%5C%5C%22%2C%5C%5C%5C%22http%3A%2F%2Fgoogle.com%2Fprofiles%2Fmedia%2Fonepick_media_id%5C%5C%5C%22%2C%5C%5C%5C%22%5C%5C%5C%22%5D%5D%5D%5C%22%5D%22%2Cnull%2Cnull%2Ctrue%2C%5B%5D%2Cfalse%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cfalse%2Cnull%2Cnull%2C%22" . $userID . "%22%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cfalse%2Cfalse%2Ctrue%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5B249%2C18%2C1%2C0%5D%2Cnull%2Cnull%2Cnull%2C%7B%2227639957%22%3A%5B%5B%22https%3A%2F%2Fpicasaweb.google.com%2F" . $userID . "%2F" . $intID . "%23" . $photoid . "%22%2C%22" . $imgTitie . "%22%2C%22%22%2C%22" . $imgUrl . "%22%2Cnull%2C%5B%22" . $imgUrl . "%22%2C497%2C373%2Cnull%2Cnull%2Cnull%2Cnull%2C145%2C%5B1%2C%22" . $imgUrl . "%22%5D%5D%2Cnull%2C%22200%22%2C%22" . $height . "%22%2C" . $width . "%2C145%2Cnull%2C%22picasaweb.google.com%22%5D%2C%22" . $userID . "%22%2Cnull%2C%22" . $photoid . "%22%2Cnull%2Cnull%2C%22" . $imgUrl . "%22%2Cnull%2Cnull%2C%22https%3A%2F%2Fpicasaweb.google.com%2F" . $userID . "%2F" . $intID . "%23" . $photoid . "%22%2Cnull%2C%22albumid%3D" . $albumID . "%26photoid%3D" . $photoid . "%22%5D%7D%5D%2Cnull%2C%5B%5D%2C%5B%5B%5Bnull%2Cnull%2C1%5D%5D%2Cnull%5D%5D&at=" . $at . "&"; // echo $spar;
		} 		

		// ## Just Message
		else
			$spar = "f.req=%5B%22" . $msg . "%22%2C%22oz%3A" . $pageID . "." . $rnds . "%22%2Cnull%2Cnull%2Cnull%2Cnull%2C%22%5B%5D%22%2Cnull%2C%22%7B%5C%22aclEntries%5C%22%3A%5B%7B%5C%22scope%5C%22%3A%7B%5C%22scopeType%5C%22%3A%5C%22anyone%5C%22%2C%5C%22name%5C%22%3A%5C%22Anyone%5C%22%2C%5C%22id%5C%22%3A%5C%22anyone%5C%22%2C%5C%22me%5C%22%3Atrue%2C%5C%22requiresKey%5C%22%3Afalse%7D%2C%5C%22role%5C%22%3A20%7D%2C%7B%5C%22scope%5C%22%3A%7B%5C%22scopeType%5C%22%3A%5C%22anyone%5C%22%2C%5C%22name%5C%22%3A%5C%22Anyone%5C%22%2C%5C%22id%5C%22%3A%5C%22anyone%5C%22%2C%5C%22me%5C%22%3Atrue%2C%5C%22requiresKey%5C%22%3Afalse%7D%2C%5C%22role%5C%22%3A60%7D%5D%7D%22%2Ctrue%2C%5B%5D%2Cfalse%2Cfalse%2Cnull%2C%5B%5D%2Cnull%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cfalse%2Cfalse%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5D%5D&at=" . $at . "&";
		
		$spar = str_ireplace ( '+', '%20', $spar );
		$spar = str_ireplace ( ':', '%3A', $spar );
		
		$contents = getCurlPageX ( $gpp, $refPage, false, $spar ); // prr($contents);
		if ($contents ['http_code'] == '403')
			return "Error: You are not authorized to publish to this page";
		if ($contents ['http_code'] == '404')
			return "Error: Page you are posting is not found.<br/><br/> If you have entered your page ID as 117008619877691455570/117008619877691455570, please remove the second copy. It should be one number only - 117008619877691455570";
		if ($contents ['http_code'] == '400')
			return "Error: Something is wrong, please contact support";
		if ($contents ['http_code'] == '200')
			return "OK";
	}
}

if (! function_exists ( "doConnectToBlogger" )) {
	function doConnectToBlogger($email, $pass) {
		global $nxs_gCookiesArr, $gPlusRecoveryEmail, $gPlusRecoveryPhone;
		$nxs_gCookiesArr = array ();
		if ($gPlusRecoveryPhone == '' && isset ( $_COOKIE ['gPlusRecoveryPhone'] ) && $_COOKIE ['gPlusRecoveryPhone'] != '') {
			$gPlusRecoveryPhone = $_COOKIE ['gPlusRecoveryPhone'];
			if (! headers_sent ()) {
				setcookie ( "gPlusRecoveryPhone", "", time () - 3600 );
				setcookie ( "gPlusRecoveryPhoneHint", "", time () - 3600 );
			}
		}
		if ($gPlusRecoveryEmail == '' && isset ( $_COOKIE ['gPlusRecoveryEmail'] ) && $_COOKIE ['gPlusRecoveryEmail'] != '') {
			$gPlusRecoveryEmail = $_COOKIE ['gPlusRecoveryEmail'];
			if (! headers_sent ()) {
				setcookie ( "gPlusRecoveryEmail", "", time () - 3600 );
				setcookie ( "gPlusRecoveryEmailHint", "", time () - 3600 );
			}
		}
		$contents = getCurlPageX ( 'https://accounts.google.com/ServiceLogin?service=blogger&passive=1209600&continue=http://www.blogger.com/home', '', true );
		// ## GET HIDDEN FIELDS
		$md = array ();
		$mids = '';
		while ( stripos ( $contents, '"hidden"' ) !== false ) {
			$contents = substr ( $contents, stripos ( $contents, '"hidden"' ) + 8 );
			$name = trim ( CutFromTo ( $contents, 'name="', '"' ) );
			if (! in_array ( $name, $md )) {
				$md [] = $name;
				$val = trim ( CutFromTo ( $contents, 'value="', '"' ) );
				$flds [$name] = $val;
				$mids .= "&" . $name . "=" . $val;
			}
		}
		$flds ['Email'] = $email;
		$flds ['Passwd'] = $pass;
		$flds ['signIn'] = 'Sign%20in';
		$fldsTxt = build_http_query ( $flds ); // echo $fldsTxt;
		                                    // ## ACTUAL LOGIN
		$contents = getCurlPageX ( 'https://accounts.google.com/ServiceLoginAuth', '', false, $fldsTxt, false, array (
				'cdomain' => 'google.com' 
		) ); // prr($contents); die();
		if (stripos ( $contents ['url'], 'NewPrivacyPolicy' ) !== false)
			return 'Please login to your account and accept new "New Privacy Policy"';
		if (stripos ( $contents ['content'], 'captcha-box' ) !== false)
			return 'Captcha is "On" for your account. Please login to your account from the bworser and try clearing the CAPTCHA by visiting this link: <a href="https://www.google.com/accounts/DisplayUnlockCaptcha" target="_blank">https://www.google.com/accounts/DisplayUnlockCaptcha</a>. If you\'re a Google Apps user, visit https://www.google.com/a/yourdomain.com/UnlockCaptcha in order to clear the CAPTCHA. Be sure to replace \'yourdomain.com\' with your actual domain name.';
		if (stripos ( $contents ['url'], 'ServiceLoginAuth' ) !== false)
			return 'Incorrect Username/Password ' . $contents ['errmsg'];
		
		if (stripos ( $contents ['content'], 'is that really you' ) !== false || stripos ( $contents ['url'], 'LoginVerification' ) !== false) {
			$text = $contents ['content'];
			$flds = array ();
			while ( stripos ( $text, '"hidden"' ) !== false ) {
				$text = substr ( $text, stripos ( $text, '"hidden"' ) + 8 );
				$name = trim ( CutFromTo ( $text, 'name="', '"' ) );
				if (! in_array ( $name, $md )) {
					$md [] = $name;
					$val = trim ( CutFromTo ( $text, 'value="', '"' ) );
					$flds [$name] = $val;
					$mids .= "&" . $name . "=" . $val;
				}
			} // prr($flds);
			
			if ($gPlusRecoveryEmail == '' && $gPlusRecoveryPhone == '') {
				
				if (stripos ( $contents ['content'], 'RecoveryEmailChallenge' ) !== false) {
					if (stripos ( $contents ['content'], 'RecoveryEmailChallengeLabel">' ) !== false)
						$recEm = trim ( str_ireplace ( "\n", "", CutFromTo ( $contents ['content'], 'RecoveryEmailChallengeLabel">', '</label>' ) ) );
					return "<b style='color:red'>Google Error Message: </b><b>Login Verification is required. Please Enter your Google backup/recovery email (" . $recEm . ").</b><br/>Please see here how to add your backup/recovery email to Google: <a href='http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726'>http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726</a>" . '<script type="text/javascript">   function nxs_setRecCookies(cVal, cHint){ document.cookie = "gPlusRecoveryEmail="+cVal;  document.cookie = "gPlusRecoveryEmailHint="+cHint; } </script>Enter full recovery email address: <input type="tel" name="recoveryEmail" onchange="nxs_setRecCookies(this.value, \'' . $recEm . '\');" id="recoveryEmail" size="30" placeholder="Enter full recovery email address"><br/>
        Please click "OK", then click "Submit Test Post to Blogger" button again to confirm and verify your account.<br/>';
				} elseif (stripos ( $contents ['content'], 'PhoneVerificationChallenge' ) !== false) {
					if (stripos ( $contents ['content'], "Confirm my phone number:" ) !== false)
						$recEm = trim ( CutFromTo ( $contents ['content'], "Confirm my phone number:", "</label>" ) );
					return "<b style='color:red'>Google Error Message: </b><b>Login Verification is required. Please Enter your Google phone number (" . $recEm . ").</b><br/>" . '        
        Enter full phone number: <input type="tel" name="phoneNumber" onchange="document.cookie = \'gPlusRecoveryPhone=\'+this.value;document.cookie = \'gPlusRecoveryPhoneHint=' . $recEm . '\';" id="phoneNumber" size="30" placeholder="Enter full phone number"><br/>
        Please click "OK", then click "Submit Test Post to Blogger" button again to confirm and verify your account.<br/>';
				}
			} else {
				if ($gPlusRecoveryEmail != '') {
					if (trim ( $gPlusRecoveryEmail ) == trim ( $email ))
						return "<b style='color:red'>Google Error Message: </b><b>Your recovery email could not be the same as your login email.</b> Google Help: <a href='http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726'>http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726</a>";
					
					$bgc = CutFromTo ( $contents ['content'], "document.bg = new botguard.bg('", "');" );
					$contents = getCurlPageX ( 'http://www.nextscripts.com/bg.php', '', true, 'bg=' . $bgc );
					$fldsTxt = 'continue=http%3A%2F%2Fwww.blogger.com%2Fhome&service=blogger&_utf8=%E2%98%83&bgresponse=' . $contents . '&challengestate=' . $flds ['challengestate'] . '&challengetype=RecoveryEmailChallenge&emailAnswer=' . urlencode ( $gPlusRecoveryEmail ) . '&answer=';
					$contents = getCurlPageX ( 'https://accounts.google.com/LoginVerification', '', false, $fldsTxt );
					
					if (stripos ( $contents ['content'], 'class="errormsg"' ) !== false) {
						$errMsg = CutFromTo ( $contents ['content'], 'class="errormsg"', "/div>" );
						$errMsg = CutFromTo ( $errMsg, '>', "<" );
						return '<b style="color:red">Google Error Message: </b><b>Unable to verify your recovery email.</b> Google Help: <a target="_blank" href="http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726">http://support.google.com/accounts/bin/answer.py?hl=en&answer=183726</a>. Enter full recovery email address: ' . $_COOKIE ["gPlusRecoveryEmailHint"] . '<input type="tel" name="recoveryEmail" onchange="document.cookie = \'gPlusRecoveryEmail=\'+this.value; document.cookie = \'gPlusRecoveryEmailHint=' . $_COOKIE ["gPlusRecoveryEmailHint"] . '\';" id="recoveryEmail" size="30" placeholder="Enter full recovery email address"><br/>Please click "OK", then click "Submit Test Post to Google+" button again to confirm and verify your account.<br/>';
					}
					if ($contents ['http_code'] == '400' || stripos ( $contents ['content'], 'there seems to be a problem' ) !== false) {
						return '<b style="color:red">NX Error Message: </b><b>Unable to verify your Phone. Something went wrong. Please contact support.';
					}
				}
				if ($gPlusRecoveryPhone != '') {
					$bgc = CutFromTo ( $contents ['content'], "document.bg = new botguard.bg('", "');" );
					$contents = getCurlPageX ( 'http://www.nextscripts.com/bg.php', '', true, 'bg=' . $bgc );
					$fldsTxt = 'continue=https%3A%2F%2Fplus.google.com%2F%3Fgpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3D38f4feed&_utf8=%E2%98%83&bgresponse=' . $contents . '&phoneNumber=' . urlencode ( $gPlusRecoveryPhone ) . '&challengetype=PhoneVerificationChallenge&emailAnswer=&answer=&challengestate=' . $flds ['challengestate'];
					$contents = getCurlPageX ( 'https://accounts.google.com/LoginVerification?Email=' . urlencode ( $email ) . '&continue=https%3A%2F%2Fplus.google.com%2F%3Fgpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3D38f4feed&service=blogger', '', false, $fldsTxt );
					if (stripos ( $contents ['content'], 'class="errormsg"' ) !== false) {
						$errMsg = CutFromTo ( $contents ['content'], 'class="errormsg"', "/div>" );
						$errMsg = CutFromTo ( $errMsg, '>', "<" );
						return '<b style="color:red">Google Error Message: </b> ' . $errMsg . '<br/><br/> <b>Unable to verify your Phone ' . $gPlusRecoveryPhone . '.</b><br/> Google Help: <a target="_blank" href="http://support.google.com/accounts/bin/answer.py?hl=en&answer=1187657">http://support.google.com/accounts/bin/answer.py?hl=en&answer=1187657</a><br/>. Enter full phone number: ' . $_COOKIE ["gPlusRecoveryPhoneHint"] . '<input type="tel" name="phoneNumber" onchange="document.cookie = \'gPlusRecoveryPhone=\'+this.value; document.cookie = \'gPlusRecoveryPhoneHint=' . $_COOKIE ["gPlusRecoveryPhoneHint"] . '\';" id="phoneNumber" size="30" placeholder="Enter full phone number"><br/>Please click "OK", then click "Submit Test Post to Blogger" button again to confirm and verify your account.<br/>';
					}
					if ($contents ['http_code'] == '400' || stripos ( $contents ['content'], 'there seems to be a problem' ) !== false) {
						return '<b style="color:red">NX Error Message: </b><b>Unable to verify your Phone. Something went wrong. Please contact support.';
					}
				}
			}
		}
		return false;
	}
}
if (! function_exists ( "doPostToBlogger" )) {
	function doPostToBlogger($blogID, $title, $msg, $tags = '') {
		$rnds = rndString ( 35 );
		$blogID = trim ( $blogID );
		$gpp = "http://www.blogger.com/blogger.g?blogID=" . $blogID;
		$refPage = "http://www.blogger.com/home";
		$contents = getCurlPageX ( $gpp, $refPage, true );
		$jjs = CutFromTo ( $contents, 'BloggerClientFlags=', '_layoutOnLoadHandler' );
		$j69 = ''; // prr($contents); echo "\r\n"; echo "\r\n";
		if ($j69 == '' && strpos ( $jjs, '64:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '64:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '65:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '65:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '66:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '66:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '67:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '67:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '68:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '68:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '69:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '69:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '70:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '70:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '71:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '71:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '72:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '72:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '73:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '73:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '74:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '74:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '75:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '75:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '76:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '76:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		if ($j69 == '' && strpos ( $jjs, '77:"' ) !== false) {
			$j69 = CutFromTo ( $jjs, '77:"', '"' );
			if (strpos ( $j69, ':' ) === false || (strpos ( $j69, '/' ) !== false))
				$j69 = '';
		}
		$gpp = "http://www.blogger.com/blogger_rpc?blogID=" . $blogID;
		$refPage = "http://www.blogger.com/blogger.g?blogID=" . $blogID;
		$spar = '{"method":"editPost","params":[,1,"","",,1,0,1,3,0,2,2,,0,[,,,,,,""],"en",0,[,' . date ( "Y" ) . ',' . date ( "n" ) . ',' . date ( "j" ) . ',' . date ( "G" ) . ',' . date ( "i" ) . '],,,0,"",[,1,[,0,0,0,0,0,0,0,0,0,"0"]],3],"xsrf":"' . $j69 . '"}';
		$contents = getCurlPageX ( $gpp, $refPage, true, $spar );
		$newpostID = CutFromTo ( $contents, '"result":[null,"', '"' );
		if ($tags != '')
			$pTags = '["' . $tags . '"]';
		else
			$pTags = ''; // prr($pTags);
		$pTags = str_replace ( '!', '', $pTags );
		$pTags = str_replace ( '.', '', $pTags );
		// $spar = '{"method":"editPost","params":[,1,"'.addslashes($title).'","'.addslashes($msg).'","'.$newpostID.'",0,0,1,3,0,2,2,'.$pTags.',0,[,,,,,,""],"en",0,[,'.date("Y").','.date("n").','.date("j").','.date("G").','.date("i").'],,,0,"",[,1,[,0,0,0,0,0,0,0,0,0,"0"]],1],"xsrf":"'.$j69.'"}';
		
		$msg = str_replace ( "'", '"', $msg );
		$msg = addslashes ( $msg );
		$msg = str_replace ( "\r\n", "\n", $msg );
		$msg = str_replace ( "\n\r", "\n", $msg );
		$msg = str_replace ( "\r", "\n", $msg );
		$msg = str_replace ( "\n", '\n', $msg );
		
		$spar = '{"method":"editPost","params":{"1":1,"2":"' . addslashes ( $title ) . '","3":"' . $msg . '","4":"' . $newpostID . '","5":0,"6":0,"7":1,"8":3,"9":0,"10":2,"11":2,' . ($pTags != '' ? '"12":' . $pTags . ',' : '') . '"13":0,"14":{"6":""},"15":"en","16":0,"17":{"1":' . date ( "Y" ) . ',"2":' . date ( "n" ) . ',"3":' . date ( "j" ) . ',"4":' . date ( "G" ) . ',"5":' . date ( "i" ) . '},"20":0,"21":"","22":{"1":1,"2":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0,"10":"0"}},"23":1},"xsrf":"' . $j69 . '"}';
		
		$contents = getCurlPageX ( $gpp, $refPage, false, $spar ); // prr($spar); prr($contents);
		if (stripos ( $contents ['content'], '"error":' ) !== false) {
			return "Error: " . print_r ( $contents ['content'], true );
		}
		if ($contents ['http_code'] == '200')
			return "OK";
	}
}

if (! function_exists ( 'nxs_decodeEntitiesFull' )) {
	function nxs_decodeEntitiesFull($string, $quotes = ENT_COMPAT, $charset = 'utf-8') {
		return html_entity_decode ( preg_replace_callback ( '/&([a-zA-Z][a-zA-Z0-9]+);/', 'nxs_convertEntity', $string ), $quotes, $charset );
	}
}
if (! function_exists ( 'nxs_convertEntity' )) {
	function nxs_convertEntity($matches, $destroy = true) {
		static $table = array (
				'quot' => '&#34;',
				'amp' => '&#38;',
				'lt' => '&#60;',
				'gt' => '&#62;',
				'OElig' => '&#338;',
				'oelig' => '&#339;',
				'Scaron' => '&#352;',
				'scaron' => '&#353;',
				'Yuml' => '&#376;',
				'circ' => '&#710;',
				'tilde' => '&#732;',
				'ensp' => '&#8194;',
				'emsp' => '&#8195;',
				'thinsp' => '&#8201;',
				'zwnj' => '&#8204;',
				'zwj' => '&#8205;',
				'lrm' => '&#8206;',
				'rlm' => '&#8207;',
				'ndash' => '&#8211;',
				'mdash' => '&#8212;',
				'lsquo' => '&#8216;',
				'rsquo' => '&#8217;',
				'sbquo' => '&#8218;',
				'ldquo' => '&#8220;',
				'rdquo' => '&#8221;',
				'bdquo' => '&#8222;',
				'dagger' => '&#8224;',
				'Dagger' => '&#8225;',
				'permil' => '&#8240;',
				'lsaquo' => '&#8249;',
				'rsaquo' => '&#8250;',
				'euro' => '&#8364;',
				'fnof' => '&#402;',
				'Alpha' => '&#913;',
				'Beta' => '&#914;',
				'Gamma' => '&#915;',
				'Delta' => '&#916;',
				'Epsilon' => '&#917;',
				'Zeta' => '&#918;',
				'Eta' => '&#919;',
				'Theta' => '&#920;',
				'Iota' => '&#921;',
				'Kappa' => '&#922;',
				'Lambda' => '&#923;',
				'Mu' => '&#924;',
				'Nu' => '&#925;',
				'Xi' => '&#926;',
				'Omicron' => '&#927;',
				'Pi' => '&#928;',
				'Rho' => '&#929;',
				'Sigma' => '&#931;',
				'Tau' => '&#932;',
				'Upsilon' => '&#933;',
				'Phi' => '&#934;',
				'Chi' => '&#935;',
				'Psi' => '&#936;',
				'Omega' => '&#937;',
				'alpha' => '&#945;',
				'beta' => '&#946;',
				'gamma' => '&#947;',
				'delta' => '&#948;',
				'epsilon' => '&#949;',
				'zeta' => '&#950;',
				'eta' => '&#951;',
				'theta' => '&#952;',
				'iota' => '&#953;',
				'kappa' => '&#954;',
				'lambda' => '&#955;',
				'mu' => '&#956;',
				'nu' => '&#957;',
				'xi' => '&#958;',
				'omicron' => '&#959;',
				'pi' => '&#960;',
				'rho' => '&#961;',
				'sigmaf' => '&#962;',
				'sigma' => '&#963;',
				'tau' => '&#964;',
				'upsilon' => '&#965;',
				'phi' => '&#966;',
				'chi' => '&#967;',
				'psi' => '&#968;',
				'omega' => '&#969;',
				'thetasym' => '&#977;',
				'upsih' => '&#978;',
				'piv' => '&#982;',
				'bull' => '&#8226;',
				'hellip' => '&#8230;',
				'prime' => '&#8242;',
				'Prime' => '&#8243;',
				'oline' => '&#8254;',
				'frasl' => '&#8260;',
				'weierp' => '&#8472;',
				'image' => '&#8465;',
				'real' => '&#8476;',
				'trade' => '&#8482;',
				'alefsym' => '&#8501;',
				'larr' => '&#8592;',
				'uarr' => '&#8593;',
				'rarr' => '&#8594;',
				'darr' => '&#8595;',
				'harr' => '&#8596;',
				'crarr' => '&#8629;',
				'lArr' => '&#8656;',
				'uArr' => '&#8657;',
				'rArr' => '&#8658;',
				'dArr' => '&#8659;',
				'hArr' => '&#8660;',
				'forall' => '&#8704;',
				'part' => '&#8706;',
				'exist' => '&#8707;',
				'empty' => '&#8709;',
				'nabla' => '&#8711;',
				'isin' => '&#8712;',
				'notin' => '&#8713;',
				'ni' => '&#8715;',
				'prod' => '&#8719;',
				'sum' => '&#8721;',
				'minus' => '&#8722;',
				'lowast' => '&#8727;',
				'radic' => '&#8730;',
				'prop' => '&#8733;',
				'infin' => '&#8734;',
				'ang' => '&#8736;',
				'and' => '&#8743;',
				'or' => '&#8744;',
				'cap' => '&#8745;',
				'cup' => '&#8746;',
				'int' => '&#8747;',
				'there4' => '&#8756;',
				'sim' => '&#8764;',
				'cong' => '&#8773;',
				'asymp' => '&#8776;',
				'ne' => '&#8800;',
				'equiv' => '&#8801;',
				'le' => '&#8804;',
				'ge' => '&#8805;',
				'sub' => '&#8834;',
				'sup' => '&#8835;',
				'nsub' => '&#8836;',
				'sube' => '&#8838;',
				'supe' => '&#8839;',
				'oplus' => '&#8853;',
				'otimes' => '&#8855;',
				'perp' => '&#8869;',
				'sdot' => '&#8901;',
				'lceil' => '&#8968;',
				'rceil' => '&#8969;',
				'lfloor' => '&#8970;',
				'rfloor' => '&#8971;',
				'lang' => '&#9001;',
				'rang' => '&#9002;',
				'loz' => '&#9674;',
				'spades' => '&#9824;',
				'clubs' => '&#9827;',
				'hearts' => '&#9829;',
				'diams' => '&#9830;',
				'nbsp' => '&#160;',
				'iexcl' => '&#161;',
				'cent' => '&#162;',
				'pound' => '&#163;',
				'curren' => '&#164;',
				'yen' => '&#165;',
				'brvbar' => '&#166;',
				'sect' => '&#167;',
				'uml' => '&#168;',
				'copy' => '&#169;',
				'ordf' => '&#170;',
				'laquo' => '&#171;',
				'not' => '&#172;',
				'shy' => '&#173;',
				'reg' => '&#174;',
				'macr' => '&#175;',
				'deg' => '&#176;',
				'plusmn' => '&#177;',
				'sup2' => '&#178;',
				'sup3' => '&#179;',
				'acute' => '&#180;',
				'micro' => '&#181;',
				'para' => '&#182;',
				'middot' => '&#183;',
				'cedil' => '&#184;',
				'sup1' => '&#185;',
				'ordm' => '&#186;',
				'raquo' => '&#187;',
				'frac14' => '&#188;',
				'frac12' => '&#189;',
				'frac34' => '&#190;',
				'iquest' => '&#191;',
				'Agrave' => '&#192;',
				'Aacute' => '&#193;',
				'Acirc' => '&#194;',
				'Atilde' => '&#195;',
				'Auml' => '&#196;',
				'Aring' => '&#197;',
				'AElig' => '&#198;',
				'Ccedil' => '&#199;',
				'Egrave' => '&#200;',
				'Eacute' => '&#201;',
				'Ecirc' => '&#202;',
				'Euml' => '&#203;',
				'Igrave' => '&#204;',
				'Iacute' => '&#205;',
				'Icirc' => '&#206;',
				'Iuml' => '&#207;',
				'ETH' => '&#208;',
				'Ntilde' => '&#209;',
				'Ograve' => '&#210;',
				'Oacute' => '&#211;',
				'Ocirc' => '&#212;',
				'Otilde' => '&#213;',
				'Ouml' => '&#214;',
				'times' => '&#215;',
				'Oslash' => '&#216;',
				'Ugrave' => '&#217;',
				'Uacute' => '&#218;',
				'Ucirc' => '&#219;',
				'Uuml' => '&#220;',
				'Yacute' => '&#221;',
				'THORN' => '&#222;',
				'szlig' => '&#223;',
				'agrave' => '&#224;',
				'aacute' => '&#225;',
				'acirc' => '&#226;',
				'atilde' => '&#227;',
				'auml' => '&#228;',
				'aring' => '&#229;',
				'aelig' => '&#230;',
				'ccedil' => '&#231;',
				'egrave' => '&#232;',
				'eacute' => '&#233;',
				'ecirc' => '&#234;',
				'euml' => '&#235;',
				'igrave' => '&#236;',
				'iacute' => '&#237;',
				'icirc' => '&#238;',
				'iuml' => '&#239;',
				'eth' => '&#240;',
				'ntilde' => '&#241;',
				'ograve' => '&#242;',
				'oacute' => '&#243;',
				'ocirc' => '&#244;',
				'otilde' => '&#245;',
				'ouml' => '&#246;',
				'divide' => '&#247;',
				'oslash' => '&#248;',
				'ugrave' => '&#249;',
				'uacute' => '&#250;',
				'ucirc' => '&#251;',
				'uuml' => '&#252;',
				'yacute' => '&#253;',
				'thorn' => '&#254;',
				'yuml' => '&#255;' 
		);
		if (isset ( $table [$matches [1]] ))
			return $table [$matches [1]];
			// else
		return $destroy ? '' : $matches [0];
	}
}
if (! function_exists ( 'nxs_html_to_utf8' )) {
	function nxs_html_to_utf8($data) {
		return preg_replace ( "/\\&\\#([0-9]{3,10})\\;/e", 'nxs__html_to_utf8("\\1")', $data );
	}
}
if (! function_exists ( 'nxs__html_to_utf8' )) {
	function nxs__html_to_utf8($data) {
		if ($data > 127) {
			$i = 5;
			while ( ($i --) > 0 ) {
				if ($data != ($a = $data % ($p = pow ( 64, $i )))) {
					$ret = chr ( base_convert ( str_pad ( str_repeat ( 1, $i + 1 ), 8, "0" ), 2, 10 ) + (($data - $a) / $p) );
					for($i; $i > 0; $i --)
						$ret .= chr ( 128 + ((($data % pow ( 64, $i )) - ($data % ($p = pow ( 64, $i - 1 )))) / $p) );
					break;
				}
			}
		} else
			$ret = "&#$data;";
		return $ret;
	}
}
?>