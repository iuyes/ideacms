<?php

class PluginController extends Admin {
    
    private $dir;
    private $plugin;
    
    public function __construct() {
		parent::__construct();
		$this->dir = PLUGIN_DIR;
		$this->plugin = $this->model('plugin');
	}
	
	/**
	 * 本地应用
	 */
	public function indexAction() {
	    $data = file_list::get_file_list($this->dir); //扫描应用目录
	    $list = array();
		if ($data) {
			foreach ($data as $id => $dir) {
				if (!in_array($dir, array('.', '..', '.svn', '', DIRECTORY_SEPARATOR)) && is_dir($this->dir . $dir)) {
					$file = $this->dir . $dir . DIRECTORY_SEPARATOR . 'config.php';
					if (file_exists($file) && filesize($file) != 0) {
						$setting = require $file;
						$setting['dir'] = $dir;
						$row    = $this->plugin->where('dir=?', $dir)->select(false);
						$list[] = $row ? $row : $setting;
					} else {
						$list[] = array('name' => '<font color="#FF0000">' . lang('a-plu-2') . '</font>', 'dir' => $dir);
					}
				}
			}
		}
	    $this->view->assign('list', $list);
	    $this->view->display('admin/plugin_list');
	}
	
	/**
	 * 应用配置
	 */
	public function setAction() {
	    $pluginid = $this->get('pluginid');
	    $data     = $this->plugin->find($pluginid);
	    if (empty($data)) $this->adminMsg(lang('a-plu-3'));
	    if ($this->post('submit')) {
	        $setting = $this->post('data');
	        $setting = array2string($setting);
	        $this->plugin->update(array('setting' => $setting), 'pluginid=' . $pluginid);
	        $this->adminMsg(lang('success'), url('admin/plugin/set/', array('pluginid' => $pluginid)), 3, 1, 1);
	    }
	    $setting = string2array($data['setting']);
	    $set     = $this->load_plugin_setting($data['dir']);
		$field   = array('data' => $set['fields']);
	    $fields  = $this->getFields($field, $setting);
	    $show    = empty($set['fields']) ? 1 : 0;
	    $this->view->assign(array(
	        'data'   => $data,
	        'show'   => $show,
	        'fields' => $fields
	    ));
	    $this->view->display('admin/plugin_set');
	}
	
	/**
	 * 安装应用
	 */
	public function addAction() {
	    $dir    = $this->get('dir');
	    $file   = $this->dir . $dir . DIRECTORY_SEPARATOR . 'config.php';
	    if (!file_exists($file)) $this->adminMsg(lang('a-plu-4'));
	    $config = require $file;
	    if ($config['typeid'] == 1) {
	        //包含控制器的应用    
	        $install = $this->dir . $dir . DIRECTORY_SEPARATOR . 'install.php';
	        if (!file_exists($install)) $this->adminMsg(lang('a-plu-5'));
	        $data = require $install;
	        if ($data) {
	            //数据表安装
	            if (is_array($data)) {
	                foreach ($data as $sql) {
	                    $this->plugin->query(str_replace('{prefix}', $this->plugin->prefix, $sql));
	                }
	            } else {
	                $this->plugin->query(str_replace('{prefix}', $this->plugin->prefix, $data));
	            }
	        }
	    }
	    //代码调用应用，直接添加表中记录
	    $config['dir'] = $dir;
	    $config['setting'] = array2string($config['fields']);
        $config['markid'] = (int)$config['key'];
	    $this->plugin->insert($config);
	    $this->adminMsg($this->getCacheCode('plugin') . lang('a-plu-6'), url('admin/plugin/index'), 3, 0, 1);
	}
	
	/**
	 * 卸载应用
	 */
	public function delAction() {
	    $pluginid = $this->get('pluginid');
	    $result   = $this->get('result');
	    $data     = $this->plugin->find($pluginid);
	    if (empty($data)) $this->adminMsg(lang('a-plu-3'));
		if (empty($result)) {
		    $html = lang('a-plu-7') . '<div style="padding-top:10px;text-align:center">
			<a href="' . url('admin/plugin/del', array('pluginid' => $pluginid, 'result' => 1)) . '" style="font-size:14px;">' . lang('a-plu-8') . '</a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="' . url('admin/plugin/index') . '" style="font-size:14px;">' . lang('a-plu-9') . '</a></div>';
			$this->adminMsg($html, '', 3, 1, 2);
		}
	    if ($data['typeid'] == 1) {
	        //包含控制器的应用
	        $uninstall = $this->dir . $data['dir'] . DIRECTORY_SEPARATOR . 'uninstall.php';
	        if (!file_exists($uninstall)) $this->adminMsg(lang('a-plu-10'));
	        $data = require $uninstall;
	        if ($data) {
	            //数据表
	            if (is_array($data)) {
	                foreach ($data as $sql) {
	                    $this->plugin->query(str_replace('{prefix}', $this->plugin->prefix, $sql));
	                }
	            } else {
	                $this->plugin->query(str_replace('{prefix}', $this->plugin->prefix, $data));
	            }
	        }
	    }
	    //代码调用应用，直接删除表中记录
	    $this->plugin->delete('pluginid=' . $pluginid);
	    $this->adminMsg($this->getCacheCode('plugin') . lang('a-plu-11'), url('admin/plugin/index'), 1, 0, 1);
	}
	
