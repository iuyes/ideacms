<?php

class FormController extends Common {

    private $model;
	private $form;
	private $modelid;
	
	public function __construct() {
        parent::__construct();
		$model = $this->get_model('form');
		$modelid = (int)$this->get('modelid');
		if (empty($modelid)) {
            $this->msg(lang('for-0'));
        }
		$this->model = $model[$modelid];
		if (empty($this->model)) {
            $this->msg(lang('for-1', array('1'=>$modelid)));
        }
		$this->form = $this->model($this->model['tablename']);
		$this->modelid = $modelid;
		$this->view->assign(array(
			'table' => $this->model['tablename'],
			'modelid' => $this->modelid,
		    'form_name' => $this->model['modelname']
		));
	}
	
	/*
	 * 提交页面
	 */
	public function postAction() {
	    $cid = (int)$this->get('cid');
		$backurl = urldecode($this->get('backurl'));
		$joinmodel = $this->cache->get('model_join_' . $this->siteid);
		$joindata = isset($joinmodel[$this->model['joinid']]) ? $joinmodel[$this->model['joinid']] : null;
		if ($joindata && empty($cid)) {
            $this->callback(lang('for-2', array('1'=>$joindata['modelname'])));
        }
		if ($joindata) { //关联内容数据
			$cdata = $this->content->getOne('id=' . $cid . ' AND modelid=' . $this->model['joinid']);
			$backurl = isset($cdata['url']) ? $cdata['url'] : $backurl;
			if (empty($cdata)) {
                $this->callback(lang('for-3', array('1'=>$joindata['modelname'], '2'=>$cid)));
            }
		}
	    if ($this->isPostForm()) {
			$data = $this->post('data');
			//会员投稿权限验证
		    if ($this->model['setting']['form']['code']
                && !$this->checkCode($this->post('code'))) {
                $this->callback(lang('for-4'));
            }
			if ($this->memberPost($this->model['setting']['auth']))	{
                $this->callback(lang('m-con-12'));
            }
			if ($this->model['setting']['form']['post'] && empty($this->memberinfo)) {
                $this->callback(lang('for-5'));
            }
			if ($this->model['setting']['form']['num'] && $this->check_num($joindata, $cid)) {
                $this->callback(lang('for-6'));
            }
			if ($this->model['setting']['form']['ip'] && $this->check_ip($joindata, $cid)) {
                $this->callback(lang('for-7',array('1'=>$this->model['setting']['form']['ip'])));
            }
			if (isset($cdata['userid']) && $this->model['setting']['form']['postme']
                && $this->memberinfo['id'] != $cdata['userid']) {
                $this->callback(lang('a-mod-153'));
            }
			if ($result = $this->checkFields($this->model['fields'], $data, 3)) {
                $this->callback($result);
            }
			$data['ip']	= client::get_user_ip();
			$data['cid'] = $cid;
			$data['status'] = empty($this->model['setting']['form']['check']) ? 1 : 0;
			$data['userid'] = empty($this->memberinfo) ? 0  : $this->memberinfo['id'];
			$data['username'] = empty($this->memberinfo) ? '' : $this->memberinfo['username'];
			$data['inputtime']= $data['updatetime'] = time();
			if ($data['id'] = $this->form->set(0, $data)) {
				if (isset($this->model['setting']['form']['url']['tohtml'])
                    && $this->model['setting']['form']['url']['tohtml'] && $data['status'] == 1) {
					$this->createForm($this->modelid, $data);
				}
			    $this->callback($data['status'] ? lang('for-8') : lang('for-9'), $backurl, 1);
			} else {
			    $this->callback(lang('for-10'));
			}
		}
	    $this->view->assign(array(
			'code' => $this->model['setting']['form']['code'],
			'cdata' => $cdata,
			'fields' => $this->getFields($this->model['fields'], null, $this->model['setting']['form']['field']),
			'joindata' => $joindata,
	        'meta_title' => $this->model['setting']['form']['meta_title'],
	        'meta_keywords' => $this->model['setting']['form']['meta_keywords'],
	        'meta_description' => $this->model['setting']['form']['meta_description']
	    ));
		$this->view->display(is_file(VIEW_DIR . SYS_THEME_DIR . $this->model['categorytpl']) ? substr($this->model['categorytpl'], 0, -5) : 'post_form');
	}
	
