<?php

class SiteController extends Admin {

	private $string;

    public function __construct() {
		parent::__construct();
		$this->string = array(
			'SITE_EXTEND_ID'			=> lang('a-sit-8'),
            'SITE_LANGUAGE'				=> lang('a-cfg-12'),
			'SITE_TIMEZONE'				=> lang('a-cfg-15'),
	        'SITE_THEME'				=> lang('a-cfg-16'),
	        'SITE_NAME'					=> lang('a-cfg-17'),
            'SITE_TITLE'				=> lang('a-cfg-18'),
            'SITE_KEYWORDS'				=> lang('a-cfg-19'),
            'SITE_DESCRIPTION'			=> lang('a-cfg-20'),
            'SITE_BOTTOM_INFO'			=> lang('a-cfg-ex-1'),
	        'SITE_WATERMARK'			=> lang('a-cfg-21'),
	        'SITE_WATERMARK_ALPHA'		=> lang('a-cfg-22'),
	        'SITE_WATERMARK_TEXT'		=> lang('a-cfg-23'),
			'SITE_WATERMARK_SIZE'		=> lang('a-cfg-70'),
			'SITE_WATERMARK_IMAGE'		=> lang('a-sit-0'),
	        'SITE_WATERMARK_POS'		=> lang('a-cfg-67'),
			'SITE_THUMB_TYPE'			=> lang('a-mod-204'),
			'SITE_THUMB_WIDTH'			=> lang('a-cfg-44'),
			'SITE_THUMB_HEIGHT'			=> lang('a-cfg-45'),
			'SITE_TIME_FORMAT'			=> lang('a-cfg-57'),
			'SITE_MOBILE'				=> lang('a-cfg-72'),
			'SITE_MURL'				    => lang('da013'),
			'SITE_ICP'				    => lang('a-site-tcp'),
            'SITE_JS'                  => lang('a-site-tps')
        );
	}

	/**
	 * 站点列表
	 */
	public function indexAction() {
		if ($this->isPostForm()) {
			$ids = $this->post('ids');
			if ($ids) {
				foreach ($ids as $id) {
					$this->delAction($id, 1);
				}
				$this->adminMsg(lang('success'), purl('site/index'), 3, 1, 1);
			}
		}
	    $this->view->assign(array(
			'list' => App::get_site(),
		));
	    $this->view->display('admin/site_list');
	}

	/**
	 * 添加站点
	 */
	public function addAction() {
	    if ($this->isPostForm()) {
			$data = $this->post('data');
			$site = App::get_site();
			$id = count($site) + 1;
			if (empty($data['name']) || empty($data['url'])) {
                $this->adminMsg(lang('a-sit-9'));
            }
			$data['url'] = trim($data['url'], '/');
			if (strpos($data['url'], '/')) {
				$this->adminMsg('域名地址无效');
			}
			$site[$id] = array(
				'DOMAIN' => $data['url'],
				'SITE_NAME' => $data['name'],
				'SITE_EXTEND_ID' => $data['siteid']
			);
			//保存网站域名配置文件
			$this->set_site_url($site);
			//保存网站信息配置文件
			$this->set_site_cfg($site[$id], $id);
			//创建内容表
			$this->copy_table('content_{site}', $id);
			//创建内容扩展
			$this->copy_table('content_{site}_extend', $id);
			//创建内容审核
			$this->copy_table('content_{site}_verify', $id);
			//若继承网站，则创建内容模型
			if ($data['siteid']) {
				$model = $this->get_model('content', $data['siteid']);
				if ($model) {
					foreach ($model as $t) {
						$this->copy_table(preg_replace('/\_([0-9]+)\_/', '_{site}_', $t['tablename']), $id, $data['siteid']);
					}
				}
				//创建表单模型
				$model = $this->get_model('form', $data['siteid']);
				if ($model) {
					foreach ($model as $t) {
						$this->copy_table(preg_replace('/\_([0-9]+)\_/', '_{site}_', $t['tablename']), $id, $data['siteid']);
					}
				}
			}
			$this->adminMsg(lang('success'), purl('site/index'), 3, 1, 1);
		}
		$this->view->assign(array(
			'site' => App::get_site(),
		));
		$this->view->display('admin/site_add');
	}

	/**
	 * 修改站点
	 */
	public function editAction() {
		$id = (int)$this->get('id');
		$site = App::get_site();
		if (!isset($site[$id])) {
            $this->adminMsg(lang('a-sit-13'));
        }
	    if ($this->isPostForm()) {
			$data = $this->post('data');
			if (empty($data['name']) || empty($data['url'])) {
                $this->adminMsg(lang('a-sit-9'));
            }
			$extend_id = $site[$id]['SITE_EXTEND_ID'];
			$site[$id] = array(
				'DOMAIN' => $data['url'],
				'SITE_NAME' => $data['name'],
				'SITE_EXTEND_ID' => $extend_id	//继承关系不允许修改
			);
			//保存网站域名配置文件
			$this->set_site_url($site);
			//保存网站信息配置文件
			$this->set_site_cfg($site[$id], $id);
			$this->adminMsg(lang('success'), purl('site/index'), 3, 1, 1);
		}
		$this->view->assign(array(
			'id' => $id,
			'data' => $site[$id],
			'site' => $site,
			'edit' => 1
		));
		$this->view->display('admin/site_add');
	}

