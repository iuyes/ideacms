<?php

class PositionController extends Admin {
    
    protected $position;
    protected $position_data;
    
    public function __construct() {
		parent::__construct();
		$this->position      = $this->model('position');
		$this->position_data = $this->model('position_data');
	}
	
	public function indexAction() {
	    if ($this->post('submit')) {
	        foreach ($_POST as $var => $value) {
	            if (strpos($var, 'del_') !== false) {
	                $id = (int)str_replace('del_', '', $var);
	                $this->position->del($id);
	            }
	        }
			$this->adminMsg($this->getCacheCode('position') . lang('success'), url('admin/position/index'), 3, 1, 1);
	    }
	    $data = $this->position->where('site=' . $this->siteid)->select();
	    $this->view->assign('list', $data);
	    $this->view->display('admin/position_list');
	}
	
	public function addAction() {
	    if ($this->post('submit')) {
	        $data = $this->post('data');
	        if (empty($data['name'])) $this->adminMsg(lang('a-pos-10'));
	        $data['maxnum'] = $data['maxnum'] ? $data['maxnum'] : 10;
	        if ($this->position->set(0, $data)) {
	            $this->adminMsg($this->getCacheCode('position') . lang('success'), url('admin/position/index'), 3, 1, 1);
	        } else {
	            $this->adminMsg(lang('failure'));
	        }
	    }
	    $this->view->display('admin/position_add');
	}
	
    public function editAction() {
        $posid = (int)$this->get('posid');
        if (empty($posid)) $this->adminMsg(lang('a-pos-11'));
	    if ($this->post('submit')) {
	        $data = $this->post('data');
	        if (empty($data['name'])) $this->adminMsg(lang('a-pos-10'));
	        $data['maxnum'] = $data['maxnum'] ? $data['maxnum'] : 10;
	        $this->position->set($posid, $data);
	        $this->adminMsg($this->getCacheCode('position') . lang('success'), url('admin/position/index'), 3, 1, 1);
	    }
	    $data = $this->position->find($posid);
	    if (empty($data)) $this->adminMsg(lang('a-pos-11'));
	    $this->view->assign('data', $data);
	    $this->view->display('admin/position_add');
	}
	
	public function delAction() {
	    $posid = (int)$this->get('posid');
	    if (empty($posid)) $this->adminMsg(lang('a-pos-11'));
	    $this->position->del($posid);
	    $this->adminMsg($this->getCacheCode('position') . lang('success'), url('admin/position/index'), 3, 1, 1);
	}
	
	public function listAction() {
	    $posid = (int)$this->get('posid');
	    if (empty($posid)) $this->adminMsg(lang('a-pos-11'));
	    if ($this->post('submit_order') && $this->post('form') == 'order') {
	        foreach ($_POST as $var => $value) {
	            if (strpos($var, 'order_') !== false) {
	                $id = (int)str_replace('order_', '', $var);
	                $this->position_data->update(array('listorder' => $value), 'id=' . $id);
	            }
	        }
			$this->adminMsg($this->getCacheCode('position') . lang('success'), url('admin/position/list/', array('posid' => $posid)), 3, 1, 1);
	    }
	    if ($this->post('submit_del') && $this->post('form') == 'del') {
	        foreach ($_POST as $var=>$value) {
	            if (strpos($var, 'del_') !== false) {
	                $id   = (int)str_replace('del_', '', $var);
					$data = $this->position_data->find($id, 'contentid');
					if ($data['contentid']) { //判断该推荐信息是否来至文档内容
					    $cdata = $this->content->get_extend_data($data['contentid']);
						if ($cdata['position']) {
						    $cp = @explode(',', $cdata['position']);
							$pn = array();
							foreach ($cp as $t) {
							    if ($t != $posid) $pn[] = $t;
							}
							$pn = @implode(',', $pn);
							$cdata['position'] = $pn; 
							$this->content->set_extend_data($data['contentid'], $cdata); //删除文档中的推荐位信息
						}
					}
	                $this->position_data->delete('id=' . $id);
	            }
	        }
			$this->adminMsg($this->getCacheCode('position') . lang('success'), url('admin/position/list/', array('posid' => $posid)), 3, 1, 1);
	    }
	    $this->view->assign(array(
	        'posid' => $posid,
	        'list'  => $this->position_data->where('posid=' . $posid)->order('listorder ASC')->select(),
	    ));
	    $this->view->display('admin/position_data_list');
	}
	
