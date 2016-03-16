<?php

class AdminController extends Plugin {
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
		$menu = array(
		    array('index',    '投票列表'),
			array('add',      '添加投票'),
		);
		$this->assign('menu', $menu);
    }
	
	/*
	 * 投票管理
	 */
	public function indexAction() {
	    if ($this->isPostForm()) {
		    $ids   = $this->post('ids');
			if ($ids) {
			    foreach ($ids as $id) {
				     $this->vote->delete('id=' . $id);
				}
			}
		}
	    $page      = isset($_GET['page']) ? $this->get('page') : 1;
	    $pagelist  = $this->instance('pagelist');
		$pagelist->loadconfig();
		$pagesize  = 8;
		$total     = $this->vote->count('vote', 'id');
	    $url       = purl('admin/index', array('page'=>'{page}'));
	    $data      = $this->vote->page_limit($page, $pagesize)->order('addtime DESC')->select();
	    $pagelist  = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
		$this->assign(array(
			'list'     => $data,
			'pagelist' => $pagelist
		));
	    $this->display('list');
	}
	
	/*
	 * 添加投票
	 */
	public function addAction() {
	    if ($this->isPostForm()) {
		    $data = $this->post('data');
			if (empty($data['subject'])) $this->adminMsg('请填写投票主题！');
			$data['options'] = array2string($data['options']);
			$data['addtime'] = time();
			$this->vote->insert($data);
			$this->adminMsg('添加成功！', purl('admin'));
		}
	    $this->display('add');
	}
	
	/*
	 * 修改投票
	 */
	public function editAction() {
	    $id = $this->get('id');
		if (empty($id)) $this->adminMsg('Id无效！');
	    if ($this->isPostForm()) {
		    $data = $this->post('data');
			if (empty($data['subject'])) $this->adminMsg('请填写投票主题！');
			$data['options'] = array2string($data['options']);
			$data['addtime'] = time();
			$this->vote->update($data, 'id=' . $id);
			$this->adminMsg('修改成功！', purl('admin'));
		}
		$data = $this->vote->find($id);
		if (empty($data)) $this->adminMsg('投票不存在！');
		$data['options'] = string2array($data['options']);
		$this->assign('data', $data);
	    $this->display('add');
	}
	
}