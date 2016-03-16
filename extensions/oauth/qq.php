<?php

/*
 * user_info
 */
function get_user_info($config, $oauth_data) {
    $aConfig = array (
		'appid'  => $config['appid'],
		'appkey' => $config['appkey'],
		'api'    => 'get_user_info,add_topic,add_one_blog,add_album,upload_pic,list_album,add_share,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idollist,add_idol,del_idol,get_tenpay_addr'
	);
    $sUrl = "https://graph.qq.com/user/get_user_info";
    $aGetParam = array(
        "access_token"          =>    $oauth_data["access_token"],
        "oauth_consumer_key"    =>    $aConfig["appid"],
        "openid"                =>    $oauth_data["oauth_openid"],
        "format"                =>    "json"
    );
    $sContent = get($sUrl, $aGetParam);
    if($sContent!==FALSE){
        $user = json_decode($sContent, true);
		return array("name"=>$user["nickname"], "avatar"=>$user["figureurl_1"]);
    }
}

/*
 * Logout
 */
 
function oauth_logout() {
    unset($_SESSION["state"]);
	unset($_SESSION["URI"]);
	$session = new session();
	$session->delete('oauth_data');
} 

/*
 * Login
 */
function oauth_login($config) {
	if (!function_exists("curl_init")) {
		echo "<h1>腾讯开放平台提示：请先开启curl支持</h1>";
		echo "
			开启php curl函数库的步骤(for windows)<br />
			1).去掉windows/php.ini 文件里;extension=php_curl.dll前面的; /*用 echo phpinfo();查看php.ini的路径*/<br />
			2).把php5/libeay32.dll，ssleay32.dll复制到系统目录windows/下<br />
			3).重启apache<br />
			";
		exit();
	}
	$aConfig = array (
		'appid'  => $config['appid'],
		'appkey' => $config['appkey'],
		'api'    => 'get_user_info,add_topic,add_one_blog,add_album,upload_pic,list_album,add_share,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idollist,add_idol,del_idol,get_tenpay_addr'
	);
	$sState = md5(date('YmdHis' . getip()));
	$_SESSION['state'] = $sState;
	$server_name = strtolower($_SERVER['SERVER_NAME']);
	$server_port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':' . (int)$_SERVER['SERVER_PORT'];
	$secure      = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;	
	$callback    = ($secure ? 'https://' : 'http://') . $server_name . $server_port;
	$callback    = $callback . url('member/register/callback', array('app'=>'qq'));
	$_SESSION['URI'] = $callback;
	$aParam = array(
		"response_type"    =>    'code',
		"client_id"        =>    $aConfig["appid"],
		"redirect_uri"     =>    $callback,
		"scope"            =>    $aConfig["api"],
		"state"            =>    $sState
	);
	$aGet = array();
	foreach($aParam as $key=>$val){
		$aGet[] = $key . '=' . urlencode($val);
	}
	$sUrl  = "https://graph.qq.com/oauth2.0/authorize?";
	$sUrl .= join("&", $aGet);
	header("location:" . $sUrl);
}

/*
 * callback
 */
