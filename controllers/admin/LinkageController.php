<?php

class LinkageController extends Admin {
    
    private $link;
	private $level;
	private $childnode;
    
    public function __construct() {
		parent::__construct();
		$this->link  = $this->model('linkage');
		$this->level = 0;
		$this->childnode = array();
	}
    
    public function indexAction() {
	    if ($this->isPostForm()) {
			if (!auth::check($this->roleid, 'linkage-del', 'admin')) $this->adminMsg(lang('a-com-0', array('1'=>'linkage', '2'=>'del')));
		    $ids = $this->post('ids');
			if (empty($ids)) $this->adminMsg(lang('a-tag-0'));
			foreach($ids as $id) {
			    $id = intval($id);
				$this->link->delete('keyid=' . $id);
				$this->link->delete('id=' . $id);
		    }
			$this->adminMsg($this->getCacheCode('linkage') . lang('success'), url('admin/linkage/'), 3, 1, 1);
		}
		$this->view->assign('data', $this->link->where('keyid=0')->select());
		$this->view->display('admin/linkage');
    }
	
	/*
	 * 子菜单列表
	 */
	public function listAction() {
	    $keyid    = $this->get('keyid');
		if (empty($keyid)) $this->adminMsg(lang('a-lin-1'));
		if ($this->isPostForm()) {
		    $listorders = $this->post('listorders');
			foreach($listorders as $id=>$value) {
			    $value = intval($value);
			    $this->link->update(array('listorder'=>$value), 'id=' . $id);
		    }
			$this->adminMsg($this->getCacheCode('linkage') . lang('success'), url('admin/linkage/list', array('keyid'=>$keyid)));
		}
		$tree       = $this->instance('linkage_tree');
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$parentid = (int)$this->get('parentid');
		$page       = $this->get('page') ? $this->get('page') : 1;
	    $pagelist   = $this->instance('pagelist');
		$pagesize   = 130;
		$pagelist->loadconfig();
		$toplink	= $this->link->find($keyid);
		$where      = $toplink['site'] ? 'site=' . $this->siteid . ' AND keyid=' . $keyid : 'keyid=' . $keyid;
		$total      = $this->link->count('linkage', 'id', $where);
		if ($total > $pagesize) {
		    //若本页无法全部显示，就只统计本级菜单。
		    $where .= ' and parentid=' . $parentid;
			$total  = $this->link->count('linkage', 'id', $where);
			$parent = 1;
		}
		$result     = $this->link->page_limit($page, $pagesize)->where($where)->order(array('listorder ASC', 'id ASC'))->select();
		$pagelist   = $pagelist->total($total)->url(url('admin/linkage/list', array('keyid'=>$keyid, 'parentid'=>$parentid, 'page'=>'{page}')))->num($pagesize)->page($page)->output();
		$sdata      = array();
		foreach($result as $t){
			$sdata[$t['id']] = $t;
			$sdata[$t['id']]['option']  = ($parent && $this->isLastNode($t['keyid'], $t['id'])) ? '<a href="' . url('admin/linkage/list', array('keyid'=>$keyid, 'parentid'=>$t['id'])) . '">' . lang('a-lin-0') . '</a> | ' : '';
			!admin_auth($this->userinfo['roleid'], 'linkage-addson') or $sdata[$t['id']]['option'] .= '<a href="' . url('admin/linkage/addson', array('id'=>$t['id'])) . '">' . lang('a-add') . '</a> | ';
			!admin_auth($this->userinfo['roleid'], 'linkage-editson') or $sdata[$t['id']]['option'] .= '<a href="' . url('admin/linkage/editson', array('id'=>$t['id'])) . '">' . lang('a-edit') . '</a> | ';
			!admin_auth($this->userinfo['roleid'], 'linkage-del') or $sdata[$t['id']]['option'] .= '<a href="javascript:del(' . $t['id'] . ');">' . lang('a-del') . '</a>  ';
		}
		$pdata = $this->link->find($parentid, 'parentid');
		if ($pdata) $pstr = "<a href='" . url('admin/linkage/list',array('keyid'=>$keyid,'parentid'=>$pdata['parentid'])) . "'>" . lang('a-lin-2') . "</a> | ";
		$str  = "<tr>
					<td align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input-text-c'></td>
					<td align='left'>\$id</td>
					<td>\$spacer\$name</td>
					<td align='left'>" . $pstr . "\$option</td>
				</tr>";
		$tree->init($sdata);
		$list = $tree->get_tree($parentid, $str);
		$this->view->assign(array(
		    'list'     => $list,
			'parentid' => $parentid,
			'pagelist' => $pagelist,
			'keyid'    => $keyid,
		));
		$this->view->display('admin/linkage_list');
    }
    
