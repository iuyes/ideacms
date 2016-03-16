<?php

class Member extends Common {
    
	public function __construct() {
		parent::__construct();
        if ($this->config['SYS_MEMBER']) {
            $this->adminMsg('系统禁止了会员功能');
        }
	}
	
	/**
	 * 前台登陆检查
	 */
	protected function isLogin($return=0) {
	    if ($this->memberinfo) {
		    if (empty($this->memberedit) && $this->controller != 'info') {
                $this->memberMsg(lang('m-com-0'), url('member/info/edit/'));
            }
			//会员组升级检测
			$this->groupUpdate();
			return false;
		}
		if ($return) {
            return true;
        }
		$back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url('member/');
		$this->redirect(url('member/login', array('back' => urlencode($back))));
	}
	
	/**
	 * 加载一键登录
	 */
	protected function loadOauth($name='') {
	    if (empty($this->memberconfig['isoauth'])) return false;
	    if (empty($name)) {
		    $name = $this->session->get('oauth_name');
			if (empty($name)) return false;
		}
	    if (isset($this->memberconfig['oauth'][$name])) {
		    $file = EXTENSION_DIR . 'oauth' . DIRECTORY_SEPARATOR . $name . '.php';
			if (!file_exists($file)) exit(lang('m-com-1', array('1'=>$name)));
			require $file;
			$this->session->set('oauth_name', $name); //注册session
			return $this->memberconfig['oauth'][$name];
		}
		return false;
	}
	
	/**
	 * 会员组升级
	 */
	protected function groupUpdate() {
	    if (empty($this->memberinfo))  return false;
	    if (empty($this->membergroup)) return false;
		$group   = array();
		$update	 = 0;
		$credit  = $this->memberinfo['credits'];
		$groupid = $this->memberinfo['groupid'];
		if (plugin('vip')) {	//会员组付费应用
			$vip = $this->plugin_model('vip', 'vip');
			$row = $vip->find($this->memberinfo['id']);	//查询数据
			if ($row) {	//存在付费组中，判断是否到期
				if ($row['endtime'] - time() > 0) {
					return false;	//未到期直接跳过
				} else {
					$vip->delete('userid=' . $this->memberinfo['id']);	//删除该会员数据
					$update	= 1; //更新标识
				}
			}
		}
		//属于非自动升级会员组直接跳过
		if ($update == 0 && $this->membergroup[$groupid]['auto']) return false;
		foreach ($this->membergroup as $t) {
		    $group[$t['id']] = $t['credits'];
		}
		asort($group);
		foreach ($group as $gid => $g) {
		    if ($credit >= $g) {
			    $groupid = $gid;
			}
		}
		if ($groupid != $this->memberinfo['groupid']) {
		    //升级
			$this->member->update(array('groupid' => $groupid), 'id=' . $this->memberinfo['id']);
		    if (!isset($this->memberconfig['email']) || $this->memberconfig['email'] == 0) return false;
			mail::set($this->site);
			$content = $this->memberconfig['group_tpl'] ? $this->memberconfig['group_tpl'] : lang('m-com-2');
			$content = str_replace(array('{username}', '{groupname}', '{credit}'), array($this->memberinfo['username'], $this->membergroup[$groupid]['name'], $this->memberinfo['credits']), $content);
			mail::sendmail($this->memberinfo['email'], lang('m-com-3', array('1' => $this->memberinfo['username'])), htmlspecialchars_decode($content));
		}
	}
	
	/**
	 * 会员注册邮件通知
	 */
	protected function regEmail($data) {
	    if (empty($data))  return false;
		if (!isset($this->memberconfig['email']) || $this->memberconfig['email'] == 0) return false;
	    mail::set($this->site);
		$content = $this->memberconfig['reg_tpl'] ? $this->memberconfig['reg_tpl'] : lang('m-com-4', array('1' => $data['username']));
		$content = str_replace(array('{username}'), array($data['username']), $content);
		mail::sendmail($data['email'], lang('m-com-5', array('1' => $this->site['SITE_NAME'])), htmlspecialchars_decode($content));
	}
	
	/**
	 * 密码找回邮件通知
	 */
	protected function passEmail($username, $email) {
	    if (empty($username) || empty($email))  return false;
		$rand = md5(rand(0, 9999). microtime());
		$link = $this->get_server_name() . url('member/repass/find', array('id' => base64_encode(time() . '|' . $rand . '|' . md5($username))), 1);
		$this->member->update(array('randcode' => $rand), "username='" . $username . "'");
	    mail::set($this->site);
		$content = $this->memberconfig['pass_tpl'] ? $this->memberconfig['pass_tpl'] : lang('m-com-6', array('1' => $username, '2' => $link));
		$content = str_replace(array('{username}', '{link}'), array($username, $link), $content);
		return mail::sendmail($email, lang('m-com-7', array('1' => $this->site['SITE_NAME'])), htmlspecialchars_decode($content));
	}
	
}