<?php

class InfoController extends Member {

    private $memberdata;
    
    public function __construct() {
		parent::__construct();
		$this->isLogin(); //登录验证
		$this->memberdata = $this->model($this->membermodel[$this->memberinfo['modelid']]['tablename']);
		$this->view->assign('navigation', array(
		    'edit' => array('name'=> lang('m-inf-0'), 'url'=> url('member/info/edit')),
		    'avatar' => array('name'=> lang('m-inf-1'), 'url'=> url('member/info/avatar')),
		    'password' => array('name'=> lang('m-inf-2'), 'url'=> url('member/info/password')),
		    'oauth' => array('name'=> lang('m-inf-3'), 'url'=> url('member/info/oauth')),
		    'favorite' => array('name'=> lang('m-inf-4'), 'url'=> url('member/info/favorite')),
		));
	}
	
	/**
	 * 资料修改
	 */
	public function editAction() {
	    $modelid = $this->memberinfo[modelid];
	    $fields = $this->membermodel[$modelid]['fields'];
	    if ($this->isPostForm()) {
	        $data = $this->input->post('data', TRUE);
			$this->checkFields($fields, $data, 2);
			$this->member->update(array('nickname'=>$data['nickname']), 'id=' . $this->memberinfo['id']);
			$memberdata = $this->memberdata->find($this->memberinfo['id']);
			foreach ($data as $i=>$t) {
				if (is_array($t)) {
                    $data[$i] = array2string($t);
                } else {
					$data[$i] = safe_replace($t);
				}
				
			}
			if ($memberdata) {
			    //修改附表内容
				$this->memberdata->update($data, 'id=' . $this->memberinfo['id']);
			} else {
				$data['id'] = $this->memberinfo['id'];
				$this->memberdata->insert($data);
			}
			//增加会员统计表
			$count = $this->model('member_count');
			$data  = $count->find($this->memberinfo['id']);
			if (!$data) $count->insert(array('id'=>$this->memberinfo['id']));
			$this->memberMsg(lang('success'), url('member/info/edit'), 1);
	    }
	    //自定义字段
	    $data_fields = $this->getFields($fields, $this->memberinfo);
	    $this->view->assign(array(
	        'data_fields' => $data_fields,
			'meta_title'  => lang('m-inf-0') . '-' . lang('member') . '-' . $this->site['SITE_NAME'],
	    ));
	    $this->view->display('member/edit');
	}
	
	/**
	 * 头像修改
	 */
	public function avatarAction() {
	    if (empty($this->memberconfig['avatar']) && $this->isPostForm()) {
	        $data = $this->input->post('data', TRUE);
			$this->member->update(array('avatar'=> $data['avatar']), 'id=' . $this->memberinfo['id']);
			$this->memberMsg(lang('success'), url('member/info/avatar'), 1);
	    }
		$this->view->assign(array(
	        'avatar_ext_path' => EXT_PATH . 'avatar/',
			'avatar_return'   => base64_encode(url('member/info/uploadavatar')),
			'meta_title'      => lang('m-inf-1') . '-' . lang('member') . '-' . $this->site['SITE_NAME'],
	    ));
	    $this->view->display('member/avatar');
	}
	
	/**
	 *  上传头像处理
	 *  传入头像压缩包，解压到指定文件夹后删除非图片文件
	 */
	public function uploadavatarAction() {

		$post = file_get_contents('php://input');
		if (!$post) {
            exit('error');
        }
		//创建图片存储文件夹
		$dir = APP_ROOT . 'uploadfiles/member/' . $this->memberinfo['id'] . '/';
		if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        // 创建图片存储的临时文件夹
        $temp = APP_ROOT.'cache/attack/'.md5(uniqid().rand(0, 9999)).'/';
        if (!file_exists($temp)) {
            mkdir($temp, 0777);
        }
		//存储flashpost图片
		$filename = $temp . 'avatar.zip';
		file_put_contents($filename, $post);
        // 存在安全隐患后期修复
		//解压缩文件
		$zip = $this->instance('pclzip');
		$zip->PclFile($filename);
		if ($zip->extract(PCLZIP_OPT_PATH, $temp) == 0) {
			exit('Error : ' . $zip->zip(true));
        } elseif (!is_file($temp.'30x30.jpg')) {
			exit('文件存储失败');
		} elseif (!is_dir($dir)) {
			exit('文件创建失败');
		}
		//判断文件安全，删除压缩包和非jpg图片
		$avatararr = array('180x180.jpg', '30x30.jpg', '45x45.jpg', '90x90.jpg');
		if($handle = opendir($temp)) {
		    while(false !== ($file = readdir($handle))) {
				if(strlen($file)>5) {
					if(!in_array($file, $avatararr)) {
						@unlink($temp . $file);
					} else {
						copy($temp.$file, $dir.$file);
						@unlink($temp . $file);
					}
				}
		    }
		    closedir($handle);    
		} else {
			exit('0');
		}
		@unlink($filename);
        @rmdir($temp);
		//更新用户头像字段
		$this->member->update(array('avatar'=> 'uploadfiles/member/' . $this->memberinfo['id'] . '/' . '90x90.jpg'), 'id=' . $this->memberinfo['id']);
		exit('1');
	}
	
