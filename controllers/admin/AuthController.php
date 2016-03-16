<?php

class AuthController extends Admin {

    public function __construct() {
		parent::__construct();
	}
	
	public function indexAction() {
	    $role = $this->user->get_role_list();
	    $this->view->assign('list', $role);
		$this->view->display('admin/auth');
	}
	
	public function listAction() {
	    $roleid = $this->get('roleid');
	    if (!$roleid) {
            $this->adminMsg(lang('a-aut-0'));
        }
	    //权限配置文件
        $data_file = CONFIG_DIR . 'auth.role.ini.php';
        //当前角色拥有的权限
        $data_role = require $data_file;
        $role = $data_role[$roleid];
        //权限模块配置
        $data_auth = require CONFIG_DIR . 'auth.option.ini.php';
        if ($this->post('submit')) {
            if ($roleid == 1) {
                $this->adminMsg(lang('a-aut-1'));
            }
            $auth = array();
            foreach ($_POST as $v=>$t) {
                if (strpos($v, 'auth_')!==false && $t==1) {
                    $auth[] = substr($v, 5);
                }
            }
            $data_role[$roleid] = $auth;
            $content = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * 用户权限配置信息" . PHP_EOL . " */".PHP_EOL
            . "return " . var_export($data_role, true) . ";";
            $rs = file_put_contents($data_file, $content);
            if ($rs === false) {
                $this->adminMsg(lang('da009').$data_file);
            }
            $this->adminMsg($this->getCacheCode('auth') . lang('success'), url('admin/auth/list', array('roleid'=>$roleid)), 3, 1, 1);
        }
        $this->view->assign(array(
            'roleid' => $roleid,
            'role'   => $role,
            'data'   => $data_auth,
        ));
		$this->view->display('admin/auth_list');
	}
	
	public function addAction() {
	    if ($this->post('submit')) {
	        $rolename = $this->post('rolename');
			if (empty($rolename)) {
                $this->adminMsg(lang('a-aut-2'));
            }
	        $description = $this->post('description');
	        $result = $this->user->set_role(0, $rolename, $description);
	        if ($result == 1) {
	            $this->adminMsg(lang('success'), url('admin/auth'), 3, 1, 1);
	        } elseif ($result == 0) {
	            $this->adminMsg(lang('a-aut-3'));
	        } else {
	            $this->adminMsg(lang('a-aut-4'));
	        }
	    }
	    $this->view->display('admin/auth_add');
	}
	
    public function editAction() {
	    if ($this->post('submit')) {
	        $roleid = $this->post('roleid');
	        $rolename = $this->post('rolename');
			if (empty($rolename)) {
                $this->adminMsg(lang('a-aut-2'));
            }
	        $description = $this->post('description');
	        $result = $this->user->set_role($roleid, $rolename, $description);
	        if ($result == 1) {
	            $this->adminMsg(lang('success'), url('admin/auth'), 3, 1, 1);
	        } elseif ($result == 0) {
	            $this->adminMsg(lang('a-aut-3'));
	        } else {
	            $this->adminMsg(lang('a-aut-4'));
	        }
	    }
        $roleid = $this->get('roleid');
        if (!$roleid) {
            $this->adminMsg(lang('a-aut-0'));
        }
        $row = $this->user->roleinfo($roleid);
        $this->view->assign('data', $row);
	    $this->view->display('admin/auth_add');
	}
	
	public function delAction() {
	    $roleid = $this->get('roleid');
        if (!$roleid) {
            $this->adminMsg(lang('a-aut-0'));
        }
        if ($this->userinfo['roleid'] == $roleid) {
            $this->adminMsg(lang('a-aut-5'));
        }
        if ($roleid == 1) {
            $this->adminMsg(lang('a-aut-6'));
        }
        $this->user->del_role($roleid);
        $this->adminMsg($this->getCacheCode('auth') . lang('success'), url('admin/auth'), 3, 1, 1);
	}
	
	public function cacheAction($show=0) {
        //所有角色拥有的权限
        $data_role = require CONFIG_DIR . 'auth.role.ini.php';
        $role = $this->user->get_role_list();
        $roleids = array(); //角色ID表
        foreach ($role as $t) {
            $roleids[] = $t['roleid'];
        }
        foreach ($data_role as $id=>$t) {
            if (!in_array($id, $roleids)) {
                //检查角色不存在就删除该角色的配置
                unset($data_role[$id]);
            }
        }
        $content = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * 用户权限配置信息" . PHP_EOL . " */" . PHP_EOL
        . "return " . var_export($data_role, true) . ";";
        file_put_contents(CONFIG_DIR . 'auth.role.ini.php', $content);
        $show or $this->adminMsg(lang('a-update'), '', 3, 1, 1);
	}
}