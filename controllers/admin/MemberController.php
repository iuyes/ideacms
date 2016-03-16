<?php

class MemberController extends Admin {
    
	private $mgroup;
    
    public function __construct() {
		parent::__construct();
        if ($this->config['SYS_MEMBER']) {
            $this->adminMsg('系统禁止了会员功能');
        }
		$this->mgroup = $this->model('member_group');
	}
    
    public function indexAction() {
        $is_syn =FALSE;
        if(file_exists(APP_ROOT.'cache\member.lock'))
        {
            $is_syn = TRUE;
        }
		if ($this->post('submit') && $this->post('form')=='search') {
	        $kw = $this->post('kw');
	    } elseif ($this->post('submit_status_1') && $this->post('form')=='status_1') {
	        foreach ($_POST as $var=>$value) {
	            if (strpos($var, 'del_')!==false) {
	                $ids = str_replace('del_', '', $var);
					list($_id, $_mid) = explode('_', $ids);
	                $this->member->update(array('status'=>1), 'id=' . $_id);
	            }
	        }
	    } elseif ($this->post('submit_status_0') && $this->post('form')=='status_0') {
	        foreach ($_POST as $var=>$value) {
	            if (strpos($var, 'del_')!==false) {
	                $ids = str_replace('del_', '', $var);
					list($_id, $_mid) = explode('_', $ids);
	                $this->member->update(array('status'=>0), 'id=' . $_id);
	            }
	        }
	    }
	    $kw       = $kw ? $kw : $this->get('kw');
		$page     = (int)$this->get('page');
		$page     = (!$page) ? 1 : $page;
		$modelid  = (int)$this->get('modelid');
		$groupid  = (int)$this->get('groupid');
		$status   = (int)$this->get('status');
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
	    $where    = '1';
	    if ($kw) $where .= " and username like '%" . $kw . "%'";
	    if ($modelid) $where .= ' and modelid=' . $modelid;
		if ($groupid) $where .= ' and groupid=' . $groupid;
		if ($status == 1) {
		    $where .= ' and status=1';
		} elseif ($status ==2) {
		    $where .= ' and status=0';
		}
	    $total    = $this->member->count('member', null, $where);
	    $pagesize = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
	    $urlparam = array();
	    if ($kw)        $urlparam['kw']      = $kw;
	    if ($groupid)   $urlparam['groupid'] = $groupid;
	    if ($modelid)   $urlparam['modelid'] = $modelid;
		$urlparam['status'] = $status;
	    $urlparam['page']   = '{page}';
	    $data     = $this->member->where($where)->page_limit($page, $pagesize)->order(array('status ASC', 'regdate DESC', 'id DESC'))->select();
	    $pagelist = $pagelist->total($total)->url(url('admin/member/index', $urlparam))->num($pagesize)->page($page)->output();
		$count    = array();
		$count[0] = $this->member->count('member', null, '1');
		$count[1] = $this->member->count('member', null, 'status=1');
		$count[2] = $this->member->count('member', null, 'status=0');
		$count[$status] = $total;
	    $this->view->assign(array(
	        'kw'			=> $kw,
	        'list'			=> $data,
	        'page'			=> $page,
			'count'			=> $count,
			'status'		=> $status,
	        'pagelist'		=> $pagelist,
	        'membermodel'	=> $this->membermodel,
	        'membergroup'	=> $this->membergroup,
			'memberextend'	=> $this->cache->get('model_member_extend'),
            'is_syn'        => $is_syn
	    ));
	    $this->view->display('admin/member_list');
    }
	
