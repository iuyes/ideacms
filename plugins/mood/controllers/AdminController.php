<?php

class AdminController extends Plugin {
	
	private $mood;
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
		$this->mood = $this->model('mood');
    }
	
	public function indexAction() {
	    if ($this->isPostForm()) {
		    if ($this->post('form') == 'search') {
			    $kw    = $this->post('kw');
			    $catid = $this->post('catid');
			}
			if ($this->post('form') == 'del') {
			    $dels  = $this->post('dels');
				$ids   = implode(',', $dels);
				if ($ids) $this->mood->delete('id in (' . $ids . ')');
			}
		}
		$tree     = $this->instance('tree');
		$category = get_category_data();
		$tree->config(array('id' => 'catid', 'parent_id' => 'parentid', 'name' => 'catname'));
	    $kw       = $kw	? $kw : $this->get('kw');
	    $page     = $this->get('page') ? (int)$this->get('page') : 1;
	    $catid    = $catid ? $catid : (int)$this->get('catid');
		$catid    = $catid ? $catid : (int)$this->get('catid');
		//分页配置
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
		//数目
	    $where    = '';
	    if ($kw) $where .= " and a.title like '%" . $kw . "%'";
	    if ($catid && $category[$catid]['arrchilds']) $where .= " and a.catid in (" . $category[$catid]['arrchilds'] . ")";
		$content  = $this->model('content');
	    $result   = $content->execute('select count(a.id) as total from ' . $content->prefix . 'content_' . $this->siteid . ' as a, ' . $content->prefix . 'mood as b where a.id=b.contentid ' . $where, false);
	    $total    = $result['total'];
		$pagesize = 8;
		//查询
		$urlparam = array();
	    if ($kw)      $urlparam['kw']      = $kw;
	    if ($catid)   $urlparam['catid']   = $catid;
	    $urlparam['page']   = '{page}';
	    $pageurl  = purl('admin/index', $urlparam);
		$start_id = $total * ($page - 1);
		$data     = $content->execute('select * from ' . $content->prefix . 'content_' . $this->siteid . ' as a, ' . $content->prefix . 'mood as b where a.id=b.contentid ' . $where . ' order by a.updatetime desc limit ' . $start_id . ',' . $pagesize);
		$pagelist = $pagelist->total($total)->url($pageurl)->num($pagesize)->page($page)->output();
		//统计心情
		$this->assign(array(
			'list'     => $data,
		    'category' => $tree->get_tree($category, 0, null, "|-", true),
			'pagelist' => $pagelist
		));
	    $this->display('admin_list');
	}
	
	/*
	 * 配置信息
	 */
	public function configAction() {
	    //读取缓存文件
	    $mood = $this->cache->get('mood');
	    if ($this->isPostForm()) {
			$this->cache->set('mood', $this->post('data'));
			$this->adminMsg('操作成功', purl('admin/config'), 3, 1, 1);
		}
		if (empty($mood)) {
		    $mood = array(
			    'n1' => array(
				    'use'  => 1,
					'name' => '高兴',
					'pic'  => $this->viewpath . 'images/1.gif',
				),
				'n2' => array(
				    'use'  => 1,
					'name' => '感动',
					'pic'  => $this->viewpath . 'images/2.gif',
				),
				'n3' => array(
				    'use'  => 1,
					'name' => '同情',
					'pic'  => $this->viewpath . 'images/3.gif',
				),
				'n4' => array(
				    'use'  => 1,
					'name' => '愤怒',
					'pic'  => $this->viewpath . 'images/4.gif',
				),
				'n5' => array(
				    'use'  => 1,
					'name' => '搞笑',
					'pic'  => $this->viewpath . 'images/5.gif',
				),
				'n6' => array(
				    'use'  => 1,
					'name' => '难过',
					'pic'  => $this->viewpath . 'images/6.gif',
				),
				'n7' => array(
				    'use'  => 1,
					'name' => '新奇',
					'pic'  => $this->viewpath . 'images/7.gif',
				),
				'n8' => array(
				    'use'  => 1,
					'name' => '流汗',
					'pic'  => $this->viewpath . 'images/8.gif',
				),
				'n9' => array(
				    'use'  => 0,
					'name' => '',
					'pic'  => '',
				),
				'n10'=> array(
				    'use'  => 0,
					'name' => '',
					'pic'  => '',
				),
			);
		}
		$this->assign('data', $mood);
	    $this->display('admin_config');
	}

}