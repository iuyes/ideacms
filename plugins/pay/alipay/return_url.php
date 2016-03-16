<?php
/* * 
 * 功能：支付宝页面跳转同步通知页面
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
 
 * TRADE_FINISHED(表示交易已经成功结束，并不能再对该交易做后续操作);
 * TRADE_SUCCESS(表示交易已经成功结束，可以对该交易做后续操作，如：分润、退款等);
 */
 
header('Content-Type:text/html; charset=utf-8');
/* 加载系统核心程序 */

define('IN_IDEACMS', true);
define('APP_ROOT',   dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR);
define('FCPATH', APP_ROOT);
define('BASEPATH', FCPATH . 'lianpuphp/system/'); // CI框架目录
$config = require APP_ROOT . 'config/config.ini.php';
error_reporting(E_ALL^E_NOTICE);
require BASEPATH . 'libraries/drivers/App.php';
require BASEPATH . 'libraries/drivers/Base.php';
require BASEPATH . 'libraries/drivers/Model.php';
require BASEPATH . 'libraries/drivers/Controller.php';
require BASEPATH . 'libraries/cache_file.class.php';
require BASEPATH . 'libraries/cookie.class.php';
require BASEPATH . 'libraries/mysql.class.php';
require MODEL_DIR . 'MemberModel.php';
require '../models/Pay_dataModel.php';
require '../models/Pay_accountModel.php';
$userid = cookie::get('member_id');
$member = new MemberModel();
$member = $member->find($userid, 'id,username');
if (empty($member)) exit('数据返回失败，登录回话已过期。');
$cache  = new cache_file();
$pay    = $cache->get('pay_config');
$config = $pay['alipay'];
//合作身份者id，以2088开头的16位纯数字
$aliapy_config['partner']      = $config['partner'];
//安全检验码，以数字和字母组成的32位字符
$aliapy_config['key']          = $config['key'];
//签约支付宝账号或卖家支付宝帐户
$aliapy_config['seller_email'] = $config['username'];
//页面跳转同步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
//return_url的域名不能写成http://localhost/create_direct_pay_by_user_php_utf8/return_url.php ，否则会导致return_url执行无效
echo $aliapy_config['return_url']   = Controller::get_server_name() . Controller::get_base_url()  . 'return_url.php';
//服务器异步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
$aliapy_config['notify_url']   = Controller::get_server_name() . Controller::get_base_url()  . 'notify_url.php';
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
//签名方式 不需修改
$aliapy_config['sign_type']    = 'MD5';
//字符编码格式 目前支持 gbk 或 utf-8
$aliapy_config['input_charset']= 'utf-8';
//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$aliapy_config['transport']    = 'http';
require_once("alipay_notify.class.php");

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($aliapy_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代码
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
    $out_trade_no	= $_GET['out_trade_no'];	//获取订单号
    $trade_no		= $_GET['trade_no'];		//获取支付宝交易号
    $total_fee		= $_GET['total_fee'];		//获取总价格

    if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
		$account = new Pay_accountModel();
		$money   = number_format($total_fee, 2, '.', '');
		$data    = $account->where('status=0 && money= ' . $money . ' AND order_sn=' . $out_trade_no . ' AND userid=' . $member['id'] . ' AND username=?', $member['username'])->select(false);
		if ($data) {
			$pay = new Pay_dataModel();
			$set = array(
				'available' => 'available+' . $money,
				'freeze'    => $data['freeze'],
			);
			$id  = $pay->set($set, $member['id']);
			if ($id) $account->update(array('status'=>1, 'paytime'=>time()), 'id=' . $data['id']);
		}
    }
    else {
      echo "trade_status=".$_GET['trade_status'];
    }
		
	echo "验证成功<br />";
	echo "支付宝交易号=".$trade_no;
	
	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	if (isset($data['id'])) echo '<meta http-equiv="refresh" content="0; url=' . url('pay/member/order', array('id'=>$data['id'])) . '">';
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    //如要调试，请看alipay_notify.php页面的verifyReturn函数，比对sign和mysign的值是否相等，或者检查$responseTxt有没有返回true
    echo "验证失败";
}
?>
