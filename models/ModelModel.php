<?php

class ModelModel extends Model {
	
	public function get_primary_key() {
		return $this->primary_key = 'modelid';
	}
	
	//获取网站id
	public function get_site_id($site = 0) {
		$site	= $site ? $site : App::get_site_id();
		$sites	= App::get_site();
		return $sites[$site]['SITE_EXTEND_ID'] ? $sites[$site]['SITE_EXTEND_ID'] : $site;
	}
	
	//获取模型数据
	public function get_data($typeid, $site = 0) {
		if ($typeid != 2) $this->where('site=' . $this->get_site_id($site));
		return $this->where('typeid=' . $typeid)->select();
	}
	
	//添加、修改模型
	public function set($modelid = 0, $data) {
	    if ($modelid) {
	        //修改模型
	        $this->update($data, 'modelid=' . $modelid);
	        return $modelid;
	    }
		$tablename = $data['tablename'];
		$data['tablename'] = str_replace('{site}', $data['site'], $data['tablename']); //生成该站点(被继承的站点)的表名称
	    //添加模型入库
	    $this->insert($data);
	    $modelid = $this->get_insert_id();
	    if (empty($modelid)) return false;
		//根据类别创建表
		if ($data['typeid'] != 2 && $data['typeid'] != 4) {
			if ($data['typeid'] == 1) {	//内容模型
				$sql = "CREATE TABLE IF NOT EXISTS `" . $this->prefix . "{table}` (`id` int(10) NOT NULL ,`catid` SMALLINT(5) NOT NULL ,`content` MEDIUMTEXT NOT NULL ,PRIMARY KEY (`id`), KEY `catid` (`catid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
				//内容表默认字段
				$this->query("INSERT INTO `" . $this->prefix . "model_field` (fieldid,modelid,field,name,formtype,isshow) VALUES (NULL, $modelid,'content','" . lang('a-con-128') . "','editor',1)");
			} elseif ($data['typeid'] == 3) {	//表单模型
				$sql = "CREATE TABLE IF NOT EXISTS `" . $this->prefix . "{table}` (`id` int(10) NOT NULL,`cid` mediumint(8) NOT NULL,`userid` mediumint(8) NOT NULL,`username` char(20) NOT NULL,`listorder` tinyint(3) unsigned NOT NULL DEFAULT '0',`status` tinyint(2) unsigned NOT NULL DEFAULT '1',`inputtime` int(10) unsigned NOT NULL DEFAULT '0', `updatetime` int(10) unsigned NOT NULL DEFAULT '0',`ip` char(20) NULL,PRIMARY KEY (`id`),KEY `listorder` (`listorder`),KEY `status` (`status`),KEY `updatetime` (`updatetime`),KEY `inputtime` (`inputtime`),KEY `userid` (`userid`),KEY `cid` (`cid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			}
			$this->query(str_replace('{table}', $data['tablename'], $sql)); //创建模型表
			$this->create_model($data['tablename'], $data['typeid']); //创建Model文件
			//创建多站点
			$sites	= App::get_site();
			$config = App::get_config();
			foreach ($sites as $sid => $t) {
				if ($t['SITE_EXTEND_ID'] == $data['site'] || $data['site'] == $sid) { 
					//判断当前继承站点的id与目标站点id一致，或者同类继承站点的站点都同步模型
					$sitetable = str_replace('{site}', $sid, $tablename); //当前循环的站点的表名称
					$this->query(str_replace('{table}', $sitetable, $sql)); //创建模型表
					$this->create_model($sitetable, $data['typeid']); //创建Model文件
				}	
			}
		} elseif ($data['typeid'] == 2) {	//会员模型
			$sql = "CREATE TABLE IF NOT EXISTS `" . $this->prefix . $data['tablename'] . "` (`id` MEDIUMINT(8) NOT NULL ,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$this->query($sql);
			//创建Model
			$this->create_model($data['tablename'], $data['typeid']);
		} elseif ($data['typeid'] == 4) {	//会员扩展模型
			$sql = "CREATE TABLE IF NOT EXISTS `" . $this->prefix . $data['tablename'] . "` (`id` int(10) NOT NULL AUTO_INCREMENT,`touserid` mediumint(8) NOT NULL,`userid` mediumint(8) NOT NULL,`username` char(20) NOT NULL,`status` tinyint(2) unsigned NOT NULL DEFAULT '1',`inputtime` int(10) unsigned NOT NULL DEFAULT '0', `updatetime` int(10) unsigned NOT NULL DEFAULT '0',`ip` char(20) NULL,`verify` text NOT NULL,PRIMARY KEY (`id`),KEY `status` (`status`),KEY `updatetime` (`updatetime`),KEY `inputtime` (`inputtime`),KEY `userid` (`userid`),KEY `touserid` (`touserid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$this->query($sql);
			//创建Model
			$this->create_model($data['tablename'], $data['typeid']);
		}
	    return $modelid;
	}
	
	//删除模型
	public function del($data) {
	    $table = $this->prefix . $data['tablename'];
	    $this->query('DROP TABLE IF EXISTS `' . $table . '`');
	    $this->delete('modelid=' . $data['modelid']);
	    $this->del_model($data['tablename']);
	    $this->query('DELETE FROM `' . $this->prefix . 'model_field` where modelid=' . $data['modelid']);
		//删除多站点
		$sites	= App::get_site();
		$config = App::get_config();
		foreach ($sites as $sid => $t) {
			if ($t['SITE_EXTEND_ID'] == $data['site'] || $data['site'] == $sid) {
				//继承网站则同步删除模型
				$table = preg_replace('/\_([0-9]+)\_/', '_' . $sid . '_', $data['tablename']);
				$this->query('DROP TABLE IF EXISTS `' . $this->prefix . $table . '`');
				$this->del_model($table);
			}
		}
		//删除栏目
	    $this->query('DELETE FROM `' . $this->prefix . 'category` where modelid=' . $data['modelid']);
	}
	
	//创建模型
	public function create_model($table, $typeid) {
        if (strpos($table, 'member') !== false) return;
        $file = MODEL_DIR .'callback/'. $table . '.php';
        if (is_file($file)) return;

        $c = "<?php" . PHP_EOL . PHP_EOL .
            "function callback_{$table}(\$data) {" . PHP_EOL . PHP_EOL .
            " " . PHP_EOL . PHP_EOL .
            "}";
        file_put_contents($file, $c);
        return;
		$table	= ucfirst($table);
		$e		= $typeid == 3 ? "FormModel" : "Model";
		$c		= "<?php" . PHP_EOL . PHP_EOL .
		"class " . $table . "Model extends " . $e . " {" . PHP_EOL . PHP_EOL .
		"    public function get_primary_key() {" . PHP_EOL .
		"        return \$this->primary_key = 'id';" . PHP_EOL .
		"    }" . PHP_EOL . PHP_EOL .
		"    public function get_fields() {" . PHP_EOL .
		"        return \$this->get_table_fields();" . PHP_EOL .
		"    }" . PHP_EOL . PHP_EOL .
		"}";
		file_put_contents(MODEL_DIR . $table . 'Model.php', $c);
	}
	
	//删除模型文件
    protected function del_model($table) {
	    $file  = MODEL_DIR . ucfirst($table) . 'Model.php';
	    if (file_exists($file)) @unlink($file);
	}
}