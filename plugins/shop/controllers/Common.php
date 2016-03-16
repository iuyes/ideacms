<?php

/**
 * 文件名称: Common.php for v1.6 +
 * 应用控制器公共类
 */

class Plugin extends Common {
    
    protected $plugin;   //应用模型
	protected $data;     //应用数据
	protected $viewpath; //视图目录
	protected $setting;
	protected $address;
	protected $shipping;
	protected $order;
    
    public function __construct() {
        parent::__construct();
		$this->plugin = $this->model('plugin');
        $this->data = $this->plugin->where('dir=?', $this->namespace)->select(false);
		//if (empty($this->data))     $this->adminMsg('应用尚未安装', url('admin/plugin'));
		//if ($this->data['disable']) $this->adminMsg('应用尚未开启', url('admin/plugin'));
		$this->viewpath = SITE_PATH . $this->site['PLUGIN_DIR'] . '/' . $this->data['dir'] . '/views/';
		$this->assign(array(
		    'viewpath'  => $this->viewpath,
            'pluginid'  => $this->data['pluginid'],
		));
		date_default_timezone_set(SYS_TIME_ZONE);
		$this->setting  = $this->cache->get('shop_config');
		$this->address  = $this->model('shop_address');
		$this->shipping = $this->model('shop_shipping');
		$this->order    = $this->model('shop_order');
    }
	
	/**
	 * 获取商品数据
	 */
	public function getItemData($catid, $id, $num) {
		$modelid = (int)$this->cats[$catid]['modelid'];
		$models  = get_model_data();
		$table   = $models[$modelid]['tablename'];
		if (empty($table)) $this->adminMsg('内容模型(#' . $modelid . ')不存在！');
		$price   = $this->setting['field']['item_price'][$modelid];
		if (empty($price)) $this->adminMsg('系统没有为该模型(#' . $modelid . ')绑定价格字段！');
		$table   = $this->model($table);
		$field   = $price . ' AS item_price';
		if (isset($this->setting['field']['item_total'][$modelid]) && $this->setting['field']['item_total'][$modelid]) {
		    $field .= ',' . $this->setting['field']['item_total'][$modelid] . ' AS item_total';
		}
		if (isset($this->setting['field']['item_num'][$modelid]) && $this->setting['field']['item_num'][$modelid]) {
		    $field .= ',' . $this->setting['field']['item_num'][$modelid] . ' AS item_num';
		}
		$_data   = $table->find($id, $field);
		$data    = $this->content->getOne('id=' . $id . ' AND `status`=1', null, 'id,catid,modelid,title,thumb');
		if (empty($_data) || empty($data)) $this->msg('商品(#' . $id . ')不存在！', '', 1);
		//判断数量
		if (isset($this->setting['field']['item_total'][$modelid]) && $this->setting['field']['item_total'][$modelid]) {
		    if (empty($_data['item_total'])) $this->msg('商品(' . $data['title'] . ')数量不足！', '', 1);
			if ($num + $_data['item_num']  > $_data['item_total']) $this->msg('商品(' . $data['title'] . ')剩余数量不足！', '', 1);
			if (isset($this->setting['field']['item_num'][$modelid]) && $this->setting['field']['item_num'][$modelid] && $_data['item_num'] >= $_data['item_total']) {
				$this->msg('商品(' . $data['title'] . ')已经售完！', '', 1);
			}
		}
		return array_merge($data, $_data);
	}
	
}

/**
 * 生成购物订单号
 */
function create_order_sn(){
	mt_srand((double)microtime() * 10000);
	return date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT );
}

class cart extends Fn_base {

    public $ci;

    /**
     * 购物车名称
     */
    public $cart_name = 'fiencmssss_cart';

    /**
     * 购物车cookie存放路径
     */
    public $cookie_path = '/';

    /**
     * 购物车cookie生存周期(单位:秒)
     */
    public $life_time = 2592000;

    public function __construct() {
        $this->ci = &get_instance();
    }

    /**
     * 显示购物车内容
     * 返回数据类型为:array(唯一id=>array(商品ID, 商品名称, 商品数量, 商品单价,...), ...);
     */
    public function read_cart() {
        //从购物车cookie中读取数据
        $data = $this->ci->session->get($this->cart_name);
        if (!$data) {
            return false;
        }
        return string2array($data);
    }

    /**
     * 添加商品
     * @param integer    $id           商品ID(唯一)
     * @param array      $options      商品属性
     * @return boolean
     */
    public function add($id = 0, $options = array()) {
        if (!$id) return false;
        $num  = (int)$options['num'];
        $data = $this->read_cart();
        //当购物车中没有商品记录时
        if (!$data) {
            $data = array(
			    $id => $options,
			);
        } else {
            //当购物车中已存在所要添加的商品时,只进行库存更改操作
            if (isset($data[$id])) {
                $data[$id]['num'] += $num;
            } else {
                $data[$id] = $options;
            }
        }

        $this->ci->session->set($this->cart_name, array2string($data));
        return true;
    }

    /**
     * 修改购物车商品数量
     */
    public function update_num($cartid, $num) {
        if(!$cartid || !$num) return false;
        $cart_data = $this->read_cart();
        //判断将要更改的商品数据是否在购物车中存在
        if (!isset($cart_data[$cartid])) return  false;
        $cart_data[$cartid]['num'] = $num;
        $this->ci->session->set($this->cart_name, array2string($cart_data));
        return true;
    }

    /**
     * 删除购物车中的某商品
     */
    public function delete($key) {
        if (!$key) return false;
        $cart_data = $this->read_cart();
        if(!$cart_data) return  true;
        if (isset($cart_data[$key])) {
            unset($cart_data[$key]);
            $this->ci->session->set($this->cart_name, array2string($data));
        }
        return true;
    }

    /**
     * 清空购物车的内容
     */
    public function clear() {
        $this->ci->session->set($this->cart_name, '');
    }

    /**
     * 获取购物车内的总商种数(商品种类)
     */
    public function get_total_items() {
        $cart_data = $this->read_cart();
        if(!$cart_data) {
            $items = 0;
        } else {
            $items = sizeof($cart_data);
        }
        return $items;
    }

    /**
     * 获取购物车总金额
     */
    public function get_total_price() {
        $cart_data     = $this->read_cart();
        $total_price   = 0;
        //当购物车中有商品记录时
        if($cart_data) {
            foreach ($cart_data as $lines) {
                $total_price += $lines['num'] * $lines['item_price'];
            }
        }
        return $total_price;
    }

    /**
     * 购物车中是存在$key的商品
     */
    public function isset_in_cart($key) {
        if (!$key) return false;
        $cart_data = $this->read_cart();
        if($cart_data && isset($cart_data[$key])) {
            return true;
        }
        return false;
    }

}