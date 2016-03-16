<?php

class MemberController extends Plugin {
	
    public function __construct() {
        parent::__construct();
		if (empty($this->memberinfo)) $this->redirect(url('member/login', array('back'=>urlencode($_SERVER['HTTP_REFERER']))));
		$this->view->assign('navigation', array(
		    'add'   => array('name'=> '在线充值', 	'url'=> url('pay/member/add')),
		    'card'	=> array('name'=> '充值卡充值', 'url'=> url('pay/member/card')),
		    'index' => array('name'=> '充值记录',	'url'=> url('pay/member/index')),
		    'spend' => array('name'=> '消费记录',	'url'=> url('pay/member/spend')),
		));
    }
	
	/*
	 * 充值记录
	 */
	public function indexAction() {
	    $page     = isset($_GET['page']) ? $this->get('page') : 1;
		$time     = (int)$this->get('time');
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
		$pagesize = 8;
		$where    = 'username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'];
		if ($time) {
		    if ($time == 1) {
			    $where .= ' AND addtime>' . strtotime('-7 days');
			} elseif ($time == 2) {
			    $where .= ' AND addtime>' . strtotime('-30 days');
			} elseif ($time == 3) {
			    $where .= ' AND addtime>' . strtotime('-180 days');
			} elseif ($time == 4) {
			    $where .= ' AND addtime>' . strtotime('-360 days');
			}
		    $total    = $this->pay_account->count('pay_account', 'id', $where);
			$data     = $this->pay_account->page_limit($page, $pagesize)->order('addtime DESC')->where($where)->select();
			$pagelist = $pagelist->total($total)->url(purl('member/index', array('page'=>'{page}', 'time'=>$time)))->num($pagesize)->page($page)->output();
		} else {
		    $data     = $this->pay_account->limit($pagesize)->order('addtime DESC')->where($where)->select();
			$pagelist = '';
		}
		$config		= $this->cache->get('pay_config');
		$config[99]	= array('name' => '充值卡');
	    $this->view->assign(array(
			'list'       => $data,
			'time'       => $time,
			'pagelist'   => $pagelist,
			'pay_config' => $config,
			'meta_title' => '充值记录-会员中心-' . $this->site['SITE_NAME']
	    ));
	    $this->view->display('member/pay_list');
	}
	
	/*
	 * 消费记录
	 */
	public function spendAction() {
	    $page     = isset($_GET['page']) ? $this->get('page') : 1;
		$time     = (int)$this->get('time');
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
		$pagesize = 8;
		$where    = 'username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'];
		if ($time) {
		    if ($time == 1) {
			    $where .= ' AND addtime>' . strtotime('-7 days');
			} elseif ($time == 2) {
			    $where .= ' AND addtime>' . strtotime('-30 days');
			} elseif ($time == 3) {
			    $where .= ' AND addtime>' . strtotime('-180 days');
			} elseif ($time == 4) {
			    $where .= ' AND addtime>' . strtotime('-360 days');
			}
		    $total    = $this->pay_spend->count('pay_spend', 'id', $where);
			$data     = $this->pay_spend->page_limit($page, $pagesize)->order('addtime DESC')->where($where)->select();
			$pagelist = $pagelist->total($total)->url(purl('member/', array('page'=>'{page}', 'time'=>$time)))->num($pagesize)->page($page)->output();
		} else {
		    $data     = $this->pay_spend->limit($pagesize)->order('addtime DESC')->where($where)->select();
			$pagelist = '';
		}
	    $this->view->assign(array(
			'list'       => $data,
			'time'       => $time,
			'pagelist'   => $pagelist,
			'pay_config' => $this->cache->get('pay_config'),
			'meta_title' => '消费记录-会员中心-' . $this->site['SITE_NAME']
	    ));
	    $this->view->display('member/pay_spend');
	}
	
	/*
	 * 充值卡充值
	 */
	public function cardAction() {
		if ($this->isPostForm()) {
		    $card	= $this->post('data');
			if (!$this->checkCode($this->post('code'))) $this->memberMsg('验证码不正确！');
			if (empty($card['card']) || empty($card['password'])) $this->memberMsg('请填写完整的充值信息');
			$data	= $this->pay_card->where('card_sn=?', trim($card['card']))->where('password=?', trim($card['password']))->select(false);
			if (empty($data)) {
				$this->memberMsg('充值卡有误或者密码不正确');
			} elseif ($data['status']) {
				$this->memberMsg('该卡已经被使用');
			} elseif ($data['endtime'] < time()) {
				$this->memberMsg('该卡已经过期了');
			}
			$payinfo = $this->pay_data->find($this->memberinfo['id']);
			if ($payinfo && $this->pay_data->set(array('available'=>$payinfo['available'] + $data['money']), $this->memberinfo['id'])) {
			    //充值记录
				$insert = array(
					'ip'        => client::get_user_ip(),
					'money'     => $data['money'],
					'paytype'   => 99,
					'order_sn'  => create_sn(),
					'userid'    => $this->memberinfo['id'],
					'username'  => $this->memberinfo['username'],
					'addtime'   => time(),
					'paytime'   => time(),
					'status'    => 1,
					'adminnote' => '充值卡号：' . $card['card']
				);
				$this->pay_account->insert($insert);
				//更新充值卡状态
				$update	= array(
					'status'	=> 1,
					'username'	=> $this->memberinfo['username'],
					'usertime'	=> time()
				);
				$this->pay_card->update($update, 'id=' . $data['id']);
				$this->memberMsg('充值成功：￥' . $data['money'] . '元', '', 3, 1);
			} else {
			    $this->memberMsg('充值失败！');
			}
		}
		$this->view->assign(array(
			'pay_data'   => $this->pay_data->getData($this->memberinfo['id']),
			'meta_title' => '充值卡充值-会员中心-' . $this->site['SITE_NAME'],
	    ));
	    $this->view->display('member/pay_card');
	}
	
	/*
	 * 会员充值
	 */
	public function addAction() {
	    $config = $this->cache->get('pay_config');
		if ($this->isPostForm()) {
		    $post = $this->post('data');
			if (!$this->checkCode($this->post('code'))){
                $this->memberMsg('验证码不正确！');
            }
			if (empty($post['money'])){
                $this->memberMsg('请填写充值金额！');
            }
			if (empty($post['paytype'])){
                $this->memberMsg('请选择充值方式！');
            }
			if ($post['money'] <= 0){
                $this->memberMsg('请填写有效金额！');
            }
			$data = array(
				'ip'        => client::get_user_ip(),
			    'money'     => $post['money'],
				'status'    => 0,
				'userid'    => $this->memberinfo['id'],
				'paytype'   => $post['paytype'],
				'addtime'   => time(),
				'order_sn'  => create_sn(),
				'username'  => $this->memberinfo['username']
			);
			$id = $this->pay_account->insert($data);
			if ($id) $this->redirect(purl('member/recharge', array('id'=>$id)));
			$this->memberMsg('支付失败');
		}
		$this->view->assign(array(
			'price'      => $this->get('price'),
			'pay_data'   => $this->pay_data->getData($this->memberinfo['id']),
			'pay_config' => $config,
			'meta_title' => '在线充值-会员中心-' . $this->site['SITE_NAME']
	    ));
	    $this->view->display('member/pay_add');
	}
	
	/*
	 * 确认支付
	 */
	public function rechargeAction() {
	    $id   = (int)$this->get('id');
		$data = $this->pay_account->find($id);
		if (empty($data)) $this->memberMsg('支付订单不存在！');
		if ($data['userid'] != $this->memberinfo['id'] || $data['username'] != $this->memberinfo['username']) $this->memberMsg('支付身份不正确！');
		if ($this->isPostForm()) {
		    $this->redirect(purl('member/charge', array('id'=>$id)));exit;
		}
		$this->view->assign(array(
			'data'       => $data,
			'meta_title' => '在线充值-会员中心-' . $this->site['SITE_NAME'],
			'pay_config' => $this->cache->get('pay_config')
	    ));
		$this->view->display('member/pay_charge');
	}
	
	/*
	 * 支付
	 */
	public function chargeAction() {
	    $id   = (int)$this->get('id');
		$data = $this->pay_account->find($id);
		if (empty($data))    $this->memberMsg('支付订单不存在！');
		if ($data['status']) $this->memberMsg('支付订单已经支付过！');
		if ($data['userid'] != $this->memberinfo['id'] || $data['username'] != $this->memberinfo['username']) $this->memberMsg('支付身份不正确！');
		if ($data['paytype'] == 'alipay') {
		    $this->get_alipay($data);
		} elseif ($data['paytype'] == 'tenpay') {
		    $this->get_tenpay($data);
		}
	}
	
	/*
	 * 订单
	 */
	public function orderAction() {
	    $id   = (int)$this->get('id');
		$data = $this->pay_account->find($id);
		if (empty($data)) $this->memberMsg('支付订单不存在！');
		if ($data['userid'] != $this->memberinfo['id'] || $data['username'] != $this->memberinfo['username']) $this->memberMsg('支付身份不正确！');
		if ($this->isPostForm()) {
		    $this->redirect(purl('member/charge', array('id'=>$id)));exit;
		}
		$this->view->assign(array(
			'data'       => $data,
			'meta_title' => '在线充值-会员中心-' . $this->site['SITE_NAME'],
			'pay_config' => $this->cache->get('pay_config')
	    ));
		$this->view->display('member/pay_order');
	}
	
}