	/*
	 * 用户组
	 */
	public function groupAction() {
	    $type = $this->get('type');
		switch ($type) {
		    case 'add':
			    if ($this->isPostForm()) {
				    $data = $this->post('data');
					if (empty($data['name'])) $this->adminMsg(lang('a-mem-1'));
					$this->mgroup->insert($data);
					$this->adminMsg($this->getCacheCode('member') . lang('success'), url('admin/member/group/'), 3, 1, 1);
				}
			    $this->view->display('admin/member_group_add');
				break;
			case 'edit':
			    $id = (int)$this->get('id');
			    if ($this->isPostForm()) {
				    $data = $this->post('data');
					if (empty($data['name'])) $this->adminMsg(lang('a-mem-1'));
					$this->mgroup->update($data, 'id=' . $id);
					$this->adminMsg($this->getCacheCode('member') . lang('success'), url('admin/member/group/'), 3, 1, 1);
				}
				$this->view->assign('data', $this->mgroup->find($id));
			    $this->view->display('admin/member_group_add');
				break;
			case 'cache':
			    $this->cacheAction();
			    break;
			case 'delete':
			    $id = (int)$this->get('id');
			    $this->mgroup->delete('id=' . $id);
				$this->adminMsg($this->getCacheCode('member') . lang('success'), url('admin/member/group/'), 3, 1, 1);
				break;
		    default:
			    if ($this->post('submit_order') && $this->post('form')=='order') {
					foreach ($_POST as $var=>$value) {
						if (strpos($var, 'order_')!==false) {
							$id = (int)str_replace('order_', '', $var);
							$this->mgroup->update(array('listorder'=>$value), 'id=' . $id);
						}
					}
					$this->cacheAction(1);
				} elseif ($this->post('submit_del') && $this->post('form')=='del') {
					foreach ($_POST as $var=>$value) {
						if (strpos($var, 'del_')!==false) {
							$id = (int)str_replace('del_', '', $var);
							$this->mgroup->delete('id=' . $id);
						}
					}
					$this->cacheAction(1);
				}
				$page     = (int)$this->get('page');
		        $page     = (!$page) ? 1 : $page;
	            $pagelist = $this->instance('pagelist');
				$pagelist->loadconfig();
				$total    = $this->mgroup->count('member_group');
	            $pagesize = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
				$url      = url('admin/member/group', array('page'=>'{page}'));
				$select   = $this->mgroup->page_limit($page, $pagesize)->order(array('listorder ASC', 'id DESC'));
				$data     = $select->select();
				$pagelist = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
				$this->view->assign(array(
					'list'     => $data,
					'pagelist' => $pagelist,
				));
				$this->view->display('admin/member_group_list');
		}
	}
    
	/*
	 * 配置信息
	 */
    public function configAction() {
        $type   = $this->get('type') ? $this->get('type') : 'reg';
	    $member = $this->cache->get('member');
        if ($this->post('submit')) {
		    $data  = $this->post('data');
            $data['reg_tpl'] = stripslashes($data['reg_tpl']);
            $data['pass_tpl'] = stripslashes($data['pass_tpl']);
            $data['group_tpl'] = stripslashes($data['group_tpl']);
			$data['username_pattern'] = stripslashes($data['username_pattern']);
			$oauth = $this->post('oauth');
			if ($data['uc_use']) {
			    $m = self::load_config('database');
				$s = '<?php ' . PHP_EOL . '/* UCenter配置 */' . PHP_EOL
				. stripslashes($data['uc_config']) 
				. PHP_EOL . '/* IdeaCMS配置 */' . PHP_EOL
				. '$dbhost    = \'' . $m['host'] . ':' . $m['port'] . '\';' . PHP_EOL
				. '$dbuser    = \'' . $m['username'] . '\';' . PHP_EOL
				. '$dbpw      = \'' . $m['password'] . '\';' . PHP_EOL
				. '$dbname    = \'' . $m['dbname'] . '\';' . PHP_EOL
				. '$pconnect  = 0;' . PHP_EOL
				. '$tablepre  = \'' . $m['prefix'] . '\';' . PHP_EOL
				. '$dbcharset = \'' . $m['charset'] . '\';' . PHP_EOL
				. '/* 同步登录Cookie */' . PHP_EOL
				. '$cookiedomain = \'' . SYS_COOKIE_DOMAIN . '\';' . PHP_EOL
				. '$cookiepath   = \'/\';' . PHP_EOL
				. '$cookiepre    = \'' . SYS_VAR_PREX . '\';' . PHP_EOL
				. '$cookiecode   = \'' . SITE_MEMBER_COOKIE . '\';' . PHP_EOL
				. '?>';
				$file = EXTENSION_DIR . 'ucenter' . DIRECTORY_SEPARATOR . 'config.inc.php';
				if (!file_put_contents($file, $s)) $this->adminMsg(lang('a-mem-2', array('1' => $file)));
			}
            $this->cache->set('member', array_merge($data, array('oauth' => $oauth)));
            $this->adminMsg(lang('success'), url('admin/member/config', array('type' => $type)), 3, 1, 1);
        }
        $this->view->assign(array(
			'type'        => $type,
            'data'        => $member,
            'string'      => $string,
			'membermodel' => $this->membermodel,
			'membergroup' => $this->membergroup
        ));
        $this->view->display('admin/member_config');
	}
	
