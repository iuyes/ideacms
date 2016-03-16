<?php

class SpaceController extends Member {
    
    public function __construct() {
		parent::__construct();
	}
	
	/**
	 * 会员资料
	 */
	public function indexAction() {
	    $id    = (int)$this->get('userid');
		$name  = $this->get('username');
		if (empty($id) && empty($name)) $this->msg(lang('m-spa-0'));
		$data  = $id ? $this->member->find($id) : $this->member->getOne('username=?', $name);
		if (empty($data)) $this->msg(lang('m-spa-1', array('1'=>$id ? '#' . $id : $name)));
		$model = $this->membermodel[$data['modelid']];
		$data['nickname'] = $data['nickname'] ? $data['nickname'] : $data['username'];
		if ($model) {
		    $table = $this->model($model['tablename']);
			$_data = $table->find($data['id']);
	        $data  = array_merge($data, $_data); //合并主表和附表
			$data  = $this->getFieldData($model, $data);
		}
		$data['avatar'] = image($data['avatar']);
		if ($this->memberconfig['uc_use'] == 1 && function_exists('uc_api_mysql')) {
			$uc = uc_api_mysql('user', 'get_user', array('username'=> $data['username']));
			if ($uc != 0) {
			    $data['uid']    = $uc[0];
				$data['avatar'] = UC_API . '/avatar.php?uid=' . $data['uid'] . '&size=middle';
			}
		}
		unset($data['password']);
		$this->view->assign($data);
		$this->view->assign(array(
			'meta_title' => lang('m-spa-2', array('1'=>$data['nickname'])) . '-' . $this->site['SITE_NAME'],
			'userid'     => $data['id'],
			'tablename'  => $model['tablename'],
			'modelname'  => $model['modelname'],
			'groupname'  => $this->membergroup[$data['groupid']]['name'],
			'page'       => $this->get('page') ? $this->get('page') : 1,
	    ));
	    $this->view->display('member/space');
	}
	
}