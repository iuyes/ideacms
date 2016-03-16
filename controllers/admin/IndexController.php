<?php

class IndexController extends Admin {

    public function __construct() {
		parent::__construct();
	}
	
	/**
	 * 首页
	 */
	public function indexAction() {
        $this->view->assign('cat', $this->get_category());
	    $this->view->assign('menu', $this->optionMenu());
		$this->view->display('admin/index');
	}
	
	/**
	 * 后台首页
	 */
	public function mainAction() {
	    $this->view->assign(array(
			'model'  => $this->get_model(),
		));
	    $this->view->display('admin/main');
	}
	
	/**
	 * 系统配置
	 */
	public function configAction() {
        //变量注释
	    $string = array(
            'ADMIN_NAMESPACE'         => lang('a-cfg-8'),
	        'SYS_DOMAIN'              => lang('a-cfg-11'),
            'SYS_DEBUG'               => lang('a-cfg-9'),
	        'SYS_LOG'                 => lang('a-cfg-10'),
            'SYS_VAR_PREX'            => lang('a-cfg-13'),
			'SYS_GZIP'                => lang('a-cfg-14'),
			'SITE_MEMBER_COOKIE'      => lang('a-cfg-0'),
			'SESSION_COOKIE_DOMAIN'   => lang('a-cfg-71'),
			'SYS_EDITOR'              => lang('a-cfg-68'),
			'SYS_CAPTCHA_MODE'        => lang('a-mod-161'),
	    
	        'SYS_ILLEGAL_CHAR'        => lang('a-cfg-7'),
			'SYS_ATTACK_LOG'          => lang('a-cfg-1'),
			'SYS_ATTACK_MAIL'         => lang('a-cfg-2'),
			'SITE_SYSMAIL'            => lang('a-cfg-4'),
	        'SITE_ADMINLOG'           => lang('a-cfg-24'),
	        'SITE_MAIL_TYPE'          => lang('a-cfg-25'),
	        'SITE_MAIL_SERVER'        => lang('a-cfg-26'),
	        'SITE_MAIL_PORT'          => lang('a-cfg-27'),
	        'SITE_MAIL_FROM'          => lang('a-cfg-28'),
	        'SITE_MAIL_AUTH'          => lang('a-cfg-29'),
	        'SITE_MAIL_USER'          => lang('a-cfg-30'),
	        'SITE_MAIL_PASSWORD'      => lang('a-cfg-31'),
	        'SITE_MAP_TIME'           => lang('a-cfg-32'),
	        'SITE_MAP_NUM'            => lang('a-cfg-33'),
	        'SITE_MAP_UPDATE'         => lang('a-cfg-34'),
	        'SITE_MAP_AUTO'           => lang('a-cfg-35'),
	        'SITE_SEARCH_PAGE'        => lang('a-cfg-36'),
	        'SITE_SEARCH_TYPE'        => lang('a-cfg-37'),
			'SITE_SEARCH_INDEX_CACHE' => lang('a-cfg-38'),
			'SITE_SEARCH_DATA_CACHE'  => lang('a-cfg-39'),
			'SITE_SEARCH_SPHINX_HOST' => lang('a-cfg-40'),
			'SITE_SEARCH_SPHINX_PORT' => lang('a-cfg-41'),
			'SITE_SEARCH_SPHINX_NAME' => lang('a-cfg-42'),
			'SITE_ADMIN_CODE'         => lang('a-cfg-43'),
			'SITE_ADMIN_PAGESIZE'     => lang('a-cfg-46'),
			'SITE_SEARCH_KW_FIELDS'   => lang('a-cfg-47'),
			'SITE_SEARCH_KW_OR'       => lang('a-cfg-48'),
			'SITE_SEARCH_URLRULE'     => lang('a-cfg-49'),
			'SITE_TAG_PAGE'           => lang('a-cfg-50'),
			'SITE_TAG_CACHE'          => lang('a-cfg-51'),
			'SITE_TAG_URLRULE'        => lang('a-cfg-52'),
			'SITE_TAG_LINK'           => lang('a-cfg-53'),
			'SITE_KEYWORD_NUMS'       => lang('a-cfg-54'),
			'SITE_KEYWORD_CACHE'      => lang('a-cfg-55'),
			'SITE_TAG_URL'            => lang('a-cfg-56'),
			'SITE_COMMENT_SWITCH'     => lang('a-cfg-75'),
            'SYS_MEMBER'              => lang('t-002'),
            'SYS_MODE'                => lang('t-003'),
			'SITE_BDPING'              => '百度Ping推送',

        );
	    //加载应用程序配置文件.
	    $config = self::load_config('config');
        $chunk = array_chunk($string, 10, true);
        $config_core = $chunk[0]; //系统核心文件
        if ($this->post('submit')) {
            $configdata = $this->post('data');
            $content  = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * 应用程序配置信息" . PHP_EOL . " */" . PHP_EOL . "return array(" . PHP_EOL .
            PHP_EOL . "	/* 系统核心配置 */" . PHP_EOL . PHP_EOL;
			$system     = array();
            foreach ($config_core as $var=>$msg) {
                $value  = "'" . $config[$var] . "'";
                if (is_bool($config[$var])) {
				    $value = $config[$var] ? 'true' : 'false';
				} elseif ($var == 'SYS_EDITOR' && (!isset($config[$var]) || empty($config[$var]))) {
				    $value = "'kindeditor'";
				} elseif ($var == 'ADMIN_NAMESPACE' && (!isset($config[$var]) || empty($config[$var]))) {
				    $value = "'admin'";
				} elseif ($var == 'SYS_VAR_PREX' && (!isset($config[$var]) || empty($config[$var]))) {
				    $value = "'idea_" . substr(md5(time()), 0, 5) . "_'";
				} elseif ($var == 'SITE_MEMBER_COOKIE' && (!isset($config[$var]) || empty($config[$var]))) {
				    $value = "'" . substr(md5(time()), 5, 15) . "'";
				} elseif ($var == 'SYS_CAPTCHA_MODE' && (!isset($config[$var]) || empty($config[$var]))) {
				    $value = "'0'";
				} elseif ($config[$var] == 'true') {
				    $value = 'true';
				} elseif ($config[$var] == 'false') {
				    $value = 'false';
				}
                $content  .= "	'" . strtoupper($var) . "'" . $this->setspace($var) . " => " . $value . ",  //" . $msg . PHP_EOL;
				$system[]  = $var;
            }

            $content .= PHP_EOL . "	/* 网站相关配置 */" . PHP_EOL . PHP_EOL;
            foreach ($configdata as $var=>$val) {
			    if (!in_array($var, $system)) {
                    $value    = $val == 'false' || $val == 'true' ? $val : "'" . $val . "'";
                    $content .= "	'" . strtoupper($var) . "'" . $this->setspace($var) . " => " . $value . ",  //" . $string[$var] . PHP_EOL;
				}
            }
            $content .= PHP_EOL . ");";
            file_put_contents(CONFIG_DIR . 'config.ini.php', $content);
            $this->adminMsg(lang('success'), purl('index/config', array('type' => $this->get('type'))), 3, 1, 1);
        }
        $this->view->assign(array(
            'data'   => $config,
			'type'   => $this->get('type') ? $this->get('type') : 1,
            'string' => $string,
        ));
        $this->view->display('admin/config');
	}
	
	/**
	 * 全站缓存
	 */
	public function cacheAction() {
	    $caches = array(
	        array('20',  'plugin',      'cache'),
			array('21',  'auth',        'cache'),
			array('103', 'ip',          'cache'),
			array('30',  'tag',         'cache'),
	        array('26',  'block',       'cache'),
	        array('27',  'theme',       'cache'),
			array('29',  'member',      'cache'),
	        array('25',  'relatedlink', 'cache')
	    );
		$sites = App::get_site();

        $body = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * 多站点域名配置文件" . PHP_EOL . " */" . PHP_EOL . "return array(" . PHP_EOL . PHP_EOL;
        $body2 = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * 移动端域名配置文件" . PHP_EOL . " */" . PHP_EOL . "return array(" . PHP_EOL . PHP_EOL;
        foreach ($sites as $id=>$t) {
            $body.= "	'" . $id . "'  => '" . $t['DOMAIN'] . "', " . PHP_EOL;
            if ($t['SITE_MURL']) {
                $body2.= "	'" . $id . "'  => '" . $t['SITE_MURL'] . "', " . PHP_EOL;
            }

        }
        $body.= PHP_EOL . ");";
        $body2.= PHP_EOL . ");";
        file_put_contents(CONFIG_DIR . 'site.ini.php', $body);
        file_put_contents(CONFIG_DIR . 'mobile.ini.php', $body2);

        //
		$count = count($sites);
		//多网站模型缓存
		foreach ($sites as $sid => $t) {
			$caches[] = array('22', 'model',	'cache', array('site' => $sid, 'text' => '(' . $sid . '/' . $count . ')'));
		}
		//多网站栏目缓存
		foreach ($sites as $sid => $t) {
			$caches[] = array('23', 'category', 'cache', array('site' => $sid, 'text' => '(' . $sid . '/' . $count . ')'));
		}
		//多网站推荐位缓存
		foreach ($sites as $sid => $t) {
			$caches[] = array('24', 'position', 'cache', array('site' => $sid, 'text' => '(' . $sid . '/' . $count . ')'));
		}
		//多网站联动菜单缓存
		foreach ($sites as $sid => $t) {
			$caches[] = array('28', 'linkage',  'cache', array('site' => $sid, 'text' => '(' . $sid . '/' . $count . ')'));
		}
	    if ($this->get('show')) {
            $this->cache->delete('install');	//删除安装提示文件
            $this->content->clear_cache_id();	//清理内容id缓存
            $form = $this->model('form');		//实例化表单对象
            $form->clear_cache_id();			//清理表单id缓存
            $version = CMS_VERSION;				//生成版本标识符
            if (substr_count($version, '.') == 1) {
                $version.= '.0';
            }
            $this->cache->set('version', str_replace('.', '', $version));
            // 删除全部缓存文件
            $this->load->helper('file');
            delete_files(FCPATH.'cache/models/');
            delete_files(FCPATH.'cache/views/');
	    } else {
            $this->view->assign('caches', $caches);
	        $this->view->display('admin/cache');
	    }
	}
	
	/**
	 * 后台日志
	 */
	public function logAction() {
	    $page = (int)$this->get('page') ? (int)$this->get('page') : 1;
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
	    $logsdir  = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
		$filedata = file_list::get_file_list($logsdir);
		$data = array();
		$username = $this->post('submit') ? $this->post('kw') : $this->get('username');
		if ($filedata) {
		    $filedata      = array_reverse($filedata);
			foreach ($filedata as $file) {
				if (substr($file, -4) == '.log') {
					$fdata = file_get_contents($logsdir . $file);
					$fdata = explode(PHP_EOL, $fdata);
					foreach ($fdata as $v) {
						$t = unserialize($v);
						if (is_array($t) && $t) {
							if ($username) {
								if ($t['username'] == $username) $data[] = $t;
							} else {
								$data[] = $t;
							}
						}
					}
				}
			}
		}
		$total = count($data);
		$pagesize = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
		$list = array();
		$count_pg = ceil($total/$pagesize);
        $offset   = ($page - 1) * $pagesize;		
		foreach ($data as $i => $t) {
		    if ($i >= $offset && $i < $offset + $pagesize) $list[] = $t;
		}
		$pagelist = $pagelist->total($total)->url(purl('index/log', array('page' => '{page}', 'username' => $username)))->num($pagesize)->page($page)->output();
		$this->view->assign(array(
	        'list'     => $list,
	        'pagelist' => $pagelist
	    ));
	    $this->view->display('admin/log');
	}
	
	/**
	 * 修改版权
	 */
	public function bqAction() {
        $file = FCPATH.'config/version.ini.php';
        $data = require $file;
        if (IS_POST) {
            $post = $this->input->post('data', true);
            $version = "<?php
return array(

	'cms' => '".safe_replace($post['cms'])."',
	'name' => '".safe_replace($post['name'])."',
    'company' => '".safe_replace($post['company'])."',
	'version' => '".$data['version']."',
	'update' => '".$data['update']."',

);";
            $data = $post;
            file_put_contents($file, $version);
            $this->adminMsg(lang('success'), url('admin/index/bq'), 3, 1, 1);
        }
        $this->view->assign('data', $data);
        $this->view->display('admin/bq');
    }

	/**
	 * 攻击日志
	 */
	public function attackAction() {
	    $page     = (int)$this->get('page') ? (int)$this->get('page') : 1;
	    $pagelist = $this->instance('pagelist');
		$pagelist->loadconfig();
	    $logsdir  = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'attack' . DIRECTORY_SEPARATOR;
		$filedata = file_list::get_file_list($logsdir);
		$data     = array();
		$ip       = $this->post('submit') ? $this->post('kw') : $this->get('ip');
		if ($filedata) {
		    $filedata      = array_reverse($filedata);
			foreach ($filedata as $file) {
				if (substr($file, -4) == '.log') {
					$fdata = file_get_contents($logsdir . $file);
					$fdata = explode(PHP_EOL, $fdata);
					foreach ($fdata as $v) {
						$t = unserialize($v);
						if ($t && is_array($t)) {
							if ($ip) {
								if ($t['ip'] == $ip) $data[] = $t;
							} else {
								$data[] = $t;
							}
						}
					}
				}
			}
		}
		$total    = count($data);
		$pagesize = isset($this->site['SITE_ADMIN_PAGESIZE']) && $this->site['SITE_ADMIN_PAGESIZE'] ? $this->site['SITE_ADMIN_PAGESIZE'] : 8;
		$list     = array();
		$count_pg = ceil($total/$pagesize);
        $offset   = ($page - 1) * $pagesize;		
		foreach ($data as $i => $t) {
		    if ($i >= $offset && $i < $offset + $pagesize) $list[] = $t;
		}
		$pagelist = $pagelist->total($total)->url(purl('index/attack', array('page' => '{page}', 'ip' => $ip)))->num($pagesize)->page($page)->output();
		$this->view->assign(array(
			'ip'       => $this->cache->get('ip'),
	        'list'     => $list,
	        'pagelist' => $pagelist
	    ));
	    $this->view->display('admin/attacklog');
	}
	
	/**
	 * 清除日志
	 */
	public function clearlogAction() {
	    $time     = strtotime('-30 day');
	    $logsdir  = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
		$filedata = file_list::get_file_list($logsdir);
		$count    = 0;
		if ($filedata) {
			foreach ($filedata as $file) {
				if (substr($file, -4) == '.log') {
					$name = substr($file, 0, 4) . '-' . substr($file, 4, 2) . '-' . substr($file, 6, 2);
					if ($time > strtotime($name)) {
						@unlink($logsdir . $file);
						$count ++;
					}
				}
			}
		}
	    $this->adminMsg(lang('a-ind-32') . '(#' . $count . ')', purl('index/log'), 3, 1, 1);
	}
	
	/**
	 * 清除攻击日志
	 */
	public function clearattackAction() {
	    $time     = strtotime('-30 day');
	    $logsdir  = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'attack' . DIRECTORY_SEPARATOR;
		$filedata = file_list::get_file_list($logsdir);
		$count    = 0;
		if ($filedata) {
			foreach ($filedata as $file) {
				if (substr($file, -4) == '.log') {
					$name = substr($file, 0, 4) . '-' . substr($file, 4, 2) . '-' . substr($file, 6, 2);
					if ($time > strtotime($name)) {
						@unlink($logsdir . $file);
						$count ++;
					}
				}
			}
		}
	    $this->adminMsg(lang('a-ind-32') . '(#' . $count . ')', purl('index/attack'), 3, 1, 1);
	}
	
	/**
	 * 验证Email
	 */
	public function ajaxmailAction() {
	    if ($this->get('submit')) {
	        $toemail = $this->get('mail_to');
	        if (empty($toemail)) exit(lang('a-ind-33'));
	        $config  = array(
	            'SITE_MAIL_TYPE'     => (int)$this->post('mail_type'),
	            'SITE_MAIL_SERVER'   => $this->post('mail_server'),
	            'SITE_MAIL_PORT'     => (int)$this->post('mail_port'),
	            'SITE_MAIL_FROM'     => $this->post('mail_from'),
	            'SITE_MAIL_AUTH'     => $this->post('mail_auth'),
	            'SITE_MAIL_USER'     => $this->post('mail_user'),
	            'SITE_MAIL_PASSWORD' => $this->post('mail_password')
	        );
	        mail::set($config);
	        if (mail::sendmail($toemail, lang('a-ind-34'), lang('a-ind-35'))) {
	            echo lang('a-ind-36');
	        } else {
	            echo lang('a-ind-37');
	        }
	    } else {
	        exit(lang('a-ind-38'));
	    }
	}
	
	/**
	 * 更新地图
	 */
	public function updatemapAction() {
	    $fp = @fopen(APP_ROOT . 'idea_test.txt', 'wb');
		if (!file_exists(APP_ROOT . 'idea_test.txt') || $fp === false) $this->adminMsg(lang('app-9', array('1' => APP_ROOT)));
		@fclose($fp);
		if (file_exists(APP_ROOT . 'idea_test.txt')) unlink(APP_ROOT . 'idea_test.txt');
	    $count = sitemap_xml();
	    $this->adminMsg(lang('a-ind-39') . '(#' . $count . ')', '', 3, 1, 1);
	}
	
	/**
	 * 更新指定缓存
	 */
	public function updatecacheAction() {
	    $a = $this->get('ca') ? $this->get('ca') : 'cache';
	    $c = $this->get('cc');
        Controller::redirect(url('admin/'.$c.'/'.$a).'&cache=1');
	}
	
	/**
	 * 数据统计
	 */
	public function ajaxcountAction() {
		if ($this->get('type') == 'member') {
		    $c1 = $this->content->count('member', 'id', null);
			$c2 = $this->content->count('member', 'id', 'status=0');
			echo '$("#member_1").html("' . $c1 . '");$("#member_2").html("' . $c2 . '");';
		} elseif ($this->get('type') == 'install') {
		    $ck = $this->cache->get('install');
			echo empty($ck) ? '' : "window.top.art.dialog({title:'" . lang('a-ind-41') . "',fixed:true, content: '<a href=" . url('admin/index/cache') . " target=right>" . lang('a-ind-42') . "</a>'});";
		} else {
		    $id = (int)$this->get('modelid');
			$c1 = $this->content->_count(null, 'modelid=' . $id, null, 36000);
			if ($catids = $this->getVerifyCatid()) {	//角色审核权限
				$where = 'catid not in (' . implode(',', $catids) . ') and modelid=' . $id;
			} else {
				$where = 'modelid=' . $id;
			}
			$c2 = $this->content->count('content_' . $this->siteid . '_verify', null, $where, null, 36000);
			echo '$("#m_' . $id . '_1").html("' . $c1 . '");$("#m_' . $id . '_2").html("' . $c2 . '");';
		}
		exit;
	}
	
	/**
	 * 空格填补
	 */
	private function setspace($var) {
	    $len = strlen($var) + 2;
	    $cha = 25 - $len;
	    $str = '';
	    for ($i = 0; $i < $cha; $i ++) $str .= ' ';
	    return $str;
	}
}