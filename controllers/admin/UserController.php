<?php

class UserController extends Admin {

    public function __construct() {
		parent::__construct();
	}

    // 会员同步转移
    public function synAction() {
        //变量初始化
        $is_syn = FALSE;//是否已经转移
        $domain = '';//高级版域名
        $has_config = FALSE;//是否有配置文件
        $newDataBase = NULL;//高级版数据库连接
        $newUserModels = array();//新用户组

        if(file_exists(APP_ROOT.'cache\member.lock'))
        {
            $is_syn = TRUE;
            $domain = explode(';',file_get_contents(APP_ROOT.'cache\member.lock'));
            $domain = $domain[1];
        }
        if(file_exists((APP_ROOT.'config\database.user.ini.php')))
        {
            $has_config = TRUE;
            $config = self::load_config('database.user');
            $newDataBase = $this->load->database($config,TRUE);
            $newUserModels = $newDataBase->get('member_group')->result_array();
        }
        $userModel = $this->db->where('typeid','2')->get('model')->result_array();


        if (IS_POST) {
            $options = $this->post('data');
            $users = $this->db->get('member')->result_array();//当前成员表中的数据

            $count = 0;
            foreach ($users as $user) {

                $data['username'] = $user['username'];
                $data['password'] = $user['password'];
                $data['salt'] = $user['salt'];
                $data['email'] = $user['email'];
                $data['avatar'] = $user['avatar'];
                if($options['exp'])
                    $data['experience'] = $user['credits'];
                $data['regtime'] = $user['regdate'];
                $data['regip'] = $user['regip'];
                $temp = 1;

                foreach ($options['model'] as $key => $value)
                {
                    if($user['modelid'] == $key)
                    {
                        $temp = $value;
                    }
                }

                $data['groupid'] = $temp;

                $exist = $newDataBase->where('username',$data['username'])->count_all_results('member');

                if(!$exist) {
                    $newDataBase->insert('member', $data);
                    $count ++;
                }
            }

            if($count) {
                file_put_contents(APP_ROOT.'cache\member.lock',date('Y-m-d h:i:s',time()).';'.$options['domain']);
                $this->adminMsg(lang('success') . ',转移' . $count . '条会员数据！', url('admin/index/main'), 3, 1, 1);
            }

        }


        $this->template->assign(
            array(
                'userModels' => $userModel,
                'newUserModels' => $newUserModels,
                'is_syn' => $is_syn,
                'domain' => $domain,
                'has_config' => $has_config
            )
        );

        $this->template->display('admin/user_syn');
    }

	public function indexAction() {

	    $roleid = $this->get('roleid');
	    $data   = $this->user->get_user_list($roleid, 1);
	    $this->view->assign('list', $data);
		$this->view->display('admin/user_list');
	}
	
	public function addAction() {
	    $role = $this->user->get_role_list();
	    if ($this->post('submit')) {
	        $username = $this->post('username');
	        if (!$username) $this->adminMsg(lang('a-use-0'));
	        if ($this->user->getOne('username=?', $username)) $this->adminMsg(lang('a-use-2'));
			$usermenu = $this->post('menu');
			$menu     = array();
			foreach ($usermenu['name'] as $id=>$v) {
			    if ($v && $usermenu['url'][$id]) {
				    $menu[$id] = array('name'=>$v, 'url'=>$usermenu['url'][$id]);
				}
			}
	        $data = array(
	            'username' => $username,
	            'realname' => $this->post('realname'),
	            'email'    => $this->post('email'),
	            'roleid'   => $this->post('roleid'),
				'site'	   => $this->post('site'),
				'usermenu' => array2string($menu),
	        );
			$data['salt']     = substr(md5(time()), 0, 10);
	        $data['password'] = md5(md5($this->post('password')) . $data['salt'] . md5($this->post('password')));
	        $this->user->insert($data);
	        $this->adminMsg(lang('success'), url('admin/user/index/'), 3, 1, 1);
	    }
	    $this->view->assign('role', $role);
	    $this->view->assign('pwd', '');
	    $this->view->display('admin/user_add');
	}
	
	public function editAction() {
	    $role = $this->user->get_role_list();
	    if ($this->post('submit')) {
	        $userid   = (int)$this->post('userid');
			$usermenu = $this->post('menu');
			$menu     = array();
			if (!$user = $this->user->getOne('userid=' . $userid)) $this->adminMsg(lang('a-use-3'));
			foreach ($usermenu['name'] as $id=>$v) {
			    if ($v && $usermenu['url'][$id]) {
				    $menu[$id] = array('name'=>$v, 'url'=>$usermenu['url'][$id]);
				}
			}
	        $data = array(
	            'realname' => $this->post('realname'),
	            'email'    => $this->post('email'),
	            'roleid'   => $this->post('roleid'),
				'usermenu' => array2string($menu),
				'site'	   => $this->post('site'),
	        );
	        if ($this->post('password')) $data['password'] = md5(md5($this->post('password')) . $user['salt'] . md5($this->post('password')));
	        $this->user->update($data, 'userid=' . $userid);
	        $this->adminMsg(lang('success'), url('admin/user/index/'), 3, 1, 1);
	    }
	    $userid = $this->get('userid');
	    $user   = $this->user->find($userid);
	    if (empty($user)) $this->adminMsg(lang('a-use-3'));
	    $this->view->assign(array(
	        'data' => $user,
	        'role' => $role,
			'menu' => string2array($user['usermenu']),
	    ));
	    $this->view->display('admin/user_add');
	}
	
	public function ajaxeditAction() {
	    $user   = $this->userinfo;
	    $userid = $user['userid'];
	    if ($this->post('submit')) {
			$usermenu = $this->post('menu');
			$menu     = array();
			foreach ($usermenu['name'] as $id=>$v) {
			    if ($v && $usermenu['url'][$id]) {
				    $menu[$id] = array('name'=>$v, 'url'=>$usermenu['url'][$id]);
				}
			}
	        $data = array(
	            'realname' => $this->post('realname'),
	            'email'    => $this->post('email'),
				'usermenu' => array2string($menu),
	        );
	        if ($this->post('password')) $data['password'] = md5(md5($this->post('password')) . $user['salt'] . md5($this->post('password')));
	        $this->user->update($data, 'userid=' . $userid);
	        $this->adminMsg(lang('success'), url('admin/user/ajaxedit/'), 3, 1, 1);
	    }
	    if (empty($user)) $this->adminMsg(lang('a-use-3'));
	    $this->view->assign(array(
	        'data' => $user,
			'menu' => string2array($user['usermenu']),
	    ));
	    $this->view->display('admin/user_edit');
	}
	
	public function delAction() {
	    $userid = (int)$this->get('userid');
	    if (empty($userid)) $this->adminMsg(lang('a-use-3'));
	    if ($this->userinfo['userid'] == $userid) $this->adminMsg(lang('a-use-4'));
	    $this->user->delete('userid=' . $userid);
	    $this->adminMsg(lang('success'), url('admin/user/index/'), 3, 1, 1);
	}
}