	/**
	 * 密码修改
	 */
	public function passwordAction() {
	    if ($this->isPostForm()) {
	        $data = $this->post('data');
			if (empty($data['password2'])) $this->memberMsg(lang('m-inf-5'));
			if ($data['password2'] != $data['password3']) $this->memberMsg(lang('m-inf-6'));
			if ($this->memberconfig['uc_use']) {
			    $ucresult = uc_user_edit($this->memberinfo['username'], $data['password1'], $data['password2'], null, 1);
				if ($ucresult != -1) {
					$this->memberMsg(lang('m-inf-7'));
				} elseif($ucresult == -4) {
					$this->memberMsg(lang('m-inf-8'));
				} elseif($ucresult == -5) {
					$this->memberMsg(lang('m-inf-9'));
				} elseif($ucresult == -6) {
					$this->memberMsg(lang('m-inf-10'));
				}
			} elseif (md5(md5($data['password1']) . $this->memberinfo['salt'] . md5($data['password1'])) != $this->memberinfo['password']) {
			    $this->memberMsg(lang('m-inf-7'));
			}
			$this->member->update(array('password'=>md5(md5($data['password2']) . $this->memberinfo['salt'] . md5($data['password2']))), 'id=' . $this->memberinfo['id']);
			$this->memberMsg(lang('success'), url('member/info/password'), 1);
	    }
		$this->view->assign(array(
			'meta_title' => lang('m-inf-2') . '-' . lang('member') . '-' . $this->site['SITE_NAME'],
	    ));
	    $this->view->display('member/password');
	}
	
	/**
	 * 一键登录
	 */
	public function oauthAction() {
	    $oauth = $this->model('oauth');
		$data  = $oauth->where('username=?', $this->memberinfo['username'])->select();
		$this->view->assign(array(
			'list'		 => $data,
			'listdata'   => $data, 
			'meta_title' => lang('m-inf-3') . '-' . lang('member') . '-' . $this->site['SITE_NAME']
	    ));
	    $this->view->display('member/oauth');
	}
	
	/**
	 * 解除一键登录绑定
	 */
	public function jieAction() {
	    $id = (int)$this->get('id');
	    $oauth = $this->model('oauth');
		$oauth->delete('id=' . $id . ' and username=?', $this->memberinfo['username']);
		$this->memberMsg(lang('success'), url('member/info/oauth'), 1);
	}
	
	/**
	 * 收藏夹
	 */
	public function favoriteAction() {
		$favorite = $this->model('favorite');
	    if ($this->isPostForm()) {
            $ids = '';
            foreach ($this->post('ids') as $i) {
                $ids.= ','.(int)$i;
            }
            $ids = trim($ids, ',');
			if (empty($ids)) {
                $this->memberMsg(lang('m-inf-11'));
            }
			$favorite->delete('userid=' . $this->memberinfo['id'].' and id IN(' . $ids . ')');
	    }
	    $page = (int)$this->get('page');
		$page = (!$page) ? 1 : $page;
		//分页配置
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
		$where    = 'site=' . $this->siteid . ' AND userid=' . $this->memberinfo['id'];
		$total    = $favorite->count('favorite', 'id', $where);
	    $pagesize = isset($this->memberconfig['pagesize']) && $this->memberconfig['pagesize'] ? $this->memberconfig['pagesize'] : 8;
	    $data     = $favorite->page_limit($page, $pagesize)->where($where)->order('adddate desc')->select();
	    $pagelist = $pagelist->total($total)->url(url('member/info/favorite'))->num($pagesize)->page($page)->output();
	    $this->view->assign(array(
		    'data'       => $data,
		    'list'       => $data,
			'pagelist'   => $pagelist,
		    'meta_title' => lang('m-inf-4').'-'.lang('member').'-'.$this->site['SITE_NAME'],
		));
	    $this->view->display('member/favorite');
	}
	
}