	/*
	 * 添加顶级菜单
	 */
	public function addAction() {
	    if ($this->isPostForm()) {
			$name = $this->post('name');
			if (empty($name)) $this->adminMsg(lang('a-lin-3'));
			$data = array(
				'name' => $name,
				'site' => $this->post('site'),
			);
			$this->link->insert($data);
			$this->adminMsg($this->getCacheCode('linkage') . lang('success'), url('admin/linkage/'), 3, 1, 1);
		}
	    $this->view->display('admin/linkage_add');
	}
	
	/*
	 * 修改顶级菜单
	 */
	public function editAction() {
	    $id   = (int)$this->get('id');
		$data = $this->link->find($id);
		if (empty($data)) $this->adminMsg(lang('a-lin-4'));
	    if ($this->isPostForm()) {
			$name = $this->post('name');
			if (empty($name)) $this->adminMsg(lang('a-lin-3'));
			$data = array(
				'name' => $name,
				'site' => $this->post('site'),
			);
			$this->link->update($data, 'id='. $id);
			$this->adminMsg($this->getCacheCode('linkage') . lang('success'), url('admin/linkage/'), 3, 1, 1);
		}
		$this->view->assign('data', $data);
	    $this->view->display('admin/linkage_add');
	}
	
	/*
	 * 添加子级菜单
	 */
	public function addsonAction() {
	    $id    = (int)$this->get('id');
		$keyid = $this->get('keyid');
		if ($id) {
		    $data  = $this->link->find($id, 'keyid');
            $keyid = $keyid ? $keyid : $data['keyid'];			
		}
	    if ($this->isPostForm()) {
			$names = $this->post('name');
			if (empty($names)) $this->adminMsg(lang('a-lin-3'));
			$names = explode(chr(13), $names);
			$pid   = $this->post('parentid'); 
			foreach ($names as $name) {
			    $n = trim($name);
			    if (!empty($n)) {
				    $sdata = array(
					    'keyid'    => $keyid,
						'site'     => $this->siteid,
						'name'     => $n,
						'parentid' => $pid,
					);
					$this->link->insert($sdata);
				}
			}
			$this->link->update(array('child'=>1), 'id=' . $pid);
			$this->adminMsg($this->getCacheCode('linkage') . lang('success'), url('admin/linkage/list', array('keyid'=>$keyid, 'parentid'=>$pid)), 3, 1, 1);
		}
		//菜单列表
		$tree    = $this->instance('linkage_tree');
		$linkage = $this->cache->get('linkage');
		$string  = "<select name='parentid' id='parentid'>\n<option value=" . $id . ">" . lang('a-lin-5') . "</option>\n";
		$datas   = $linkage[$keyid]['data'];
		$str     = "<option value='\$id' \$selected>\$spacer \$name</option>";
		$tree->init($datas);
		$string .= $tree->get_tree($id, $str);
		$string .= '</select>';
		$this->view->assign(array(
		    'data'     => $data,
			'select'   => $string,
			'keyid'    => $keyid,
			'parentid' => $data['parentid'],
		));
	    $this->view->display('admin/linkage_addson');
	}
	
	/*
	 * 修改子级菜单
	 */
	public function editsonAction() {
	    $id   = (int)$this->get('id');
		$data = $this->link->find($id);	
		if (empty($data)) $this->adminMsg(lang('a-lin-4'));
	    if ($this->isPostForm()) {
			$name  = $this->post('name');
			if (empty($name)) $this->adminMsg(lang('a-lin-3'));
			$sdata = array(
				'parentid' => $this->post('parentid'),
				'name'     => trim($name),
			);
			$this->link->update($sdata, 'id=' . $id);
			$this->link->update(array('child'=>1), 'id=' . $this->post('parentid'));
			$this->adminMsg($this->getCacheCode('linkage') . lang('success'), url('admin/linkage/list', array('keyid'=>$data['keyid'], 'parentid'=>$this->post('parentid'))), 3, 1, 1);
		}
		//菜单列表
		$tree    = $this->instance('linkage_tree');
		$linkage = $this->cache->get('linkage');
		$string  = "<select name='parentid' id='parentid'>\n<option value=" . $data['parentid'] . ">" . lang('a-lin-6') . "</option>\n";
		$datas   = $linkage[$data['keyid']]['data'];
		$str     = "<option value='\$id' \$selected>\$spacer \$name</option>";
		$tree->init($datas);
		$string .= $tree->get_tree(0, $str, $data['parentid']);
		$string .= '</select>';
		$this->view->assign(array(
		    'data'     => $data,
			'select'   => $string,
			'keyid'    => $data['keyid'],
			'parentid' => $data['parentid'],
			'edit'     => 1,
		));
	    $this->view->display('admin/linkage_addson');
	}
	
