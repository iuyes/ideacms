<?php

class AdminController extends Plugin {
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
		$menu = array(
		    array('index',		'订单列表'),
			array('setting',	'核心配置'),
			array('user',		'使用方法'),
		);
		$this->assign('menu', $menu);
    }
	
	/*
	 * 使用方法
	 */
	public function userAction() {
		$this->display('admin_user');
	}
	
	/*
	 * 订单管理
	 */
	public function indexAction() {
	    if ($this->isPostForm() && $this->post('form') == 'search') {
		    $order_sn = $this->post('order_sn');
		    $username = $this->post('username');
		    $status   = $this->post('status');
			if ($order_sn && !is_numeric($order_sn)) $this->adminMsg('订单号码不正确！');
		} elseif ($this->isPostForm() && $this->post('form') == 'delete') {
		    $ids = $this->post('dels');
			if ($ids) {
				$ids = implode(',', $ids);
				$this->yuedu->delete('id IN (' . $ids . ')');
			}
		}
		$order_sn  = $order_sn      ? $order_sn : $this->get('order_sn');
		$username  = $username      ? $username : $this->get('username');
		$status    = isset($status) ? $status   : $this->get('status');
		$status    = $status != ''  ? $status   : -1;
	    $page      = isset($_GET['page']) ? $this->get('page') : 1;
	    $pagelist  = $this->instance('pagelist');
		$pagelist->loadconfig();
		$pagesize  = 8;
		$where     = '1';
		if ($order_sn)    $where .= ' AND order_sn=' . $order_sn;
		if ($username)    $where .= ' AND username="' . $username . '"';
		if ($status >= 0) $where .= ' AND status=' . $status;
		$total     = $this->yuedu->count('yuedu', 'id', $where);
	    $url       = purl('admin/index', array('page'=>'{page}', 'order_sn'=>$order_sn, 'username'=>$username, 'status'=>$status));
	    $data      = $this->yuedu->page_limit($page, $pagesize)->order('addtime DESC')->where($where)->select();
	    $pagelist  = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
		$this->assign(array(
			'list'     => $data,
			'pagelist' => $pagelist,
			'pay'      => $this->setting['pay'],
			'status'   => $status,
		));
	    $this->display('admin_list');
	}
	
	/*
	 * 查看订单详情
	 */
	public function showAction() {
	    $id   = (int)$this->get('id');
		$data = $this->yuedu->find($id);
		if (empty($data)) $this->adminMsg('订单不存在！');
		$this->assign(array(
		    'data' => $data,
		));
	    $this->display('admin_show');
	}
	
	/*
	 * 核心配置
	 */
	public function settingAction() {
	    if ($this->isPostForm()) {
		    $data = $this->post('data');
			$this->cache->set('config', $data);
			$this->adminMsg('操作成功', purl('admin/setting'), 3, 1, 1);
		}
		$this->assign(array(
		    'data' => $this->setting,
			'mods' => get_model_data(),
		));
	    $this->display('admin_setting');
	}
}