function oauth_callback($config) {
	$aConfig = array (
		'appid'  => $config['appid'],
		'appkey' => $config['appkey'],
		'api'    => 'get_user_info,add_topic,add_one_blog,add_album,upload_pic,list_album,add_share,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idollist,add_idol,del_idol,get_tenpay_addr'
	);
	$sUrl = "https://graph.qq.com/oauth2.0/token";
	$aGetParam = array(
		"grant_type"       =>    "authorization_code",
		"client_id"        =>    $aConfig["appid"],
		"client_secret"    =>    $aConfig["appkey"],
		"code"             =>    $_GET["code"],
		"state"            =>    $_GET["state"],
		"redirect_uri"     =>    $_SESSION["URI"]
	);
	unset($_SESSION["state"]);
	unset($_SESSION["URI"]);
	$sContent = get($sUrl,$aGetParam);
	
	if($sContent!==FALSE){
		$aTemp  = explode("&", $sContent);
		$aParam = $oauth_data = array();
		foreach($aTemp as $val){
			$aTemp2 = explode("=", $val);
			$aParam[$aTemp2[0]] = $aTemp2[1];
		}
		$oauth_data["access_token"] = $aParam["access_token"];
		$sUrl = "https://graph.qq.com/oauth2.0/me";
		$aGetParam = array(
			"access_token"    => $aParam["access_token"]
		);
		$sContent = get($sUrl, $aGetParam);
		if($sContent!==FALSE){
			$aTemp = array();
			preg_match('/callback\(\s+(.*?)\s+\)/i', $sContent,$aTemp);
			$aResult = json_decode($aTemp[1],true);
			$session = new session();
			$oauth_data['oauth_openid'] = $aResult["openid"];
			$session->set('oauth_data', $oauth_data);
		}
	}
}


/*
 * 获取IP
*/
function getip() {
	if (isset ( $_SERVER )) {
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
			$aIps = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
			foreach ( $aIps as $sIp ) {
				$sIp = trim ( $sIp );
				if ($sIp != 'unknown') {
					$sRealIp = $sIp;
					break;
				}
			}
		} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
			$sRealIp = $_SERVER ['HTTP_CLIENT_IP'];
		} else {
			if (isset ( $_SERVER ['REMOTE_ADDR'] )) {
				$sRealIp = $_SERVER ['REMOTE_ADDR'];
			} else {
				$sRealIp = '0.0.0.0';
			}
		}
	} else {
		if (getenv ( 'HTTP_X_FORWARDED_FOR' )) {
			$sRealIp = getenv ( 'HTTP_X_FORWARDED_FOR' );
		} elseif (getenv ( 'HTTP_CLIENT_IP' )) {
			$sRealIp = getenv ( 'HTTP_CLIENT_IP' );
		} else {
			$sRealIp = getenv ( 'REMOTE_ADDR' );
		}
	}
	return $sRealIp;
}


/*
 * GET请求
 */
function get($sUrl,$aGetParam){
    global $aConfig;
    $oCurl = curl_init();
    if(stripos($sUrl,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    $aGet = array();
    foreach($aGetParam as $key=>$val){
        $aGet[] = $key."=".urlencode($val);
    }
    curl_setopt($oCurl, CURLOPT_URL, $sUrl."?".join("&",$aGet));
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aConfig["debug"])===1){
        echo "<tr><td class='narrow-label'>请求地址:</td><td><pre>".$sUrl."</pre></td></tr>";
        echo "<tr><td class='narrow-label'>GET参数:</td><td><pre>".var_export($aGetParam,true)."</pre></td></tr>";
        echo "<tr><td class='narrow-label'>请求信息:</td><td><pre>".var_export($aStatus,true)."</pre></td></tr>";
        if(intval($aStatus["http_code"])==200){
            echo "<tr><td class='narrow-label'>返回结果:</td><td><pre>".$sContent."</pre></td></tr>";
            if((@$aResult = json_decode($sContent,true))){
                echo "<tr><td class='narrow-label'>结果集合解析:</td><td><pre>".var_export($aResult,true)."</pre></td></tr>";
            }
        }
    }
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        echo "<tr><td class='narrow-label'>返回出错:</td><td><pre>".$aStatus["http_code"].",请检查参数或者确实是腾讯服务器出错咯。</pre></td></tr>";
        return FALSE;
    }
}

/*
 * POST 请求
 */
