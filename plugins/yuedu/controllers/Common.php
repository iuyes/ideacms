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
	protected $yuedu;
    
    public function __construct() {
        parent::__construct();
		$this->plugin   = $this->model('plugin');
        $this->data     = $this->plugin->where('dir=?', $this->namespace)->select(false);
		if (empty($this->data))     $this->adminMsg('应用尚未安装', url('admin/plugin'));
		if ($this->data['disable']) $this->adminMsg('应用尚未开启', url('admin/plugin'));
		$this->viewpath = SITE_PATH . $this->site['PLUGIN_DIR'] . '/' . $this->data['dir'] . '/views/';
		$this->assign(array(
		    'viewpath'  => $this->viewpath,
            'pluginid'  => $this->data['pluginid'],	
		));
		date_default_timezone_set(SYS_TIME_ZONE);
		$this->cache	= new cache_file($this->data['dir']);
		$this->setting  = $this->cache->get('config');
		$this->yuedu	= $this->model('yuedu');
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