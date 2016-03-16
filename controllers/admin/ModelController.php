<?php

class ModelController extends Admin {
    
    private $_model;
	private $typeid;
	private $modeltype; //模型类型
    
    public function __construct() {
		parent::__construct();
		$this->modeltype = array(
		    1 => 'content', //内容表模型
			2 => 'member',  //会员表模型
			3 => 'form',    //表单表模型
			4 => 'extend',	//会员扩展模型
		);
		$this->_model = $this->model('model');
	    $this->typeid = $this->get('typeid') ? $this->get('typeid') : 1;
		if (!isset($this->modeltype[$this->typeid])) $this->adminMsg(lang('a-mod-0'));
		$this->view->assign(array(
			'modeltype' => $this->modeltype,
		    'typeid'    => $this->typeid,
			'typename'  => array(
			    1 => lang('a-men-27'),
				2 => lang('a-men-40'),
				3 => lang('a-men-60'),
				4 => lang('a-mod-167')
			),
		));
	}
	
	/*
	 * 模型所属站点ID
	 */
	private function get_model_site_id() {
		return $this->site['SITE_EXTEND_ID'] ? $this->site['SITE_EXTEND_ID'] : $this->siteid;
	}
	
	/*
	 * 模型管理
	 */
	public function indexAction() {
		$this->view->assign('list', $this->_model->get_data($this->typeid, $site_id));
		$this->view->display('admin/model_list');
	}
	
	/*
	 * 添加模型
	 */
	public function addAction() {
	    if ($this->post('submit')) {
	        $tablename = trim($this->post('tablename'));
	        if (!$tablename) {
                $this->adminMsg(lang('a-mod-1'));
            }
	        if (!preg_match('/^[0-9a-z]+$/', $tablename)) {
                $this->adminMsg(lang('a-mod-2'));
            }
	        $list = $this->post('listtpl') ? $this->post('listtpl') : 'list_' . $tablename . '.html';
	        $show = $this->post('showtpl') ? $this->post('showtpl') : 'show_' . $tablename . '.html';
			$siteid = $this->get_model_site_id();
	        $category = $this->post('categorytpl') ? $this->post('categorytpl') : ($this->typeid == 3 || $this->typeid == 4 ? 'post_' : 'category_') . $tablename . '.html';
			//根据站点生成表名称
			if ($this->typeid == 2) {
				$tablename	= 'member_' . $tablename;
			} elseif ($this->typeid == 4) {
				$tablename	= 'member_extend_' . $tablename;
			} else {
				$tablename	= $this->modeltype[$this->typeid]. '_{site}_' . $tablename;
			}
	        $data = array(
				'site'	=> $siteid,
				'typeid' => $this->typeid,
	            'listtpl' => $list,
	            'showtpl' => $show,
				'setting' => array2string($this->post('setting')),
	            'tablename' => $tablename,
	            'modelname' => $this->post('modelname'),
	            'categorytpl' => $category
	        );
			$tablename = str_replace('{site}', $siteid, $tablename); //判断表是否存在
	        if ($this->_model->is_table_exists($tablename)) {
                $this->adminMsg(lang('a-mod-3', array('1' => $tablename)));
            }
	        if ($modelid = $this->_model->set(0, $data)) { 
			    if ($this->typeid == 1) {	//如果模型类型是内容模型且有关联关系,则更新关联表单
					$join = $this->post('join');
					if (is_array($join) && $join) {
					    foreach ($join as $id) {
						    $this->_model->set($id, array('joinid' => $modelid));
						}
					}
				}
			    $this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/index/', array('typeid' => $this->typeid)), 3, 1, 1);
			} else {
			    $this->adminMsg(lang('failure'));
			}
	    }
		$fdata = $this->get_model('form');	//表单模型数据缓存
		$jdata = array();
		if ($fdata) {
		    foreach ($fdata as $t) {
			    if (!empty($t['joinid'])) $jdata[] = $t['modelid'];	//未被关联的表单
			}
		}
		$this->view->assign(array(
			'join'      => array(),
			'joindata'  => $jdata,
		    'formmodel' => $fdata,
			'rolemodel' => $this->user->get_role_list()
		));
	    $this->view->display('admin/model_add');
	}
	
