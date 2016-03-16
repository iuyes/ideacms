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
		    array('config',		'分享配置'),
			array('cache',		'更新缓存'),
		));
    }
	
	/*
	 * 应用首页
	 */
	public function indexAction() {
		$this->assign('curl', function_exists("curl_init"));
	    $this->display('admin_list');
	}
	
	/*
	 * 配置
	 */
	public function configAction() {
		if (!function_exists("curl_init")) $this->adminMsg('请先开启curl支持');
	    if ($this->isPostForm()) {	//提交表单的处理
			$this->cache->set('config', $this->post('data'));
		}
		$data = $this->cache->get('config');
		$data['name'] = $data['name'] ? $data['name'] : 'IdeaCMS分享';
		//赋值给模板
		$this->assign(array(
			'data'	=> $data
		));
		//设定模板
	    $this->display('admin_config');
	}
	
	/*
	 * 应用缓存
	 * 缓存数据格式	array('动作名称'=>array('函数名称前缀'=>'函数文件', ... ))
	 */
	public function cacheAction() {
		if (!function_exists("curl_init")) $this->adminMsg('请先开启curl支持');
	    $cache	= new cache_file();
		$later	= $cache->get('post_event_later');	//加载后的缓存
		$config	= $this->cache->get('config');
		if ($config['admin']) {	//后台操作
			//发布文章执行后动作
			$later['admin']['share'] = 'plugins/share/function/admin_function.php';	//只执行会员投稿（admin）动作
			$cache->set('post_event_later', $later);	//保存后的缓存
		} else {	//否者删除该动作
			unset($later['admin']['share']);
		}
		if ($config['admin']) {	//会员操作
			//发布文章执行后动作
			$later['member']['share'] = 'plugins/share/function/member_function.php';	//只执行会员投稿（member）动作
			$cache->set('post_event_later', $later);	//保存后的缓存
		} else {	//否者删除该动作
			unset($later['member']['share']);
		}
		$this->adminMsg('缓存更新成功', '', 1, 1, 1);
	}
	
}