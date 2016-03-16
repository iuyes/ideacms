<?php

class FormController extends Admin {
    
	private $cid;
	private $form;
	private $join;
	private $model;
	private $table;
	private $modelid;
    
    public function __construct() {
		parent::__construct();
		if ($this->action == 'index') $this->redirect(url('admin/model/index', array('typeid'=>3)));
		$this->cid     = (int)$this->get('cid');
		$formmodel     = $this->get_model('form');
		$this->modelid = (int)$this->get('modelid');
		if (empty($this->modelid)) $this->adminMsg(lang('a-for-1'));
		$this->model   = $formmodel[$this->modelid];
		if (empty($this->model)) $this->adminMsg(lang('a-for-2', array('1'=>$this->modelid)));
		$this->table   = $this->model['tablename'];
		$this->form    = $this->model($this->table);
		$joinmodel     = $this->cache->get('model_join_' . $this->siteid);
		$this->join    = isset($joinmodel[$this->model['joinid']]) ? $joinmodel[$this->model['joinid']] : null;
		$join_info     = lang('a-for-3');
		if ($this->join) {
		   $join_info  = lang('a-for-4', array('1'=>$this->join['modelname']));
		   if ($this->join['typeid'] == 1) $join_info  = '<a href="' . url('admin/content/', array('modelid'=>$this->join['modelid'])) . '">' . lang('a-for-4', array('1'=>$this->join['modelname'])) . '</a>';
		}
	    $this->view->assign(array(
	        'cid'       => $this->cid,
			'model'     => $this->model,
			'modelid'   => $this->modelid,
			'join_info' => $join_info
	    ));
	}
	