	/*
	 * 修改模型
	 */
    public function editAction() {
	    if ($this->post('submit')) {
	        $modelid  = (int)$this->post('modelid');
			$data     = $this->_model->find($modelid);
			if (empty($data)) $this->adminMsg(lang('a-mod-4'));
	        $list     = $this->post('listtpl');
	        $show     = $this->post('showtpl');
			$setting  = @array_merge(string2array($data['setting']), $this->post('setting'));
	        $update   = array(
				'joinid' => 0,
	            'listtpl' => $list,
	            'showtpl' => $show,
				'setting' => array2string($setting),
	            'modelname' => $this->post('modelname'),
	            'categorytpl' => $this->post('categorytpl')
	        );
			if ($this->typeid == 3) {
                unset($update['joinid']);
            }
	        $this->_model->set($modelid, $update);
			if ($this->typeid == 1) {
				$join = $this->post('join');
				$this->_model->update(array('joinid' => 0), 'joinid=' . $modelid);
				if (is_array($join) && $join) {
					foreach ($join as $id) {
						$this->_model->set($id, array('joinid' => $modelid));
					}
				}
			}
	        $this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/index/', array('typeid' => $this->typeid)), 3, 1, 1);
	    }
	    $modelid = (int)$this->get('modelid');
		$fdata = $this->get_model('form');	//表单模型数据缓存
		$jdata = $join = array();
		$data = $this->_model->find($modelid);
		if ($fdata) {
		    foreach ($fdata as $t) {
			    if (!empty($t['joinid']))     $jdata[] = $t['modelid'];	//未被关联的表单
				if ($t['joinid'] == $modelid) $join[]  = $t['modelid'];	//已经被该模型关联的表单
			}
		}
		$this->view->assign(array(
			'join'      => $join,
			'data'      => $data,
			'setting'   => string2array($data['setting']),
			'joindata'  => $jdata,
		    'formmodel' => $fdata,
			'rolemodel' => $this->user->get_role_list()
		));
	    $this->view->display('admin/model_add');
	}
	
	/*
	 * 删除模型
	 */
	public function delAction($mid = 0, $all = 0) {
		$mid  = $mid ? $mid : (int)$this->get('modelid');
	    $data = $this->_model->find($mid);
	    if (!$data) $this->adminMsg(lang('a-mod-4'));
	    $this->_model->del($data);
		$name = $this->typeid == 2 ? 'model_member' : 'model_' . $this->modeltype[$this->typeid] . '_' . $this->siteid;
		$data = $this->cache->get($name);
		unset($data[$mid]);
		$this->cache->set($name, $data);
	    $all or $this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/index/', array('typeid' => $this->typeid)), 3, 1, 1);
	}
	
	/**
	 * 字段管理
	 */
	public function fieldsAction() {
	    $modelid = (int)$this->get('modelid');
	    $data    = $this->_model->find($modelid);
	    if (!$data) $this->adminMsg(lang('a-mod-4'));
	    $table   = $this->model($data['tablename']);
	    $field   = $this->model('model_field');
	    if ($this->post('submit')) {
	        foreach ($_POST as $var=>$value) {
	            if (strpos($var, 'order_')!==false) {
	                $id = (int)str_replace('order_', '', $var);
	                $field->update(array('listorder' => $value), 'fieldid=' . $id);
	            }
	        }
			$this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/fields/', array('modelid' => $modelid, 'typeid' => $this->typeid)), 3, 1, 1);
	    }
		$setting = string2array($data['setting']);
	    $this->view->assign(array(
			'list'    => $field->where('modelid=' . $modelid)->order('listorder ASC')->select(),
		    'modelid' => $modelid,
			'content' => $setting['default']
		));
	    $this->view->display('admin/model_fields');
	}
	
