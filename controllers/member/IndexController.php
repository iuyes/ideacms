<?php

class IndexController extends Member {
    
    public function __construct() {
		parent::__construct();
	}
	
	public function indexAction() {
		$this->isLogin(); //登录验证

	    $this->view->assign(array(
			'indexc'     => 1,
			'model'		 => $this->get_model(),
			'form'       => $this->getFormMember(),
		    'meta_title' => lang('member') . '-' . $this->site['SITE_NAME'],
		));
	    $this->view->display('member/index');
	}
	
	/**
	 * 会员列表
	 */
	public function listAction() {
	    $page = (int)$this->get('page');
	    $page = $page ? $page : 1;
		$mid  = (int)$this->get('modelid');
		if ($mid && !isset($this->membermodel[$mid])) $this->msg(lang('m-ind-0', array('1'=>$mid)));
		$this->view->assign(array(
		    'page'    => $page,
			'modelid' => $mid,
		));
	    $this->view->display('list_member');
	}
	
}