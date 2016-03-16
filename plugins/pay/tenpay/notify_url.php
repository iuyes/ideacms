<?php

//---------------------------------------------------------
//�Ƹ�ͨ��ʱ����֧����̨�ص�ʾ���̻����մ��ĵ����п�������
//---------------------------------------------------------

header('Content-Type:text/html; charset=utf-8');
/* ����ϵͳ���ĳ��� */

define('IN_IDEACMS', true);
define('APP_ROOT',   dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR);
$config = require APP_ROOT . 'config/config.ini.php';
error_reporting(E_ALL^E_NOTICE);
define('FCPATH', APP_ROOT);
define('BASEPATH', FCPATH . 'lianpuphp/system/'); // CI框架目录
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
if (empty($member)) exit('��ݷ���ʧ�ܣ���¼�ػ��ѹ��ڡ�');
$cache  = new cache_file();
$pay    = $cache->get('pay_config');
$config = $pay['tenpay'];
$spname = '�Ƹ�ͨ';
$partner= $config['partner'];
$key    = $config['key'];
require ("classes/ResponseHandler.class.php");
require ("classes/RequestHandler.class.php");
require ("classes/client/ClientResponseHandler.class.php");
require ("classes/client/TenpayHttpClient.class.php");
require ("./classes/function.php");

		log_result("�����̨�ص�ҳ��");


	/* ����֧��Ӧ����� */
		$resHandler = new ResponseHandler();
		$resHandler->setKey($key);

	//�ж�ǩ��
		if($resHandler->isTenpaySign()) {
	
	//֪ͨid
		$notify_id = $resHandler->getParameter("notify_id");
	
	//ͨ��֪ͨID��ѯ��ȷ��֪ͨ�����Ƹ�ͨ
	//������ѯ����
		$queryReq = new RequestHandler();
		$queryReq->init();
		$queryReq->setKey($key);
		$queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
		$queryReq->setParameter("partner", $partner);
		$queryReq->setParameter("notify_id", $notify_id);
		
	//ͨ�Ŷ���
		$httpClient = new TenpayHttpClient();
		$httpClient->setTimeOut(5);
	//������������
		$httpClient->setReqContent($queryReq->getRequestURL());
	
	//��̨����
		if($httpClient->call()) {
	//���ý�����
			$queryRes = new ClientResponseHandler();
			$queryRes->setContent($httpClient->getResContent());
			$queryRes->setKey($key);
		
		if($resHandler->getParameter("trade_mode") == "1"){
	//�ж�ǩ����ʱ���ʣ�
	//ֻ��ǩ����ȷ,retcodeΪ0��trade_stateΪ0����֧���ɹ�
		if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {
				log_result("��ʱ������ǩID�ɹ�");
	//ȡ��������ҵ����
				$out_trade_no = $resHandler->getParameter("out_trade_no");
	//�Ƹ�ͨ������
				$transaction_id = $resHandler->getParameter("transaction_id");
	//���,�Է�Ϊ��λ
				$total_fee = $resHandler->getParameter("total_fee");
	//�����ʹ���ۿ�ȯ��discount��ֵ��total_fee+discount=ԭ�����total_fee
				$discount = $resHandler->getParameter("discount");
				
				//------------------------------
				//����ҵ��ʼ
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
				//����ҵ�����
				//------------------------------
				log_result("��ʱ���ʺ�̨�ص��ɹ�");
				echo "success";
				
			} else {
	//����ʱ�����ؽ�����û��ǩ��д��־trade_state��retcode��retmsg��ʧ�����顣
	//echo "��֤ǩ��ʧ�� �� ҵ�������Ϣ:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes->                         getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
			   log_result("��ʱ���ʺ�̨�ص�ʧ��");
			   echo "fail";
			}
		}elseif ($resHandler->getParameter("trade_mode") == "2")
		
	    {
    //�ж�ǩ�����н鵣����
	//ֻ��ǩ����ȷ,retcodeΪ0��trade_stateΪ0����֧���ɹ�
		if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" ) 
		{
				log_result("�н鵣����ǩID�ɹ�");
	//ȡ��������ҵ����
				$out_trade_no = $resHandler->getParameter("out_trade_no");
	//�Ƹ�ͨ������
				$transaction_id = $resHandler->getParameter("transaction_id");
	//���,�Է�Ϊ��λ
				$total_fee = $resHandler->getParameter("total_fee");
	//�����ʹ���ۿ�ȯ��discount��ֵ��total_fee+discount=ԭ�����total_fee
				$discount = $resHandler->getParameter("discount");
				
				//------------------------------
				//����ҵ��ʼ
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
	
			log_result("�н鵣����̨�ص���trade_state="+$resHandler->getParameter("trade_state"));
				switch ($resHandler->getParameter("trade_state")) {
						case "0":	//����ɹ�
						
							break;
						case "1":	//���״���
						
							break;
						case "2":	//�ջ��ַ��д���
						
							break;
						case "4":	//���ҷ����ɹ�
						
							break;
						case "5":	//����ջ�ȷ�ϣ����׳ɹ�
						
							break;
						case "6":	//���׹رգ�δ��ɳ�ʱ�ر�
						
							break;
						case "7":	//�޸Ľ��׼۸�ɹ�
						
							break;
						case "8":	//��ҷ����˿�
						
							break;
						case "9":	//�˿�ɹ�
						
							break;
						case "10":	//�˿�ر�			
							
							break;
						default:
							//nothing to do
							break;
					}
					
				
				//------------------------------
				//����ҵ�����
				//------------------------------
				echo "success";
			} else
			
		     {
	//����ʱ�����ؽ�����û��ǩ��д��־trade_state��retcode��retmsg��ʧ�����顣
	//echo "��֤ǩ��ʧ�� �� ҵ�������Ϣ:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes->             										       getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
			   log_result("�н鵣����̨�ص�ʧ��");
				echo "fail";
			 }
		  }
		
		
		
	//��ȡ��ѯ��debug��Ϣ,���������Ӧ�����ݡ�debug��Ϣ��ͨ�ŷ�����д����־�����㶨λ����
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
	//ͨ��ʧ��
		echo "fail";
	//��̨����ͨ��ʧ��,д��־�����㶨λ����
	echo "<br>call err:" . $httpClient->getResponseCode() ."," . $httpClient->getErrInfo() . "<br>";
	 } 
	
	
   } else 
     {
    echo "<br/>" . "��֤ǩ��ʧ��" . "<br/>";
    echo $resHandler->getDebugInfo() . "<br>";
}

 

?>