	/**
	 * 添加字段
	 */
	public function addfieldAction() {
	    $field      = $this->model('model_field');
	    $modelid    = (int)$this->get('modelid');
	    $model_data = $this->_model->find($modelid);
	    if (!$model_data) $this->adminMsg(lang('a-mod-4'));
	    if ($this->post('submit')) {
	        $name		= $this->post('name');
	        $type		= $this->post('type');
	        $ftype		= $this->post('formtype');
	        $fieldname  = $this->post('field');
			//字段名称格式验证
	        if (empty($fieldname ) || !preg_match('/^[a-zA-Z]{1}[a-zA-Z0-9]{0,19}$/', $fieldname)) {
                $this->adminMsg(lang('a-mod-5'));
            }
			//加载模型验证字段是否存在
	        if ($this->typeid == 1) {	//内容模型
				$t_fields	= $this->content->get_fields();				//主表字段
				$table		= $this->model($model_data['tablename']);	//实例化附表对象
				$d_fields	= $table->get_fields();						//附表字段
				$fields     = array_merge($t_fields, $d_fields);		//组合字段
			} elseif ($this->typeid == 2) {	//会员模型
				$t_fields	= $this->member->get_fields();				//主表字段
				$table		= $this->model($model_data['tablename']);	//实例化附表对象
				$d_fields	= $table->get_fields();						//附表字段
				$fields     = @array_merge($t_fields, $d_fields);		//组合字段
			} elseif ($this->typeid == 3) {	//表单模型
				$table		= $this->model($model_data['tablename']);	//实例化表单对象
				$fields		= $table->get_fields();						//表单字段
			} elseif ($this->typeid == 4) {	//会员扩展
				$table		= $this->model($model_data['tablename']);	//实例化会员扩展对象
				$fields		= $table->get_fields();						//表单字段
			}
	        //判断新加字段是否存在
	        if (in_array($fieldname, $fields)) $this->adminMsg(lang('a-mod-6'));
	        if (empty($name))  $this->adminMsg(lang('a-mod-7'));
	        if (empty($ftype)) $this->adminMsg(lang('a-mod-8'));
	        if (!in_array($ftype, array('editor', 'checkbox', 'files', 'merge', 'date', 'fields')) && empty($type)) $this->adminMsg(lang('a-mod-9'));
	        $data  = array(
	            'name'      => $name,
	            'tips'      => $this->post('tips'),
				'type'      => $type,
	            'field'     => $fieldname,
				'isshow'    => isset($_POST['isshow']) ? $this->post('isshow') : 1,
				'length'    => $this->post('length'),
	            'pattern'   => $this->post('pattern'),
	            'modelid'   => $modelid,
	            'setting'   => array2string($this->post('setting')),
				'pattern'   => $this->post('pattern'),
				'not_null'  => $this->post('not_null'),
	            'formtype'  => $ftype,
				'indexkey'  => $this->post('indexkey'),
	            'errortips' => $this->post('errortips'),
				'errortips' => $this->post('errortips')
	        );
	        //添加字段入库
	        if ($field->set(0, $data)) {
	            $this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/fields/', array('modelid' => $modelid, 'typeid' => $this->typeid)), 3, 1, 1);
	        } else {
	            $this->adminMsg(lang('failure'));
	        }
	    }
	    //加载字段配置文件
	    App::auto_load('fields');
	    $formtype = formtype();
	    $this->view->assign(array(
			'merge'      => $field->where('modelid=' . $modelid)->where('formtype=?', 'fields')->select(),
	        'modelid'    => $modelid,
	        'formtype'   => $formtype,
	        'model_data' => $model_data
	    ));
	    $this->view->display('admin/model_addfield');
	}
	
