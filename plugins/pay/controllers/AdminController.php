<?php

class AdminController extends Plugin {
	
	protected $userinfo;
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
		$this->userinfo = $this->content->from('user')->where('userid=' . (int)$this->session->get('user_id'))->select(false);
		$this->assign('menu', array(
		    array('index',		'充值明细'),
			array('spend',		'消费明细'),
			array('setting',	'支付配置'),
			array('add',		'充值/扣减'),
			array('card',		'充值卡管理'),
			array('addcard',	'生成充值卡')
		));
    }
	
	public function addcardAction() {
	    if ($this->isPostForm()) {
		    $data	= $this->post('data');
			if (empty($data['money']) || empty($data['num']) || empty($data['time'])) $this->adminMsg('数据没有填写完整');
			$result	= array();
			$print	= '';
			for ($i = 0; $i < $data['num']; $i++) {
				$card	= array(
					'card_sn'	=> create_card_sn(),
					'password'	=> strtoupper(substr(md5(create_sn()), 0, 6)),
					'money'		=> $data['money'],
					'addtime'	=> time(),
					'endtime'	=> time() + $data['time'] * 24* 3600,
					'adduser'	=> $this->userinfo['username'],
					'status'	=> 0,
				);
				if ($this->pay_card->insert($card)) {
					$result[]	= $card;
					$print		.= '卡号：' . $card['card_sn'] . ' 密码：' . $card['password'] . '<br>';
				}
			}
			echo '<pre><font color=green size=3><b>共生成张' . count($result) . 
			'虚拟充值卡 <a href="javascript:copycard()" style="color:green">复制内容</a></b></font><br /><br /><div id="copycard">' . 
			$print . '</div></pre>
			<script type="text/javascript">
			function copycard() {
				var meintext="' . str_replace('<br>', '\\r\\n', $print) . '";
				if (window.clipboardData){
					window.clipboardData.setData("Text", meintext);
				} else if (window.netscape){
					try {
						netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
					} catch (e) {
						alert("被浏览器拒绝\n请在浏览器地址栏输入\'about:config\'并回车\n然后将 \'signed.applets.codebase_principal_support\'设置为\'true\'"); 
					} 
					var clip = Components.classes[\'@mozilla.org/widget/clipboard;1\'].
					createInstance(Components.interfaces.nsIClipboard);
					if (!clip) return;
					var trans = Components.classes[\'@mozilla.org/widget/transferable;1\'].
					createInstance(Components.interfaces.nsITransferable);
					if (!trans) return;
					trans.addDataFlavor(\'text/unicode\');
					var len = new Object();
					var str = Components.classes["@mozilla.org/supports-string;1"].
					createInstance(Components.interfaces.nsISupportsString);
					var copytext=meintext;
					str.data=copytext;
					trans.setTransferData("text/unicode",str,copytext.length*2);
					var clipid=Components.interfaces.nsIClipboard;
					if (!clip) return false;
					clip.setData(trans,null,clipid.kGlobalClipboard);
				}
				alert("复制成功");
			}
			</script>
			';
		} else {
			$this->display('admin_addcard');
		}
	}
	
	public function cardAction() {
	    if ($this->isPostForm() && $this->post('form') == 'search') {
		    $card_sn	= $this->post('card_sn');
		    $username	= $this->post('username');
			unset($_GET['page']);
		} elseif ($this->isPostForm() && $this->post('form') == 'delete') {
		    $ids = $this->post('dels');
			if ($ids) {
				$ids = implode(',', $ids);
				$this->pay_card->delete('id IN (' . $ids . ')');
			}
			unset($_GET['page']);
		}
	    $page		= isset($_GET['page']) ? $this->get('page') : 1;
		$where		= '1';
		$status		= isset($_POST['status']) ? $this->post('status') : $this->get('status');
		$card_sn	= $card_sn ? $card_sn : $this->get('card_sn');
		$username	= $username ? $username : $this->get('username');
	    $pagelist	= $this->instance('pagelist');
		$pagelist->loadconfig();
		$pagesize	= isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
		if ($card_sn)	$where .= ' AND card_sn="' . $card_sn . '"';
		if ($username)	$where .= ' AND username="' . $username . '"';
		if ($status != '' && $status >= 0) $where .= ' AND `status`=' . $status;
		$total		= $this->pay_card->count('pay_card', 'id', $where);
	    $url		= purl('admin/card', array('page'=>'{page}', 'username'=>$username, 'status'=>$status));
	    $data		= $this->pay_card->page_limit($page, $pagesize)->order('addtime DESC')->where($where)->select();
	    $pagelist	= $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
		$this->assign(array(
			'list'		=> $data,
			'pagelist'	=> $pagelist,
		));
	    $this->display('admin_card');
	}
	
	public function indexAction() {
	    if ($this->isPostForm() && $this->post('form') == 'search') {
		    $order_sn = $this->post('order_sn');
		    $username = $this->post('username');
			if ($order_sn && !is_numeric($order_sn)) $this->adminMsg('订单号码不正确！');
			unset($_GET['page']);
		} elseif ($this->isPostForm() && $this->post('form') == 'delete') {
		    $ids = $this->post('dels');
			if ($ids) {
				$ids = implode(',', $ids);
				$this->pay_account->delete('id IN (' . $ids . ')');
			}
			unset($_GET['page']);
		}
		$order_sn  = $order_sn ? $order_sn : $this->get('order_sn');
		$username  = $username ? $username : $this->get('username');
		$status    = isset($_POST['status']) ? $this->post('status') : $this->get('status');
	    $page      = isset($_GET['page']) ? $this->get('page') : 1;
	    $pagelist  = $this->instance('pagelist');
		$pagelist->loadconfig();
		$pagesize  = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
		$where     = '1';
		if ($order_sn) $where .= ' AND order_sn=' . $order_sn;
		if ($username) $where .= ' AND username="' . $username . '"';
		if ($status != '' && $status >= 0) $where .= ' AND `status`=' . $status;
		$total		= $this->pay_account->count('pay_account', 'id', $where);
	    $url		= purl('admin/index', array('page'=>'{page}', 'order_sn'=>$order_sn, 'username'=>$username, 'status'=>$status));
	    $data		= $this->pay_account->page_limit($page, $pagesize)->order('addtime DESC')->where($where)->select();
	    $pagelist	= $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
		$config		= $this->cache->get('pay_config');
		$config[99]	= array('name' => '充值卡');
		$this->assign(array(
			'list'       => $data,
			'pagelist'   => $pagelist,
			'pay_config' => $config,
		));
	    $this->display('admin_list');
	}
	
	public function spendAction() {
	    if ($this->isPostForm() && $this->post('form') == 'search') {
		    $username = $this->post('username');
			unset($_GET['page']);
		}
		$username  = $username ? $username : $this->get('username');
	    $page      = isset($_GET['page']) ? $this->get('page') : 1;
	    $pagelist  = $this->instance('pagelist');
		$pagelist->loadconfig();
		$pagesize  = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
		$where     = '1';
		if ($username) $where .= ' AND username="' . $username . '"';
		$total     = $this->pay_spend->count('pay_spend', 'id', $where);
	    $url       = purl('admin/spend', array('page'=>'{page}', 'username'=>$username));
	    $data      = $this->pay_spend->page_limit($page, $pagesize)->order('addtime DESC')->where($where)->select();
	    $pagelist  = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
		$this->assign(array(
			'list'       => $data,
			'pagelist'   => $pagelist,
		));
	    $this->display('admin_spend');
	}
	
	public function settingAction() {
	    if ($this->isPostForm()) {
		    $data = $this->post('data');
			$this->cache->set('pay_config', $data);
			$this->adminMsg('操作成功', purl('admin/setting'), 3, 1, 1);
		}
		$this->assign('data', $this->cache->get('pay_config'));
	    $this->display('admin_setting');
	}
	
	public function addAction() {
	    if ($this->isPostForm()) {
		    $username  = $this->post('username');
			$pay_unit  = $this->post('pay_unit');
			$money     = (float)$this->post('money');
			$adminnote = $this->post('adminnote');
			$sendemail = $this->post('sendemail');
		    if (empty($username)) $this->adminMsg('请填写会员名称！');
			if (empty($money))    $this->adminMsg('请填写金额！');
			$data = $this->pay_data->getDataName($username);
			if (empty($data))     $this->adminMsg('会员不存在！');
			$data['available'] = $pay_unit ? ($data['available'] + $money) : ($data['available'] - $money);
			if ($this->pay_data->set($data, $data['userid'])) {
			    if ($pay_unit) {
				    //充值
					$insert = array(
						'ip'        => client::get_user_ip(),
						'money'     => $money,
						'userid'    => $data['userid'],
						'status'    => 1,
						'paytype'   => 0,
						'addtime'   => time(),
						'paytime'   => time(),
						'order_sn'  => create_sn(),
						'username'  => $data['username'],
						'adminnote' => $adminnote
					);
					$this->pay_account->insert($insert);
				} else {
				    //消费
					$insert = array(
						'note'      => $adminnote,
						'money'     => $money,
						'userid'    => $data['userid'],
						'addtime'   => time(),
						'username'  => $data['username']
					);
					$this->pay_spend->insert($insert);
				}
				$msg = $pay_unit ? '充值' : '扣除';
				if ($sendemail) {
					mail::set($this->site);
					$title    = $this->site['SITE_NAME'] . '会员(' . $data['username'] . ')充值提醒！';
					$content  = "<H2 style='font-size:16px;font-weight:bold;'>尊敬的会员 " . $data['username'] . "：</H2><div style='font-size:12px;padding-top:10px;'>";
					$content .= '管理员于' . date('Y-m-d H:i:s') . '为您' . $msg . '￥' . $money . '元，当前可用金额￥' . $data['available'] . '元，冻结金额￥' . $data['freeze'] . '元。<br>' . $adminnote;
					$content .= '<HR style="MARGIN: 5px 0px"><div style="text-align:center;">此信是系统自动发出，请不要"回复"本邮件。</div> </div> ';
					$user     = $this->member->find($data['userid'], 'email');
					if ($user['email']) mail::sendmail($user['email'], $title, $content);
				}
				$this->adminMsg('账户(' . $data['username'] . ')' . $msg . '￥' . $money . '元<br>账户可用金额￥' . $data['available'] . '元，冻结金额￥' . $data['freeze'] . '元。', '', 3, 1, 1);
			} else {
			    $this->adminMsg('充值失败！');
			}
		}
	    $this->display('admin_add');
	}
	
	public function checkuserAction() {
	    $username = $this->get('username');
		if (empty($username)) exit('请填写会员名称！');
		$data = $this->pay_data->getDataName($username);
		if (empty($data)) exit('会员不存在！');
		echo '可用金额：' . $data['available'];
		echo ' 冻结金额：' . $data['freeze'];
	}
	
}