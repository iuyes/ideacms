<?php

//---------------------------------------------------------
//财付通即时到帐支付后台回调示例，商户按照此文档进行开发即可
//---------------------------------------------------------

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
$spname = '财付通';
$partner= $config['partner'];
$key    = $config['key'];
require ("classes/ResponseHandler.class.php");
require ("classes/RequestHandler.class.php");
require ("classes/client/ClientResponseHandler.class.php");
require ("classes/client/TenpayHttpClient.class.php");
require ("./classes/function.php");

		log_result("进入后台回调页面");


	/* 创建支付应答对象 */
		$resHandler = new ResponseHandler();
		$resHandler->setKey($key);

	//判断签名
		if($resHandler->isTenpaySign()) {
	
	//通知id
		$notify_id = $resHandler->getParameter("notify_id");
	
	//通过通知ID查询，确保通知来至财付通
	//创建查询请求
		$queryReq = new RequestHandler();
		$queryReq->init();
		$queryReq->setKey($key);
		$queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
		$queryReq->setParameter("partner", $partner);
		$queryReq->setParameter("notify_id", $notify_id);
		
	//通信对象
		$httpClient = new TenpayHttpClient();
		$httpClient->setTimeOut(5);
	//设置请求内容
		$httpClient->setReqContent($queryReq->getRequestURL());
	
	//后台调用
		if($httpClient->call()) {
	//设置结果参数
			$queryRes = new ClientResponseHandler();
			$queryRes->setContent($httpClient->getResContent());
			$queryRes->setKey($key);
		
		if($resHandler->getParameter("trade_mode") == "1"){
	//判断签名及结果（即时到帐）
	//只有签名正确,retcode为0，trade_state为0才是支付成功
		if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {
				log_result("即时到帐验签ID成功");
	//取结果参数做业务处理
				$out_trade_no = $resHandler->getParameter("out_trade_no");
	//财付通订单号
				$transaction_id = $resHandler->getParameter("transaction_id");
	//金额,以分为单位
				$total_fee = $resHandler->getParameter("total_fee");
	//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
				$discount = $resHandler->getParameter("discount");
				
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
				log_result("即时到帐后台回调成功");
				echo "success";
				
			} else {
	//错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
	//echo "验证签名失败 或 业务错误信息:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes->                         getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
			   log_result("即时到帐后台回调失败");
			   echo "fail";
			}
		}elseif ($resHandler->getParameter("trade_mode") == "2")
		
	    {
    //判断签名及结果（中介担保）
	//只有签名正确,retcode为0，trade_state为0才是支付成功
		if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" ) 
		{
				log_result("中介担保验签ID成功");
	//取结果参数做业务处理
				$out_trade_no = $resHandler->getParameter("out_trade_no");
	//财付通订单号
				$transaction_id = $resHandler->getParameter("transaction_id");
	//金额,以分为单位
				$total_fee = $resHandler->getParameter("total_fee");
	//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
				$discount = $resHandler->getParameter("discount");
				
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
	
			log_result("中介担保后台回调，trade_state="+$resHandler->getParameter("trade_state"));
				switch ($resHandler->getParameter("trade_state")) {
						case "0":	//付款成功
						
							break;
						case "1":	//交易创建
						
							break;
						case "2":	//收获地址填写完毕
						
							break;
						case "4":	//卖家发货成功
						
							break;
						case "5":	//买家收货确认，交易成功
						
							break;
						case "6":	//交易关闭，未完成超时关闭
						
							break;
						case "7":	//修改交易价格成功
						
							break;
						case "8":	//买家发起退款
						
							break;
						case "9":	//退款成功
						
							break;
						case "10":	//退款关闭			
							
							break;
						default:
							//nothing to do
							break;
					}
					
				
				//------------------------------
				//处理业务完毕
				//------------------------------
				echo "success";
			} else
			
		     {
	//错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
	//echo "验证签名失败 或 业务错误信息:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes->             										       getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
			   log_result("中介担保后台回调失败");
				echo "fail";
			 }
		  }
		
		
		
	//获取查询的debug信息,建议把请求、应答内容、debug信息，通信返回码写入日志，方便定位问题
	/*
		echo "<br>------------------------------------------------------<br>";
		echo "http res:" . $httpClient->getResponseCode() . "," . $httpClient->getErrInfo() . "<br>";
		echo "query req:" . htmlentities($queryReq->getRequestURL(), ENT_NOQUOTES, "GB2312") . "<br><br>";
		echo "query res:" . htmlentities($queryRes->getContent(), ENT_NOQUOTES, "GB2312") . "<br><br>";
		echo "query reqdebug:" . $queryReq->getDebugInfo() . "<br><br>" ;
		echo "query resdebug:" . $queryRes->getDebugInfo() . "<br><br>";
		*/
	}else
	 {
	//通信失败
		echo "fail";
	//后台调用通信失败,写日志，方便定位问题
	echo "<br>call err:" . $httpClient->getResponseCode() ."," . $httpClient->getErrInfo() . "<br>";
	 } 
	
	
   } else 
     {
    echo "<br/>" . "认证签名失败" . "<br/>";
    echo $resHandler->getDebugInfo() . "<br>";
}

 

?>