	/**
	 * 修改字段
	 */
	public function editfieldAction() {
	    $field   = $this->model('model_field');
	    $fieldid = (int)$this->get('fieldid');
	    $data    = $field->getOne('fieldid=' . $fieldid);
	    if (empty($data)) $this->adminMsg(lang('a-mod-10'));
	    $modelid    = $data['modelid'];
	    $model_data = $this->_model->find($modelid);
	    if (!$model_data) $this->adminMsg(lang('a-mod-4'));
	    if ($this->post('submit')) {
	        $fieldid = $this->post('fieldid');
	        if (empty($fieldid)) $this->adminMsg(lang('a-mod-10'));
	        $name    = $this->post('name');
	        if (empty($name)) $this->adminMsg(lang('a-mod-7'));
	        $data    = array(
	            'name'      => $name,
	            'tips'      => $this->post('tips'),
				'isshow'    => isset($_POST['isshow']) ? $this->post('isshow') : 1,
	            'pattern'   => $this->post('pattern'),
				'pattern'   => $this->post('pattern'),
	            'setting'   => array2string($this->post('setting')),
				'not_null'  => $this->post('not_null'),
	            'errortips' => $this->post('errortips'),
				'errortips' => $this->post('errortips')
	        );
	        //字段入库
	        if ($field->set($fieldid, $data)) {
	            $this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/fields/', array('typeid' => $this->typeid, 'modelid' => $modelid)), 3, 1, 1);
	        } else {
	            $this->adminMsg(lang('failure'));
	        }
	    }
	    //加载字段配置文件
	    App::auto_load('fields');
	    $this->view->assign(array(
	        'data'       => $data,
			'merge'      => $field->where('modelid=' . $modelid)->where('formtype=?', 'fields')->select(),
	        'modelid'    => $modelid,
	        'formtype'   => formtype(),
	        'model_data' => $model_data
	    ));
	    $this->view->display('admin/model_addfield');
	}
	
	/**
	 * 修改默认字段
	 */
	public function ajaxeditAction() {
	    $modelid = (int)$this->get('modelid');
		$name    = $this->get('name');
	    $data    = $this->_model->find($modelid);
	    if (empty($data)) $this->adminMsg(lang('a-mod-4'));
		$setting = string2array($data['setting']);
		if (!isset($setting['default'][$name])) $this->adminMsg(lang('a-mod-10'));
		$field   = $setting['default'][$name];
	    if ($this->post('submit')) {
			$setting['default'][$name] = $this->post('data');
			$this->_model->update(array('setting' => array2string($setting)), 'modelid=' . $modelid);
			$this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/fields/', array('typeid' => $this->typeid, 'modelid' => $modelid)), 3, 1, 1);
	    }
	    $this->view->assign(array(
	        'data'    => $field,
			'name'    => $data['modelname'],
	        'modelid' => $modelid
	    ));
	    $this->view->display('admin/model_ajaxedit');
	}
	
	/**
	 * 动态加载字段类型配置信息
	 */
	public function ajaxformtypeAction() {
	    $type = $this->get('type');
	    if (empty($type)) exit('');
	    //加载字段配置文件
	    App::auto_load('fields');
	    $func = 'form_' . $type;
	    if (!function_exists($func)) exit('');
	    eval('echo ' . $func . '();');
	    
	}
	
	/**
	 * 禁用/启用字段
	 */
	public function disableAction() {
	    $fieldid = (int)$this->get('fieldid');
	    $field   = $this->model('model_field');
	    $data    = $field->getOne('fieldid=' . $fieldid);
	    if (empty($data)) $this->adminMsg(lang('a-mod-10'));
	    $disable = $data['disabled'] == 1 ? 0 : 1;
	    $field->update(array('disabled' => $disable), 'fieldid=' . $fieldid);
	    $this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/fields/', array('typeid' => $this->typeid, 'modelid' => $data['modelid'])), 3, 1, 1);
	}
	
	/**
	 * 删除字段
	 */
	public function delfieldAction() {
	    $fieldid = (int)$this->get('fieldid');
	    $field   = $this->model('model_field');
	    $data    = $field->getOne('fieldid=' . $fieldid);
	    if (empty($data)) $this->adminMsg(lang('a-mod-10'));
		if ($data['field'] == 'content') $this->adminMsg(lang('a-mod-11'));
	    if ($field->del($data)) {
	        $this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/fields/', array('typeid' => $this->typeid, 'modelid' => $data['modelid'])), 3, 1, 1);
	    } else {
	        $this->adminMsg(lang('failure'));
	    }
	}
	
	/*
	 * 禁用/启用模型
	 */
	public function cdisabledAction() {
	    $modelid = (int)$this->get('modelid');
	    $data    = $this->_model->find($modelid);
	    if (!$data) $this->adminMsg(lang('a-mod-4'));
		$setting = string2array($data['setting']);
	    $setting['disable'] = $setting['disable'] == 1 ? 0 : 1;
	    $this->_model->update(array('setting' => array2string($setting)), 'modelid=' . $modelid);
	    $this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/index/', array('typeid' => $this->typeid)), 3, 1, 1);
	}
	
