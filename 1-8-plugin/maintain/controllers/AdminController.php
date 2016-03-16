<?php

class AdminController extends Plugin {
    
	protected $db;
	protected $table;
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
		$menu = array(
		    array('index',    '文章内容替换'),
			array('keyword',  '重获关键字'),
			array('repeat',   '文章重复检测'),
			array('content',  '文章内容处理'),
			array('thumb',    '图片本地化'),
		);
		$this->table = $this->content->prefix . 'content_' . $this->siteid;
		$this->assign('menu', $menu);
    }
	
    /*
	 * 内容替换
	 */
    public function indexAction() {
	    if ($this->isPostForm()) {
		    $this->cache->delete('maintain_index');
		    $fields  = $this->post('fields');
		    $regex   = $this->post('regex');
		    $replace = $this->post('replace');
			if (empty($fields)) $this->pluginMsg('<font color=red>替换字段不能为空！</font>');
			if (empty($regex))  $this->pluginMsg('<font color=red>原内容不能为空！</font>');
			$_fields = explode(',', $fields);
			foreach ($_fields as $f) {
			    if ($f && in_array($f, array('id', 'catid', 'modelid')))  $this->pluginMsg('<font color=red>字段包含有关键字段！</font>');
			}
			$data    = array(
				'fields'  => $fields,
				'regex'   => $regex,
				'replace' => $replace,
			);
			$this->cache->set('maintain_index', $data);
			$this->pluginMsg('正在替换内容，请稍后 ....', purl('admin/index', array('submit'=>1)), 0);
		}
		if ($this->get('submit')) {
		    $data	= $this->cache->get('maintain_index');
			if (empty($data)) $this->pluginMsg('<font color=red>数据缓存文件读取失败！</font>');
			//筛选字段
			$fields	= explode(',', $data['fields']);
			if (!is_array($fields) || empty($data)) $this->pluginMsg('<font color=red>字段数据不存在！</font>');
			$more	= 0;
			$main	= $this->content->get_fields();
			foreach ($fields as $f) {
			    if (!in_array($f, $main) && $f) {
				    $more = 1;
					break;
				}
			}
			$regex   = explode(chr(13), $data['regex']);
			$replace = explode(chr(13), $data['replace']);
			$update  = '';
			//替换主表
			foreach ($regex as $i=>$t) {
				if ($t) {
					foreach ($fields as $f) {
						if (in_array($f, $main) && $f) {
							$r = isset($replace[$i]) ? $replace[$i] : '';
							$update .= ',`' . $this->table . '`.`' . $f . '`=REPLACE(`' . $this->table . '`.`' . $f . '`, \'' . $t . '\', \'' . $r . '\')';
						}
					}
				}
			}
			$result  = 0;
			if ($update) {
				$sql = 'update `' . $this->table . '` set ' . substr($update, 1);
				$this->content->query($sql);
				$result += mysql_affected_rows();
			}
			//替换附表
			if ($more) {
			    $model  = get_model_data();
				foreach ($model as $t) {
				    $table	= $t['tablename'];
					$_data  = $this->model($table);
					$_field = $_data->get_fields();
					$update = '';
					foreach ($regex as $i=>$t) {
						if ($t) {
							foreach ($fields as $f) {
								if (in_array($f, $_field) && $f) {
									$r = isset($replace[$i]) ? $replace[$i] : '';
									$update .= ',`' . $this->content->prefix . $table . '`.`' . $f . '`=REPLACE(`' . $this->content->prefix . $table . '`.`' . $f . '`, \'' . $t . '\', \'' . $r . '\')';
								}
							}
						}
					}
					if ($update) {
						$sql = 'update `' . $this->content->prefix . $table . '` set ' . substr($update, 1);
						$this->content->query($sql);
						$result += mysql_affected_rows();
					}
				}
			}
			$this->cache->delete('maintain_index');
			$this->pluginMsg('<font color=green><b>共计替换' . $result . '条数据。<b></font>');
		} else {
			$this->display('replace');
		}
    }
	
	/*
	 * 重获关键字
	 */
    public function keywordAction() {
	    if ($this->isPostForm()) {
		    $type = $this->post('type');
			$nums = $this->post('nums');
			$this->pluginMsg('正在重新获取关键字，请稍后 ....', purl('admin/keyword', array('submit'=>1, 'type'=>$type, 'nums'=>$nums)), 0);
		}
		if ($this->get('submit')) {
		    $type  = $this->get('type');
		    $page  = $this->get('page') ? $this->get('page') : 1;
			$nums  = $this->get('nums') ? $this->get('nums') : 100;
			$count = $this->content->_count(null, null, null, 36000);
	        $total = ceil($count/$nums);
			$data  = $this->content->page_limit($page, $nums)->select();
			if (empty($data)) {
			    $this->pluginMsg('<font color=green><b>操作完成<b></font>');
			} else {
			    //$model = get_model_data();
			    foreach ($data as $t) {
				    //$table = $model[$this->cats[$t['catid']]['modelid']]['tablename'];
					$kw = getKw($t['title']);
					if ($kw) {
					    $kws  = explode(',', $kw);
						$_kws = $t['keywords'] ? explode(',', $t['keywords']) : array();
						if ($type == 0) {
						    $kws = @array_merge($kws, $_kws);
							$kws = @array_unique($kws);
						}
						$kws  = implode(',', $kws);
						$tags = @explode(',', $kws);
						if ($tags && function_exists('word2pinyin')) {
							foreach ($tags as $name) {
							    $name = trim($name);
								if ($name) {
									$r= $this->content->from('tag', 'id')->where('name=?', $name)->select(false);
									if (empty($r)) {
										$this->content->query('INSERT INTO `' . $this->content->prefix . 'tag` (`name`,`letter`) VALUES ("' . $name . '", "' . word2pinyin($name) . '")');
									}
								}
							}
						}
						$this->content->update(array('keywords'=>$kws), 'id=' . $t['id']);
					}
				}
				$this->pluginMsg('正在重新获取关键字(' . $page . '/' . $total . ')', purl('admin/keyword', array('submit'=>1, 'type'=>$type, 'nums'=>$nums, 'page'=>$page+1)), 0);
			}
		} else {
			$this->display('keyword');
		}
    }
	
	/*
	 * 重复文章
	 */
    public function repeatAction() {
	    if ($this->isPostForm()) {
		    $nums = $this->post('nums');
			$type = $this->post('type');
			$this->pluginMsg('正在检测重复文章，请稍后 ....', purl('admin/repeat', array('submit'=>1, 'nums'=>$nums, 'type'=>$type)), 0);
		}
		if ($this->get('submit')) {
		    $type  = $this->get('type');
			$nums  = $this->get('nums') ? $this->get('nums') : 100;
			$data  = $this->content->from(null, 'COUNT(title) AS tt,title,id')->group('title')->order('tt desc')->limit($nums)->select();
			if (empty($data)) {
			    $this->pluginMsg('<font color=green><b>没有检测到重复文章<b></font>');
			} else {
			    $order = $type ? 'inputtime desc,id desc' : 'inputtime asc,id asc';
				$count = 0;
			    foreach ($data as $t) {
					if ($t['tt'] > 1) {
					    $list = $this->content->where('title=?', $t['title'])->order($order)->select();
						unset($list[0]);
						if (count($list) > 0) {
						    $ids = '';
							foreach ($list as $c) {
							    if ($c['id']) $ids .= ',' . $c['id'];
							}
							if ($ids) {
							    $count += count($list);
							    $this->content->query('update ' . $this->table . ' set `status`=0 where id IN (' . substr($ids, 1) . ')');
							}
						}
					}
				}
				$this->pluginMsg('<font color=green><b>重复文章(' . $count . ')已经放入回收站<b></font>');
			}
		} else {
			$this->display('repeat');
		}
    }
	
	/*
	 * 内容处理
	 */
    public function contentAction() {
	    if ($this->isPostForm()) {
		    $this->cache->delete('maintain_content');
		    $data = $this->post('data');
			$nums = $this->post('nums');
			if (empty($data)) $this->pluginMsg('<font color=red>至少要选择一个选项！</font>');
		    $this->cache->set('maintain_content', $data);
			$this->pluginMsg('正在处理内容，请稍后 ....', purl('admin/content', array('submit'=>1, 'nums'=>$nums)), 0);
		}
		if ($this->get('submit')) {
		    $data   = $this->cache->get('maintain_content');
			if (empty($data)) $this->pluginMsg('<font color=red>缓存文件不存在，请重新执行！</font>');
			$model  = get_model_data();
			$mark   = 0;
			$mid    = $this->get('mid')  ? $this->get('mid')  : 0;
			$result = (int)$this->get('result');
			foreach ($model as $m) {
			    if ($mark == 0 && $mid == 0) {
				    $mid = $m['modelid'];
					break;
				}
			}
			$table = isset($model[$mid]) ? $model[$mid]['tablename'] : null;
			if (!$table) $this->pluginMsg('<font color=green><b>处理完毕(' . $result . ')！<b></font>');
		    $page  = $this->get('page') ? $this->get('page') : 1;
			$nums  = $this->get('nums') ? $this->get('nums') : 100;
			$count = $this->content->count($table, 'id');
	        $total = ceil($count/$nums);
			$list  = $this->content->from($table)->page_limit($page, $nums)->select();
			if (empty($list)) {
			    $mark   = 0;
				foreach ($model as $m) {
					if ($mid == $m['modelid']) {
						$mark = 1;
						continue;
					}
					if ($mark == 1) {
					    $_mid = $m['modelid'];
						break;
					}
				}
			    if (!isset($model[$_mid])) $this->pluginMsg('<font color=green><b>处理完毕(' . $result . ')！<b></font>');
				$this->pluginMsg('正在处理【' . $model[$_mid]['modelname'] . '】模型内容，请稍后 ....', purl('admin/content', array('submit'=>1, 'nums'=>$nums, 'page'=>1, 'mid'=>$_mid, 'result'=>$result)), 1);
			} else {
			    foreach ($list as $t) {
				    if (!isset($t['content'])) continue;
				    $content = $t['content'];
					$mark    = 0;
				    if (isset($data['clearimg']) && $data['clearimg'] == 1) {
					    if (preg_match_all('/&lt;img (.+)&gt;/iU', $content, $match)) {
						    $content = str_replace($match[0], array(), $content);
							$this->content->query('update ' . $this->content->prefix . $table . ' set content=\'' . addslashes($content) . '\' where id=' . $t['id']);
							$mark    = 1; 
						}
						if ($mark == 1) $result ++;
					}
					if (isset($data['clearpage']) && $data['clearpage'] == 1) {
					    if (strpos($content, '{-page-}') !== false) {
						    $content = str_replace('{-page-}', '', $content);
							$this->content->query('update ' . $this->content->prefix . $table . ' set content=\'' . addslashes($content) . '\' where id=' . $t['id']);
							$mark    = 1; 
						}
						if ($mark == 1) $result ++;
					}
				}
				$this->pluginMsg('正在处理【' . $model[$mid]['modelname'] . '】模型内容(' . $page . '/' . $total . ')', purl('admin/content', array('submit'=>1, 'nums'=>$nums, 'page'=>$page+1, 'mid'=>$mid, 'result'=>$result)), 0);
			}
		} else {
			$this->display('content');
		}
    }
	
	/*
	 * 图片本地化
	 */
    public function thumbAction() {
	    if ($this->isPostForm()) {
		    $type = $this->post('type');
			$nums = $this->post('nums');
			$this->pluginMsg('正在处理图片，请稍后 ....', purl('admin/thumb', array('submit'=>1, 'nums'=>$nums, 'type'=>$type)), 0);
		}
		if ($this->get('submit')) {
			$page   = $this->get('page') ? $this->get('page') : 1;
			$nums   = $this->get('nums') ? $this->get('nums') : 100;
			$type   = $this->get('type') ? $this->get('type') : 1;
			$result = (int)$this->get('result');
			$error	= (int)$this->get('error');
			if ($type == 1) {
			    //缩略图本地化
				$count = $this->content->_count(null, 'thumb like \'http://%\'', null, 36000);
				$total = ceil($count/$nums);
				$list  = $this->content->where('thumb like \'http://%\'')->page_limit($page, $nums)->select();
				if (empty($list)) $this->pluginMsg('<font color=green><b>处理完毕，成功(' . $result . ')，失败(' . $error . ')！<b></font>');
				foreach ($list as $c) {
				    $file = 'uploadfiles/thumb/' . $c['id'] . '.' . strtolower(trim(substr(strrchr($c['thumb'], '.'),1)));
				    if (loadImage($c['thumb'], $file)) {
					    $this->content->update(array('thumb'=>$file), 'id=' . $c['id']);
					    $result ++;
					} else {
					    $error ++;
					}
				}
				$this->pluginMsg('正在下载缩略图(' . $page . '/' . $total . ')', purl('admin/thumb', array('submit'=>1, 'nums'=>$nums, 'page'=>$page+1, 'type'=>$type, 'result'=>$result, 'error'=>$error)), 0);
			}
		} else {
			$this->display('thumb');
		}
    }
}