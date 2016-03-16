<?php

class LoginController extends Admin {
    
    public function __construct() {
		parent::__construct();
	}
	
    public function indexAction() {
	    $url = isset($_GET['url']) && $_GET['url'] ? urldecode($this->get('url')) : url('admin//');
		if ($this->isPostForm()) {
		    if (isset($this->site['SITE_ADMIN_CODE'])
                && $this->site['SITE_ADMIN_CODE']
                && !$this->checkCode($this->post('code'))) {
                $this->adminMsg(lang('code'), url('admin/login'));
            }
			if (get_cookie('admin_login')) {
                $this->adminMsg(lang('a-com-25'));
            }
		    $username = $this->post('username');
		    $password = $this->post('password');
		    $result   = $this->user->check_login($username, $password);
		    if ($result) {
				if ($result['site'] && $result['site'] != $this->siteid) {
                    $this->adminMsg(lang('a-sit-23'));
                }
		        $this->session->set('user_id', $result['userid']);
			    $this->adminMsg(lang('a-com-26'), $url, 3, 1, 1);
		    } else {
			    if ($this->session->is_set('error_admin_login')) {
				    $error = (int)$this->session->get('error_admin_login') - 1;
					if ($error <= 1) {
						$this->session->delete('error_admin_login');
                        set_cookie('admin_login', 1, 60*15);
					} else {
					    $this->session->set('error_admin_login', $error);
					}
				} else {
				    $error = 5;
					$this->session->set('error_admin_login', 5);
				}
			    $this->adminMsg(lang('a-com-27', array('1'=>$error)), url('admin/login', array('url'=>$this->get('url'))));
			}
		}
		$this->view->display('admin/login');
    }
    
    public function logoutAction() {
        if ($this->session->is_set('user_id')) {
            $this->session->unset_userdata('user_id');
        }
        $this->adminMsg(lang('a-com-28'), url('admin/login'), 3, 1, 1);
    }
}