	/*
	 * 修改资料
	 */
	public function editAction() {
	    $id     = (int)$this->get('id');
		$member = $this->member->find($id);
		if (empty($member)) $this->adminMsg(lang('a-mem-3'));
		$model  = $this->membermodel[$member['modelid']];
		if (empty($model)) $this->adminMsg(lang('a-mem-4'));
		$info   = $this->model($model['tablename']);
		$_data  = $info->find($id);
		if ($this->isPostForm()) {
		    $data = $this->post('data');
			if ($this->post('password')) $data['password'] = md5(md5($this->post('password')) . $member['salt'] . md5($this->post('password')));
			foreach ($data as $i=>$t) {
				if (is_array($t)) $data[$i] = array2string($t);
			}
			$this->member->update($data, 'id=' . $id);
			if ($_data) {
			    $info->update($data, 'id=' . $id); //修改附表内容
			} else {
				$data['id'] = $id;
				$info->insert($data); //新增附表内容
			}
			$this->adminMsg(lang('success'), url('admin/member/edit', array('id' => $id)), 3, 1, 1);
		}
		$fields   = $this->membermodel[$member['modelid']]['fields'];
		$count    = array();
		$count[0] = $this->member->count('member', null, '1');
		$count[1] = $this->member->count('member', null, 'status=1');
		$count[2] = $this->member->count('member', null, 'status=0');
		$oauth    = $this->model('oauth');
		$odata    = $oauth->where('username=?', $member['username'])->select();
		$this->view->assign(array(
			'id'			=> $id,
			'info'			=> $_data,
            'data'			=> $member,
			'model'			=> $model,
			'group'			=> $this->membergroup,
			'count'			=> $count,
			'oauth'			=> $odata,
			'data_fields'	=> $this->getFields($fields, $_data)
        ));
        $this->view->display('admin/member_edit');
	}
	
	/*
	 * 注册会员
	 */
	public function regAction() {
		if ($this->isPostForm()) {
			$addall  = $this->post('addall');
			$modelid = (int)$this->post('modelid');
		    if (!$modelid) $this->adminMsg(lang('a-mem-5'));
			if ($addall) {
			    //批量
				$data	= $this->post('members');
				if (empty($data)) $this->adminMsg(lang('a-mem-6'));
				$data	= explode(chr(13), $data);
				$y = $n = 0;
				foreach ($data as $val) {
				    list($username, $password, $email) = explode(' ', $val);
					$email    = trim($email);
					$username = trim($username);
					$password = trim($password);
					if (empty($username) || empty($password) || empty($email)) {
					    $n ++;
					} elseif (!$this->is_username($username)) {
					    $n ++;
					} elseif (!check::is_email($email)) {
					    $n ++;
					} else {
					    $row1 = $this->member->getOne('username=?', $username, 'id');
						$row2 = $this->member->getOne('email=?', $email, 'id');
						if (empty($row1) && empty($row2)) {
						    $salt   = substr(md5(rand(0, 999)), 0, 10);
						    $insert = array(
								'salt'     => $salt,
								'regip'    => client::get_user_ip(),
								'email'    => $email,
								'status'   => $_POST['data']['status'],
								'regdate'  => time(),
								'groupid'  => 1,
								'modelid'  => $modelid,
								'loginip' => '',
								'logintime' => 0,
								'lastloginip' => '',
								'lastlogintime' => 0,
								'nickname' => '',
								'randcode' => 0,
								'credits' => 0,
								'username' => $username,
								'password' => md5(md5($password) . $salt . md5($password))
							);
							if ($this->member->insert($insert)) {
								$y ++;
							} else {
						        $n ++;
							}
						} else {
						    $n ++;
						}
					}
				}
				$this->adminMsg(lang('a-mem-7', array('1' => $y, '2' => $n)), url('admin/member/index'), 3, 1, 1);
			} else {
			    //注册
				$data   = $this->post('data');
				if (empty($data['username']) || empty($data['password']) || empty($data['email'])) $this->adminMsg(lang('a-mem-8'));
				if (!$this->is_username($data['username'])) $this->adminMsg(lang('a-mem-9'));
				if (!check::is_email($data['email'])) $this->adminMsg(lang('a-mem-10'));
				$row    = $this->member->getOne('username=?', $data['username'], 'id');
				if ($row) $this->adminMsg(lang('a-mem-11'));
				$row    = $this->member->getOne('email=?', $data['email'], 'id');
				if ($row) $this->adminMsg(lang('a-mem-12'));
				$salt   = substr(md5(rand(0, 999)), 0, 10);
				$insert = array(
					'salt'     => $salt,
					'email'    => $data['email'],
					'regip'    => client::get_user_ip(),
					'status'   => $data['status'],
					'regdate'  => time(),
					'groupid'  => 1,
				    'modelid'  => $modelid,
					'randcode' => 0,
					'credits' => 0,
					'logintime' => 0,
					'loginip' => '',
					'lastlogintime' => 0,
					'lastloginip' => '',
					'nickname' => '',
				    'username' => $data['username'],
					'password' => md5(md5($data['password']) . $salt . md5($data['password']))
				);
				if ($this->member->insert($insert)) {
				    $this->adminMsg(lang('success'), url('admin/member'), 3, 1, 1);
				} else {
				    $this->adminMsg(lang('a-mem-13'));
				}
			}
		}
		$count    = array();
		$count[0] = $this->member->count('member', null, '1');
		$count[1] = $this->member->count('member', null, 'status=1');
		$count[2] = $this->member->count('member', null, 'status=0');
		if ($this->memberconfig['uc_use'] == 1) {
			include EXTENSION_DIR . 'ucenter' . DIRECTORY_SEPARATOR . 'config.inc.php';
		}
		$this->view->assign(array(
			'uc'    => $this->memberconfig['uc_use'],
			'model' => $this->membermodel,
			'count' => $count
        ));
        $this->view->display('admin/member_reg');
	}
	
