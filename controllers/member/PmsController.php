<?php

class PmsController extends Member {

    private $pms;
    
    public function __construct() {
		parent::__construct();
		$this->isLogin(); //登录验证//判断审核
		if (!$this->memberinfo['status']) $this->memberMsg(lang('m-con-0'));
		$this->pms   = $this->model('member_pms');
		$inbox       = $this->pms->count('member_pms', 'id', 'hasview=0 AND toid=' . $this->memberinfo['id']); //未读收件箱短信条数
		$this->view->assign('navigation', array(
		    'send'   => array('name'=> lang('m-pms-0'), 'url'=> url('member/pms/send')),
		    'inbox'  => array('name'=> lang('m-pms-1'), 'url'=> url('member/pms/inbox')),
		    'outbox' => array('name'=> lang('m-pms-2'), 'url'=> url('member/pms/outbox')),
		));
		$this->view->assign('inbox', $inbox);
	}
	
	/*
	 * 发送
	 */
	public function sendAction() {
	    if ($this->isPostForm()) {
		    $this->postCheck(); //发送数量检测
			$data = $this->post('data');
			if (empty($data['toname'])) $this->memberMsg(lang('m-pms-3'));
			if (empty($data['title']) || empty($data['content'])) $this->memberMsg(lang('m-pms-4'));
			if ($data['toname'] == $this->memberinfo['username']) $this->memberMsg(lang('m-pms-5'));
			$memberinfodata   = $this->member->from(null, 'id')->where('username=?', $data['toname'])->select(false);
		    if (!$memberinfodata) $this->memberMsg(lang('m-pms-6'));
			$data['toid']     = $memberinfodata['id'];
			$data['sendtime'] = time();
			$data['sendname'] = $this->memberinfo['username'];
			$data['sendid']   = $this->memberinfo['id'];
			$data['isadmin']  = 0;
			$data['hasview']  = 0;
			$id = $this->pms->insert($data);
			if (empty($id)) $this->memberMsg(lang('m-pms-7'));
			//增加会员统计数量
			$this->pms->query('UPDATE ' . $this->member->prefix . 'member_count SET pms=pms+1 WHERE id=' . $this->memberinfo['id']);
			$this->memberMsg(lang('success'), url('member/pms/outbox'), 1);
	    }
		$this->view->assign(array(
		    'isinbox'    => 1,
		    'meta_title' => lang('m-pms-0') . '-' . lang('member') . '-' . $this->site['SITE_NAME'],
		));
	    $this->view->display('member/pms_send');
	}
	
	/*
	 * 收件箱
	 */
	public function inboxAction() {
		$where   = 'toid=' . $this->memberinfo['id'] . ' and todel=0';
	    if ($this->isPostForm()) {
		    $ids = $this->post('ids');
			if (empty($ids)) $this->memberMsg(lang('m-inf-11'));
			foreach ($ids as $id) {
			    $id  = (int)$id;
				if ($id) {
					$row = $this->pms->find($id, 'todel,senddel');
					if ($row) {
						if ($row['senddel']) {
							$this->pms->delete($where . ' and id=' . $id); //删除
						} else {
							$this->pms->update(array('todel'=>1), $where . ' and id=' . $id);
						}
					}
				}
			}
	    }
	    $page     = (int)$this->get('page');
		$page     = (!$page) ? 1 : $page;
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
		$total    = $this->pms->count('member_pms', 'id', $where);
	    $pagesize = isset($this->memberconfig['pagesize']) && $this->memberconfig['pagesize'] ? $this->memberconfig['pagesize'] : 8;
	    $data     = $this->pms->page_limit($page, $pagesize)->where($where)->order(array('hasview ASC', 'isadmin DESC', 'sendtime DESC'))->select();
	    $pagelist = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
	    $this->view->assign(array(
		    'data'       => $data,
		    'list'       => $data,
			'pagelist'   => $pagelist,
		    'meta_title' => lang('m-pms-1') . '-' . lang('member') . '-' . $this->site['SITE_NAME'],
			'countinfo'  => $this->getPosts(),
		));
	    $this->view->display('member/pms_list');
	}
	
