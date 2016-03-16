<?php

/**
 * 后台控制器
 */
 
class AdminController extends Plugin {	//必须继承应用公共控制器
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
		//应用后台管理菜单,并赋值到模板
		$this->assign('menu', array(
		    array('index',		'使用方法'),
		    array('config',		'收费配置'),
			array('cache',		'更新缓存'),
		));
    }
	
	/*
	 * 应用首页
	 */
	public function indexAction() {
	    $this->display('admin_list');
	}
	
	/*
	 * 收费配置
	 */
	public function configAction() {
	    if ($this->isPostForm()) {	//提交表单的处理
			if (!plugin('pay')) $this->adminMsg('请先安装“在线充值”应用');
			$this->cache->set('config', $this->post('data'));
		}
		$tree	= $this->instance('tree');
		$tree->config(array('id'=>'catid', 'parent_id'=>'parentid', 'name'=>'catname'));
		//赋值给模板
		$this->assign(array(
			'data'	=> $this->cache->get('config'),
			'list'  => $tree->get_tree_data($this->cats, 0, $pre_fix='|-&nbsp;&nbsp;'),
			'model'	=> $this->membermodel,
			'group'	=> $this->membergroup
		));
		//设定模板
	    $this->display('admin_config');
	}
	
	/*
	 * 应用缓存
	 * 缓存数据格式	array('动作名称'=>array('函数名称前缀'=>'函数文件', ... ))
	 */
	public function cacheAction() {
		if (!plugin('pay')) $this->adminMsg('请先安装“在线充值”应用');
		//发布文章执行前（判断是否有可用余款）和后（扣款操作）的动作
	    $cache	= new cache_file();
		$later	= $cache->get('post_event_later');	//加载后的缓存
		$before = $cache->get('post_event_before');	//加载前的缓存
		$later['member']['fees'] = $before['member']['fees'] = 'plugins/fees/function.php';	//只执行会员投稿（member）动作
		$cache->set('post_event_later', $later);	//保存后的缓存
		$cache->set('post_event_before', $before);	//保存前的缓存
		//检查是否保存成功
		$later	= $cache->get('post_event_later');	//加载后的缓存
		$before = $cache->get('post_event_before');	//加载前的缓存
		if (!isset($later['member']['fees'])) $this->adminMsg('执行后的缓存文件不存在');
		if (!isset($before['member']['fees'])) $this->adminMsg('执行前的缓存文件不存在');
		$this->adminMsg('缓存更新成功', '', 1, 1, 1);
	}
	
}