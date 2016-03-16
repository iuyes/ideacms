<?php

/**
 * 文件名称: Common.php for v1.6 +
 * 应用控制器公共类
 */

class Plugin extends Common {
    
    protected $plugin;   //应用模型
	protected $data;     //应用数据
	protected $viewpath; //视图目录
	protected $pay_data;
	protected $pay_card;
	protected $pay_spend;
	protected $pay_account;
    
    public function __construct() {
        parent::__construct();
		$this->plugin   = $this->model('plugin');
        $this->data     = $this->plugin->where('dir=?', $this->namespace)->select(false);
		if (empty($this->data))     $this->adminMsg('应用尚未安装', url('admin/plugin'));
		if ($this->data['disable']) $this->adminMsg('应用尚未开启', url('admin/plugin'));
		$this->viewpath	= SITE_PATH . $this->site['PLUGIN_DIR'] . '/' . $this->data['dir'] . '/views/';
		$this->assign(array(
		    'viewpath'  => $this->viewpath,
            'pluginid'  => $this->data['pluginid'],	
		));
		date_default_timezone_set(SYS_TIME_ZONE);
		$this->cache		= new cache_file($this->data['dir']);
		$this->pay_data		= $this->model('pay_data');
		$this->pay_card		= $this->model('pay_card');
		$this->pay_spend	= $this->model('pay_spend');
		$this->pay_account	= $this->model('pay_account');
    }
	
	public function get_alipay($data) {
		$pay_config = $this->cache->get('pay_config');
		if (!isset($pay_config['alipay']) || $pay_config['alipay']['use'] == 0) $this->adminMsg('系统尚未开启支付宝接口！');
		$config     = $pay_config['alipay'];
		$aliapy_config['partner']      = $config['partner'];
		$aliapy_config['key']          = $config['key'];
		$aliapy_config['seller_email'] = $config['username'];
		$aliapy_config['return_url']   = SITE_URL . $this->site['PLUGIN_DIR'] . '/' . $this->data['dir'] . '/alipay/return_url.php';
		$aliapy_config['notify_url']   = SITE_URL . $this->site['PLUGIN_DIR'] . '/' . $this->data['dir'] . '/alipay/notify_url.php';
		$aliapy_config['sign_type']    = 'MD5';
		$aliapy_config['input_charset']= 'utf-8';
		$aliapy_config['transport']    = 'http';
		require PLUGIN_DIR . $this->data['dir'] . '/alipay/alipay_submit.class.php';
		require PLUGIN_DIR . $this->data['dir'] . '/alipay/alipay_service.class.php';
		/**************************请求参数**************************/
		$out_trade_no = $data['order_sn'];
		$subject      = $this->site['SITE_NAME'] . '会员充值';
		$body         = '会员(' . $data['username'] . ')充值，订单编号：' . $data['order_sn'];
		$total_fee    = $data['money'];
		//构造要请求的参数数组
		$parameter = array(
				'service'			=> 'create_direct_pay_by_user',
				'payment_type'		=> '1',
				
				'partner'			=> trim($aliapy_config['partner']),
				'_input_charset'	=> trim(strtolower($aliapy_config['input_charset'])),
				'seller_email'		=> trim($aliapy_config['seller_email']),
				'return_url'		=> trim($aliapy_config['return_url']),
				'notify_url'		=> trim($aliapy_config['notify_url']),
				
				'out_trade_no'		=> $out_trade_no,
				'subject'			=> $subject,
				'body'				=> $body,
				'total_fee'			=> $total_fee,
		);
		//构造即时到帐接口
		$alipayService = new AlipayService($aliapy_config);
		$html_text = $alipayService->create_direct_pay_by_user($parameter);
		$this->adminMsg('正在为您跳转到支付宝页面...<p style="padding-top:5px;">' . $html_text . '</p>', 0, 0);
	}
    
	public function get_tenpay($data) {
		$pay_config = $this->cache->get('pay_config');
		if (!isset($pay_config['tenpay']) || $pay_config['tenpay']['use'] == 0) $this->adminMsg('系统尚未开启财付通接口！');
		$config     = $pay_config['tenpay'];
		require PLUGIN_DIR . $this->data['dir'] . '/tenpay/classes/RequestHandler.class.php';
		/* 创建支付请求对象 */
		$reqHandler = new RequestHandler();
		$reqHandler->init();
		$reqHandler->setKey($config['key']);
		$reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");
		$return_url = SITE_URL . $this->site['PLUGIN_DIR'] . '/' . $this->data['dir'] . '/tenpay/return_url.php';
		$notify_url = SITE_URL . $this->site['PLUGIN_DIR'] . '/' . $this->data['dir'] . '/tenpay/notify_url.php';
		//----------------------------------------
		//设置支付参数 
		//----------------------------------------
		$reqHandler->setParameter("partner", $config['partner']);
		$reqHandler->setParameter("out_trade_no", $data['order_sn']);
		$reqHandler->setParameter("total_fee", $data['money'] * 100);  //总金额,单位分，所有扩大100倍
		$reqHandler->setParameter("return_url",  $return_url);
		$reqHandler->setParameter("notify_url", $notify_url);
		$reqHandler->setParameter("body", '会员(' . $data['username'] . ')充值，订单编号：' . $data['order_sn']);
		$reqHandler->setParameter("bank_type", "DEFAULT");  	   //银行类型，默认为财付通
		//用户ip
		$reqHandler->setParameter("spbill_create_ip", $data['ip']);//客户端IP
		$reqHandler->setParameter("fee_type", "1");                //币种
		$reqHandler->setParameter("subject", $this->site['SITE_NAME'] . '会员充值');          //商品名称，（中介交易时必填）
		//系统可选参数
		$reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
		$reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
		$reqHandler->setParameter("input_charset", "UTF-8");   	  //字符集
		$reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号
		//业务可选参数
		$reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
		$reqHandler->setParameter("product_fee", "");        	  //商品费用
		$reqHandler->setParameter("transport_fee", "0");      	  //物流费用
		$reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
		$reqHandler->setParameter("time_expire", "");             //订单失效时间
		$reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
		$reqHandler->setParameter("goods_tag", "");               //商品标记
		$reqHandler->setParameter("trade_mode", "1");             //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
		$reqHandler->setParameter("transport_desc", "");          //物流说明
		$reqHandler->setParameter("trans_type", "1");             //交易类型
		$reqHandler->setParameter("agentid", "");                 //平台ID
		$reqHandler->setParameter("agent_type", "");              //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
		$reqHandler->setParameter("seller_id", "");    
		//请求的URL
		$reqUrl = $reqHandler->getRequestURL();
		$this->adminMsg('正在为您跳转到财付通页面...', $reqUrl, 0);
	}
	
}

/**
 * 生成流水号
 */
function create_sn() {
	mt_srand((double)microtime() * 1000000 );
	return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * 生成充值卡号
 */
function create_card_sn() {
	mt_srand((double)microtime() * 1000000 );
	return date('Ys') . strtoupper(substr(md5(mt_rand(100000, 999999)), 1, 8)) . mt_rand(100000, 999999);
}
