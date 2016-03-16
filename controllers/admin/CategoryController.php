<?php

class CategoryController extends Admin {
    
    private $tree;
    
    public function __construct() {
		parent::__construct();
		$this->tree	= $this->instance('tree');
		$this->tree->config(array('id' => 'catid', 'parent_id' => 'parentid', 'name' => 'catname'));
	}
	
	/**
	 * 栏目列表
	 */
	public function indexAction() {
	    if ($this->post('submit')) {
	        foreach ($_POST as $var => $value) {
	            if (strpos($var, 'order_') !== false) {
	                $this->category->update(array('listorder'=>$value), 'catid=' . (int)str_replace('order_', '', $var));
	            }
	        }
			$this->adminMsg($this->getCacheCode('category') . lang('success'), url('admin/category/index'), 3, 1, 1);
	    }
		if ($this->post('delete')) {
			$ids = $this->post('ids');
			if ($ids) {
			    foreach($ids as $catid) {
				    $this->delAction($catid, 1);
				}
			}
			$this->adminMsg($this->getCacheCode('category') . lang('success'), url('admin/category/index'), 3, 1, 1);
	    }
		$data = $this->category->getData();
        $form = $this->get_model('form');
        $model = $this->get_model();
        if ($form) {
            foreach ($form as $i => $t) {
                if ($t['joinid']) {
                    unset($form[$i]);
                }
            }
        }
        if ($form) {
            $model = $model + $form;
        }
	    $this->view->assign(array(
			'model' => $model,
			'list' => $this->tree->get_tree_data($data)
		));
		$this->view->display('admin/category_list');
	}
	
	/**
	 * 添加栏目
	 */
	public function addAction() {
	    if ($this->post('submit')) {
	        $data = $this->post('data');
	        if ($data['typeid'] == 1) {
	            if (empty($data['modelid'])) {
                    $this->adminMsg(lang('a-cat-0'));
                }
	        } elseif ($data['typeid'] == 4) {
                if (empty($data['modelid2'])) {
                    $this->adminMsg(lang('a-cat-0'));
                }
            } elseif ($data['typeid'] == 2) {
	            if (empty($data['content'])) {
                    $this->adminMsg(lang('a-cat-1'));
                }
	        } elseif ($data['typeid'] == 3) {
	            if (empty($data['urlpath'])) {
                    $this->adminMsg(lang('a-cat-2'));
                }
	        } else {
	            $this->adminMsg(lang('a-cat-3'));
	        }
	        if ($this->post('addall')) {
			    $names = $this->post('names');
				if (empty($names)) {
                    $this->adminMsg(lang('a-cat-4'));
                }
				$names = explode(PHP_EOL, $names);
				$y = $n = 0;
				foreach ($names as $val) {
				    list($catname, $catdir) = explode('|', $val);
					$catdir = $catdir ? $catdir : word2pinyin($catname);
					if ($data['typeid'] != 3 && $this->category->check_catdir(0, $catdir)) {
                        $catdir.= rand(0, 9);
                    }
					$data['url'] = '';
					$data['catdir'] = $catdir;
					$data['catname'] = $catname;
				    $data['setting'] = $this->post('setting');
				    $catid = $this->category->set(0, $data);
					if (!is_numeric($catid)) {
					    $n++;
					} else {
					    $this->category->url($catid, $this->getCaturl($data));
						$y++;
					}
				}
				$this->adminMsg($this->getCacheCode('category') . lang('a-cat-5', array('1' => $y, '2' => $n)), url('admin/category/index'), 3, 1, 1);
			} else {
				if (empty($data['catname'])) {
                    $this->adminMsg(lang('a-cat-4'));
                }
				if ($data['typeid'] != 3
                    && $this->category->check_catdir(0, $data['catdir'])) {
                    $this->adminMsg(lang('a-cat-6'));
                }
				$data['url'] = '';
				$data['setting'] = $this->post('setting');
				$result = $this->category->set(0, $data);
				if (!is_numeric($result)) {
                    $this->adminMsg($result);
                }
				$data['catid'] = $result;
				$this->category->url($result, $this->getCaturl($data));
				$this->adminMsg($this->getCacheCode('category') . lang('success'), url('admin/category/index'), 3, 1, 1);
			}
	    }
        $form = $this->get_model('form');
        if ($form) {
            foreach ($form as $i => $t) {
                if ($t['joinid']) {
                    unset($form[$i]);
                }
            }
        }
	    $model = $this->get_model();
	    $catid = (int)$this->get('catid');
		$json_m = json_encode($model);
	    $this->view->assign(array(
			'add' => 1,
            'form' => $form,
	        'model' => $model,
			'rolemodel' => $this->user->get_role_list(),
	        'json_model' => $json_m ? $json_m : '""',
			'membergroup' => $this->cache->get('membergroup'),
			'membermodel' => $this->membermodel,
	        'category_select' => $this->tree->get_tree($this->cats, 0, $catid)
	    ));
	    $this->view->display('admin/category_add');
	}
	