	/*
	 * 导出模型
	 */
	public function exportAction() {
		$model   = $this->typeid != 2 ? $this->get_model($this->modeltype[$this->typeid]) : $this->cache->get('model_member');
		$modelid = (int)$this->get('modelid');
	    if (!$model) $this->adminMsg(lang('a-mod-4'));
		if (!isset($model[$modelid])) $this->adminMsg(lang('a-mod-4'));
		$result  = array2string($model[$modelid]);
		header('Content-Disposition: attachment; filename="' . $model[$modelid]['tablename'] . '.mod"');
		echo $result;exit;
	}
	
	/*
	 * 导入模型
	 */
	public function importAction() {
	    if ($this->post('submit')) {
	        $tablename		= $this->post('tablename');
	        if (!$tablename) $this->adminMsg(lang('a-mod-1'));
	        if (!preg_match('/^[0-9a-z]+$/', $tablename)) $this->adminMsg(lang('a-mod-2'));
			$tablename		= $this->typeid == 2 ? 'member_' . $tablename : $this->modeltype[$this->typeid]. '_{site}_' . $tablename;
	        if ($this->_model->is_table_exists(str_replace('{site}', $this->siteid, $tablename))) {	//判断表是否存在
				$this->adminMsg(lang('a-mod-2', array('1' => str_replace('{site}', $this->siteid, $tablename))));
			}
			if(!empty($_FILES['import']['tmp_name'])) {
				$model		= @file_get_contents($_FILES['import']['tmp_name']);
				if(!empty($model)) {
					$data	= string2array(trim($model));
					if(empty($data)) $this->adminMsg(lang('a-mod-12'));
				} else {
				    $this->adminMsg(lang('a-mod-12'));
				}
			} else{
			    $this->adminMsg(lang('a-mod-13'));
			}
			if ($data['typeid'] != $this->typeid) $this->adminMsg(lang('a-mod-14', array('1' => $this->modeltype[$data['typeid']])));
			$insert = array(
				'site'		  => $this->siteid,
				'typeid'      => $this->typeid,
	            'listtpl'     => $data['listtpl'],
	            'showtpl'     => $data['showtpl'],
				'setting'	  => array2string($data['setting']),
	            'tablename'   => $tablename,
	            'modelname'   => $this->post('modelname'),
	            'categorytpl' => $data['categorytpl']
	        );
	        $modelid	= $this->_model->set(0, $insert);
			if (empty($modelid)) $this->adminMsg(lang('a-mod-15'));
			$field		= $this->model('model_field');
			$content	= $data['fields']['data']['content'];
			unset($data['fields']['data']['content']);
			if (isset($data['fields']['data']) && $data['fields']['data']) {
			    foreach ($data['fields']['data'] as $t) {
				    unset($t['fieldid']);
					$t['modelid'] = $modelid;
					$t['setting'] = var_export($t['setting'], true);
					if (substr($t['setting'], 0, 1) == "'") $t['setting'] = substr($t['setting'], 1);
					if (substr($t['setting'], -1) == "'")   $t['setting'] = substr($t['setting'], 0, -1);
					$field->set(0, $t);
				}
			}
			unset($content['fieldid']);
			$content['modelid'] = $modelid;
			$content['setting'] = var_export($content['setting'],true);
			if (substr($content['setting'], 0, 1) == "'") $content['setting'] = substr($content['setting'], 1);
			if (substr($content['setting'], -1) == "'")   $content['setting'] = substr($content['setting'], 0, -1);
			$field->update($content, 'modelid=' . $modelid . ' and field="content"');
			$this->adminMsg($this->getCacheCode('model') . lang('success'), url('admin/model/index/', array('typeid' => $this->typeid)), 3, 1, 1);
	    }
	    $this->view->display('admin/model_import');
	}
	
