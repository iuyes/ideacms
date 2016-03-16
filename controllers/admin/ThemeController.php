<?php

class ThemeController extends Admin {
    
    public function __construct() {
		parent::__construct();
	}
    
    public function indexAction() {
        $dir = $this->get('dir') ? base64_decode($this->get('dir')) : '';
        $iframe = $this->get('iframe') ? 1 : 0;
		if ($this->checkFileName($dir)) {
            $this->adminMsg(lang('m-con-20'));
        }
        $dir = substr($dir, 0, 1) == DIRECTORY_SEPARATOR ? substr($dir, 1) : $dir;
        $dir = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $dir);
        $data = file_list::get_file_list(VIEW_DIR . $dir);
        $dlist = $flist = array();
        if ($data) {
            foreach ($data as $t) {
                if (!$dir && $t == 'header.html') {
                    continue;
                };
                $path = $dir . $t . DIRECTORY_SEPARATOR;
				if (@is_dir(VIEW_DIR . $path) && !in_array($t, array('admin', 'install'))) {  //目录
				    $ext = 'dir';
					$dlist[] = array('name'=>$t, 'dir'=>base64_encode($path), 'ico'=>ADMIN_THEME . 'images/ext/dir.gif', 'isdir'=>1, 'url'=>url('admin/theme/index', array('dir'=>base64_encode($path), 'iframe'=>$iframe)));
				} else { //文件
				    $ext = strtolower(trim(substr(strrchr($t, '.'), 1, 10)));
					if (in_array($ext, array('html', 'js', 'css'))) {
					    $ico  = ADMIN_THEME . 'images/ext/' . $ext . '.gif';
					    $flist[] = array('name'=>$t, 'dir'=>base64_encode($path), 'ico'=>$ico);
					}
				}
            }
        }
		sort($flist);
        $this->view->assign(array(
            'dir' => VIEW_DIR . $dir,
            'pdir' => url('admin/theme/index', array('dir'=>base64_encode(str_replace(basename($dir), '', $dir)), 'iframe'=>$iframe)),
            'istop' => $dir ? 1 : 0,
            'dlist' => $dlist,
			'flist' => $flist,
			'cpath' => base64_encode($dir),
            'iframe' => $iframe,
			'iswrite' => is_writable(VIEW_DIR)
        ));
        $this->view->display('admin/theme_list');
    }
    
    public function editAction() {
        $dir = base64_decode($this->get('dir'));
		$dir = substr($dir, -1) == DIRECTORY_SEPARATOR ? substr($dir, 0, -1) : $dir;
		if ($this->checkFileName($dir)) {
            $this->adminMsg(lang('m-con-20'));
        }
        $name = VIEW_DIR . $dir;
		if (!is_file($name)) $this->adminMsg(lang('a-con-123', array('1'=>$name)));
		if ($this->isPostForm()) {
		    $Pdir = VIEW_DIR == dirname($name) . DIRECTORY_SEPARATOR ? '' : str_replace(VIEW_DIR, '', dirname($name));
		    file_put_contents($name, stripslashes($_POST['file_content']), LOCK_EX);
			//$this->adminMsg(lang('success'), url('admin/theme/index', array('dir'=>base64_encode($Pdir . DIRECTORY_SEPARATOR))), 3, 1, 1);
			$is_post = 1;
		} else {
			$is_post = 0;
		}
        $file = file_get_contents($name);
		$this->view->assign(array(
			'file' => $file,
		    'name' => str_replace(VIEW_DIR, '', $name),
			'syntax' => strtolower(trim(substr(strrchr($name, '.'), 1, 10))),
			'action' => 'edit',
			'is_post' => $is_post,
			'iswrite' => is_writable(VIEW_DIR),
		));
		$this->view->display('admin/theme_add');
    }
	
	public function addAction() {
        $dir  = base64_decode($this->get('cpath'));
		if ($this->checkFileName($dir)) $this->adminMsg(lang('m-con-20'));
        $name = VIEW_DIR . $dir;
		$path = str_replace(VIEW_DIR, '', $name);
		if ($this->isPostForm()) {
		    $file = $this->post('file');
			if ($file == '' || $this->checkFileName($file)) $this->adminMsg(lang('m-con-20'));
			$ext  = strtolower(trim(substr(strrchr($file, '.'), 1, 10)));
			if ($this->post('filetype') == 1 && in_array($ext, array('html', 'css', 'js'))) {
				file_put_contents($name . $file, stripslashes($_POST['file_content']), LOCK_EX);
			} elseif ($this->post('filetype') == 2) {
				mkdir($name . $file, 0777);
			} else {
				$this->adminMsg(lang('a-con-124'));
			}
			$this->adminMsg(lang('success'),url('admin/theme/index', array('dir'=>base64_encode($path))), 3, 1, 1);
		}
		$this->view->assign(array(
		    'path'		=> $path,
			'syntax'	=> 'html',
			'action'	=> 'add',
			'iswrite'	=> is_writable(VIEW_DIR)
		));
		$this->view->display('admin/theme_add');
    }
	
	public function delAction() {
	    $dir  = base64_decode($this->get('name'));
		if ($this->checkFileName($dir)) $this->adminMsg(lang('m-con-20'));
		$dir  = substr($dir, -1) == DIRECTORY_SEPARATOR ? substr($dir, 0, -1) : $dir;
		$name = VIEW_DIR . $dir;
		$this->delDir($name);
		$Pdir = VIEW_DIR == dirname($name) . DIRECTORY_SEPARATOR ? '' : str_replace(VIEW_DIR, '', dirname($name));
		$this->adminMsg(lang('success'),url('admin/theme/index', array('dir'=>base64_encode($Pdir . DIRECTORY_SEPARATOR))), 3, 1, 1);
	}
	
	public function demoAction() {
		if ($this->isPostForm()) {
		    $type = $this->post('type');
			$data = $this->post('data' . $type);
			switch ($type) {
				case 1:
					//内容
					$code = '{list';
					if ($data['modelid']) $code .=' modelid=' . (int)$data['modelid'];
					if ($data['catid']) $code .= strpos($data['catid'], ',') !== false ? ' INcatid=' . $data['catid'] : ' catid=' . (int)$data['catid'];
					if ($data['order']) $code .=' order=' . $data['order'];
					if ($data['num']) $code .=' num=' . $data['num'];
					if (is_numeric($data['thumb'])) $code .=' thumb=' . (int)$data['thumb'];
					if (is_numeric($data['more'])) $code .=' more=1';
					if ($data['cache']) $code .=' cache=' . (int)$data['cache'];
					if ($data['return'] && $data['return'] != 't') $code .=' return=' . $data['return'];
					$code .= '}' . PHP_EOL;
					$code .= '计数(从0开始):' . ($data['return'] == 't' ? '{$key} ' : '{$key_' . $data['return'] . '} ') .
					'标题:{$' . $data['return'] . '[\'title\']} ' .
					'栏目:{$cats[$' . $data['return'] . '[\'catid\']][\'catname\']} ' .
					'地址:{$' . $data['return'] . '[\'url\']} ' .
					PHP_EOL;
                    if ($data['more']) {
                        $code.= '自定义字段：{$' . $data['return'] . '[\'自定义字段名称\']}'.PHP_EOL.PHP_EOL;
                    }
					$code .='{/list}';
					break;
				case 2:
					//会员
					$code = '{list table=member';
					if ($data['modelid']) $code .=' modelid=' . (int)$data['modelid'];
					if ($data['groupid']) $code .= strpos($data['groupid'], ',') !== false ? ' INgroupid=' . $data['groupid'] : ' groupid=' . (int)$data['groupid'];
					if ($data['order']) $code .=' order=' . $data['order'];
					if ($data['num']) $code .=' num=' . $data['num'];
					if ($data['modelid'] && is_numeric($data['more'])) $code .=' more=1';
					if ($data['cache']) $code .=' cache=' . (int)$data['cache'];
					if ($data['return'] && $data['return'] != 't') $code .=' return=' . $data['return'];
					$code .= '}' . PHP_EOL;
					$code .= '计数(从0开始):' . ($data['return'] == 't' ? '{$key} ' : '{$key_' . $data['return'] . '} ') .
					'账号:{$' . $data['return'] . '[\'username\']} ' .
					'昵称:{$' . $data['return'] . '[\'nickname\']} ' .
					'会员组:{$membergroup[$' . $data['return'] . '[\'groupid\']][\'name\']} ' .
					'空间地址:{url("member/space", array("userid"=>$' . $data['return'] . '[\'id\']))} ' .
					PHP_EOL;
					$code .='{/list}';
					break;
				case 3:
					//表单
					$code = '{list ' . (strpos($data['modelid'], 'form_' . $this->siteid . '_') === 0 ? str_replace('_' . $this->siteid . '_', '=', $data['modelid']) : 'table=' . $data['modelid']);
					if ($data['cid']) $code .=' cid=' . (int)$data['cid'];
					if ($data['order']) $code .=' order=' . $data['order'];
					if ($data['num']) $code .=' num=' . $data['num'];
					if ($data['cache']) $code .=' cache=' . (int)$data['cache'];
					if ($data['return'] && $data['return'] != 't') $code .=' return=' . $data['return'];
					$code .= '}' . PHP_EOL;
					$code .= '计数(从0开始):' . ($data['return'] == 't' ? '{$key} ' : '{$key_' . $data['return'] . '} ') .
					'id:{$' . $data['return'] . '[\'id\']} ' .
					'支持表单自定义字段显示{$' . $data['return'] . '[\'字段名称\']} ' .
					PHP_EOL;
					$code .='{/list}';
					break;
				case 4:
					//其他
					$code = '{list table=' . $data['table'];
					if ($data['order']) $code .=' order=' . $data['order'];
					if ($data['num']) $code .=' num=' . $data['num'];
					if ($data['cache']) $code .=' cache=' . (int)$data['cache'];
					if ($data['return'] && $data['return'] != 't') $code .=' return=' . $data['return'];
					$code .= '}' . PHP_EOL;
					$code .= '计数(从0开始):' . ($data['return'] == 't' ? '{$key} ' : '{$key_' . $data['return'] . '} ') .
					'支持该表的所有字段显示{$' . $data['return'] . '[\'字段名称\']} ' .
					PHP_EOL;
					$code .='{/list}';
					break;
				case 5:
					//单条数据
					$code = strpos($data['table'], '::') !== false ? '{plugin:' : '{sql:';
					if (is_numeric($data['where'])) {
						//主键
						$code .= $data['table'] . ' find(' . (int)$data['where'] . ');}';
					} else {
						//非主键
						$code .= $data['table'] . ' where("' . $data['where'] . '")->select(false);}' . PHP_EOL . '注意where中的引号及转义字符';
					}
					$code .= PHP_EOL . '返回数组: {$return[\'字段名称\']}';
					break;
				case 6:
					$code = '{loop $cats $c}' . PHP_EOL .
					'{if $c[\'parentid\']==' . (int)$data['catid'] . '}' . PHP_EOL . 
					'栏目名称: {$c[\'catname\']}' . PHP_EOL . 
					'{/if}' . PHP_EOL . 
					'{/loop}' . PHP_EOL;
					break;
			}
			echo "<script type=\"text/javascript\" src=\"" . ADMIN_THEME . "js/jquery.min.js\"></script><script type=\"text/javascript\">$(\"#load\",window.parent.document).hide();</script><pre style='font-size:12px;'>$code<pre>";
			exit;
		}
		$tree = $this->instance('tree');
		$tree->config(array('id' => 'catid', 'parent_id' => 'parentid', 'name' => 'catname'));
		$this->view->assign(array(
			'model'			=> $this->get_model(),
		    'category'		=> $tree->get_tree($this->cats, 0),
			'formmodel'		=> $this->get_model('form'),
			'membermodel'	=> $this->cache->get('model_member')
		));
		$this->view->display('admin/theme_demo');
    }
	
	public function cacheAction($show=0) {
		$dir = APP_ROOT . 'cache/views/';
		if (!file_exists($dir)) mkdir($dir);
	    $show or $this->adminMsg(lang('a-update'), url('admin/theme/index'), 3, 1, 1);
	}
}