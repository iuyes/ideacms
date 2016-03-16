<?php

class AdminController extends Plugin {
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
    }
	
	public function indexAction() {
	    if ($this->isPostForm()) {
		    if ($this->post('form') == 'del') {
				$dels  = $this->post('dels');
				if (is_array($dels)) {
					foreach ($dels as $id) {
					    $cdata = $this->comment_data->find($id, 'contentid');
						$cid   = $cdata['contentid'];
						$this->comment_data->delete('id=' . $id);
						$count = $this->comment_data->count('comment_data', 'id', '`reply`=0 AND status=1 AND contentid=' . $cid);
						$this->comment->update(array('total'=>$count), 'contentid=' . $cid);
					}
				}
			} elseif ($this->post('form') == 'status') {
			    $dels = $this->post('dels');
				if (is_array($dels)) {
					foreach ($dels as $id) {
					    $cdata = $this->comment_data->find($id, 'contentid');
						$cid   = $cdata['contentid'];
						$this->comment_data->update(array('status'=>1), 'id=' . $id);
						$count = $this->comment_data->count('comment_data', 'id', '`reply`=0 AND status=1 AND contentid=' . $cid);
						$this->comment->update(array('total'=>$count), 'contentid=' . $cid);
					}
				}
			}
		}
	    $status    = isset($_GET['status']) ? $this->get('status') : 1;
	    $page      = (int)$this->get('page');
		$page      = (!$page) ? 1 : $page;
	    $pagelist  = $this->instance('pagelist');
		$pagelist->loadconfig();
		$pagesize  = 8;
		$total     = $this->comment_data->count('comment_data', 'id', 'status=' . $status);
	    $url       = purl('admin/index', array('page'=>'{page}', 'status'=>$status));
	    $data      = $this->comment_data->page_limit($page, $pagesize)->order('addtime DESC')->where('status=' . $status)->select();
	    $pagelist  = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
		$this->assign(array(
			'list'     => $data,
			'pagelist' => $pagelist,
			'status'   => $status,
		));
	    $this->display('admin_clist');
	}
	
}