	/**
	 * 更新模型缓存
	 * array(
	 *     '模型ID'=> array(
	 *                    ...模型字段
	 *                    'content' => array(
	 *                        内容模型默认字段
	 *                    ),
	 *                    'fields'=> array(
	 *                                   'data'  => array(
	 *                                             ...该模型的可用字段
	 *                                           ),
	 *                                   'merge' => array(
	 *                                             ...组合字段
	 *                                           ),
	 *                                   'mergefields' => array(
	 *                                             被组合过的字段（将不会被单独显示）
	 *                                           ),
	 *                               ),
	 *                ),
	 * );
	 */
	public function cacheAction($show = 0, $site_id = 0) {
		$this->delDir($this->_model->cache_dir); //清空模型缓存数据
		if (!file_exists($this->_model->cache_dir)) @mkdir($this->_model->cache_dir, 0777, true);
	    $field		= $this->model('model_field');
        $site_id   = $site_id ? $site_id : ($_GET['siteid'] ? $_GET['siteid'] : $this->siteid);
		$siteid		= $this->_model->get_site_id($site_id); //当前站点继承的id
		foreach ($this->modeltype as $typeid => $c) {
	        $model	= $this->_model->get_data($typeid, $site_id);
	        $data	= $now = array(); //该类模型的数据
			foreach ($model as $t) {
			    $setting   = string2array($t['setting']);
				if ($setting['disable'] == 1) continue;
				$id = $t['modelid'];
				$data[$id] = $t;
				if ($site_id != $siteid) {	//属于继承站点，模型表名称重新赋值
					$now[$id]['site']		= $site_id;
					$now[$id]['tablename']	= preg_replace('/\_([0-9]+)\_/', '_' . $site_id . '_', $t['tablename']);
				}
				$fields    = $field->where('modelid=' . $id)->where('disabled=0')->order('listorder ASC')->select();
				$_fields   = $merge  = array();
				foreach ($fields as $k => $f) {
				    $_fields[$f['field']] = $f;
				    if ($f['formtype'] == 'merge' || $f['formtype'] == 'fields') {
						$set = string2array($f['setting']);
						if (preg_match_all('/\{([a-zA-Z]{1}[a-zA-Z]{0,19})\}/Ui', $set['content'], $fs)) {
						    $mergefields = $fs[1];
					        $_fields[$f['field']]['data'] = $mergefields;
							$merge = array_merge($merge, $mergefields);
						}
					}
				}
				if ($typeid == 1 && !isset($setting['default'])) {
				    $setting['default'] = array(
					    'title'         => array('name' => lang('a-con-26'), 'show' => 1),
					    'thumb'         => array('name' => lang('a-con-45'), 'show' => 1),
					    'keywords'      => array('name' => lang('a-con-43'), 'show' => 1),
					    'description'	=> array('name' => lang('a-desc'),   'show' => 1)
					);
					$this->_model->update(array('setting' => array2string($setting)), 'modelid=' . $id);
				}
				$data[$id]['fields']['data']  = $_fields;
				$data[$id]['fields']['merge'] = $merge;
				$data[$id]['setting']         = $setting;
				$data[$id]['content']         = $setting['default'];
                // 生成模型回调文件
                $this->_model->create_model($t['tablename'], '');
			}
	        //保存到缓存文件中
			if ($typeid == 2) {
				$this->cache->set('model_member', $data); //会员模型直接保存
			} elseif ($typeid == 4) {
				$this->cache->set('model_member_extend', $data); //会员扩展模型直接保存
			} else {
				if ($site_id == $siteid) {	//独立站点
					$this->cache->set('model_' . $c . '_' . $siteid,  $data);
				} else {	//继承站点
					$this->cache->set('model_' . $c . '_' . $site_id, $now);
				}
			}
			
		}
		//缓存关联表单被关联的模型
		$join = array();
		$data = $this->_model->get_data(3, $site_id);
		if ($data) {
		    foreach ($data as $t) {
			    if ($t['joinid'] && !isset($join[$t['joinid']])) {
				    $join[$t['joinid']] = $this->_model->where('site=' . $site_id)->where('modelid=' . $t['joinid'])->select(false);
				}
			}
		}
		$this->cache->set('model_join_' . $site_id, $join);
	    $show or $this->adminMsg(lang('a-update'), '', 3, 1, 1);
	}
}