<?php

class AdminController extends Plugin {
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
    }
	
	public function indexAction() {
	    if ($this->isPostForm()) {
		    if ($this->post('form') == 'search') {
			    $kw    = $this->post('kw');
			}
			if ($this->post('form') == 'del') {
			    $dels  = $this->post('dels');
				$ids   = @implode(',', $dels);
				if ($ids) $this->digg->delete('id in (' . $ids . ')');
			}
		}
	    $kw       = $kw      ? $kw : $this->get('kw');
	    $page     = (int)$this->get('page');
		$page     = (!$page) ? 1 : $page;
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
		$pagesize = 8;
		$where    = $kw ? 'title like "%' . $kw . '%"' : 1;
		$total    = $this->digg->count('digg', 'id', $where);
	    $url      = purl('admin/index', array('page'=>'{page}', 'kw'=>$kw));
	    $select   = $this->digg->page_limit($page, $pagesize)->order('addtime DESC');
		if ($kw) $select->where('title like "%' . $kw . '%"');
		$data     = $select->select();
	    $pagelist = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
		$this->assign(array(
			'list'     => $data,
			'pagelist' => $pagelist,
		));
	    $this->display('admin_list');
	}

}