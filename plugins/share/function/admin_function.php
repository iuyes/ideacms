<?php

if (!defined('IN_IDEACMS')) exit();

include_once PLUGIN_DIR . 'share/function/function.php';

/**
 *  发布之后执行的动作
 */
function share_later($data) {
	$data	= string2array($data);	//转换成数组
	$userid	= (int)cookie::get('member_id');	//后台发布以前端用户为基准
	if (empty($userid)) return false;
	$member	= get_member_info($userid);
	if (empty($member)) return false;
	$cache	= new cache_file('share');
	$config	= $cache->get('config');	//配置信息
	if (empty($config['member'])) return false;	//关闭状态直接跳过
	$oauth	= Controller::model('oauth');	//实例化一键登录对象
	$oauth	= $oauth->where('username=?', $member['username'])->select();
	if (empty($oauth)) return false;	//没有一键登录信息则跳过
	$cache	= new cache_file();
	$member	= $cache->get('member');
	foreach ($oauth as $t) {
		//加载类库
		if (!function_exists('share_' . $t['oauth_name'])) continue;
		eval("share_" . $t['oauth_name'] . "('" . array2string($member['oauth'][$t['oauth_name']]) . "', '" . $t['oauth_data'] . "', '" . array2string($data) . "', '" . safe_replace($config['name']) . "');");
		
	}
	return false;
}