	/**
	 * 删除站点
	 */
	public function delAction($id = 0, $show = 0) {
		if (!auth::check($this->roleid, 'site-del', 'admin')) {
            $this->adminMsg(lang('a-com-0', array('1' => 'site', '2' => 'del')));
        }
		$site = $id ? $id : (int)$this->get('id');
		if ($show && empty($site)) {
            $this->adminMsg(lang('a-sit-28'));
        }
		if ($site == 1) {
            return false;
        }
		//删除内容表
		$this->delete_table('content_{site}', $site);
		//删除内容扩展
		$this->delete_table('content_{site}_extend', $site);
		//删除内容审核
		$this->delete_table('content_{site}_verify', $site);
		//删除自定义模型表中数据
		$model	= $this->get_model('content');
		if ($model) {
			foreach ($model as $t) {
				$this->delete_table(preg_replace('/\_([0-9]+)\_/', '_{site}_', $t['tablename']), $site);
			}
		}
		//删除自定义表单中数据
		$model = $this->get_model('form');
		if ($model) {
			foreach ($model as $t) {
				$this->delete_table(preg_replace('/\_([0-9]+)\_/', '_{site}_', $t['tablename']), $site);
			}
		}
		//删除模型
		$this->content->set_table_name('model');
		$this->content->delete('site=' . $site);
		//删除栏目数据
		$this->content->set_table_name('category');
		$this->content->delete('site=' . $site);
		//删除block
		$this->content->set_table_name('block');
		$this->content->delete('site=' . $site);
		//删除position
		$this->content->set_table_name('position');
		$this->content->delete('site=' . $site);
		//删除linkage
		$this->content->set_table_name('linkage');
		$this->content->delete('keyid<>0 AND site=' . $site);
		//删除网站配置文件
		@unlink(CONFIG_DIR . 'site' . DIRECTORY_SEPARATOR . $site . '.ini.php');
		//删除网站域名文件
		$data = App::get_site();
		unset($data[$site]);
		$this->set_site_url($data);
		@unlink(APP_ROOT . 'cache/index/' . $site . '.html');
		$show or $this->adminMsg(lang('success'), purl('site/index'), 3, 1, 1);
	}

