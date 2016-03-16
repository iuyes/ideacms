<?php

header('Content-Type:text/html; charset=utf-8');
/* 加载系统核心程序 */

define('IN_IDEACMS', true);
define('APP_ROOT',   dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR);
$config = require APP_ROOT . 'config/config.ini.php';
error_reporting(E_ALL^E_NOTICE);
require APP_ROOT . 'core/App.php';
require APP_ROOT . 'core/Base.php';
require APP_ROOT . 'core/Model.php';
require APP_ROOT . 'core/Controller.php';
require APP_ROOT . 'core/lib/cache_file.class.php';
require APP_ROOT . 'core/lib/cookie.class.php';
require APP_ROOT . 'core/lib/mysql.class.php';
require MODEL_DIR . 'MemberModel.php';
require '../models/Pay_dataModel.php';
require '../models/Pay_accountModel.php';
$userid = cookie::get('member_id');
$member = new MemberModel();
$member = $member->find($userid, 'id,username');
if (empty($member)) exit('数据返回失败，登录回话已过期。');
$cache  = new cache_file();
$pay    = $cache->get('pay_config');
$config = $pay['tenpay'];
require './classes/ResponseHandler.class.php';
require './classes/function.php';
$spname = '财付通';
$partner= $config['partner'];
$key    = $config['key'];
/* 创建支付应答对象 */
$resHandler = new ResponseHandler();
$resHandler->setKey($key);

//判断签名
if($resHandler->isTenpaySign()) {
	//通知id
	$notify_id = $resHandler->getParameter("notify_id");
	//商户订单号
	$out_trade_no = $resHandler->getParameter("out_trade_no");
	//财付通订单号
	$transaction_id = $resHandler->getParameter("transaction_id");
	//金额,以分为单位
	$total_fee = $resHandler->getParameter("total_fee");
	//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
	$discount = $resHandler->getParameter("discount");
	//支付结果
	$trade_state = $resHandler->getParameter("trade_state");
	//交易模式,1即时到账
	$trade_mode = $resHandler->getParameter("trade_mode");
	
	if("1" == $trade_mode ) {
		if( "0" == $trade_state){ 
			//------------------------------
			//处理业务开始
			//------------------------------
			$account = new Pay_accountModel();
			$money   = number_format(($total_fee / 100), 2, '.', '');
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
			//------------------------------
			//处理业务完毕
			//------------------------------	
			
			echo "<br/>" . "即时到帐支付成功(" . $money . ")" . "<br/>";
			
	        echo '<meta http-equiv="refresh" content="3; url=' . url('pay/member/order', array('id'=>$data['id'])) . '">';
	
		} else {
			//当做不成功处理
			echo "<br/>" . "即时到帐支付失败" . "<br/>";
		}
	}elseif( "2" == $trade_mode  ) {
		if( "0" == $trade_state) {
		
			//------------------------------
			//处理业务开始
			//------------------------------
			
			$account = new Pay_accountModel();
			$money   = number_format(($total_fee / 100), 2, '.', '');
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
			
			//------------------------------
			//处理业务完毕
			//------------------------------	
			
			echo "<br/>" . "中介担保支付成功" . "<br/>";
		
	        echo '<meta http-equiv="refresh" content="3; url=' . url('pay/member/order', array('id'=>$data['id'])) . '">';
		} else {
			//当做不成功处理
			echo "<br/>" . "中介担保支付失败" . "<br/>";
		}
	}
	
} else {
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
}

?>