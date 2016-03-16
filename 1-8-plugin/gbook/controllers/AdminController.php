<?php

class AdminController extends Plugin {
    
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
    }
    
    public function indexAction() {
	    if ($this->post("submit_del") && $this->post("form")=='del') {
	        foreach ($_POST as $var=>$value) {
	            if (strpos($var, "id_")!==false) {
	                $id = (int)str_replace("id_", "", $var);
	                $this->gbook->delete('id=' . $id);
	            }
	        }
	    }
        if ($this->post("submit_status") && $this->post("form")=='status') {
	        foreach ($_POST as $var=>$value) {
	            if (strpos($var, "id_")!==false) {
	                $id = (int)str_replace("id_", "", $var);
	                $this->gbook->update(array("status"=>1), 'id=' . $id);
	            }
	        }
	    }
        $page     = (int)$this->get('page');
		$page     = (!$page) ? 1 : $page;
		$status   = (int)$this->get('status');
		$where    = $status ? '`status`=0' : null;
		//分页配置
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
	    $total    = $this->gbook->count("gbook", $where);
	    $pagesize = 8;
	    $url      = url('gbook/admin/index', array('page'=>'{page}'));
	    $this->gbook->page_limit($page, $pagesize)->order(array("addtime DESC"));
	    if ($where) $this->gbook->where("`status`=0");
	    $data     = $this->gbook->select();
	    $pagelist = $pagelist->total($total)->url($url)->num($pagesize)->page($page)->output();
	    $this->assign(array(
	        "list"     => $data,
	        "pagelist" => $pagelist,
	        "status"   => $status,
	    ));
	    $this->display("admin_list");
    }
    
    public function editAction() {
        $id   = $this->get("id");
        $data = $row = $this->gbook->find($id);
        if (empty($data)) $this->adminMsg("信息不存在");
        if ($this->post("submit")) {
            unset($data);
            $data = $this->post("data");
            $this->gbook->update($data, "id=" . $id);
            $set  = string2array($this->plugin['setting']);
            if ($set['emailto']) {
               if (empty($row['r_content']) && $data['r_content'] && check::is_email($row['email'])) {
                   //第一次回复，邮件通知留言者
                   mail::set($this->site);
                   $title    = '[' . $row['name'] . ']' . $this->site['SITE_NAME'] . "提醒您管理员已经回复了您的留言。";
	               $content  = "<H2 style='font-size:16px;font-weight:bold;'>尊敬的 " . trim($row['name']) . "，管理员回您的留言：</H2><div style='font-size:12px;padding-top:10px;'>";
	               $content .= htmlspecialchars_decode($data['content']);
	               $content .= '<HR style="MARGIN: 5px 0px"><div style="text-align:center;">此信是系统自动发出，请不要"回复"本邮件。</div> </div> ';
	               mail::sendmail($row['email'], $title, $content);
               }
            }
            //if ($this->plugin)
            $this->adminMsg("操作成功", url("gbook/admin"), 3, 1, 1);
        }
        $this->assign(array(
            "data"	=> $data,
        ));
        $this->display("admin_add");
    }
    
    public function delAction() {
        $id = $this->get("id");
        $this->gbook->delete('id=' . $id);
        $this->adminMsg("操作成功", url("gbook/admin"), 3, 1, 1);
    }
}