	/*
	 * 短消息
	 */
	public function pmsAction() {
	    $type      = $this->get('type');
		$memberpms = $this->model('member_pms');
		switch ($type) {
		    case 'show';
			    $id   = (int)$this->get('id');
			    if ($this->isPostForm()) {
				    if ($id) $memberpms->delete('id=' . $id);
					$this->adminMsg(lang('success'), url('admin/member/pms'));
				}
                if (empty($id)) $this->adminMsg(lang('a-mem-14'));
				$data = $memberpms->find($id);
                if (empty($data)) $this->adminMsg(lang('a-mem-15'));
				$this->view->assign(array(
					'data'  => $data,
					'model' => $this->membermodel,
					'group' => $this->membergroup
				));
			    $this->view->display('admin/member_pms_show');
			    break;
		    case 'send':
			    if ($this->isPostForm()) {
					$type = $this->post('type');
					$data = $this->post('data');
					if (empty($type)) $this->adminMsg(lang('a-mem-16'));
					if (empty($data['title']) || empty($data['content'])) $this->adminMsg(lang('a-mem-17'));
					$data['sendid']   = $this->userinfo['userid'];
					$data['isadmin']  = 1;
					$data['sendname'] = $this->userinfo['username'];
					$data['sendtime'] = time();
					$sendtotal	= 0;
					if ($type == 1) {
					    //群发
						if (empty($data['modelid'])) $this->adminMsg(lang('a-mem-18'));
						$where = 'modelid=' . $data['modelid'];
						if ($data['groupid']) $where .= ' AND groupid=' . $data['groupid'];
						$list  = $this->member->from(null, 'id,username')->where($where)->select();
						foreach ($list as $row) {
						    $data['toid']   = $row['id'];
							$data['toname'] = $row['username'];
							if ($memberpms->insert($data)) $sendtotal ++;
						}
					} elseif ($type == 2) {
					    //个人
						unset($data['togroupid'], $data['tomodelid']);
						if (empty($data['tonames'])) $this->adminMsg(lang('a-mem-19'));
						$users = explode(',', $data['tonames']);
						foreach ($users as $user) {
						   $row = $this->member->from(null, 'id')->where('username=?', $user)->select(false);
						   if ($row) {
						       $data['toid'] = $row['id'];
							   $data['toname'] = $user;
							   if ($memberpms->insert($data)) $sendtotal ++;
						   }
						}
					}
					$this->adminMsg(lang('a-mem-20') . '(' . $sendtotal . ')', url('admin/member/pms'), 3, 1, 1);
				}
				$this->view->assign(array(
					'model' => $this->membermodel,
					'group' => $this->membergroup
				));
			    $this->view->display('admin/member_pms_send');
			    break;
			default:
			    if ($this->isPostForm()) {
					$ids  = $this->post('ids');
					$ids  = implode(',', $ids);
					if ($ids) $memberpms->delete('id IN(' . $ids . ')');
				}
				$page     = $this->get('page') ? (int)$this->get('page') : 1;
	            $pagelist = $this->instance('pagelist');
				$pagelist->loadconfig();
				$where    = null;
				if ($this->get('toid')) $where = 'toid=' . $this->get('toid');
				$total    = $memberpms->count('member_pms', 'id', $where);
	            $pagesize = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
				$url      = url('admin/member/pms', array('page' => '{page}'));
				if ($this->get('toid')) $url = url('admin/member/pms', array('toid' => $this->get('toid'), 'page' => '{page}'));
				$select   = $memberpms->page_limit($page, $pagesize)->order('sendtime DESC');
				if ($this->get('toid')) $select->where('toid=' . $this->get('toid'));
				$data     = $select->select();
				$pagelist = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
				$this->view->assign(array(
					'list'     => $data,
					'model'    => $this->membermodel,
					'group'    => $this->membergroup,
					'pagelist' => $pagelist
				));
			    $this->view->display('admin/member_pms_list');
			    break;
		}
	}
	
