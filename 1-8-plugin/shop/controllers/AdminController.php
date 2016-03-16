<?php

class AdminController extends Plugin {
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
		$menu = array(
		    array('index',    '订单列表'),
			array('shipping', '物流管理'),
			array('setting',  '核心配置'),
		);
		$this->assign('menu', $menu);
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
		$total     = $this->order->count('shop_order', 'id', $where);
	    $url       = purl('admin/index', array('page'=>'{page}', 'order_sn'=>$order_sn, 'username'=>$username, 'status'=>$status));
	    $data      = $this->order->page_limit($page, $pagesize)->order('addtime DESC')->where($where)->select();
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
	 * 订单操作
	 */
	public function editAction() {
	    $id   = (int)$this->get('id');
		$data = $this->order->find($id);
		if (empty($data)) $this->adminMsg('订单不存在！');
	    if ($this->isPostForm()) {
		    $admin  = $this->order->from('user')->where('userid=?', $this->session->get('user_id'))->select(false);
			$log    = string2array($data['adminlog']);
			$action = $this->post('action');
			if (empty($action)) $this->adminMsg('请选择”订单操作“！');
			if ($action == 1) {
			    //关闭交易
				$log[] = array('time'=>time(), 'action'=>'关闭交易', 'admin'=>$admin['username'], 'note'=>$this->post('note'));
				$this->order->update(array('status'=>4, 'note'=>$this->post('note')), 'id=' . $id);
				//资金返回
				if ($data['paytime']) {
				    $pay    = $this->model('pay_data');
					$pdata  = $pay->getData($data['userid']);
				    $update = array(
						'available' => $pdata['available'] + $data['price'], 
						'freeze'    => $pdata['freeze'] - $data['price']
					);
					$pay = $this->model('pay_data');
					$pay->update($update, 'userid=' . $data['userid']);
				}
			} elseif ($action == 3 && $data['status'] == 0) {
			    //确认订单
				$log[] = array('time'=>time(), 'action'=>'确认订单', 'admin'=>$admin['username'], 'note'=>$this->post('admintext'));
				$this->order->update(array('status'=>1), 'id=' . $id);
			} else {
				if ($data['status'] == 0) {
					//改价
					$price = $this->post('price');
					if ($price - $data['price'] != 0) {
						$log[] = array('time'=>time(), 'action'=>$data['price'] . '改价成' . $price, 'admin'=>$admin['username'], 'note'=>$this->post('admintext'));
						$this->order->update(array('price'=>$price), 'id=' . $id);
					}
				} elseif ($data['status'] == 1) {
					//去配货
					$log[] = array('time'=>time(), 'action'=>'配货', 'admin'=>$admin['username'], 'note'=>$this->post('admintext'));
					$this->order->update(array('status'=>2), 'id=' . $id);
				} elseif ($data['status'] == 2) {//更新商品表中出售数量
					//发货
					$shipping = $this->post('shipping_id');
					if (empty($shipping)) $this->adminMsg('请填写发货编号，以便买家查询！');
					$log[] = array('time'=>time(), 'action'=>'发货', 'admin'=>$admin['username'], 'note'=>$this->post('admintext'));
					$this->order->update(array('status'=>3, 'sendtime'=>time(), 'shipping_id'=>$shipping), 'id=' . $id);
					$items = string2array($data['items']);
					foreach ($items as $t) {
						if (isset($this->setting['field']['item_num'][$t['modelid']]) && $this->setting['field']['item_num'][$t['modelid']]) {
							$models = get_model_data();
							$table  = $models[$t['modelid']]['tablename'];
							$table  = $this->model($table);
							$field  = $this->setting['field']['item_num'][$t['modelid']];
						    $table->update(array($field=>$field . '+' . $t['num']), 'id=' . $t['id']);
						}
					}
				} elseif ($data['status'] == 3) {
					//确认
					$log[]  = array('time'=>time(), 'action'=>'确认收货', 'admin'=>$admin['username'], 'note'=>$this->post('admintext'));
					$result = $this->order->update(array('status'=>9, 'confirmtime'=>time()), 'id=' . $id);
					if ($result && $this->setting['pay'] && $data['paytime']) {
					    //资金变动
						$pay    = $this->model('pay_data');
						$pdata  = $pay->getData($data['userid']);
						$update = array('freeze' => $pdata['freeze'] - $data['price']);
						$pay->set($update, $data['userid']);
						$spend  = $this->model('Pay_spend');
						$insert = array(
						    'userid'   => $this->memberinfo['id'],
							'username' => $this->memberinfo['username'],
							'money'    => $data['price'],
							'addtime'  => time(),
							'note'     => '购物消费，订单编号：' . $data['order_sn'],
						);
						$spend->insert($insert);
					}
				}
			}
			//写入日志
			$this->order->update(array('adminlog'=>array2string($log)), 'id=' . $id);
			$this->adminMsg('操作成功', purl('admin/'), 3, 1, 1);
		}
		$this->assign(array(
		    'data' => $data,
			'pay'  => $this->setting['pay'],
		));
	    $this->display('admin_edit');
	}
	
	/*
	 * 查看订单详情
	 */
	public function showAction() {
	    $id   = (int)$this->get('id');
		$data = $this->order->find($id);
		if (empty($data)) $this->adminMsg('订单不存在！');
	    $log  = string2array($data['adminlog']);
		$this->assign(array(
		    'data' => $data,
			'log'  => $log,
			'pay'  => $this->setting['pay'],
		));
	    $this->display('admin_show');
	}
	
	/*
	 * 核心配置
	 */
	public function settingAction() {
	    if ($this->isPostForm()) {
		    $data = $this->post('data');
			$this->cache->set('shop_config', $data);
			$this->adminMsg('操作成功', purl('admin/setting'), 3, 1, 1);
		}
		$this->assign(array(
		    'data' => $this->setting,
			'mods' => get_model_data(),
		));
	    $this->display('admin_setting');
	}
	
	/*
	 * 物流管理
	 */
	public function shippingAction() {
	    if ($this->isPostForm()) {
		    $form = $this->post('form');
			if ($form == 'add') {
			    $data = $this->post('data');
				if (empty($data['name'])) $this->memberMsg('填写物流名称！');
				$id = $this->shipping->insert($data);
				if (!$id) $this->adminMsg('添加失败！');
			} else {
			    $delete = $this->post('delete');
				if (is_array($delete) && $delete) {
				    $this->shipping->delete('id IN (' . implode(',', $delete) . ')');
				}
				$data = $this->post('data');
				if (is_array($data)) {
				    foreach ($data as $id=>$t) {
					    if ($id) $this->shipping->update($t, 'id=' . $id);
					}
				}
			}
		}
		$data = $this->shipping->select();
		$this->assign('data', $data);
	    $this->display('admin_shipping');
	}
	
	/*
	 * 检测用户
	 */
	public function checkuserAction() {
	    $username = $this->get('username');
		if (empty($username)) exit('请填写会员名称！');
		$data = $this->pay_data->getDataName($username);
		if (empty($data)) exit('会员不存在！');
		echo '可用金额：' . $data['available'];
		echo ' 冻结金额：' . $data['freeze'];
	}
	
}