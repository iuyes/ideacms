<?php

class RepassController extends Member {
    
    public function __construct() {
		parent::__construct();
	}
	
	public function indexAction() {
	    if ($this->isPostForm()) {
		    $username = $this->post('username');
			if (empty($username)) $this->memberMsg(lang('m-reg-7'));
			if (!$this->checkCode($this->post('code'))) $this->memberMsg(lang('for-4'));
			$data     = $this->member->where('username=?', $username)->select(false);
			if (empty($data)) $this->memberMsg(lang('m-rep-0'));
			$result   = $this->passEmail($data['username'], $data['email']);
			if ($result) $this->memberMsg(lang('m-rep-1'), 0, 1);
			$this->memberMsg(lang('m-rep-2'));
		}
	    $this->view->assign(array(
		    'step'       => 1,
			'meta_title' => lang('m-rep-3') . '-' . $this->site['SITE_NAME'],
		));
	    $this->view->display('member/repass');
	}
	
	public function findAction() {
		$id = $this->get('id');
		if (empty($id)) $this->memberMsg(lang('m-rep-4'));
		$id = base64_decode($id);
		list($time, $randcode, $username) = explode('|', $id);
		if (time() - $time >= 3600*48) $this->memberMsg(lang('m-rep-5'));
		$data = $this->member->where('randcode=?', $randcode)->select(false);
		if (empty($data))  $this->memberMsg(lang('m-rep-0'));
		if (md5($data['username']) != $username) $this->memberMsg(lang('m-rep-6'));
		$step = 2;
		if ($this->isPostForm()) {
		    $password  = $this->post('password');
			$password2 = $this->post('password2');
			if (empty($password)) $this->memberMsg(lang('m-inf-5'));
			if ($password2 != $password) $this->memberMsg(lang('m-inf-6'));
	        $password  = $data['salt'] ? md5($password) . $data['salt'] . md5($password) : $password;
			$this->member->update(array('password'=>md5($password), 'randcode'=>''), 'id=' . $data['id']);
			$step = 3;
		}
		$this->view->assign(array(
		    'step'       => $step,
			'name'       => $data['username'],
			'meta_title' => lang('m-rep-7') . '-' . $this->site['SITE_NAME'],
		));
		$this->view->display('member/repass');
	}
	
}