	/**
	 * 删除会员
	 */
	public function delAction() {
	    $id   = (int)$this->get('id');
	    if (empty($id)) $this->adminMsg(lang('a-mem-14'));
		$data = $this->member->find($id);
	    if (empty($data)) $this->adminMsg(lang('a-mem-3'));
		//删除内容表信息
		$list = $this->content->from(null, 'id,catid')->where('sysadd=0 and userid=' . $id)->select();
		foreach ($list as $t) {
		    $this->content->del($t['id'], $t['catid']);
		}
		//删除会员
		$this->member->delete('id=' . $id);
		//删除模型数据
		$table = $this->membermodel[$data['modelid']]['tablename'];
		if ($table) {
			$model = $this->model($table);
			$model->delete('id=' . $id);
		}
		//删除会员统计表
		$count = $this->model('member_count');
		$count->delete('id=' . $id);
		//删除一键登录
		$oauth = $this->model('oauth');
		$oauth->delete('username=?', $data['username']);
		//删除短消息
		$pms   = $this->model('member_pms');
		$pms->delete("sendid=" . $id . " AND sendname='" . $data['username'] . "'");
		//删除收藏夹
		$favorite = $this->model('favorite');
		$favorite->delete('userid=' . $id);
		//删除支付应用
		if (plugin('pay')) {
		    $pay  = $this->plugin_model('pay', 'pay_data');
            $pay->delete('userid=' . $id);			
		}
		//删除会员付费应用
		if (plugin('vip')) {
		    $vip  = $this->plugin_model('vip', 'vip');
            $vip->delete('userid=' . $id);			
		}
		//删除关联表单
		$model = $this->get_model('form');
		if ($model) {
		    foreach ($model as $m) {
			    $db = $this->model($m['tablename']);
		        $db->delete('userid=' . $id);
			}
		}
		//删除会员扩展
		$model = $this->cache->get('model_member_extend');
		if ($model) {
		    foreach ($model as $m) {
			    $db = $this->model($m['tablename']);
		        $db->delete('userid=' . $id);
			}
		}
		//删除会员附件目录
		$path = 'uploadfiles/member/' . $id . '/';
		if (file_exists($path)) $this->delDir($path);
		$this->adminMsg(lang('success'), url('admin/member'), 3, 1, 1);
	}
	
	/**
	 * 会员组缓存
	 */
	public function cacheAction($show=0) {
	    $data  = $this->mgroup->order(array('listorder ASC', 'id DESC'))->select();
		$cache = array(); 
		foreach ($data as $t) {
			$cache[$t[id]] = $t;
		}
		$this->cache->set('membergroup', $cache);
		$show or $this->adminMsg(lang('a-update'), url('admin/member/group/'), 3, 1, 1);
	}
	