	/**
	 * 删除菜单
	 */
	public function delAction() {
		$id    = (int)$this->get('id');
		$keyid = $this->get('keyid');
		if (empty($id) || empty($keyid)) $this->adminMsg(lang('a-lin-7'));
		$this->getChildNode($id);
		if(is_array($this->childnode)){
			foreach($this->childnode as $sid) {
				$this->link->delete('id=' . $sid);
			}
		}
		$this->childnode = array();
		$this->link->delete('id=' . $id);
		$this->adminMsg($this->getCacheCode('linkage') . lang('success'), url('admin/linkage/list', array('keyid'=>$keyid)), 3, 1, 1);
	}
	
	/**
	 * 获取联动菜单子节点
	 */
	private function getChildNode($id) {
		$this->childnode[] = (int)$id;
		$result = $this->link->where('parentid=' . $id)->select();
		if ($result) {
			foreach($result as $r) {
				$this->getChildNode($r['id']);
			}
		}
	}
	
	/*
	 * 菜单缓存
	 * array(
	        顶级菜单id => array(
			                  ...内容
							  'data' => array( 
							      //所有子菜单
							  ),
			              )
	   )
	 */
    public function cacheAction($show=0, $site_id=0) {
		if ($show == 0 && !$this->get('qok')) {
            $this->adminMsg(lang('a-lin-8'), url('admin/linkage/cache', array('qok'=>1)), 0, 1, 2);
        }
		$data = $this->link->where('keyid=0')->select();
		$list = $site = array();
        $site_id = $site_id ? $site_id : ($_GET['siteid'] ? $_GET['siteid'] : $this->siteid);
		foreach ($data as $t) {
		    $keyid = $t['id'];
			if (empty($t['site'])) { //全局类型
				$list[$keyid] = $t;
				$sdata = $this->link->from('linkage')->where('keyid=' . $keyid)->order(array('parentid ASC', 'listorder ASC', 'id ASC'))->select();
				$_data = $this->getChildData($t['parentid'], $sdata);
				$list[$keyid]['data'] = $this->setChildData($t['parentid'], $_data);
			} else {
				$site[$keyid] = $t;
				$sdata = $this->link->from('linkage')->where('keyid=' . $keyid . ' AND site=' . $site_id)->order(array('parentid ASC', 'listorder ASC', 'id ASC'))->select();
				$_data = $this->getChildData($t['parentid'], $sdata);
				$site[$keyid]['data'] = $this->setChildData($t['parentid'], $_data);
			}
		}
		$this->cache->set('linkage', $list);
		$this->cache->set('linkage_' . $site_id, $site);
	    $show or $this->adminMsg(lang('a-update') . '(' . runtime() . 's)', '', 3, 1, 1);
	}
	
	/**
	 * 递归调出菜单所有子菜单
	 */
	private function getChildData($parentid, $data) {
		$child = array();
		foreach ($data as $k=>$t) {
		    //遍历是否有子菜单
			$arrchilds = $mark = NULL;
			if ($t['child']) {
				foreach ($data as $c) {
					if ($c['parentid'] == $t['id']) {
						$arrchilds .= $c['id'] . ',';
						$mark = true;
					} else {
					    if ($mark) break;
					}
				}
			}
			if ($arrchilds) {
				$arrchilds .= $t['id'];
				$data[$k]['arrchilds'] = $arrchilds;
			}
			if ($t['parentid'] == $parentid) {
			    //下属子菜单
			    $t['arrchilds']  = $arrchilds; //兼容以往版本
				$child[$t['id']] = $t;
				if ($arrchilds) {
				    $sondata = $this->getChildData($t['id'], $data);
					$child   = $child + $sondata;
				}
			}
		}
		return $child;
	}
	
	/**
	 * 递归设置菜单所有子菜单组
	 */
	private function setChildData($parentid, $data) {
		foreach ($data as $k=>$t) {
			if ($t['arrchilds']) {
				$data[$k]['arrchilds'] = $this->getArrchildid($t['id'], $data);
			}
		}
		return $data;
	}
	
	/**
	 * 获取子菜单ID列表
	 */
	private function getArrchildid($id, $data) {
		$arrchildid = $id;
		foreach ($data as $m) {
			if ($m['parentid'] && $m['id'] != $id && $m['parentid'] == $id) {
				$arrchildid .= ',' . $this->getArrchildid($m['id'], $data);
			}
		}
		return $arrchildid;
	}
	
	/*
	 * 查询是否有子菜单
	 */
    private function isLastNode($keyid, $id) {
		$result = $this->link->count('linkage', 'id', 'keyid=' . $keyid . ' AND parentid=' . $id);
		return $result ? true : false;
	}
}