function post($sUrl,$aPOSTParam){
    global $aConfig;
    $oCurl = curl_init();
    if(stripos($sUrl,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
    }
    $aPOST = array();
    foreach($aPOSTParam as $key=>$val){
        $aPOST[] = $key."=".urlencode($val);
    }
    curl_setopt($oCurl, CURLOPT_URL, $sUrl);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_POST,true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, join("&", $aPOST));
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aConfig["debug"])===1){
        echo "<tr><td class='narrow-label'>请求地址:</td><td><pre>".$sUrl."</pre></td></tr>";
        echo "<tr><td class='narrow-label'>POST参数:</td><td><pre>".var_export($aPOSTParam,true)."</pre></td></tr>";
        echo "<tr><td class='narrow-label'>请求信息:</td><td><pre>".var_export($aStatus,true)."</pre></td></tr>";
        if(intval($aStatus["http_code"])==200){
            echo "<tr><td class='narrow-label'>返回结果:</td><td><pre>".$sContent."</pre></td></tr>";
            if((@$aResult = json_decode($sContent,true))){
                echo "<tr><td class='narrow-label'>结果集合解析:</td><td><pre>".var_export($aResult,true)."</pre></td></tr>";
            }
        }
    }
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        echo "<tr><td class='narrow-label'>返回出错:</td><td><pre>".$aStatus["http_code"].",请检查参数或者确实是腾讯服务器出错咯。</pre></td></tr>";
        return FALSE;
    }
}

/*
 * 上传图片
 */
function upload($sUrl,$aPOSTParam,$aFileParam){
    //防止请求超时
    global $aConfig;
    set_time_limit(0);
    $oCurl = curl_init();
    if(stripos($sUrl,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
    }
    $aPOSTField = array();
    foreach($aPOSTParam as $key=>$val){
        $aPOSTField[$key]= $val;
    }
    foreach($aFileParam as $key=>$val){
        $aPOSTField[$key] = "@".$val; //此处对应的是文件的绝对地址
    }
    curl_setopt($oCurl, CURLOPT_URL, $sUrl);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $aPOSTField);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aConfig["debug"])===1){
        echo "<tr><td class='narrow-label'>请求地址:</td><td><pre>".$sUrl."</pre></td></tr>";
        echo "<tr><td class='narrow-label'>POST参数:</td><td><pre>".var_export($aPOSTParam,true)."</pre></td></tr>";
		echo "<tr><td class='narrow-label'>文件参数:</td><td><pre>".var_export($aFileParam,true)."</pre></td></tr>";
        echo "<tr><td class='narrow-label'>请求信息:</td><td><pre>".var_export($aStatus,true)."</pre></td></tr>";
        if(intval($aStatus["http_code"])==200){
            echo "<tr><td class='narrow-label'>返回结果:</td><td><pre>".$sContent."</pre></td></tr>";
            if((@$aResult = json_decode($sContent,true))){
                echo "<tr><td class='narrow-label'>结果集合解析:</td><td><pre>".var_export($aResult,true)."</pre></td></tr>";
            }
        }
    }
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        echo "<tr><td class='narrow-label'>返回出错:</td><td><pre>".$aStatus["http_code"].",请检查参数或者确实是腾讯服务器出错咯。</pre></td></tr>";
        return FALSE;
    }
}

function download($sUrl,$sFileName){
	$oCurl = curl_init();
	global $aConfig;
    set_time_limit(0);
    $oCurl = curl_init();
    if(stripos($sUrl,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
    }
	curl_setopt($oCurl, CURLOPT_USERAGENT, $_SERVER["USER_AGENT"] ? $_SERVER["USER_AGENT"] : "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.7) Gecko/20100625 Firefox/3.6.7");
    curl_setopt($oCurl, CURLOPT_URL, $sUrl);
	curl_setopt($oCurl, CURLOPT_REFERER, $sUrl);
	curl_setopt($oCurl, CURLOPT_AUTOREFERER, true);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
	file_put_contents($sFileName,$sContent);
    if(intval($aConfig["debug"])===1){
        echo "<tr><td class='narrow-label'>请求地址:</td><td><pre>".$sUrl."</pre></td></tr>";
        echo "<tr><td class='narrow-label'>请求信息:</td><td><pre>".var_export($aStatus,true)."</pre></td></tr>";
    }
    return(intval($aStatus["http_code"])==200);
}