	/**
	 * 硬盘删除应用
	 */
	public function unlinkAction() {
	    $dir      = $this->get('dir');
	    $result   = $this->get('result');
		if (empty($result)) {
		    $html = lang('a-plu-13') . '<div style="padding-top:10px;text-align:center">
			<a href="' . url('admin/plugin/unlink', array('dir' => $dir, 'result' => 1)) . '" style="font-size:14px;">' . lang('a-plu-12') . '</a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="' . url('admin/plugin/index') . '" style="font-size:14px;">' . lang('a-plu-9') . '</a></div>';
			$this->adminMsg($html, '', 3, 1, 2);
		}
	    $data    = $this->plugin->getOne('dir=?', $dir);
		if ($data) {
			if ($data['typeid'] == 1) {
				//包含控制器的应用
				$uninstall = $this->dir . $data['dir'] . DIRECTORY_SEPARATOR . 'uninstall.php';
				if (!file_exists($uninstall)) $this->adminMsg(lang('a-plu-10'));
				$sqldata   = require $uninstall;
				if ($sqldata) {
					//数据表
					if (is_array($sqldata)) {
						foreach ($sqldata as $sql) {
							$this->plugin->query(str_replace('{prefix}', $this->plugin->prefix, $sql));
						}
					} else {
						$this->plugin->query(str_replace('{prefix}', $this->plugin->prefix, $sqldata));
					}
				}
			}
	        //代码调用应用，直接删除表中记录
	        $this->plugin->delete('pluginid=' . $data['pluginid']);
		}
		//删除硬盘数据
		if (is_dir($this->dir . $dir)) {
		    $this->delDir($this->dir . $dir);
			$this->adminMsg($this->getCacheCode('plugin') . lang('a-plu-14'), url('admin/plugin/index'), 3, 1, 1);
		} else {
		    $this->adminMsg(lang('a-plu-15'), url('admin/plugin/index'));
		}
	}
	
    /**
	 * 禁用/启用
	 */
	public function disableAction() {
	    $pluginid = $this->get('pluginid');
	    $data     = $this->plugin->find($pluginid);
	    if (empty($data)) $this->adminMsg(lang('a-plu-3'));
	    $disable  = $data['disable'] == 1 ? 0 : 1;
	    $this->plugin->update(array('disable' => $disable), 'pluginid=' . $pluginid);
	    $this->adminMsg($this->getCacheCode('plugin') . lang('success'), url('admin/plugin/index/'), 3, 1, 1);
	}
	
	/**
	 * 应用缓存
	 */
	public function cacheAction($show=0) {
	    $data = $this->plugin->where('disable=0')->select();
	    $row  = array();
	    foreach ($data as $t) {
	        $row[$t['dir']] = $t;
	        $row[$t['dir']]['setting'] = string2array($t['setting']);
	    }
	    $this->cache->set('plugin', $row);
	    $show or $this->adminMsg(lang('a-update'), '', 3, 1, 1);
	}
	
    /**
	 * 加载模板调用代码
	 */
	public function ajaxviewAction() {
	    $pluginid = $this->get('pluginid');
	    $data     = $this->plugin->find($pluginid);
	    if (empty($data)) exit(lang('a-plu-3'));
	    $msg  = "<textarea id='p_" . $pluginid . "' style='font-size:12px;width:100%;height:60px;overflow:hidden;'>";
	    $msg .= "{plugin('" . $data['dir'] . "')}" . PHP_EOL . "<!--将代码放到index.html" . PHP_EOL . "或者footer.html最底部-->";
	    $msg .= "</textarea>";
	    echo $msg;
	}
	
	/**
	 * 测试应用是否包含在模板中
	 */
	public function ajaxtestpAction() {
	    $id    = $this->post('id');
	    $data  = $this->plugin->find($id);
		if (empty($data)) exit('<font color=red>' . lang('a-plu-16') . '</font>');
		$code1 = "{plugin('" . $data['dir'] . "')}";
		$code2 = '{plugin("' . $data['dir'] . '")}';
		$file1 = @file_get_contents(VIEW_DIR . SYS_THEME_DIR . 'footer.html');
		$file2 = @file_get_contents(VIEW_DIR . SYS_THEME_DIR . 'index.html');
		if (strpos($file1, $code1) !== false || strpos($file1, $code2) !== false)  exit('<font color=green>√</font>');
		if (strpos($file2, $code1) !== false || strpos($file2, $code2) !== false)  exit('<font color=green>√</font>');
		exit('<font color=red>' . lang('a-plu-17') . '</font>');
	}
	
	
	/*
	 * 版本号比较
	 */
	private function check_version($v1, $v2) {
		$leng = max(substr_count($v1, '.'), substr_count($v2, '.'));
		$arr1 = explode('.', $v1);
		$arr2 = explode('.', $v2);
		$maxk = 0;
		for ($i = 0; $i <= $leng; $i ++) {
			$arr1[$i] = isset($arr1[$i]) ? $arr1[$i] : 0;
			$arr2[$i] = isset($arr2[$i]) ? $arr2[$i] : 0;
		}
		for ($i = $leng; $i >= 0; $i --) {
			if ($arr1[$i] > $arr2[$i]) {
				$maxk = 1;
			} elseif ($arr1[$i] < $arr2[$i]) {
				$maxk = 2;
			}
		}
		return $maxk;
	}
}