	/**
	 * 表单内容管理
	 */
	public function listAction() {
	    if ($this->post('submit') && $this->post('form') == 'search') {
	        $kw		= $this->post('kw');
			$stype	= $this->post('stype');
			$userid = $this->post('userid');
	    } elseif ($this->post('submit_order') && $this->post('form') == 'order') {
	        foreach ($_POST as $var => $value) {
	            if (strpos($var, 'order_') !== false) {
	                $id = (int)str_replace('order_', '', $var);
	                $this->form->update(array('listorder' => $value), 'id=' . $id);
	            }
	        }
	    } elseif ($this->post('form') == 'del') {
	        foreach ($_POST as $var => $value) {
	            if (strpos($var, 'del_') !== false) {
	                $_id = (int)str_replace('del_', '', $var);
	                $this->delAction($_id, 1);
	            }
	        }
	    } elseif ($this->post('submit_status_0') && $this->post('form') == 'status_0') {
	        foreach ($_POST as $var => $value) {
	            if (strpos($var, 'del_') !== false) {
	                $_id = (int)str_replace('del_', '', $var);
	                $this->form->update(array('status' => 0), 'id=' . $_id);
					$this->delFile($_id);
	            }
	        }
	    } elseif ($this->post('submit_status_1') && $this->post('form') == 'status_1') {
	        foreach ($_POST as $var=>$value) {
	            if (strpos($var, 'del_') !== false) {
	                $_id = (int)str_replace('del_', '', $var);
	                $this->form->update(array('status' => 1), 'id=' . $_id);
					if (isset($this->model['setting']['form']['url']['tohtml']) && $this->model['setting']['form']['url']['tohtml']) {
						$this->createForm($this->modelid, $this->form->find($_id));
					}
	            }
	        }
			$this->adminMsg(lang('success'), '', 3, 1, 1);
	    } elseif ($this->post('submit_status_3') && $this->post('form') == 'status_3') {
	        foreach ($_POST as $var => $value) {
	            if (strpos($var, 'del_') !== false) {
	                $_id = (int)str_replace('del_', '', $var);
	                $this->form->update(array('status' => 3), 'id=' . $_id);
					$this->delFile($_id);
	            }
	        }
	    } elseif ($this->post('submit_join') && $this->post('form') == 'join') {
		    $_cid = (int)$this->post('toid');
			if ($this->join && $_cid) {
			    $jdata = $this->content->from($this->join['tablename'], 'id')->where('id=' . $_cid)->select(false);
				if (empty($jdata)) $this->adminMsg(lang('a-for-5', array('1' => $this->join['modelname'], '2' => $_cid)));
				foreach ($_POST as $var => $value) {
					if (strpos($var, 'del_') !== false) {
						$_id = (int)str_replace('del_', '', $var);
						$this->form->update(array('cid' => $_cid), 'id=' . $_id);
						if (isset($this->model['setting']['form']['url']['tohtml']) && $this->model['setting']['form']['url']['tohtml']) {
							$data = $this->form->find($_id);
							$this->createForm($this->modelid, $data);
						}
					}
				}
				$this->adminMsg(lang('success'), '', 3, 1, 1);
			}
	    }
	    $kw       = $kw ? $kw : $this->get('kw');
		$page     = $this->get('page') ? $this->get('page') : 1;
	    $stype    = $stype ? $stype : (int)$this->get('stype');
		$userid   = $userid ? $userid : (int)$this->get('userid');
		$status   = isset($_GET['status']) ? (int)$this->get('status') : 1;
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
	    $where    = '`status`=' . $status;
		if ($userid) $where .= ' and userid=' . $userid;
		if ($this->cid) $where .= ' and cid=' . $this->cid;
		if ($kw && $stype && isset($this->model['fields']['data'][$stype])) $where .= ' and `' . $stype . '` like "%' . $kw . '%"';
	    $total    = $this->content->count($this->table, 'id', $where);
	    $pagesize = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
	    $urlparam = array(
		    'kw' => $kw,
			'cid' => $this->cid,
			'page' => '{page}',
			'stype' => $stype,
			'status' => $status,
			'userid' => $userid,
			'modelid' => $this->modelid,
		);
	    $data = $this->form->page_limit($page, $pagesize)->where($where)->order(array('listorder DESC', 'updatetime DESC'))->select();
	    $pagelist = $pagelist->total($total)->url(url('admin/form/list', $urlparam))->num($pagesize)->page($page)->output();
		$count = array();
		$count[1] = $this->content->count($this->table, null, 'status=1');
		$count[0] = $this->content->count($this->table, null, 'status=0');
		$count[3] = $this->content->count($this->table, null, 'status=3');
		$count[$status]	= $total;

	    $this->view->assign(array(
	        'kw' => $kw,
            'page' => $page,
	        'list' => $data,
			'join' => empty($this->join) ? 0 : 1,
			'count'=> $count,
			'status' => $status,
	        'pagelist'=> $pagelist,
            'tpl' => !is_file(VIEW_DIR.'admin/'.$this->table.'.html') ? 'admin/form_default' : 'admin/'.$this->table,
            'diy_file' => is_file(VIEW_DIR.'admin/'.$this->table.'.html') ? '' : '/views/admin/'.$this->table.'.html',
	    ));
	    $this->view->display('admin/form_list');
	}
	
