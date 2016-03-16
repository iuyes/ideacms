<?php

class ExtendController extends Member {

    private $model;
	private $table;
	private $modelid;
	private $touserid;
	
    public function __construct() {
		parent::__construct();
		$model			= $this->cache->get('model_member_extend');
		$this->modelid	= (int)$this->get('modelid');
		$this->model	= $model[$this->modelid];
		$this->touserid	= (int)$this->get('touserid');
		if (empty($this->model)) $this->msg(lang('a-mod-4'));
		$this->table	= $this->model($model[$this->modelid]['tablename']);
	}
	
	public function indexAction() {
		 if ($this->isPostForm()) {
			$data	= $this->post('data');
			if (empty($this->touserid)) $this->callback(lang('a-mod-188'));	//接收者验证
			if ($this->model['setting']['member']['code'] && !$this->checkCode($this->post('code'))) $this->callback(lang('for-4'));
			if (!get_member_info($this->touserid)) $this->callback(lang('a-mod-198'));	//接收者验证
			if ($this->memberPost($this->model['setting']['auth']))	$this->callback(lang('m-con-12'));
			if ($this->model['setting']['member']['post'] && empty($this->memberinfo)) $this->callback(lang('for-5'));
			if ($this->touserid == $this->memberinfo['id']) $this->callback(lang('a-mod-199'));	//不对自己提交
			if ($this->model['setting']['member']['num'] && $this->check_num()) $this->callback(lang('for-6'));
			if ($this->model['setting']['member']['ip'] && $this->check_ip()) $this->callback(lang('for-7',array('1'=>$this->model['setting']['member']['ip'])));
			if ($result = $this->checkFields($this->model['fields'], $data, 3)) $this->callback($result);
			$data['ip']			= client::get_user_ip();
			$data['status']		= empty($this->model['setting']['member']['check']) ? 1 : 0;
			$data['userid']		= empty($this->memberinfo) ? 0  : $this->memberinfo['id'];
			$data['username']	= empty($this->memberinfo) ? '' : $this->memberinfo['username'];
			$data['touserid']	= $this->touserid;
			$data['inputtime']	= $data['updatetime'] = time();
			if ($this->table->insert($data)) {	//添加成功
			    $this->callback($data['status'] ? lang('for-8') : lang('for-9'), $this->post('backurl'), 1);
			} else {
			    $this->callback(lang('for-10'));
			}
		}
		$this->view->assign(array(
			'model'			=> $this->model,
			'fields'		=> $this->getFields($this->model['fields'], null, $this->model['setting']['member']['field']),
			'modelid'		=> $this->modelid,
		    'touserid'		=> $this->touserid,
			'tomember'		=> get_member_info($this->touserid, 1),
	        'meta_title'	=> $this->site['SITE_NAME']
		));
		$this->view->display('member/' . (is_file(VIEW_DIR . SYS_THEME_DIR . 'member' . DIRECTORY_SEPARATOR . $this->model['categorytpl']) ? substr($this->model['categorytpl'], 0, -5) : 'extend_post'));
	}
	
	/*
	 * 返回信息处理[callback]
	 */
	private function callback($msg, $url = '', $state = 0) {
		if ($this->model['setting']['member']['callback'] && function_exists($this->model['setting']['member']['callback'])) {
			eval($this->model['setting']['member']['callback'] . '("' . safe_replace($msg) . '", "' . safe_replace($url) . '", ' . $state . ');');
		} else {
			$this->msg($msg, $url, 1);
		}
		exit;
	}
	
	/*
	 * 同一会员（游客）提交次数
	 */
	private function check_num() {
		$num = $this->model['setting']['member']['num'];
		if (empty($num)) return false;
		if (empty($this->memberinfo)) {
			$where	= 'ip="' . client::get_user_ip() . '" AND userid=0 AND username="" AND touserid=' . $this->touserid;
		} else {
			$where	= 'userid=' . $this->memberinfo['id'] . ' AND username="' . $this->memberinfo['username'] . '" AND touserid=' . $this->touserid;
		}
		$data = $this->table->count($this->model['tablename'], null, $where);
		return $data >= $num ? true : false;
	}
	
	/*
	 * 同一IP提交间隔
	 */
	private function check_ip() {
	    $time	= $this->model['setting']['member']['ip'] * 60; //秒
		$data	= $this->table->from(null, 'id,inputtime')->where('ip=?', client::get_user_ip())->where('touserid=' . $this->touserid)->order('inputtime DESC')->select(false);
		if (empty($data)) return false;
		if (time() - $data['inputtime'] < $time) return true;
		return false;
	}
}