	public function adddataAction() {
	    $posid = (int)$this->get('posid');
	    if (empty($posid)) $this->adminMsg(lang('a-pos-11'));
	    if ($this->post('submit')) {
	        $data = $this->post('data');
	        if (empty($data['title']) || empty($data['url'])) $this->adminMsg(lang('a-pos-12'));
	        $data['posid'] = $posid;
	        if ($this->position_data->set(0, $data)) {
	            $this->adminMsg($this->getCacheCode('position') . lang('success'), url('admin/position/list/', array('posid' => $posid)), 3, 1, 1);
	        } else {
	            $this->adminMsg(lang('a-pos-13'));
	        }
	    }
	    
	    $position = $this->position->find($posid);
	    if (empty($position)) $this->adminMsg(lang('a-pos-11'));
	    $this->view->assign(array(
	        'posid'    => $posid,
	        'position' => $position
	    ));
	    $this->view->display('admin/position_data_add');
	}
	
    public function editdataAction() {
	    $id    = (int)$this->get('id');
	    $posid = (int)$this->get('posid');
	    if (empty($posid)) $this->adminMsg(lang('a-pos-11'));
	    if ($this->post('submit')) {
	        $data = $this->post('data');
	        if (empty($data['title']) || empty($data['url'])) $this->adminMsg(lang('a-pos-12'));
	        $data['posid'] = $posid;
	        if ($this->position_data->set($id, $data)) {
	            $this->adminMsg($this->getCacheCode('position') . lang('success'), url('admin/position/list/', array('posid' => $posid)), 3, 1, 1);
	        } else {
	            $this->adminMsg(lang('a-pos-13'));
	        }
	    }
	    $position = $this->position->find($posid);
	    if (empty($position)) $this->adminMsg(lang('a-pos-11'));
	    $data     = $this->position_data->find($id);
	    if (empty($data)) $this->adminMsg(lang('a-pos-14'));
	    $this->view->assign(array(
	        'data'     => $data,
	        'posid'    => $posid,
	        'position' => $position,
	    ));
	    $this->view->display('admin/position_data_add');
	}
	
	/**
	 * $POSITION 缓存格式
	 * array(
	 *     Posid => array(
	 *                  该推荐位信息列表
	 *              ),
	 * );
	 */
	public function cacheAction($show=0, $site_id=0) {
	    $data     = array();
        $site_id   = $site_id ? $site_id : ($_GET['siteid'] ? $_GET['siteid'] : $this->siteid);
	    $position = $this->position->where('site=' . $site_id)->select();
	    foreach ($position as $t) {
	        $posid        = $t['posid'];
	        $data[$posid] = $this->position_data->where('posid=' . $posid)->order('listorder ASC, id DESC')->select();
			if ($data[$posid]) {
			    foreach ($data[$posid] as $id=>$c) {
				    if ($c['contentid']) {
					    $row = $this->content->find($c['contentid']);
						if ($row && $row['url'] != $c['url']) {
						    $data[$posid][$id]['url'] = $row['url'];
							$this->position_data->update(array('url'=>$row['url']), 'id=' . $c['id']);
						}
					}
				}
			}
	        $data[$posid]['maxnum'] = $t['maxnum'];
	        $data[$posid]['catid']  = $t['catid'];
	    }
	    //写入缓存文件中
	    $this->cache->set('position_' . $site_id, $data);
	    $show or $this->adminMsg(lang('a-update'), '', 3, 1, 1);
	}
	
	
	/**
	 * 加载模板调用代码
	 */
	public function ajaxviewAction() {
	    $posid = (int)$this->get('posid');
	    $data  = $this->position->find($posid);
	    if (empty($data)) exit(lang('a-pos-12'));
	    $param = empty($data['catid']) ? $posid : $posid . ' catid=$catid';
	    $msg   = "<textarea id='position_" . $posid . "' style='font-size:12px;width:100%;height:80px;overflow:hidden;'>";
		$msg  .= '<!-- ' . $data['name'] . ' -->' . PHP_EOL;
	    $msg  .= '{list action=position id=' . $param . '}' . PHP_EOL;
	    $msg  .= '<!-- ' . lang('a-pos-15') . ' -->' . PHP_EOL;
	    $msg  .= '{/list}';
	    $msg  .= '</textarea>';
	    echo $msg;
	}
	
    /**
	 * 加载内容表中的信息
	 */
	public function ajaxloadinfoAction() {
	    $select = $this->content->order('updatetime DESC')->limit(0, 20);
	    $title  = $this->post('title');
		$catid  = $this->post('catid');
		$thumb  = $this->post('thumb');
		$select->where('`status`=1');
		if ($catid && $this->cats[$catid]['arrchilds']) $select->where('catid IN (' . $this->cats[$catid]['arrchilds'] . ')');
	    if ($title) $select->where('title like "%' . $title . '%"');
		if ($thumb) $select->where('thumb<>""');
	    $list   = $select->select();
		$tree   = $this->instance('tree');
		$tree->config(array('id' => 'catid', 'parent_id' => 'parentid', 'name' => 'catname'));
		$option = $tree->get_tree($this->cats, 0, null, '&nbsp;|-', 0);
	    $this->view->assign(array(
			'list'	   => $list,
			'category' => $option
		));
	    $this->view->display('admin/position_data_load');
	}
}