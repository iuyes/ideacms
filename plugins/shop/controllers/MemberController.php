<?php

class MemberController extends Plugin {
	
    public function __construct() {
        parent::__construct();
		if (empty($this->memberinfo)) $this->redirect(url('member/login', array('back'=>urlencode($_SERVER['HTTP_REFERER']))));
		$this->view->assign('navigation', array(
		    'index'   => array('name'=> '订单管理', 'url'=> url('shop/member/index')),
		    'address' => array('name'=> '收货地址', 'url'=> url('shop/member/address')),
		));
    }
	
	/*
	 * 订单管理
	 */
	public function indexAction() {
	    $page = isset($_GET['page']) ? $this->get('page') : 1;
		$time = (int)$this->get('time');
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
		    $total    = $this->order->count('shop_order', 'id', $where);
			$data     = $this->order->page_limit($page, $pagesize)->order('addtime DESC')->where($where)->select();
			$pagelist = $pagelist->total($total)->url(purl('member/index', array('page'=>'{page}', 'time'=>$time)))->num($pagesize)->page($page)->output();
		} else {
		    $data     = $this->order->limit($pagesize)->order('addtime DESC')->where($where)->select();
			$pagelist = '';
		}
	    $this->view->assign(array(
			'meta_title' => '订单管理-会员中心-' . $this->site['SITE_NAME'],
			'list'       => $data,
			'pagelist'   => $pagelist,
			'time'       => $time,
			'pay'        => $this->setting['pay'],
	    ));
	    $this->view->display('member/shop_order');
	}
	
	/*
	 * 收货地址
	 */
	public function addressAction() {
	    if ($this->isPostForm()) {
		    $form = $this->post('form');
			if ($form == 'add') {
			    $data = $this->post('data');
				if (empty($data['name']) || empty($data['tel']) || empty($data['address']) || empty($data['zip'])) {
                    $this->memberMsg('填写不完整！');
                }
				$data['userid']   = $this->memberinfo['id'];
				$data['username'] = $this->memberinfo['username'];
				$id = $this->address->insert($data);
				if (!$id) {
                    $this->memberMsg('添加失败！');
                }
			} else {
			    $delete = $this->post('delete');
				if (is_array($delete) && $delete) {
                    $ids = array();
                    foreach ($delete as $id) {
                        $id = intval($id);
                        if ($id) $ids[] = $id;
                    }
				    $this->address->delete('userid='.$this->memberinfo['id'].' and id IN (' . implode(',', $ids) . ')');
				}
				$default = $this->post('default');
				if ($default) {
				    $this->address->update(array('default_value'=>0), '1');
				    $this->address->update(array('default_value'=>1), 'id=' . $default);
				}
				$data = $this->post('data');
				if (is_array($data)) {
				    foreach ($data as $id=>$t) {
					    if ($id) $this->address->update($t, 'id=' . $id);
					}
				}
			}
		}
		$data = $this->address->where('username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'])->select();
	    $this->view->assign(array(
			'meta_title' => '收货地址-会员中心-' . $this->site['SITE_NAME'],
			'list'       => $data,
	    ));
	    $this->view->display('member/shop_address');
	}
	
	/*
	 * 购买商品
	 */
	public function buyAction() {
		$param = array(
		    'from'  => $this->get('from'),
			'catid' => $this->get('catid'),
			'id'    => $this->get('id'),
			'num'   => $this->get('num'),
		);
	    list($param, $data) = $this->getBuyData($param);
		if (empty($data)) $this->memberMsg('商品信息不存在，请重新选择商品！');
		$shipping = $this->shipping->select();
		if ($this->isPostForm()) {
		    if (!$this->checkCode($this->post('code'))) $this->memberMsg('验证码不正确！');
			$shippingid = (int)$this->post('shipping');
			$addressid  = (int)$this->post('address');
			if ($addressid) {
			    $address= $this->address->where('id=' . $addressid . ' AND username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'])->select(false);
			} else {
			    $address= $this->post('data');
				if (empty($address['name']) || empty($address['tel']) || empty($address['address']) || empty($address['zip'])) $this->memberMsg('收货信息填写不完整！');
				$address['userid']   = $this->memberinfo['id'];
				$address['username'] = $this->memberinfo['username'];
				if (!$this->address->insert($address)) unset($address);
			}
		    if ($param != $this->post('param')) $this->memberMsg('参数不正确！');
			if (empty($address)) $this->memberMsg('收货地址不存在！');
			if (is_array($shipping) && $shipping && empty($shippingid)) $this->memberMsg('您还没有选择配送方式！');
			$price = 0;
			foreach ($data as $t) {
			    $price += $t['item_price'] * $t['num'];
			}
			if (is_array($shipping) && $shipping && $shippingid) {
			    foreach ($shipping as $t) {
				    if ($t['id'] == $shippingid) {
					    $price  += $t['price'];
						$s_name  = $t['name'];
						$s_price = $t['price'];
					}
				}
			}
			$order_sn = create_order_sn();
			$order = array(
			    'order_sn' => $order_sn,
				'userid'   => $this->memberinfo['id'],
				'username' => $this->memberinfo['username'],
				'items'    => array2string($data),
				'addtime'  => time(),
				'price'    => $price,
				'shipping_name'  => $s_name,
				'shipping_price' => $s_price,
				'name'     => $address['name'],
				'address'  => $address['address'],
				'zip'      => $address['zip'],
				'tel'      => $address['tel'],
				'status'   => 0,
			);
			$id = $this->order->insert($order);
			if (empty($id)) $this->memberMsg('提交失败！');
			if (isset($param['from'])) {
			    $cart = new cart();
				$cart->clear(); //清空购物车
			}
			$this->view->assign(array(
				'meta_title' => '订购成功-会员中心-' . $this->site['SITE_NAME'],
				'order_sn'   => $order_sn,
				'order_id'   => $id,
			    'pay'        => $this->setting['pay'],
			));
			$this->view->display('member/shop_buy');
		} else {
			$this->view->assign(array(
				'data'       => $data,
				'address'    => $this->address->where('username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'])->select(),
				'shipping'   => $shipping,
				'param'      => $param,
			));
			$this->view->display('shop_checkout');
		}
	}
	
	/*
	 * 付款
	 */
	public function payAction() {
	    if (!$this->setting['pay']){
            $this->memberMsg('无需要付款，等待客服与您联系！');
        }
		if (!plugin('pay')){
            $this->memberMsg('请安装“在线支付”应用！');
        }
		$id  = (int)$this->get('id');
		$data = $this->order->where('id='.$id.' AND username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'])->select(false);
		if (empty($data)) {
            $this->memberMsg('没有查到订单信息！');
        }
		if ($data['status']) {
            $this->memberMsg('该订单已经付款了！');
        }
		$pay = $this->model('pay_data');
		$pdata = $pay->getData($this->memberinfo['id']);
		if ($this->isPostForm()) {
		    if ($pdata['available']  - $data['price'] < 0) {
                // 增加在线支付接口
                if (function_exists('online_pay')) {
                    online_pay($data);
                }
			    $this->memberMsg('余额不足，请充值！', url('pay/member/add', array('price'=>$data['price'] - $pdata['available'])));
			}
			$update = array(
			    'available' => $pdata['available'] - $data['price'], 
				'freeze'  => $pdata['freeze'] + $data['price']
			);
			$result = $pay->update($update, 'userid=' . $this->memberinfo['id']);
			if (!$result) $this->memberMsg('付款失败！');
			$this->order->update(array('status'=>1, 'paytime'=>time()), 'id=' . $id);
			$this->memberMsg('付款成功，请等待发货！', purl('member'), 1);
		}
		$this->view->assign(array(
			'meta_title' => '付款-会员中心-' . $this->site['SITE_NAME'],
			'data'       => $data,
			'pay_data'   => $pdata,
		));
		$this->view->display('member/shop_pay');
	}
	
	/*
	 * 确认收货
	 */
	public function confirmAction() {
	    $id     = (int)$this->get('id');
		$data   = $this->order->where('id=' . $id . ' AND username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'])->select(false);
		if (empty($data)) $this->memberMsg('没有查到订单信息！');
		$result = $this->order->update(array('status'=>9, 'confirmtime'=>time()), 'id=' . $id);
		if ($result && $this->setting['pay'] && $data['paytime']) {
			//资金变动
			$pay    = $this->model('pay_data');
			$pdata  = $pay->getData($this->memberinfo['id']);
			$update = array('freeze' => $pdata['freeze'] - $data['price']);
			$pay->set($update, $this->memberinfo['id']);
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
		$this->memberMsg('操作成功', purl('member'), 1);
	}
	
	/*
	 * 订单信息
	 */
	public function orderAction() {
	    $id     = (int)$this->get('id');
		$data   = $this->order->where('id=' . $id . ' AND username="' . $this->memberinfo['username'] . '" AND userid=' . $this->memberinfo['id'])->select(false);
		if (empty($data)) $this->memberMsg('没有查到订单信息！');
		$this->view->assign(array(
			'meta_title' => '订单信息-会员中心-' . $this->site['SITE_NAME'],
			'data'       => $data,
		));
		$this->view->display('member/shop_info');
	}
	
	/*
	 * 获取购买商品数据
	 */
	private function getBuyData($param) {
	    if (!is_array($param)) return false;
		if ($param['from'] == 'cart') {
		    $cart  = new cart();
			$data  = $cart->read_cart();
			if (empty($data)) $this->memberMsg('购物车为空！');
			foreach ($data as $t) {
			    $item = $this->getItemData($t['catid'], $t['id'], $param['num']);
				if ($item['item_price'] != $t['item_price']) $this->memberMsg('商品(' . $item['title'] . ')价格不一致！');
			}
			unset($param['catid'], $param['id'], $param['num']);
		} else {
		    if (empty($param['num'])) $this->memberMsg('请填写商品数量！');
			$item  = $this->getItemData($param['catid'], $param['id'], $param['num']);
			$item['num'] = $param['num'];
			$data  = array($param['catid'] . '-' . $param['id'] => $item);
			unset($param['from']);
		}
		return array(md5(array2string($param)), $data);
	}
	
}