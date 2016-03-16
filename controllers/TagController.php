<?php

class TagController extends Common {
    
	protected $tag;
	
    public function __construct() {
		parent::__construct();
		$this->tag = $this->model('tag');
	}
	
	/**
	 * 关键词列表
	 */
	public function indexAction() {
	    $num = $this->site['SITE_KEYWORD_NUMS']  ? (int)$this->site['SITE_KEYWORD_NUMS'] : 50;
		$cache = $this->site['SITE_KEYWORD_CACHE'] ? (int)$this->site['SITE_KEYWORD_CACHE'] * 3600 : 0;
		$data  = $this->tag->getList($num, $cache);
	    $this->view->assign(array(
			'keyword'    => $data,
	        'meta_title' => lang('tag-0') . '-' . $this->site['SITE_NAME']
	    ));
		$this->view->display('keyword');
	}
	
	/**
	 * tag列表
	 */
	public function listAction() {
	    $kw = $this->get('kw');
		$data = $this->tag->getData($kw);
		$list = array();
		$where = $kws = '';
		if ($data) {
		    foreach ($data as $i=>$t) {
			    if ($t['name']) {
				    $kws.= ($i == 0 ? '' : ',') . $t['name'];
					$where .= ($i == 0 ? '' : ' OR ') . '(`title` LIKE "%' . $t['name'] . '%" OR `keywords` LIKE "%' . $t['name'] . '%" OR `description` LIKE "%' . $t['name'] . '%")';
				}
			}
			$where.= ' AND `status`=1';
			$page = (int)$this->get('page') > 0 ? (int)$this->get('page') : 1;
			$dbcache = $this->site['SITE_TAG_CACHE'] ? (int)$this->site['SITE_TAG_CACHE'] * 3600 : 0;
			$tagdata = $this->tag->listData($kw, $where, $dbcache);
			if (empty($tagdata)) {
			    header('HTTP/1.1 404 Not Found');
			    $this->msg(lang('tag-1', array('1' => $kw)));
			}
			$pagelist = $this->instance('pagelist');
			$pagelist->loadconfig();
			$pagesize = $this->site['SITE_TAG_PAGE'] ? $this->site['SITE_TAG_PAGE'] : 10;
			$start_id = $pagesize * ($page - 1);
			$list     = $this->content->execute($tagdata['sql'] . ' LIMIT ' . $start_id . ',' . $pagesize, true, $dbcache);
			$pageurl  = $this->site['SITE_TAG_URLRULE'] ? str_replace('{tag}', $kw, $this->site['SITE_TAG_URLRULE']) : url('tag/list', array('kw' => $kw, 'page' => '{page}'));
			$pagelist = $pagelist->total($tagdata['total'])->url($pageurl)->num($pagesize)->page($page)->output();
		} else {
		    header('HTTP/1.1 404 Not Found');
			$this->msg(lang('tag-1', array('1' => $kw)));
		}
	    $this->view->assign(array(
			'kw'            => $kws,
			'taglist'       => $list,
			'tagpage'       => $pagelist,
	        'meta_title'    => $kws . '-' . $this->site['SITE_NAME'], 
			'meta_keywords' => $kws
		));
		$this->view->display('tag');
	}
}