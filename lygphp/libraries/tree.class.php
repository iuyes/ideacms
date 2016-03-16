<?php
/**
 * tree class file
 * 栏目无限分类
 */

if (!defined('IN_IDEACMS')) exit();

class tree extends Fn_base {
	
	/**
	 * 分类的父ID的键名(key)
	 * 
	 * @var integer
	 */
	private $parentid;
	
	/**
	 * 分类的ID(key)
	 * 
	 * @var integer
	 */
	private $id;
	
	/**
	 * 分类名
	 * 
	 * @var string
	 */
	private $name;
	
	/**
	 * 数据
	 * 
	 * @var array
	 */
	private $data;
	
	/**
	 * 构造函数
	 * 
	 * @return void
	 */
	public function __construct() {
		
		$this->parentid	= 'parent_id';
		$this->id		= 'id';
		$this->name		= 'name';
		
		return true;
	}
	
	/**
	 * 无限级分类树-初始化配置
	 * 
	 * @param  array $config 配置分类的键
	 * @return $this
	 * 
	 * @example
	 * $params = array('parent_id'=>'pid', 'id' => 'cat_id', 'name' =>'cat_name');
	 * $this->config($params ); 
	 */
	public function config($params) {
		if (!$params || !is_array($params)) return false;
		$this->parentid = (isset($params['parent_id'])) ? $params['parent_id'] : $this->parentid;
		$this->id       = (isset($params['id']))        ? $params['id']        : $this->id;
		$this->name     = (isset($params['name']))      ? $params['name']      : $this->name;
		return $this;
	}			
	
	/**
	 * 无限级分类树-获取树
	 * 
	 * @param  array 	$data 			树的数组
	 * @param  int   	$parent_id 		初始化树时候，代表ID下的所有子集
	 * @param  int   	$select_id  	选中的ID值
	 * @param  string  	$prefix  		前缀
	 * @param  string  	$child  		是否禁用父栏目
	 * model 权限 v1.6
	 * group 权限 v1.6
	 * @param  string  	$guest  		是否支持游客
	 * @param  string  	$roleid  		后台角色组
	 * @return string|array
	 */
	public function get_tree($data, $parent_id = 0, $select_id = null, $pre_fix = '|-', $child = false, $modelid = null, $groupid = null, $guest = false, $roleid = null) {
		if (!$data || !is_array($data)) return '';
		$string = '';
		foreach ($data as $key => $value) {
		    if ($value['typeid'] == 3) continue;
		    if ($child && ($value['typeid'] != 1 && $value['child'] == 0)) continue;
			if ($value[$this->parentid] == $parent_id) {
				$str = '<option value=\'' . $value[$this->id] . '\'';
				if (!is_null($select_id)) {
					$str .= ($value[$this->id] == $select_id) ? ' selected="selected"' : '';
				}
				if ($child && $value['child'] == 1) {
				    $str .= ' disabled';
				} elseif (($modelid || $groupid) && isset($value['setting']['memberpost']) && $value['setting']['memberpost']) {
				    //会员权限判断v1.6
					if ($value['setting']['modelpost'] && in_array($modelid, $value['setting']['modelpost'])) {
						if ($value['child'] == 0) continue;
					    $str .= ' disabled';
					} elseif ($value['setting']['grouppost'] && in_array($groupid, $value['setting']['grouppost'])) {
						if ($value['child'] == 0) continue;
						$srt .= ' disabled';
					}
				} elseif ($guest && (!isset($value['setting']['guestpost']) || empty($value['setting']['guestpost']))) {
					if ($value['child'] == 0) continue;
					$str .= ' disabled';
				} elseif ($roleid && isset($value['setting']['adminpost']) && $value['setting']['adminpost'] && @in_array($roleid, $value['setting']['rolepost'])) {
					if ($value['child'] == 0) continue;
					$str .= ' disabled';
				}
				$string .= $str . '>' . ($value['parentid'] == 0 ? '' : $pre_fix) . $value[$this->name] . '</option>';
				
				$string .= $this->get_tree($data, $value[$this->id], $select_id, '&nbsp;&nbsp;' . $pre_fix, $child, $modelid, $groupid, $guest, $roleid);
			}
		}
		return $string ;
	}
	
