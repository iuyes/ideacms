<?php

/**
 *  分享qq空间
 */
function share_qq($apps, $odata, $data, $name) {
	//加载类库
	$ofile = EXTENSION_DIR . 'oauth/qq.php';
	if (!is_file($ofile)) continue;
	include_once $ofile;
    $url	= "https://graph.qq.com/share/add_share";
	$apps	= string2array($apps);
	$data	= string2array($data);
	$odata	= string2array($odata);
	$appid	= $apps['appid'];
    $Param	= array(
        "access_token"			=>	$odata["access_token"],
        "oauth_consumer_key"    =>	$appid,
        "openid"                =>	$odata["oauth_openid"],
        "format"                =>	"json",
		'title'					=>	$data['title'],
		'url'					=>	'http://' . $_SERVER['HTTP_HOST'] . getUrl($data),
		'summary'				=>	$data['description'],
		'images'				=>	$data['thumb'],
		'site'					=>	$name,
		'fromurl'				=>	SITE_URL,
		
    );
    $result = get($url, $Param);
}

/*
 * 分享新浪微博
 */
function share_sina($apps, $odata, $data, $name) {
	//加载类
	$ofile = PLUGIN_DIR . 'share/function/sina.class.php';
	if (!is_file($ofile)) continue;
	include_once $ofile;
	$apps	= string2array($apps);
	$data	= string2array($data);
	$odata	= string2array($odata);
	$c		= new SaeTClientV2($apps['appid'] , $apps['appkey'] , $odata['oauth_openid']);
	$content= '【' . $data['title'] . '】' . $data['description'] . '，点击查看：http://' . $_SERVER['HTTP_HOST'] . getUrl($data);
	if ($data['thumb']) {
		$c->upload($content, $data['thumb']);
	} else {
		$c->update($content);
	}
}
?>