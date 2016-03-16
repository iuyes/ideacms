<?php

class IndexController extends Plugin {

    public function __construct() {
        parent::__construct();
    }
    
    public function postAction() {
        if ($this->isPostForm()) {
            $data = $this->post('data');
            $set  = string2array($this->plugin['setting']);
			if ($set['code'] && !$this->checkCode($this->post('code'))) $this->msg('验证码不正确！', null, 1);
            if (empty($data['name']))    $this->msg('姓名不能为空！', null, 1);
            if (empty($data['content'])) $this->msg('内容不能为空！', null, 1);
            $data['addtime'] = time();
            $data['status']  = $set['status'] ? 0 : 1;
            $this->gbook->insert($data);
            $this->msg('留言成功');
        } else {
		    $this->msg('非POST提交，来路不正确', null, 1);
		}
    }
    
}