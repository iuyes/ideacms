<?php

class IpController extends Admin {
    
    private $ip;
    
    public function __construct() {
		parent::__construct();
		$this->ip = $this->model('ip');
	}
    
    public function indexAction() {
		if ($this->post('submit_del')) {
	        foreach ($_POST as $var=>$value) {
	            if (strpos($var, 'del_')!==false) {
	                $id = (int)str_replace('del_', '', $var);
	                $this->delAction($id, 1);
	            }
	        }
			$this->adminMsg($this->getCacheCode('ip') . lang('success'), url('admin/ip/'), 3, 1, 1);
	    }
		$ip       = $this->post('kw') ? $this->post('kw') : $this->get('ip');
	    $page     = (int)$this->get('page');
		$page     = (!$page) ? 1 : $page;
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
	    $total    = $ip ? $this->ip->count('ip', '`ip` LIKE "%' . $ip . '%"') : $this->ip->count('ip');
	    $pagesize = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
	    $url      = url('admin/ip/index', array('page'=>'{page}', 'ip'=>$ip));
		$select   = $this->ip->page_limit($page, $pagesize)->order(array('id DESC'));
	    $data     = $ip ? $select->where('`ip` LIKE ?', '%' . $ip . '%')->select() : $select->select();
	    $pagelist = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
	    $this->view->assign(array(
	        'list'     => $data,
	        'pagelist' => $pagelist,
	    ));
	    $this->view->display('admin/ip_list');
    }
    
    public function addAction() {
        if ($this->post('submit')) {
            $data = $this->post('data');
            if (empty($data['ip'])) $this->adminMsg(lang('a-aip-0'));
			if ($this->ip->getOne('ip=?', $data['ip'])) $this->adminMsg(lang('a-aip-8'));
			$data['addtime'] = time();
            $this->ip->insert($data);
            $this->adminMsg($this->getCacheCode('ip') . lang('success'), url('admin/ip'), 3, 1, 1);
        }
		App::auto_load('fields');
        $this->view->display('admin/ip_add');
    }
    
    public function editAction() {
        $id   = (int)$this->get('id');
        $data = $this->ip->find($id);
        if (empty($data)) $this->adminMsg(lang('a-aip-1'));
        if ($this->post('submit')) {
            unset($data);
            $data = $this->post('data');
            if (empty($data['ip'])) $this->adminMsg(lang('a-aip-0'));
			if ($this->ip->getOne('id<>' . $id . ' AND ip=?', $data['ip'])) $this->adminMsg(lang('a-aip-8'));
            $this->ip->update($data, 'id=' . $id);
            $this->adminMsg($this->getCacheCode('ip') . lang('success'), url('admin/ip'), 3, 1, 1);
        }
		App::auto_load('fields');
        $this->view->assign('data', $data);
        $this->view->display('admin/ip_add');
    }
    
    public function delAction($id=0, $all=0) {
        if (!auth::check($this->roleid, 'ip-del', 'admin')) $this->adminMsg(lang('a-com-0', array('1'=>'ip', '2'=>'del')));
	    $id  = $id  ? $id  : $this->get('id');
	    $all = $all ? $all : $this->get('all');
	    $this->ip->delete('id=' . $id);
	    $all or $this->adminMsg($this->getCacheCode('ip') . lang('success'), url('admin/ip/index'), 3, 1, 1);
	}
    
    public function cacheAction($show=0) {
	    $list = $this->ip->findAll();
	    $data = array();
	    foreach ($list as $t) {
	        if (empty($t['endtime']) || ($t['endtime'] - $t['addtime']) >= 0) $data[$t['ip']] = $t;
	    }
	    $this->cache->set('ip', $data);
	    $show or $this->adminMsg(lang('a-update'), '', 3, 1, 1);
	}
	
}