	/**
	 * 表单配置
	 */
	public function configAction() {
		if ($this->isPostForm()) {
			$cfg = $this->post('setting');
		    $data	= $this->post('data');
			$field	= array();
			if ($cfg['form']['field']) {
			    foreach ($cfg['form']['field'] as $c => $t) {
				    if ($t) $field[]	= $c;
				}
				$cfg['form']['field']	= $field;
			}
			$cfg = array_merge($this->model['setting'], $cfg);
		    $set = array(
				'listtpl'		=> $data['listtpl'],
				'showtpl'		=> $data['showtpl'],
				'setting'		=> array2string($cfg),
				'categorytpl'	=> $data['categorytpl'],
			);
			$model= $this->model('model');
			$model->update($set, 'modelid=' . $this->modelid);
			$this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/form/config', array('modelid' => $this->modelid, 'cid' => $this->cid, 'typeid' => $this->post('typeid'))), 3, 1, 1);
		}
		$count[1] = $this->content->count($this->table, null, 'status=1');
		$count[0] = $this->content->count($this->table, null, 'status=0');
		$count[3] = $this->content->count($this->table, null, 'status=3');
	    $form_code= '<!-- ' . lang('a-for-6') . ' -->
<link href="{ADMIN_THEME}images/table_form.css" rel="stylesheet" type="text/css" />
<link href="{ADMIN_THEME}images/dialog.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{ADMIN_THEME}js/dialog.js"></script>
<script type="text/javascript">var sitepath = "{SITE_PATH}{ENTRY_SCRIPT_NAME}";</script>
<script type="text/javascript" src="{LANG_PATH}lang.js"></script>
<script type="text/javascript" src="{ADMIN_THEME}js/core.js"></script>

<!-- ' . lang('a-for-7') . ' -->

<form action="{url(\'form/post\', array(\'modelid\'=>$modelid, \'cid\'=>$cid))}" method="post">
<table width="100%" class="table_form ">
<tr>
	<th width="200">{$form_name}</th>
	<td></td>
</tr>
{$fields}
{if $code}
<tr>
	<th>' . lang('a-for-8') . '：</th>
	<td><input name="code" type="text" class="input-text" size=10 /><img src="{url(\'api/captcha\', array(\'width\'=>80,\'height\'=>25))}"></td>
</tr>
{/if}
<tr>
	<th style="border:none"> </th>
	<td style="border:none"><input type="submit" class="button" value="' . lang('a-submit') . '" name="submit"></td>
</tr>
</table>
</form>';
        $join_code = '';
		$form_url  = '{url(\'form/post\', array(\'modelid\'=>' . $this->model['modelid'] . '))}';
        $list_code = '
{list form=' . str_replace('form_' . $this->siteid . '_', '', $this->model['tablename']) . ' order=updatetime num=10}
' . lang('a-for-9') . '：{$t[\'id\']}，' . lang('a-for-10') . '：{form_show_url(' . $this->model['modelid'] . ', $t)}，' . lang('a-for-11') . '<br>
{/list}
<!-- ' . lang('a-for-12') . ' -->';
        if ($this->join) {
		    $join_code = '
<!-- ' . lang('a-for-13') . ' -->
{list form=' . str_replace('form_' . $this->siteid . '_', '', $this->model['tablename']) . ' cid=' . lang('a-for-14') . ' order=updatetime num=10}
' . lang('a-for-9') . '：{$t[\'id\']}，' . lang('a-for-10') . '：{form_show_url(' . $this->model['modelid'] . ', $t)}，' . lang('a-for-11') . '<br>
{/list}
<!-- ' . lang('a-for-15') . ' -->';
            $form_url  = '{url(\'form/post\', array(\'modelid\'=>' . $this->model['modelid'] . ', \'cid\'=>$id))}   ' . lang('a-for-16');
        }
		$func_code	= 'function mycallback($msg, $url, $state) {' . PHP_EOL
				. '	/*' . lang('a-mod-193') . '*/' . PHP_EOL
				. '}' . PHP_EOL . '$msg ：'. lang('a-mod-194') . PHP_EOL
				. '$url ：' . lang('a-mod-195') . PHP_EOL
				. '$state ：'. lang('a-mod-196') . PHP_EOL
				. lang('a-mod-197');
		$this->view->assign(array(
			'join'      => empty($this->join) ? 0 : 1,
			'count'     => $count,
			'typeid'	=> $this->get('typeid') ? $this->get('typeid') : 1,
			'form_url'  => $form_url,
			'form_code' => $form_code,
			'list_code' => $list_code,
			'join_code' => $join_code,
			'func_code'	=> $func_code
	    ));
	    $this->view->display('admin/form_config');
	}
	
	/**
	 * 添加内容
	 */
	public function addAction() {
	    //模型投稿权限验证
		if ($this->adminPost($this->model['setting']['auth'])) $this->adminMsg(lang('a-cat-100', array('1'=>$this->userinfo['rolename'])));
		if ($this->isPostForm()) {
		    $data = $this->post('data');
			$cid  = (int)$this->post('cid');
			if ($this->join && empty($cid)) $this->adminMsg(lang('a-for-17'), '', 1);
			if ($this->join) {
				$table = $this->model($this->join['tablename']);
				$cdata = $table->find($cid, 'id');
				if (empty($cdata)) $this->adminMsg(lang('a-for-5', array('1'=>$this->join['modelname'], '2'=>$cid)));
			}
			$this->checkFields($this->model['fields'], $data, 1);
			$data['ip']			= client::get_user_ip();
			$data['cid']		= $cid;
			$data['userid']		= 0;
			$data['username']	= $this->userinfo['username'];
			$data['inputtime']	= $data['updatetime'] = time();
			if ($data['id'] = $this->form->set(0, $data)) {
				if (isset($this->model['setting']['form']['url']['tohtml']) && $this->model['setting']['form']['url']['tohtml'] && $data['status'] == 1) {
					$this->createForm($this->modelid, $data);	//生成静态
				}
			    $this->adminMsg(lang('success'), url('admin/form/list', array('modelid' => $this->modelid, 'cid' => $this->cid)), 3, 1, 1);
			} else {
			    $this->adminMsg(lang('failure'));
			}
		}
		$count[1] = $this->content->count($this->table, null, 'status=1');
		$count[0] = $this->content->count($this->table, null, 'status=0');
		$count[3] = $this->content->count($this->table, null, 'status=3');
	    $this->view->assign(array(
			'join'   => empty($this->join) ? 0 : 1,
			'count'  => $count,
			'fields' => $this->getFields($this->model['fields'], null, $this->model['setting']['form']['field']),
	    ));
	    $this->view->display('admin/form_add');
	}
	
	/**
	 * 修改内容
	 */
	public function editAction() {
		$id = (int)$this->get('id');
		if (empty($id)) $this->adminMsg(lang('a-for-18'));
		if ($this->isPostForm()) {
			//模型投稿权限验证
			if ($this->adminPost($this->model['setting']['auth'])) $this->adminMsg(lang('a-cat-100', array('1' => $this->userinfo['rolename'])));
			$cid  = (int)$this->post('cid');
		    $data = $this->post('data');
			if ($this->join && empty($cid)) $this->adminMsg(lang('a-for-17'), '', 1);
			$this->checkFields($this->model['fields'], $data, 1);
			$data['cid']        = $cid;
			$data['updatetime'] = time();
			if ($data['id']		= $this->form->set($id, $data)) {
				if (isset($this->model['setting']['form']['url']['tohtml']) && $this->model['setting']['form']['url']['tohtml'] && $data['status'] == 1) {
					$this->createForm($this->modelid, $data);
				}
			    $this->adminMsg(lang('success'), url('admin/form/list', array('modelid' => $this->modelid, 'cid' => $this->cid)), 3, 1, 1);
			} else {
			    $this->adminMsg(lang('failure'));
			}
		}
		$data	= $this->form->find($id);
		if (empty($data)) $this->adminMsg(lang('a-for-18'));
		$count[1] = $this->content->count($this->table, null, 'status=1');
		$count[0] = $this->content->count($this->table, null, 'status=0');
		$count[3] = $this->content->count($this->table, null, 'status=3');
	    $this->view->assign(array(
			'cid'    => $data['cid'],
			'join'   => empty($this->join) ? 0 : 1,
			'data'   => $data,
			'count'  => $count,
			'fields' => $this->getFields($this->model['fields'], $data, $this->model['setting']['form']['field']),
	    ));
	    $this->view->display('admin/form_add');
	}
	
	/**
	 * 删除
	 */
	public function delAction($id=0, $all=0) {
        if (!auth::check($this->roleid, 'form-del', 'admin')) $this->adminMsg(lang('a-com-0', array('1' => 'form', '2' => 'del')));
	    $id	 = $id  ? $id  : (int)$this->get('id');
	    $all = $all ? $all : $this->get('all');
		$this->delFile($id);
	    $this->form->delete('id=' . (int)$id);
	    $all or $this->adminMsg(lang('success'), url('admin/form/list', array('modelid' => $this->modelid, 'cid' => $this->cid)), 3, 1, 1);
	}
	
	/**
	 * 删除静态文件
	 */
	private function delFile($id) {
		$data = $this->form->find((int)$id);
		$file = substr(form_show_url($this->modelid, $data), strlen(Controller::get_base_url())); //去掉主域名
		$file = substr($file, 0, 9) == 'index.php' ? null : $file; //是否为动态链接
		if ($file && file_exists($file)) @unlink($file);
	}
}