	/*
	 * 列表页面
	 */
	public function listAction() {
	    $this->view->assign(array(
			'cid' => $this->get('cid') ? (int)$this->get('cid')  : '',
			'page' => $this->get('page') ? (int)$this->get('page') : 1,
			'urlrule' => url('form/list', array('modelid'=>$this->modelid, 'cid'=>$cid, 'page'=>'[page]')),
			'pagesize' => $this->model['setting']['form']['pagesize'],
	        'meta_title' => $this->model['setting']['form']['meta_title'],
	        'meta_keywords' => $this->model['setting']['form']['meta_keywords'],
	        'meta_description' => $this->model['setting']['form']['meta_description'],
	    ));
		$this->view->display(is_file(VIEW_DIR . SYS_THEME_DIR . $this->model['listtpl']) ? substr($this->model['listtpl'], 0, -5) : 'list_form');
	}
	
	/*
	 * 显示页面
	 */
	public function showAction() {
	    $id = (int)$this->get('id');
		if (empty($id)) {
            $this->msg(lang('for-11'));
        }
		$data = $this->form->find($id);
		if (empty($data)) {
            $this->msg(lang('for-12'));
        }
		if (!$this->userShow($data)) {
            $this->msg(lang('for-13', array('1'=>$id)));
        }
	    if (isset($this->model['fields']) && $this->model['fields']) {
            $data = $this->getFieldData($this->model, $data);
        }
		$this->view->assign($data);
	    $this->view->assign(array(
	        'meta_title' => $this->model['setting']['form']['meta_title'],
	        'meta_keywords' => $this->model['setting']['form']['meta_keywords'],
	        'meta_description' => $this->model['setting']['form']['meta_description'],
	    ));
		$this->view->display(is_file(VIEW_DIR . SYS_THEME_DIR . $this->model['showtpl']) ? substr($this->model['showtpl'], 0, -5) : 'show_form');
	}
	
	/*
	 * 同一会员（游客）提交一次
	 */
	private function check_num($joindata, $cid) {
		if (empty($this->memberinfo)) {
		    $select = $this->form->from(null, 'id');
			$select->where('ip=?', client::get_user_ip());
			$select->where('userid=0 AND username=?', '');
			if ($joindata && $cid) {
                $select->where('cid=' . $cid);
            }
			$data = $select->select(false);
			if ($data) {
                return true;
            }
		} else {
		    $select = $this->form->from(null, 'id');
			$select->where('userid=?', $this->memberinfo['id']);
			$select->where('username=?', $this->memberinfo['username']);
			if ($joindata && $cid) {
                $select->where('cid=' . $cid);
            }
			$data = $select->select(false);
			if ($data) return true;
		}
		return false;
	}
	
	/*
	 * 同一IP提交间隔
	 */
	private function check_ip($joindata, $cid) {
	    $time = $this->model['setting']['form']['ip'] * 60; //秒
		$select = $this->form->from(null, 'id,inputtime');
		$select->where('ip=?', client::get_user_ip());
		if ($joindata && $cid) {
            $select->where('cid=' . $cid);
        }
		$select->order('inputtime DESC');
		$data = $select->select(false);
		if (empty($data)) {
            return false;
        }
		if (time() - $data['inputtime'] < $time) {
            return true;
        }
		return false;
	}
	
	/*
	 * 返回信息处理[callback]
	 */
	private function callback($msg, $url = '', $state = 0) {
		if ($this->model['setting']['form']['callback']
            && function_exists($this->model['setting']['form']['callback'])) {
			eval($this->model['setting']['form']['callback'] . '("' . safe_replace($msg) . '", "' . safe_replace($url) . '", ' . $state . ');');
		} else {
			$this->msg($msg, $url, 1);
		}
		exit;
	}
	
}