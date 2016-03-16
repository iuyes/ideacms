<?php

class IndexController extends Plugin {
	
    public function __construct() {
        parent::__construct();
    }
	
	/*
	 * 阅读收费数据调用
	 */
	public function showAction() {
		$id			= (int)$this->get('id');
		$title		= $this->get('title');
		$model		= get_model_data();
		$modelid	= (int)$this->get('modelid');
		ob_start();
		if (empty($this->setting['field'][$modelid])) {
			echo '<font color=red>该模型(#' . $modelid . ')没有绑定价格字段，请在后台应用中绑定</font>';
		} else {
			$table	= $this->model($model[$modelid]['tablename']);
			$data	= $table->find($id);
			if (empty($data)) {
				echo '<font color=red>该文档(#' . $id . ')不存在，或者已经被删除</font>';
			} else {
				$data['title']			= $title;
				$data['modelid']		= $modelid;
				$data['yuedu_price']	= $data[$this->setting['field'][$modelid]];
				$data					= $this->getFieldData($model[$modelid], $data);
				$this->view->assign($data);
				if (empty($data[$this->setting['field'][$modelid]]) || $data[$this->setting['field'][$modelid]] == '0.00' || $this->session->get('user_id') || $this->yuedu->check($id, $this->memberinfo['id'])) {
					//显示阅读内容的模板
					$this->view->display('yuedu_show');
				} else {
					//无法阅读时的模板
					$this->view->display('yuedu_buy');
				}
			}
		}
		$html = ob_get_contents();
		ob_clean();
		$html = addslashes(str_replace(array("\r", "\n", "\t", chr(13)), array('', '', '', ''), $html));
	    echo 'document.write("' . $html . '");';
	}
	
}