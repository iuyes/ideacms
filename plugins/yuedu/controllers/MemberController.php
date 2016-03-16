<?php

class MemberController extends Plugin {
	
    public function __construct() {
        parent::__construct();
		if (empty($this->memberinfo)) $this->redirect(url('member/login', array('back'=>urlencode($_SERVER['HTTP_REFERER']))));
		$this->view->assign('navigation', array(
		    'index'	=> array('name'=> '订单管理', 'url'=> purl('member/index')),
		));
    }
	
	/*
	 * 订单管理
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
		    $total    = $this->yuedu->count('yuedu', 'id', $where);
			$data     = $this->yuedu->page_limit($page, $pagesize)->order('addtime DESC')->where($where)->select();
			$pagelist = $pagelist->total($total)->url(purl('member/index', array('page'=>'{page}', 'time'=>$time)))->num($pagesize)->page($page)->output();
		} else {
		    $data     = $this->yuedu->limit($pagesize)->order('addtime DESC')->where($where)->select();
			$pagelist = '';
		}
	    $this->view->assign(array(
			'list'       => $data,
			'pagelist'   => $pagelist,
			'time'       => $time,
			'meta_title' => '阅读收费管理-会员中心-' . $this->site['SITE_NAME'],
	    ));
	    $this->view->display('member/yuedu_order');
	}
	
	/*
	 * 购买
	 */
	public function buyAction() {
		$id			= (int)$this->get('id');
		$title		= $this->get('title');
		$model		= get_model_data();
		$modelid	= (int)$this->get('modelid');
		if (empty($this->setting['field'][$modelid])) {
			$this->memberMsg('<font color=red>该模型(#' . $modelid . ')没有绑定价格字段，请在后台应用中绑定</font>');
		} else {
			$table	= $this->model($model[$modelid]['tablename']);
			$data	= $table->find($id);
			if (empty($data)) {
				$this->memberMsg('<font color=red>该文档(#' . $id . ')不存在，或者已经被删除</font>');
			} else {
				if (empty($data[$this->setting['field'][$modelid]]) || $data[$this->setting['field'][$modelid]] == '0.00' || $this->session->get('user_id') || $this->yuedu->check($id, $this->memberinfo['id'])) {
					$this->memberMsg('<font color=red>该文档(#' . $id . ')无法购买或者已经购买</font>');
				}
			}
		}
		if ($this->isPostForm()) {
		    if (!$this->checkCode($this->post('code'))) $this->memberMsg('验证码不正确！');
			//生成订单
			$order	= array(
				'title'		=> $title,
				'cid'		=> $id,
				'modelid'	=> $modelid,
				'price'		=> $data[$this->setting['field'][$modelid]],
				'userid'	=> $this->memberinfo['id'],
				'username'	=> $this->memberinfo['username'],
				'addtime'	=> time(),
				'status'	=> 0,
			);
			$id	= $this->yuedu->insert($order);
			if (empty($id)) $this->memberMsg('<font color=red>确认失败</font>');
			$this->redirect(purl('member/pay', array('id'=>$id)));
		} else {
			$this->view->assign(array(
				'title'	=> $title,
				'price'	=> $data[$this->setting['field'][$modelid]]
			));
			$this->view->display('yuedu_checkout');
		}
	}
	
	/*
	 * 付款
	 */
	public function payAction() {
		if (!plugin('pay'))	$this->memberMsg('请安装“在线支付”应用！');
		$id   = (int)$this->get('id');
		$data = $this->yuedu->where('id=' . $id . ' AND username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'])->select(false);
		if (empty($data))    $this->memberMsg('没有查到订单信息！');
		if ($data['status']) $this->memberMsg('该订单已经付款了！');
		$pay   = $this->model('pay_data');
		$pdata = $pay->getData($this->memberinfo['id']);
		if ($this->isPostForm()) {
		    if ($pdata['available']  - $data['price'] < 0) {
			    $this->memberMsg('余额不足，请充值！', url('pay/member/add', array('price'=>$data['price'] - $pdata['available'])));
			}
			$update = array('available' => $pdata['available'] - $data['price']);
			$result = $pay->update($update, 'userid=' . $this->memberinfo['id']);
			if (!$result) $this->memberMsg('付款失败！');
			//消费记录
			$spend  = $this->model('Pay_spend');
			$insert = array(
				'userid'   => $this->memberinfo['id'],
				'username' => $this->memberinfo['username'],
				'money'    => $data['price'],
				'addtime'  => time(),
				'note'     => '阅读收费消费，订单ID：' . $id,
			);
			$spend->insert($insert);
			$this->yuedu->update(array('status'=>1, 'paytime'=>time()), 'id=' . $id);
			$this->memberMsg('付款成功！', purl('member'), 1);
		}
		$this->view->assign(array(
			'data'       => $data,
			'pay_data'   => $pdata,
			'meta_title' => '付款-会员中心-' . $this->site['SITE_NAME'],
		));
		$this->view->display('member/yuedu_pay');
	}
	
	/*
	 * 订单信息
	 */
	public function orderAction() {
	    $id     = (int)$this->get('id');
		$data	= $this->yuedu->where('id=' . $id . ' AND username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'])->select(false);
		if (empty($data)) $this->memberMsg('没有查到订单信息！');
		$this->view->assign(array(
			'data'       => $data,
			'meta_title' => '订单信息-会员中心-' . $this->site['SITE_NAME'],
		));
		$this->view->display('member/yuedu_info');
	}
	
}