<?php

if (!defined('IN_IDEACMS')) exit();

/**
 *  发布之前执行的动作（判断用户资金是否充足）
 */
function fees_before($data) {
	$data	= string2array($data);	//转换成数组
	if (empty($data['userid'])) return false;
	$cache	= new cache_file('fees');
	$config	= $cache->get('config');	//获取配置缓存
	$member	= get_member_info($data['userid']);	//获取会员数据
	if ($member && $config[$data['catid']]['model'] && @in_array($member['modelid'], $config[$data['catid']]['model'])) return false;	//判断会员模型
	if ($member && $config[$data['catid']]['group'] && @in_array($member['groupid'], $config[$data['catid']]['group'])) return false;	//判断会员组
	$money	= $config[$data['catid']]['money'];	//扣款资金数
	if (empty($money) || $money == 0.00) return false;	//扣款资金数判断
	$pay	= App::plugin_model('fees', 'Pay_data');	//实例化应用模型
	$pdata	= $pay->getData($data['userid']);	//得到资金数据
	if ($pdata['available'] < $money) return '可用资金不足，请充值(￥' . $money . '元)';
	return false;
}

/**
 *  发布之后执行的动作（扣资金并记录消费情况）
 */
function fees_later($data) {
	$data	= string2array($data);	//转换成数组
	if (empty($data['userid'])) return false;
	$cache	= new cache_file('fees');
	$config	= $cache->get('config');	//获取配置缓存
	$member	= get_member_info($data['userid']);	//获取会员数据
	if ($member && $config[$data['catid']]['model'] && @in_array($member['modelid'], $config[$data['catid']]['model'])) return false;	//判断会员模型
	if ($member && $config[$data['catid']]['group'] && @in_array($member['groupid'], $config[$data['catid']]['group'])) return false;	//判断会员组
	$money	= $config[$data['catid']]['money'];	//扣款资金数
	if (empty($money) || $money == 0.00) return false;	//扣款资金数判断
	$pay	= App::plugin_model('fees', 'Pay_data');	//实例化应用模型
	$spend	= App::plugin_model('fees', 'Pay_spend');	//实例化应用模型
	$pay->query('UPDATE `' . $pay->prefix . 'pay_data` SET `available`=`available`-' . $money . ' WHERE `userid`=' . $data['userid']);	//可用资金扣除
	$insert = array(
		'userid'   => $data['userid'],
		'username' => $data['username'],
		'money'    => $money,
		'addtime'  => time(),
		'note'     => '投稿费用，文档ID：' . $data['id'],
	);
	$spend->insert($insert);	//记录在消费表
	return false;
}