	/**
	 * 无限级分类树-获取模型树
	 * 
	 * @param  array 	$data 			树的数组
	 * @param  int   	$parent_id 		初始化树时候，代表ID下的所有子集
	 * @param  int   	$select_id  	选中的ID值
	 * @param  string  	$prefix  		前缀
	 * @param  int   	$modelid  	    模型的ID值
	 * @return string|array
	 */
	public function get_model_tree($data, $parent_id = 0, $select_id = null, $pre_fix = '|-', $modelid, $_modelid = null, $_groupid = null, $_roleid = null) {
		if (!$data || !is_array($data)) return '';
		$string = '';
		foreach ($data as $key => $value) {
		    if ($value['typeid'] == 3 || ($value['arrmodelid'] && !in_array($modelid, $value['arrmodelid']))) continue;
			if ($value[$this->parentid] == $parent_id) {
				$str = '<option value=\'' . $value[$this->id] . '\'';
				if (!is_null($select_id)) $str .= ($value[$this->id] == $select_id) ? ' selected="selected"' : '';
				if ($value['child'] == 1) {
					$str .= ' disabled';
				} elseif (($_modelid || $_groupid) && isset($value['setting']['memberpost']) && $value['setting']['memberpost']) {
				    //会员权限判断v1.6
					if ($value['setting']['modelpost'] && in_array($_modelid, $value['setting']['modelpost'])) {
						if ($value['child'] == 0) continue;
					    $str .= ' disabled';
					} elseif ($value['setting']['grouppost'] && in_array($_groupid, $value['setting']['grouppost'])) {
						if ($value['child'] == 0) continue;
						$str .= ' disabled';
					}
				} elseif ($_roleid && isset($value['setting']['adminpost']) && $value['setting']['adminpost'] && @in_array($_roleid, $value['setting']['rolepost'])) {
					//if ($value['child'] == 0) continue;
					$str .= ' disabled';
				}
				$string .= $str . '>' . ($value['parentid']==0 ? '' : $pre_fix) . $value[$this->name] . '</option>';
				$string .= $this->get_model_tree($data, $value[$this->id], $select_id, '&nbsp;&nbsp;' . $pre_fix, $modelid, $_modelid, $_groupid, $_roleid);
			}
		}
		return $string ;
	}
	
	/**
	 * 无限级分类树-获取数据
	 * 
	 * @param  array 	$data 			树的数组
	 * @param  int   	$parent_id 		初始化树时候，代表ID下的所有子集
	 * @param  string  	$prefix  		前缀
	 * @return string|array
	 */
	public function get_tree_data($data, $parent_id = 0, $pre_fix='|-') {
		if (!$data || !is_array($data)) return '';
		foreach ($data as $key => $value) {
			if ($value[$this->parentid] == $parent_id) {
				$this->data[$key] = $value;
				$this->data[$key]['prefix'] = $pre_fix . $value[$this->name];
				$this->get_tree_data($data, $value[$this->id], '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $pre_fix);
			}
		}
		return $this->data ;
	}
	
	
	/**
	 * 无限级分类树-获取子类
	 * 
	 * @param  array $data 树的数组
	 * @param  int   $id   父类ID
	 * @return string|array
	 */
	public function get_child($data, $id) {
		if (!$data || !is_array($data)) return array();
		$temp_array = array();
		foreach ($data as $value) {
			if ($value[$this->parentid] == $id) {
				$temp_array[] = $value;
			}
		}
		return $temp_array;
	}
	
	/**
	 * 无限级分类树-获取父类
	 * 
	 * @param  array $data 树的数组
	 * @param  int   $id   子类ID
	 * @return string|array
	 */
	public function get_parent($data, $id) {
		if (!$data || !is_array($data)) return array();
		$temp = array();
		foreach ($data as $vaule) {
			$temp[$vaule[$this->id]] = $vaule;
		}
		$parentid = $temp[$id][$this->parentid];
		return $temp[$parentid];
	}
	
}