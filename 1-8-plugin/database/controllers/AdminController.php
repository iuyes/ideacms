<?php

class AdminController extends Plugin {
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
		$menu = array(
		    array('index',   '数据操作'),
			array('export',  '数据备份'),
			array('import',  '数据恢复'),
			array('execute', '执行SQL'),
		);
		$this->assign('menu', $menu);
    }
	
    /*
	 * 数据操作
	 */
    public function indexAction() {
		if ($this->isPostForm()) {
			$tables    = $this->post('table');
		    $list_form = $this->post('list_form');
			if (is_array($tables)) {
				foreach ($tables as $table) {
					$this->content->query($list_form . ' table ' . $table);
				}
			}
		    $this->adminMsg('操作成功', purl('admin'), 3, 1, 1);
		}
		$this->assign(array(
		    'data' => $this->getTables(),
		));
	    $this->display('admin_list');
    }
	
	/*
	 * 数据备份
	 */
    public function exportAction() {
	    $action = $this->get('action');
		$size   = $this->get('size');
		if ($this->isPostForm()) {
		   $size   = $this->post('size');
		   $tables = $this->post('table');
		   if (empty($tables)) $this->adminMsg('对不起，您还没有选择表。');
		   //存入缓存中
		   $this->cache->set('bakup_tables', array('tables' => $tables, 'time' => time()));
		   $this->adminMsg('正在备份数据...', purl('admin/export', array('action' => 1, 'size' => $size)), 0, 1, 2);
		}
		if ($action) {
		    $fileid    = $this->get('fileid');
			$random    = $this->get('random');
			$tableid   = $this->get('tableid');
			$startfrom = $this->get('startfrom');
		    $this->export_database($size, $action, $fileid, $random, $tableid, $startfrom);
		} else {
		    $this->assign('data', $this->getTables());
	        $this->display('admin_export');
		}
    }
	
	/*
	 * 数据恢复
	 */
    public function importAction() {
		$dir  = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'bakup' . DIRECTORY_SEPARATOR;
		$path = $this->get('path');
		if ($path && is_dir($dir . $path)) {
		    $fileid = $this->get('fileid');
		    $this->importdb($path, $fileid);
		    exit;
		}
		if ($this->isPostForm()) {
			$paths    = $this->post('paths');
			if (is_array($paths)) {
				foreach ($paths as $path) {
					$this->delDir($dir . $path);
				}
			}
		    $this->adminMsg('操作成功', purl('admin/import'), 3, 1, 1);
		}
		if (!is_dir($dir)) mkdir($dir, 0777);
		$data = file_list::get_file_list($dir); //扫描备份目录
	    $list = array();
		if ($data) {
			foreach ($data as $path) {
				if (!in_array($path, array('.', '..', '.svn')) && is_dir($dir . $path)) {
					$size   = 0;
					$_dir   = scandir($dir . $path);
					foreach ($_dir as $c) {
						$size += filesize($dir . $path . DIRECTORY_SEPARATOR . $c);
					}
					if (is_file($dir . $path . DIRECTORY_SEPARATOR . 'version.txt')) {
						$version = file_get_contents($dir . $path . DIRECTORY_SEPARATOR . 'version.txt');
					} else {
						$version = '未知';
					}
					$list[] = array('path' => $path, 'size' => formatFileSize($size, 2), 'version' => $version);
					clearstatcache();
				}
			}
		}
		$this->assign(array(
		    'data' => $list,
		));
	    $this->display('admin_import');
    }
	
	/*
	 * 执行sql
	 */
    public function executeAction() {
		if ($this->isPostForm()) {
			$sql = stripslashes($_POST['sql']);
			if (empty($sql)) $this->adminMsg('内容为空，不能执行', purl('admin/execute'));
			$cfg = Controller::load_config('database');
			$sql = str_replace("{pre}", $cfg['prefix'], $sql);
			if (strtoupper(substr($sql, 0, 6)) != 'SELECT') {
			    $result = $this->sql_execute($sql);
			    $this->adminMsg('操作成功，影响' . $result .'条记录', '', 3, 1, 1);
			}
		    $data = $this->content->execute($sql);
		    $this->assign('data', $data);
		    $this->assign('sql',  $sql);
		}
		$this->display('admin_sql');
    }
	
	/*
	 * 修复表
	 */
    public function repairAction() {
		$name = $this->get('name');
		$this->content->query("repair table $name");
		$this->adminMsg('操作成功', purl('admin'), 3, 1, 1);
    }
	
	/*
	 * 优化表
	 */
    public function optimizeAction() {
		$name = $this->get('name');
		$this->content->query("optimize table $name");
		$this->adminMsg('操作成功', purl('admin'), 3, 1, 1);
    }
	
	/*
	 * 数据表结构
	 */
    public function tableAction() {
		$name = $this->get('name');
		$data = $this->content->execute("SHOW CREATE TABLE $name", false);
		echo '<div class="table-list"><xmp>' . $data['Create Table'] . '</xmp></div>';
    }
	
	/*
	 * 取当前数据库中的所有表信息
	 */
	private function getTables() {
	    $data = $this->content->execute('SHOW TABLE STATUS FROM `' . $this->content->dbname . '`');
		foreach ($data as $key=>$t) {
		    $data[$key]['fc'] = substr($t['Name'], 0, strlen($this->content->prefix)) != $this->content->prefix ? 0 : 1;
		}
		return $data;
	}
	
	/**
	 * 数据库导出方法
	 * @param  $sizelimit 卷大小
	 * @param  $action 操作
	 * @param  $fileid 卷标
	 * @param  $random 随机字段
	 * @param  $tableid 
	 * @param  $startfrom 
	 */
	private function export_database($sizelimit, $action, $fileid, $random, $tableid, $startfrom) {
	    set_time_limit(0);
		$dumpcharset = 'utf8';
		$fileid      = ($fileid != '') ? $fileid : 1;
        $c_data      = $this->cache->get('bakup_tables');
		$tables      = $c_data['tables'];
		$time        = $c_data['time'];
		if (empty($tables)) $this->adminMsg('数据缓存不存在，请重新选择备份');
		if ($fileid  == 1) $random = mt_rand(1000, 9999);
		$this->content->query("SET NAMES 'utf8';\n\n");
		$tabledump   = '';
		$tableid     = ($tableid!= '') ? $tableid : 0;
		$startfrom   = ($startfrom != '') ? intval($startfrom) : 0;
		for ($i      = $tableid; $i < count($tables) && strlen($tabledump) < $sizelimit * 1000; $i++) {
			$offset  = 100;
			if (!$startfrom) {
				$tabledump  .= "DROP TABLE IF EXISTS `$tables[$i]`;\n"; 
				$createtable = $this->content->execute("SHOW CREATE TABLE `$tables[$i]` ", false);
				$tabledump  .= $createtable['Create Table'] . ";\n\n";
				$tabledump   = preg_replace("/(DEFAULT)*\s*CHARSET=[a-zA-Z0-9]+/", "DEFAULT CHARSET=utf8", $tabledump);
			}
			$numrows       = $offset;
			while (strlen($tabledump) < $sizelimit * 1000 && $numrows == $offset) {
				$sql       = "SELECT * FROM `$tables[$i]` LIMIT $startfrom, $offset";
				$numfields = $this->content->num_fields($sql);
				$numrows   = $this->content->num_rows($sql);
				//获取表字段
				$fields_data = $this->content->execute("SHOW COLUMNS FROM `$tables[$i]`");
				$fields_name = array();
				foreach($fields_data as $r) {
					$fields_name[$r['Field']] = $r['Type'];
				}
				$rows = $this->content->execute($sql);
				$name = array_keys($fields_name);
				$r    = array();
				if ($rows) {
					foreach ($rows as $row) {
						$r[]   = $row;
						$comma = "";
						$tabledump .= "INSERT INTO `$tables[$i]` VALUES(";
						for($j = 0; $j < $numfields; $j++) {
							$tabledump .= $comma . "'" . mysql_escape_string($row[$name[$j]]) . "'";
							$comma  = ",";
						}
						$tabledump .= ");\n";
					}
				}
				$startfrom += $offset;
			}
			$tabledump .= "\n";
			$startfrom  = $numrows == $offset ? $startfrom : 0;
		}
		$i   = $startfrom ? $i - 1 : $i;
		$dir = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'bakup' . DIRECTORY_SEPARATOR;
		if (!is_dir($dir)) {
		    //创建备份主目录
		    mkdir($dir, 0777);
			file_put_contents($dir . 'index.html', '');
		}
		$bakfile_path  = $dir . DIRECTORY_SEPARATOR . $time . DIRECTORY_SEPARATOR;
		if (trim($tabledump)) {
			$tabledump = "# ideacms bakfile\n# version:" . CMS_VERSION . " \n# time:" . date('Y-m-d H:i:s') . "\n# http://www.lygphp.com\n# --------------------------------------------------------\n\n\n" . $tabledump;
			$tableid   = $i;
			$filename  = 'idea_' . date('Ymd') . '_' . $random . '_' . $fileid . '.sql';
			$altid     = $fileid;
			$fileid++;
			if (!is_dir($bakfile_path)) mkdir($bakfile_path, 0777);
			$bakfile = $bakfile_path . $filename;
			file_put_contents($bakfile, $tabledump);
			@chmod($bakfile, 0777);
			$url = purl('admin/export', array('size' => $sizelimit, 'action' => $action, 'fileid' => $fileid, 'random' => $random, 'tableid' => $tableid, 'startfrom' => $startfrom));
			$this->adminMsg("备份#$filename", $url, 0, 1, 2);
		} else {
			file_put_contents($bakfile_path . 'index.html', '');
			file_put_contents($bakfile_path . 'version.txt', CMS_VERSION);
		    $this->cache->delete('bakup_tables');
		    $this->adminMsg("备份完成", purl('admin/export'), 3, 1, 1);
		}
	}
	
	/**
	 * 数据库恢复
	 */
	private function importdb($path, $fileid = 1) {
		$dir  = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'bakup' . DIRECTORY_SEPARATOR;
	    $fid  = $fileid ? $fileid : 1;
		if (is_file($dir . $path . DIRECTORY_SEPARATOR . 'version.txt')) {
			$version = file_get_contents($dir . $path . DIRECTORY_SEPARATOR . 'version.txt');
			if ($version  != CMS_VERSION) $this->adminMsg("备份版本($version)与当前版本(" . CMS_VERSION . ")不一致");
		}
		$data = scandir($dir . $path); //扫描备份目录
	    $list = array();
	    foreach ($data as $t) {
	        if (is_file($dir . $path . DIRECTORY_SEPARATOR . $t) && substr($t, -3) == 'sql') {
			    $id = substr(strrchr($t, '_'), 1, -4);
	            $list[$id] = $t;
	        }
	    }
		if (!isset($list[$fid])) $this->adminMsg('恢复完毕', purl('admin/import'), 3, 1, 1);
		$file = $list[$fid];
		$sql  = file_get_contents($dir . $path . DIRECTORY_SEPARATOR .$file);
		$this->sql_execute($sql);
		$fid++;
		$this->adminMsg('恢复文件 ' . $file, purl('admin/import', array('path' => $path, 'fileid' => $fid)), 1, 1, 2);
	}
	
	/**
	 * 执行SQL
	 * @param  $sql
	 */
 	private function sql_execute($sql) {
	    $sqls   = $this->sql_split($sql);
		$result = 0;
		if(is_array($sqls)) {
			foreach($sqls as $sql) {
				if(trim($sql) != '') {
					$this->content->query($sql);
					$result += mysql_affected_rows();
				}
			}
		} else {
			$this->content->query($sqls);
			$result += mysql_affected_rows();
		}
		return $result;
	}
	
 	private function sql_split($sql) {
		$sql = str_replace("\r", "\n", $sql);
		$ret = array();
		$num = 0;
		$queriesarray = explode(";\n", trim($sql));
		unset($sql);
		foreach($queriesarray as $query) {
			$ret[$num] = '';
			$queries = explode("\n", trim($query));
			$queries = array_filter($queries);
			foreach($queries as $query) {
				$str1 = substr($query, 0, 1);
				if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
			}
			$num++;
		}
		return($ret);
	}			
	
}