	/**
	 * 修改栏目
	 */
    public function editAction() {
	    if ($this->post('submit')) {
	        $catid = (int)$this->post('catid');
            if (empty($catid)) {
                $this->adminMsg(lang('a-cat-7'));
            }
	        $data = $this->post('data');
	        if (empty($data['catname'])) {
                $this->adminMsg(lang('a-cat-4'));
            }
	        if ($this->post('typeid') == 1 && $this->category->check_catdir($catid, $data['catdir'])) {
                $this->adminMsg(lang('a-cat-6'));
            }
	        $data['typeid'] = $this->post('typeid');
			$data['setting'] = $this->post('setting');
	        $result = $this->category->set($catid, $data);
	        if (is_numeric($result)) {
				$data['catid'] = $result;
				$this->category->url($result, $this->getCaturl($data));
	            $this->adminMsg($this->getCacheCode('category') . lang('success'), url('admin/category/edit', array('catid'=>$catid)), 3, 1, 1);
	        } else {
	            $this->adminMsg(lang('a-cat-8'));
	        }
	    }
        $catid = (int)$this->get('catid');
        if (empty($catid)) {
            $this->adminMsg(lang('a-cat-7'));
        }
		if (!isset($this->cats[$catid])) {
            $this->adminMsg(lang('m-con-9', array('1' => $catid)));
        }
        $data = $this->category->find($catid);
	    $model = $this->get_model();

        $form = $this->get_model('form');
        if ($form) {
            foreach ($form as $i => $t) {
                if ($t['joinid']) {
                    unset($form[$i]);
                }
            }
        }

		$json_m = json_encode($model);
	    $this->view->assign(array(
	        'data' => $data,
	        'form' => $form,
	        'model' => $model,
	        'catid' => $catid,
			'setting' => string2array($data['setting']),
			'rolemodel' => $this->user->get_role_list(),
	        'json_model' => $json_m ? $json_m : '""',
			'membergroup' => $this->cache->get('membergroup'),
			'membermodel' => $this->membermodel,
	        'category_select' => $this->tree->get_tree($this->cats, 0, $data['parentid'])
	    ));
	    $this->view->display('admin/category_add');
	}
	
	/**
	 * 删除栏目
	 */
	public function delAction($catid=0, $all=0) {
        if (!auth::check($this->roleid, 'category-del', 'admin')) {
            $this->adminMsg(lang('a-com-0', array('1' => 'category', '2' => 'del')));
        }
	    $all = $all ? $all : $this->get('all');
	    $catid = $catid ? $catid : (int)$this->get('catid');
        if (empty($catid)) {
            $this->adminMsg(lang('a-cat-7'));
        }
		if (!isset($this->cats[$catid])) {
            $this->adminMsg(lang('m-con-9', array('1' => $catid)));
        }
        $result= $this->category->del($catid);
	    if ($result) {
	        $all or $this->adminMsg($this->getCacheCode('category') . lang('success'), url('admin/category/index'), 3, 1, 1);
	    } else {
	        $all or $this->adminMsg(lang('a-cat-8'));
	    }
	}
	
	/**
	 * 批量URL规则
	 */
	public function urlAction() {
	    if ($this->post('submit')) {
			$count  = 0;
	        $catids = $this->post('catids');
            if (empty($catids)) {
                $this->adminMsg(lang('a-cat-9'));
            }
	        foreach ($catids as $catid) {
			    if ($catid && isset($this->cats[$catid])) {
				    $setting = $this->cats[$catid]['setting'];
					$setting['url'] = $this->post('url');
					$setting = array2string($setting);
					$this->category->update(array('setting' => $setting), 'catid=' . $catid);
					$count ++;
				}
			}
			$this->adminMsg($this->getCacheCode('category') . lang('a-cat-10', array('1' => $count)), url('admin/category'), 3, 1, 1);
	    }
	    $this->view->assign('category', $this->tree->get_tree($this->cats));
	    $this->view->display('admin/category_url');
	}
	
