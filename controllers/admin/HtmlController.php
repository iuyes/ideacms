<?php

class HtmlController extends Admin {
    
    private $tree;
    
    public function __construct() {
		parent::__construct();
		$this->tree = $this->instance('tree');
		$ck_ob_function = function_exists('ob_start') ? 0 : 1;
		$this->tree->config(array('id' => 'catid', 'parent_id' => 'parentid', 'name' => 'catname'));
	    $this->view->assign('check', $this->dir_mode_info());
	    $this->view->assign('ck_ob', $ck_ob_function);
	    $this->view->assign('ismb', $this->site['SITE_MOBILE']);
	}
	
	/**
	 * 选项
	 */
	public function indexAction() {
	    $this->view->display('admin/html_list');
	}
	
    /**
	 * 栏目页生成静态
	 */
	public function categoryAction() {
	    if ($this->isPostForm()) {
			$sites = App::get_site();
		    $catid = $this->post('catid');
		    $isall = $catid ? 0 : 1;
			$this->cache->delete('html_cats');
			if ($this->site['SITE_EXTEND_ID']) {
                $this->adminMsg(lang('a-sit-29', array('1' => $sites[$this->site['SITE_EXTEND_ID']]['SITE_NAME'])));
            }
			foreach ($sites as $sid => $t) {
				if ($t['SITE_EXTEND_ID'] && $t['SITE_EXTEND_ID'] == $this->siteid) {
					$this->adminMsg(lang('a-sit-30', array('1' => $t['SITE_NAME'])));
				}
			}
			$this->adminMsg(lang('a-cat-101'), url('admin/html/category', array('submit' => 1, 'catid' => $catid, 'isall' => $isall)), 0, 1, 2);
		}
		$submit	= (int)$this->get('submit');
		if ($submit) {
			$catid = isset($catid) ? $catid : (int)$this->get('catid');
			$isall = isset($isall) ? $isall : (int)$this->get('isall');
			$page = $this->get('page') ? $this->get('page') : 1;
			$count = (int)$this->get('filecount');
			if ($catid && $isall == 0) {
				$key = (int)$this->get('key');
				$cats = $this->cache->get('html_cats');
				if ($cats == false) {
				    $cat = $this->cats[$catid];
					$cats = explode(',', $cat['arrchilds']);
					$this->cache->set('html_cats', $cats);
					$key = 0;
				}
				if (isset($cats[$key]) && $this->cats[$cats[$key]]) {
					$this->toCategory($cats[$key], $page, 0, $key, $count);
				} else {
				    $this->cache->delete('html_cats');
				    $this->adminMsg(lang('a-con-107') . '(' . $count . ')', '', 0, 1, 1);
				}
			} else {
				if (empty($catid)) {
				    $cats = $this->cats;
					$fcat = array_shift($cats);
					$catid = $fcat['catid'];
				}
				if (isset($this->cats[$catid])) {
					$this->toCategory($catid, $page, 1, 0, $count);
				} else {
					$this->cache->delete('html_cats');
				    $this->adminMsg(lang('a-con-107') . '(' . $count . ')', '', 0, 1, 1);
				}
			}
		} else {
	        $this->view->assign('category_select', $this->tree->get_tree($this->cats, 0));
	        $this->view->display('admin/html_create');
		}
	}

    /**
	 * 内容页生成静态
	 */
	public function showAction() {
		if ($this->isPostForm()) {
			$this->cache->delete('html_cats');
			$this->adminMsg(lang('a-cat-101'), url('admin/html/show', array('submit' => 1, 'catid' => $this->post('catid'), 'totime' => $this->post('totime'))), 0, 1, 2);
		}
		$submit = (int)$this->get('submit');
		if ($submit) {
			$page = $this->get('page') ? $this->get('page') : 1;
			$catid = (int)$this->get('catid');
			$totime	= (int)$this->get('totime');
			$tohtml	= array();
			$filecount	= (int)$this->get('filecount');
			//分析能生成静态的栏目
			if (empty($catid)) {
				foreach ($this->cats as $i=>$t) {
					if ($t['setting']['url']['use'] == 1 && $t['setting']['url']['tohtml'] == 1) {
						$tohtml[] = $i;
					}
				}
			} else {
				$array = $this->cats[$catid]['arrchilds'];
				$array = explode(',', $array);
				foreach ($array as $i) {
					if ($this->cats[$i]['setting']['url']['use'] == 1 && $this->cats[$i]['setting']['url']['tohtml'] == 1) {
						$tohtml[] = $i;
					}
				}
			}
			if (empty($tohtml)) {
				$this->cache->delete('html_cats');
				$this->adminMsg(lang('a-mod-143'));
			} else {
				$cats = implode(',', $tohtml);
				$this->cache->set('html_cats', $cats);
				$this->toContent($cats, $page, $filecount, $totime);
			}
		} else {
	        $this->view->assign('category_select', $this->tree->get_tree($this->cats, 0));
	        $this->view->display('admin/html_create');
		}
	}
	