	/*
	 * 会员扩展
	 */
	public function extendAction() {
		$mid	= (int)$this->get('modelid');
		$type	= $this->get('type');
		$model	= $this->cache->get('model_member_extend');
		$model	= $model[$mid];
		$touser	= (int)$this->get('touserid');
		if (empty($model)) $this->adminMsg(lang('a-mod-168', array('1'=>$mid)));
		$table	= $this->model($model['tablename']);
		switch ($type) {
		    case 'set':	//参数设置
				if ($this->isPostForm()) {
					$cfg	= $this->post('setting');
					$data	= $this->post('data');
					$field	= array();
					if ($cfg['member']['field']) {
						foreach ($cfg['member']['field'] as $c => $t) {
							if ($t) $field[]	= $c;
						}
						$cfg['member']['field']	= $field;
					}
					$cfg	= array_merge($model['setting'], $cfg);
					$model	= $this->model('model');
					$model->update(array('setting'	=> array2string($cfg)), 'modelid=' . $mid);
					$this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/member/extend', array('modelid'=>$mid,'type'=>'set','touserid'=>$touser,'typeid'=>$this->post('typeid'))), 3, 1, 1);
				}
				$tpl		= 'admin/member_extend_config';
				$list_code	= '{list table=' . $model['tablename'] . ' order=updatetime num=10}' . PHP_EOL
				. 'id：{$t[\'id\']}' . PHP_EOL
				. '{/list}' . PHP_EOL . '<!-- '. lang('a-for-12') . ' -->' . PHP_EOL . PHP_EOL
				. '{sql:' . $model['tablename'] . ' where("'. lang('a-mod-183') . '")->select(false);}' 
				. PHP_EOL . '<!-- '. lang('a-mod-184') . ' -->' . PHP_EOL . PHP_EOL
				. '{url(\'member/extend\', array(\'modelid\'=>' . $mid . ',\'touserid\'=>' . lang('a-mod-187') . '))}'
				. PHP_EOL . '<!-- '. lang('a-mod-185') . ' -->'
				;
				$func_code	= 'function mycallback($msg, $url, $state) {' . PHP_EOL
				. '	/*' . lang('a-mod-193') . '*/' . PHP_EOL
				. '}' . PHP_EOL . '$msg ：'. lang('a-mod-194') . PHP_EOL
				. '$url ：' . lang('a-mod-195') . PHP_EOL
				. '$state ：'. lang('a-mod-196') . PHP_EOL
				. lang('a-mod-197');
				$this->view->assign(array(
					'typeid'	=> $this->get('typeid') ? $this->get('typeid') : 1,
					'list_code'	=> $list_code,
					'func_code'	=> $func_code,
					'rolemodel' => $this->user->get_role_list(),
				));
				break;
			case 'edit':	//修改
				$id		= (int)$this->get('id');
				$data	= $table->find($id);
				if (empty($data)) $this->adminMsg(lang('m-con-29'));
				if ($this->isPostForm()) {
					//模型投稿权限验证
					if ($this->adminPost($model['setting']['auth'])) $this->adminMsg(lang('a-cat-100', array('1'=>$this->userinfo['rolename'])));
					$data = $this->post('data');
					$this->checkFields($this->model['fields'], $data, 1);
					$data['updatetime'] = time();
					$table->update($data, 'id=' . $id);
					$this->adminMsg(lang('success'), '', 3, 1, 1);
				}
				$this->view->assign(array(
					'data'  	=> $data,
					'fields' 	=> $this->getFields($model['fields'], $data, $model['setting']['member']['field']),
					'tomember'	=> get_member_info($data['touserid'])
				));
				$tpl	= 'admin/member_extend_edit';
				break;
			default:	//列表数据
				if ($this->isPostForm() && $this->post('form') == 'search') {
					$kw		= $this->post('kw');
					$stype	= $this->post('stype');
					$userid = (int)$this->post('userid');
					$touser = (int)$this->post('touserid');
				} elseif ($this->isPostForm() && $this->post('form') == 'del' && $this->post('ids')) {
					$ids	= implode(',', $this->post('ids'));
					$table->delete('id IN(' . $ids . ')');
				} elseif ($this->isPostForm() && $this->post('form') == 'status_1' && $this->post('ids')) {
					$ids	= implode(',', $this->post('ids'));
					$table->update(array('status'=>1), 'id IN(' . $ids . ')');
				} elseif ($this->isPostForm() && $this->post('form') == 'status_0' && $this->post('ids')) {
					$ids	= implode(',', $this->post('ids'));
					$table->update(array('status'=>0), 'id IN(' . $ids . ')');
				} elseif ($this->isPostForm() && $this->post('form') == 'status_3' && $this->post('ids')) {
					$ids	= implode(',', $this->post('ids'));
					$table->update(array('status'=>3), 'id IN(' . $ids . ')');
				}
				$kw			= $kw ? $kw : $this->get('kw');
				$page		= $this->get('page') ? $this->get('page') : 1;
				$stype		= $stype ? $stype : (int)$this->get('stype');
				$userid		= $userid ? $userid : (int)$this->get('userid');
				$status		= isset($_GET['status']) ? (int)$this->get('status') : 1;
				$pagelist	= $this->instance('pagelist');
				$pagelist->loadconfig();
				$where		= '`status`=' . $status;
				if ($userid) $where .= ' AND userid=' . $userid;
				if ($touser) $where .= ' AND touserid=' . $touser;
				if ($kw && $stype && isset($model['fields']['data'][$stype])) $where .= ' AND `' . $stype . '` LIKE "%' . $kw . '%"';
				$total		= $table->count($model['tablename'], 'id', $where);
				$pagesize = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
				$urlparam = array(
					'kw'		=> $kw,
					'page'		=> '{page}',
					'type'		=> $type,
					'stype'		=> $stype,
					'status'	=> $status,
					'userid'	=> $userid,
					'modelid'	=> $mid
				);
				$data		= $table->page_limit($page, $pagesize)->where($where)->order(array('updatetime DESC', 'id DESC'))->select();
				$pagelist	= $pagelist->total($total)->url(url('admin/member/extend', $urlparam))->num($pagesize)->page($page)->output();
				$this->view->assign(array(
					'kw'		=> $kw,
					'page'		=> $page,
					'list'		=> $data,
					'join'		=> empty($this->join) ? 0 : 1,
					'count'		=> $count,
					'status'	=> $status,
					'pagelist'	=> $pagelist,
				));
				$tpl	= 'admin/member_extend_list';
				break;
		}
		$count		= array();
		$count[1]	= $table->count($model['tablename'], null, 'status=1');
		$count[0]	= $table->count($model['tablename'], null, 'status=0');
		$count[2]	= $table->count($model['tablename'], null, 'status=2');
		$count[3]	= $table->count($model['tablename'], null, 'status=3');
		if (isset($total)) $count[$status]	= $total;
		$this->view->assign(array(
			'count'		=> $count,
			'model'     => $model,
			'modelid'	=> $mid,
			'touserid'	=> $touser
		));
		$this->view->display($tpl);
	}
	