	/**
	 * 调用父级栏目url规则
	 */
	public function ajaximportAction() {
	    $catid = (int)$this->get('catid');
		if (empty($catid)) {
            exit(json_encode(array('status' => 0)));
        }
		$data = $this->category->find($catid);
		if (empty($data))  {
            exit(json_encode(array('status' => 0)));
        }
		$setting = string2array($data['setting']);
		$return = array(
			'list' => isset($setting['url']['list']) ? $setting['url']['list'] : '',
			'show' => isset($setting['url']['show']) ? $setting['url']['show'] : '',
		    'status' => 1,
			'catjoin' => isset($setting['url']['catjoin']) ? $setting['url']['catjoin'] : '/',
			'show_page' => isset($setting['url']['show_page']) ? $setting['url']['show_page'] : '',
			'list_page'	=> isset($setting['url']['list_page']) ? $setting['url']['list_page'] : ''
		);
		exit(json_encode($return));
	}
	
	/**
	 * 更新栏目缓存
	 * array(
	 *     '栏目ID' => array(
	 *                     ...栏目信息
	 *                     ...模型表名称
	 *                 ),
	 * );
	 */
	public function cacheAction($show=0, $site_id=0) {
	    $this->category->repair(); //递归修复栏目数据
        $site_id = $site_id ? $site_id : ($_GET['siteid'] ? $_GET['siteid'] : $this->siteid);
	    $model = $this->get_model('content', $site_id);
	    $data = $this->category->getData($site_id); //数据库查询最新数据
		$siteid = $this->category->getSiteId($site_id);
	    $category = $category_dir = $count = array();
	    foreach ($data as $t) {
	        $catid = $t['catid'];
	        $category[$catid] = $t;
	        if ($t['typeid'] == 1) {
	            $category[$catid]['tablename'] = $model[$t['modelid']]['tablename'];
	            $category[$catid]['modelname'] = $model[$t['modelid']]['modelname'];
	        }
			$category[$catid]['arrchilds'] = $catid; //所有子栏目集,默认当前栏目ID
	        if ($t['typeid'] != 3) {
				if ($t['child']) {
                    $category[$catid]['arrchilds'] = $this->category->child($catid) . $catid;
                }
				//统计数据
	            $count[$catid]['items'] = (int)$this->content->_count($site_id, 'catid IN (' . $category[$catid]['arrchilds'] . ') and `status`<>0');
	            if ($site_id == $siteid) {
					$category[$catid]['items'] = $count[$catid]['items'];
					$this->category->update(array('items' => $count[$catid]['items']), 'catid=' . $catid);
				}
	        }
	        //把预定义的 HTML 实体转换为字符
	        $category[$catid]['content'] = htmlspecialchars_decode($category[$catid]['content']);
			//转换setting
			$category[$catid]['setting'] = string2array($category[$catid]['setting']);
			//更新分页数量
			if (empty($t['pagesize'])) {
			    $pcat = $this->category->getParentData($catid);
			    $category[$catid]['pagesize'] = $pcat['pagesize'] ? $pcat['pagesize'] : $this->site['SITE_SEARCH_PAGE'];
				$this->category->update(array('pagesize' => $category[$catid]['pagesize']), 'catid=' . $catid);
			}
	    }
		//更新URL与栏目模型id集合
		foreach ($data as $t) {
			$category[$t['catid']]['url'] = $url = $this->getCaturl($t);
			$this->category->update(array('url' => $url), 'catid=' . $t['catid']);
			$category_dir[$t['catdir']] = $t['catid'];
			if ($t['child'] == 0) {
				$category[$t['catid']]['arrmodelid'][] = $t['modelid'];
			} else {
				$category[$t['catid']]['arrmodelid'] = array();
				$ids = _catposids($t['catid'], null, $category);
				$ids = explode(',', $ids);
				foreach ($ids as $id) {
					if ($id && $id != $t['catid']) {
						$category[$t['catid']]['arrmodelid'][] = $category[$id]['modelid'];
					}
				}
			}
			$category[$t['catid']]['arrmodelid'] = array_unique($category[$t['catid']]['arrmodelid']);
	    }
	    //保存到缓存文件
		if ($site_id == $siteid) {
			$this->cache->set('category_' . $siteid,  $category);
		} else {
			$this->cache->set('category_' . $site_id, $count);
		}
	    $this->cache->set('category_dir_' . $site_id, $category_dir);
	    $show or $this->adminMsg(lang('a-update'), url('admin/category/index'), 3, 1, 1);
	}
}