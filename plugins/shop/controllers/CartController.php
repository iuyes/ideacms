<?php

class CartController extends Plugin {

    private $cart;
	
    public function __construct() {
        parent::__construct();
		$this->cart = new cart();
    }
	
	/*
	 * 查看购物车
	 */
	public function indexAction() {
		if ($this->isPostForm()) {
		    $cartids = $this->post('cartid');
			if (is_array($cartids)) {
			    foreach ($cartids as $cartid=>$num) {
				    list($catid, $id) = explode('-', $cartid);
				    $this->getItemData($catid, $id, $num);
				    $this->cart->update_num($cartid, $num);
				}
			}
		}
		$data = $this->cart->read_cart();

		$this->view->assign(array(
		    'data' => $data,
			'total_price' => $this->cart->get_total_price(),
		));
		$this->view->display('shop_cart');
	}
	
	/*
	 * 加入购物车
	 */
	public function addAction() {
	    $id = (int)$this->get('id');
	    $catid = (int)$this->get('catid');
	    $num   = (int)$this->get('num');
		if (empty($num)) $this->Msg('请填写商品数量！', '', 1);
		$data  = $this->getItemData($catid, $id, $num);
		$data['num'] = $num;
		$cartid = $catid . '-' . $id;
		$this->cart->add($cartid, $data);
		$this->redirect(purl('cart'));
	}
	
	/*
	 * 移出购物车
	 */
	public function delAction() {
	    $cartid = (int)$this->get('cartid');
		$this->cart->delete($cartid);
		$this->msg('成功移出了一件商品！', purl('cart/'));
	}
	
	/*
	 * 清空购物车
	 */
	public function clearAction() {
		$this->cart->clear();
		$this->msg('购物车已清空！', '', 1);
	}
	
}