	/**
	 * 站点配置
	 */
	public function configAction() {
	    //加载配置文件.
		$siteid = $this->get('id') ? $this->get('id') : $this->siteid;
	    $config = self::load_config('site' . DIRECTORY_SEPARATOR . $siteid);
        if ($this->post('submit')) {
            $data = $this->post('data');

            $body = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * " . $data['SITE_NAME'] . "配置" . PHP_EOL . " */" . PHP_EOL . "return array(" . PHP_EOL . PHP_EOL;
            foreach ($this->string as $var=>$str) {
			    if ($var == 'SITE_LANGUAGE' && empty($data[$var])) {
				    $value = "'zh-cn'";
				} elseif ($var == 'SITE_DOMAIN') {
				    $value = "'" . $config['SITE_DOMAIN'] . "'";
				} elseif ($var == 'SITE_EXTEND_ID') {
				    $value = "'" . $config['SITE_EXTEND_ID'] . "'";
				} else {
                    $value = $data[$var] == 'false' || $data[$var] == 'true' ? $data[$var] : "'" . $data[$var] . "'";
				}
				$body .= "	'" . strtoupper($var) . "'" . $this->setspace($var) . " => " . $value . ",  //" . $str . PHP_EOL;
            }
            $body .= PHP_EOL . ");";
            file_put_contents(CONFIG_DIR . 'site' . DIRECTORY_SEPARATOR . $siteid . '.ini.php', $body);
            $this->adminMsg(lang('success'), purl('site/config', array('id' => $siteid, 'typeid' => $this->post('typeid'))), 3, 1, 1);
        }
        //模板风格
		$theme = '';
        $file_list = file_list::get_file_list(VIEW_DIR);
		foreach ($file_list as $t) {
			if (is_dir(VIEW_DIR . $t) && strpos($t, 'mobile_') === false && !in_array($t, array('error', 'admin', 'index.html', 'install', 'mobile','weixin'))) {
				$theme .= '<option value="' . $t . '" ' . ($config['SITE_THEME'] == $t ? 'selected' : '') . '>' . $t . '</option>';
			}
		}
        $this->view->assign(array(
			'site' => auth::check($this->roleid, 'site-index', 'admin') ? 1 : 0,
            'data' => $config,
            'theme' => $theme,
			'langs' => file_list::get_file_list(EXTENSION_DIR . 'language' . DIRECTORY_SEPARATOR),
			'typeid' => $this->get('typeid') ? $this->get('typeid') : 1,
            'string' => $this->string,
			'images' => file_list::get_file_list(EXTENSION_DIR . 'watermark' . DIRECTORY_SEPARATOR)
        ));
        $this->view->display('admin/site_config');
	}

	/**
	 * 空格填补
	 */
	private function setspace($var) {
	    $len = strlen($var) + 2;
	    $cha = 25 - $len;
	    $str = '';
	    for ($i = 0; $i < $cha; $i ++) {
            $str.= ' ';
        }
	    return $str;
	}

	/**
	 * 保存网站域名配置文件
	 */
	private function set_site_url($data) {
	    $body = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * 多站点域名配置文件" . PHP_EOL . " */" . PHP_EOL . "return array(" . PHP_EOL . PHP_EOL;
	    $body2 = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * 移动端域名配置文件" . PHP_EOL . " */" . PHP_EOL . "return array(" . PHP_EOL . PHP_EOL;
		foreach ($data as $id=>$t) {
            $body.= "	'" . $id . "'  => '" . $t['DOMAIN'] . "', " . PHP_EOL;
            if ($t['SITE_MURL']) {
                $body2.= "	'" . $id . "'  => '" . $t['SITE_MURL'] . "', " . PHP_EOL;
            }

		}
		$body.= PHP_EOL . ");";
		$body2.= PHP_EOL . ");";
		file_put_contents(CONFIG_DIR . 'site.ini.php', $body);
		file_put_contents(CONFIG_DIR . 'mobile.ini.php', $body2);
	}

	/**
	 * 保存网站域名配置文件
	 */
	private function set_site_cfg($data, $id) {
		if (file_exists(CONFIG_DIR . 'site' . DIRECTORY_SEPARATOR . $id . '.ini.php') && is_file(CONFIG_DIR . 'site' . DIRECTORY_SEPARATOR . $id . '.ini.php')) {
			$SITE_NAME = $data['SITE_NAME'];
			$SITE_EXTEND_ID = $data['SITE_EXTEND_ID'];
			$data = self::load_config('site' . DIRECTORY_SEPARATOR . $id);
			$data['SITE_NAME'] = $SITE_NAME;
			$data['SITE_EXTEND_ID']	= $SITE_EXTEND_ID;
		}
	    $body = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * " . $data['SITE_NAME'] . "配置" . PHP_EOL . " */" . PHP_EOL . "return array(" . PHP_EOL . PHP_EOL;
		foreach ($this->string as $var=>$str) {
			$value = $data[$var] == 'false' || $data[$var] == 'true' ? $data[$var] : "'" . $data[$var] . "'";
			$body.= "	'" . strtoupper($var) . "'" . $this->setspace($var) . " => " . $value . ",  //" . $str . PHP_EOL;
		}
		$body.= PHP_EOL . ");";
		file_put_contents(CONFIG_DIR . 'site' . DIRECTORY_SEPARATOR . $id . '.ini.php', $body);
	}

	/**
	 * 复制表以及模型文件
	 */
	private function copy_table($table, $site, $from = 1) {
		$news = str_replace('{site}', $site, $table); //新表
		$from = str_replace('{site}', $from, $table);	//复制对象
		if (empty($table) || $site <= 1 || $this->content->is_table_exists($news)) {
            return false;
        }
		$row = $this->content->execute("SHOW CREATE TABLE " . $this->content->prefix . $from, false); //获取复制表的SQL
		$sql = str_replace($this->content->prefix . $from, $this->content->prefix . $news, $row['Create Table']); //将表名称替换成创建表的名称
		$this->content->query($sql);
		if ($table != 'content_{site}_extend') { //内容扩展表无模型
			$news = ucfirst($news); //首字母大写
			//继承对象
			if ($table == 'content_{site}') {
				$e = 'ContentModel';
			} elseif (strpos($table, 'form_{site}_') !== false) {
				$e = 'FormModel';
			} else {
				$e = 'Model';
			}
			$body = "<?php" . PHP_EOL . PHP_EOL .
			"class " . $news . "Model extends " . $e . " {" . PHP_EOL . PHP_EOL .
			"    public function get_primary_key() {" . PHP_EOL .
			"        return \$this->primary_key = 'id';" . PHP_EOL .
			"    }" . PHP_EOL . PHP_EOL .
			"    public function get_fields() {" . PHP_EOL .
			"        return \$this->get_table_fields();" . PHP_EOL .
			"    }" . PHP_EOL . PHP_EOL .
			"}";
			file_put_contents(MODEL_DIR . $news . 'Model.php', $body); //创建模型文件
		}
	}

	/**
	 * 删除表以及模型文件
	 */
	private function delete_table($table, $site) {
		$table = str_replace('{site}', $site, $table); //表的名称
		$this->content->query('DROP TABLE IF EXISTS `' . $this->content->prefix . $table . '`');
		@unlink(MODEL_DIR . ucfirst($table) . 'Model.php');
	}
}