	/**
	 * 表单生成静态
	 */
	public function formAction() {
		if ($this->isPostForm()) {
			$this->adminMsg(lang('a-cat-101'), url('admin/html/form', array('submit' => 1, 'mid' => $this->post('mid'))), 0, 1, 2);
		}
		$submit	= (int)$this->get('submit');
		$form = $this->get_model('form');
		if ($submit) {
			$mid = isset($mid) ? $mid : (int)$this->get('mid');
			$page = $this->get('page') ? $this->get('page') : 1;
			$count = (int)$this->get('count');
			if (!isset($form[$mid]['tablename'])) {
                $this->adminMsg(lang('a-cat-109', array('1' => $mid)));
            }
			if (!isset($form[$mid]['setting']['form']['url']['tohtml']) || empty($form[$mid]['setting']['form']['url']['tohtml'])) {
				$this->adminMsg(lang('a-cat-110'));
			}
			$total = $this->content->count($form[$mid]['tablename'], 'id', '`status`=1', null, 3600);
			$pagesize  = 10;
			$totalpage = ceil($total/$pagesize); //该表单的总页数
			$totalpage = $totalpage ? $totalpage : 1;
			//生成
			$data = $this->content->from($form[$mid]['tablename'])->page_limit($page, $pagesize)->where('`status`=1')->order('id ASC')->select();
			if (empty($data)) {
                $this->adminMsg(lang('a-con-107') . '(' . $count . ')', '', 0, 1, 1);
            }
			foreach ($data as $t) {
				if ($this->createForm($mid, $t)) {
                    $count++;
                }
			}
			$nextpage = $page + 1;
			if ($page >= $totalpage) {
				$this->adminMsg(lang('a-con-107') . '(' . $count . ')', '', 0, 1, 1);
			} else {
				$this->adminMsg('【' . $form[$mid]['modelname'] . '】(' . $page.'/' . $totalpage .')', url('admin/html/form',array('page' => $nextpage, 'mid' => $mid, 'submit' => 1,'count' => $count)), 3, 1, 2);
			}
		} else {
	        $this->view->assign('list', $form);
	        $this->view->display('admin/html_form');
		}
	}
	
	/**
	 * 生成首页
	 */
	public function indexcAction() {
		if ($this->site['SITE_MOBILE']) {
			$this->adminMsg(lang('da014'));
		}
		ob_start();
		$this->view->assign(array(
	        'indexc' => 1,
	        'meta_title' => $this->site['SITE_TITLE'],
	        'meta_keywords' => $this->site['SITE_KEYWORDS'],
	        'meta_description' => $this->site['SITE_DESCRIPTION'],
	    ));
		$this->view->setTheme(true);
		$this->view->display('index');
		$this->view->setTheme(false);
		$sites = App::get_site();
		if (count($sites) > 1) {
			$size = file_put_contents(APP_ROOT . 'cache/index/' . $this->siteid . '.html', ob_get_clean(), LOCK_EX);
			@unlink(APP_ROOT . 'index.html');
		} else {
			$size = file_put_contents(APP_ROOT . 'index.html', ob_get_clean(), LOCK_EX);
		}
		$this->adminMsg(lang('a-con-107') . '(' . formatFileSize($size) . ')', '', 3, 1, 1);
	}
	
	/**
	 * 清理所有静态文件
	 */
	public function clearAction() {
	    $submit	= (int)$this->get('submit');
		if (empty($submit)) {
            $this->adminMsg(lang('a-con-108'), url('admin/html/clear', array('submit' => 1)), 3, 1, 2);
        }
	    @unlink('index.html');
		$htmlfiles	= $this->cache->get('html_files');
		if (empty($htmlfiles)) {
            $this->adminMsg(lang('a-con-109'), '', 3, 1, 1);
        }
		$htmlfiles	= array_unique($htmlfiles);
		$f = $d = 0;
		if (is_array($htmlfiles)) {
		    $dirs = array();
		    foreach ($htmlfiles as $file) {
			    $dir = dirname($file);
			    $dirs[$dir] = 1;
				if (@unlink($file)) {
                    $f++;
                }
			}
			foreach ($dirs as $dir=>$n) {
			    if (!in_array($dir, array('.', '/', '\\'))) {
				    $this->delDir($dir);
					$d++;
				}
			}
		}
		$this->cache->delete('html_files');
	    $this->adminMsg(lang('a-con-110', array('1' => $d, '2' => $f)), '', 3, 1, 1);
	}
	