	/*
	 * 已发送
	 */
	public function outboxAction() {
		$where   = 'sendid=' . $this->memberinfo['id'] . ' and sendname="' . $this->memberinfo['username'] . '" and senddel=0 and isadmin=0';
	    if ($this->isPostForm()) {
		    $ids = $this->post('ids');
			if (empty($ids)) $this->memberMsg(lang('m-inf-11'));
			foreach ($ids as $id) {
			    $id  = (int)$id;
				if ($id) {
					$row = $this->pms->find($id, 'todel,senddel');
					if ($row) {
						if ($row['todel']) {
							//删除
							$this->pms->delete($where . ' and id=' . $id);
						} else {
							$this->pms->update(array('senddel'=>1), $where . ' and id=' . $id);
						}
					}
				}
			}
	    }
	    $page     = (int)$this->get('page');
		$page     = (!$page) ? 1 : $page;
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
		$total    = $this->pms->count('member_pms', 'id', $where);
	    $pagesize = isset($this->memberconfig['pagesize']) && $this->memberconfig['pagesize'] ? $this->memberconfig['pagesize'] : 8;
	    $data     = $this->pms->page_limit($page, $pagesize)->where($where)->order('sendtime DESC')->select();
	    $pagelist = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
	    $this->view->assign(array(
		    'data'       => $data,
		    'list'       => $data,
			'pagelist'   => $pagelist,
		    'meta_title' => lang('m-pms-2') . '-' . lang('member') . '-' . $this->site['SITE_NAME'],
			'countinfo'  => $this->getPosts(), 
		));
	    $this->view->display('member/pms_list');
	}
	
	/*
	 * 阅读
	 */
	public function readAction() {
	    $id   = (int)$this->get('id');
		if (empty($id)) $this->memberMsg(lang('m-pms-8'));
		$data = $this->pms->find($id); 
		if (empty($data)) $this->memberMsg(lang('m-pms-9'));
		if (($data['sendname'] == $this->memberinfo['username'] && $data['sendid'] == $this->memberinfo['id'] && $data['isadmin'] == 0) || ($data['toname'] == $this->memberinfo['username'] && $data['toid'] == $this->memberinfo['id'])) {
		    if ($data['toid'] == $this->memberinfo['id'] && !$data['hasview']) $this->pms->update(array('hasview'=>1), 'id=' . $id);
			$url  = $data['sendid'] == $this->memberinfo['id'] && $data['sendname'] == $this->memberinfo['username'] && $data['isadmin'] == 0 ? url('member/pms/outbox') : url('member/pms/inbox');
			$this->view->assign(array(
				'data'       => $data,
				'meta_title' => lang('m-pms-10') . '-' . lang('member') . '-' . $this->site['SITE_NAME'],
				'backurl'    => $url,
			));
			$this->view->display('member/pms_read');
		} else {
		    $this->memberMsg(lang('m-pms-9'));
		}
	}
	
	/**
	 * 用户名验证
	 */
	public function checkuserAction() {
	    $username = $this->get('username');
		if (empty($username)) exit(lang('m-pms-11'));
		$strlen   = strlen($username);
		if (!preg_match('/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/', $username)) {
			exit(lang('m-pms-12'));
		} elseif ( 20 < $strlen || $strlen < 2 ) {
			exit(lang('m-pms-12'));
		}
		if ($username == $this->memberinfo['username']) exit(lang('m-pms-5'));
		$member = $this->member->from(null, 'id')->where('username=?', $username)->select(false);
		if (!$member) exit(lang('m-pms-13'));
	}
	
	/**
	 * 发送数量检测
	 */
	private function postCheck() {
		$count = $this->model('member_count');
		$data  = $count->find($this->memberinfo['id']);
		if (empty($data)) return true;
		if (date('Y-m-d') != date('Y-m-d', $data['updatetime'])) {
		    //重置统计数据
			$data['pms']        = 0;
			$data['updatetime'] = time();
			$count->update($data, 'id=' . $this->memberinfo['id']);
		}
		if ($data['pms'] >= $this->membergroup[$this->memberinfo['groupid']]['allowpms']) {
		    $this->memberMsg(lang('m-pms-14', array('1'=>$this->membergroup[$this->memberinfo['groupid']]['allowpms'])));
		}
	}
	
	/**
	 * 获取发送数量
	 */
	private function getPosts() {
		$count = $this->model('member_count');
		$data  = $count->find($this->memberinfo['id']);
		if (empty($data)) return true;
		if (date('Y-m-d') != date('Y-m-d', $data['updatetime'])) {
		    //重置统计数据
			$data['pms']        = 0;
			$data['updatetime'] = time();
			$count->update($data, 'id=' . $this->memberinfo['id']);
		}
		return array('post'=>$data['pms'], 'posts'=>$this->membergroup[$this->memberinfo['groupid']]['allowpms']);
	}
	
}