	/*
	 * 连接测试
	 */
    public function testAction() {
	    $host     = isset($_GET['host']) && trim($_GET['host']) ? trim($_GET['host']) : exit('0');
		$username = isset($_GET['username']) && trim($_GET['username']) ? trim($_GET['username']) : exit('0');
		if (@mysql_connect($host, $username, $password)) {
			exit('1');
		} else {
			exit('0');
		}
    }
	
	/**
	 * Email是否重复检查
	 */
	public function ajaxemailAction() {
	    $email = $this->post('email');
		if (!check::is_email($email)) exit('<b><font color=red>' . lang('a-mem-21') . '</font></b>');
	    $id    = $this->post('id');
	    if (empty($email)) exit('<b><font color=red>' . lang('a-mem-22') . '</font></b>');
	    $where = $id ? "email='" . $email . "' and id<>" . $id : "email='" . $email . "'";
	    $data  = $this->member->getOne($where); 
	    if ($data) exit('<b><font color=red>' . lang('a-mem-23') . '</font></b>');
	    exit('<b><font color=green>√</font></b>');
	}
	
	/**
	 * username是否重复检查
	 */
	public function ajaxusernameAction() {
	    $name  = $this->post('username');
		if (!$this->is_username($name)) exit('<b><font color=red>' . lang('a-mem-24') . '</font></b>');
	    if (empty($name)) exit('<b><font color=red>' . lang('a-mem-25') . '</font></b>');
	    $data  = $this->member->getOne('username=?', $name, 'id'); 
	    if ($data) exit('<b><font color=red>' . lang('a-mem-11') . '</font></b>');
	    exit('<b><font color=green>√</font></b>');
	}
	
	/**
	 * 补填空格
	 */
	private function setspace($var) {
	    $len = strlen($var) + 2;
	    $cha = 25 - $len;
	    $str = '';
	    for ($i = 0; $i < $cha; $i ++) {
	        $str .= ' ';
	    }
	    return $str;
	}
}