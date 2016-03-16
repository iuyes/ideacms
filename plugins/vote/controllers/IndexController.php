<?php

class IndexController extends Plugin {
	
    public function __construct() {
        parent::__construct();
    }
	
	/*
	 * 投票
	 */
	public function postAction() {
	    $id   = $this->get('id');
		if (empty($id)) $this->adminMsg('Id无效！');
		$data = $this->vote->find($id);
		if (empty($data)) $this->adminMsg('投票主题不存在！');
		$data['options']  = string2array($data['options']);
		$data['votedata'] = string2array($data['votedata']);
		$cookiename       = md5($id . client::get_user_ip());
	    if ($this->isPostForm()) {
		    if (cookie::is_set($cookiename)) $this->msg('您已经投过票了！', 0 , 1);
			if (!$data['status']) $this->msg('该投票主题已经失效，不能进行投票！', 0 , 1);
		    $vote = $this->post('vote_id');
			if (empty($vote)) $this->msg('请选择投票选项！', 0 , 1);
			foreach ($data['options'] as $k=>$t) {
			    if ($data['ischeckbox']) {
				    if (in_array($k, $vote)) {
					    $data['votedata'][$k] ++;
						$data['votenums'] ++;
					}
				} else {
				    if ($vote == $k) {
					    $data['votedata'][$k] ++;
						$data['votenums'] ++;
					}
				}
			}
			$this->vote->update(array('votedata'=>array2string($data['votedata']), 'votenums'=>$data['votenums']), 'id=' . $id);
			cookie::set($cookiename, 1, 24*3600);
			$this->msg('投票成功！', purl('index/show', array('id'=>$id)));
		}
		if (is_file(VIEW_DIR . SYS_THEME_DIR . 'vote_post.html')) {
		    $this->view->assign($data);
			$this->view->display('vote_post');
		} else {
			$this->assign($data);
			$this->display('post');
		}
	}
	
	/*
	 * 查看投票
	 */
	public function showAction() {
	    $id   = $this->get('id');
		if (empty($id)) $this->adminMsg('Id无效！');
		$data = $this->vote->find($id);
		if (empty($data)) $this->adminMsg('投票主题不存在！');
		$data['options']  = string2array($data['options']);
		$data['votedata'] = string2array($data['votedata']);
		if (is_file(VIEW_DIR . SYS_THEME_DIR . 'vote_show.html')) {
		    $this->view->assign($data);
			$this->view->display('vote_show');
		} else {
			$this->assign($data);
			$this->display('show');
		}
	}
	
}