	/**
	 * 生成栏目
	 */
	private function toCategory($catid, $page, $isall, $key, $filecount) {
	    $cat = $this->cats[$catid];
	    $nextpage = 1;
		$totalpage = 1;
		$nextcatid = $catid;
		if ($cat['setting']['url']['tohtml'] == 1) {
			if (($cat['child'] && $cat['categorytpl'] != $cat['listtpl']) || $cat['typeid'] == 2) {
				if ($this->createCat($cat)) {
                    $filecount++;
                }
			} else {
				$total = $this->content->_count(null, '`status`=1 AND `catid` IN (' . $cat['arrchilds'] . ')', null, 3600);
				$pagesize = $cat['pagesize'];
				$totalpage = ceil($total/$pagesize); //该栏目的总页数
				$totalpage = $totalpage ? $totalpage : 1;
				if ($this->createCat($cat, $page)) {
                    $filecount++;
                }
				$nextpage = $page + 1;
			}
		}
		if ($page >= $totalpage) {
			$nextpage = 1;
			list($nextcatid, $key) = $this->nextCat($catid, $isall, $key); //跳转下一栏目
		}
	    $this->adminMsg('【' . $cat['catname'] . '】(' . $page . '/' . $totalpage . ')', url('admin/html/category', array('page' => $nextpage, 'catid' => $nextcatid, 'isall' => $isall, 'key' => $key, 'filecount' => $filecount, 'submit' => 1)), 0, 1, 2);
	}
	
	/**
	 * 下一栏目信息
	 */
	private function nextCat($catid, $isall, $key) {
	    if ($isall == 0) {
			$key++;
			$nextcatid = $catid;
		} else {
			$_selected = 0;
			$nextcatid = 200;
			foreach ($this->cats as $id=>$t) {
				if ($_selected == 1) {
					$nextcatid = $id;
					break;
				}
				if ($id == $catid) $_selected = 1;
			}
		}
		return array($nextcatid, $key);
	}
	
	/**
	 * 生成内容
	 */
	private function toContent($cats, $page, $filecount, $totime = 0) {
		$where = '`catid` IN(' . $cats . ') and `status`=1';
		if ($totime) {
			$where .= ' and `updatetime` between ' . (int)strtotime('-' . $totime . ' day') . ' and ' . time();
		}
		$total = $this->content->_count(null, $where, null, 3600);
		$pagesize = 50;
	    $totalpage = ceil($total/$pagesize);
        $data = $this->content->page_limit($page, $pagesize)->where($where)->order('id ASC')->select();
		if (empty($data)) {
			$this->cache->delete('html_cats');
			$this->adminMsg(lang('a-con-107') . '(' . $filecount . ')', '', 0, 1, 1);
		}
		foreach ($data as $t) {
		    if ($this->cats[$t['catid']]['setting']['url']['use'] == 1 && $this->cats[$t['catid']]['setting']['url']['tohtml'] == 1 && $this->cats[$t['catid']]['setting']['url']['show'] != '' && $this->createShow($t)) {
				$filecount ++ ;
			}
        }
        $this->adminMsg(lang('a-con-111') . " ($page/$totalpage)", url('admin/html/show', array('page' => $page+1, 'submit' => 1, 'filecount' => $filecount, 'totime' => $totime)), 0, 1, 2);
	}
	
	/**
	 * 目录权限检查函数
	 */
	private function dir_mode_info() {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            /* 测试文件 */
            $test_file = APP_ROOT . 'idea_test.txt';
			/* 检查目录是否可读 */
			$dir = @opendir(APP_ROOT);
			if ($dir === false)	return lang('a-con-112'); 
			if (@readdir($dir) === false) return lang('a-con-113');
			@closedir($dir);
			/* 检查目录是否可写 */
			$fp = @fopen($test_file, 'w+');
			//如果目录中的文件创建失败，返回不可写。
			if (!file_exists($test_file) || $fp === false)	return lang('a-con-114'); 
			if (@fwrite($fp, 'directory access testing.') === false) return lang('a-con-114');
			@fclose($fp);
			@unlink($test_file);
			/* 检查目录是否可修改 */
			$fp = @fopen($test_file, 'ab+');
			if ($fp === false)	return lang('a-con-115');
			if (@fwrite($fp, "modify test.\r\n") === false) return lang('a-con-115');
			@fclose($fp);
			@unlink($test_file);
        }
		foreach (glob(APP_ROOT . '*') as $dir) {
		   if (is_dir($dir)){
			   if (!@is_readable($dir)) return lang('a-con-113');
			   if (!@is_writable($dir)) return lang('a-con-114');
		   }
		}
        return false;
    }
	
}