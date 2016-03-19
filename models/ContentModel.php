<?php

class ContentModel extends Model {
    
    public function get_primary_key() {
        return $this->primary_key = 'id';
    }
    
    public function get_fields() {
        return $this->get_table_fields();
    }

    // 将文章推送到微信，多个id以逗号分隔
    public function post_weixin($id,$catid) {
        $url = url('admin/wx/sendContent', array('ids' => $id,'$catid'=>$catid));
        header('Location: '.$url);
    }
	
	/**
     * 获取内容数据
     */
	public function get_data($id) {
		$data = $this->find($id);
		$extend = $this->get_extend_data($id);
		return empty($extend) ? $data : array_merge($data, $extend);
	}
	
	/**
     * 内容表数据统计
     */
	public function _count($site = null, $where = null, $value = null, $cache = 0) {
		$site = empty($site) ? App::get_site_id() : $site;
		return $this->count('content_' . $site, null, $where, $value, $cache);
	}
	
	/**
     * 会员添加、修改内容数据
     */
	public function member($id, $tablename, $data) {
		if (!$this->is_table_exists($tablename)) return lang('m-con-37', array('1' => $tablename));
		if ($data['status'] == 1) {	//状态为审核时，直接入库
			$id = $this->set($id, $tablename, $data);
			if (is_numeric($id)) {
				$this->query('delete from `' . $this->prefix . 'content_' . App::get_site_id() . '_verify` where id=' . $id);	//删除审核表中数据
				$this->query('UPDATE ' . $this->prefix . 'member_count SET post=post+1 WHERE id=' . $data['userid']);	//增加会员统计数量
			}
			return $id;
		} else {
			//存入内容审核表中
			return $this->set_verify_data($id, $tablename, $data); 
		}
	}
	
    /**
     * 添加、修改内容数据
     */
    public function set($id, $tablename, $data) {
        $id = intval($id);
        if (!$this->is_table_exists($tablename)) {
            return lang('m-con-37', array('1' => $tablename));
        }
        $table = Controller::model($tablename); //加载附表Model
        if (empty($data['catid'])) {
            return lang('m-con-8');
        }
		$_data = $id ? $this->find($id) : null;
        //数组转化为字符
		foreach ($data as $i => $t) {
		    if (is_array($t)) {
                $data[$i] = array2string($t);
            }
		}
		//描述截取
	    if (empty($data['description']) && isset($data['content'])) {
		    $len = isset($data['ia_add_introduce']) && $data['ia_add_introduce'] && $data['ia_introcude_length'] ? $data['ia_introcude_length'] : 200;
		    $data['description'] = str_replace(PHP_EOL, '', strcut(clearhtml($data['content']), $len));
		}
		//下载远程图片
		if (isset($data['content']) && isset($data['ia_down_image']) && $data['ia_down_image']) {
			$content = str_replace(array('\\', '"'), array('', '\''), htmlspecialchars_decode($data['content']));
		    if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $imgs)) {
				$userid = !$data['sysadd'] && $data['userid'] ? $data['userid'] : (!$_data['sysadd'] && $_data['userid'] ? $_data['userid'] : 0);
				$sysadd = $data['sysadd'] ? $data['sysadd'] : ($_data['sysadd'] ? $_data['sysadd'] : 0);
				if ($userid) { //表示会员投稿
					$member	= $this->execute("select groupid from {$this->prefix}member where id=" . $userid, false);
					$group = $this->execute("select * from {$this->prefix}member_group where id=" . (int)$member['groupid'], false);
					$result	= $this->download_images($imgs[3], $userid, (int)$group['filesize']);
				} elseif($sysadd) { //表示管理员投稿
					$result	= $this->download_images($imgs[3]);
				}
				if (isset($result) && $result) {
					$image = $result['replace'][0];
					$data['content'] = str_replace($result['regex'], $result['replace'], $data['content']);
				}
			}
		}
		//提取缩略图
		if (empty($data['thumb']) && isset($data['content']) && isset($data['ia_auto_thumb']) && $data['ia_auto_thumb']) {
			if (isset($image)) {
				$data['thumb'] = $image;
			} else {
				$content = str_replace(array('\\', '"'), array('', '\''), htmlspecialchars_decode($data['content']));
				if (preg_match("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $img)) {
					$data['thumb'] = $img[3];
				}
			}
		}
		//关键字处理
		if ($data['keywords']) {
		    $data['keywords'] = str_replace('，', ',', $data['keywords']);
			$tags = @explode(',', $data['keywords']);
			if ($tags) {
			    foreach ($tags as $t) {
				    $name  = trim($t);
				    if ($name) {
						$d = $this->from('tag', 'id')->where('name=?', $name)->where('catid=?', $data['catid'])->select(false);
						if (empty($d)) {
							$this->query('INSERT INTO `' . $this->prefix . 'tag` (`name`,`letter`,`catid`) VALUES ("' . $name . '", "' . word2pinyin($name) . '","'.$data['catid'].'")');
						}
					}
				}
			}
		}
		$status = 1; //用于判断积分增加
        $is_add = 0;
        if ($id) { //修改
			if (empty($_data)) {
				$data['id'] = $id;
				$data['url'] = getUrl($data); //更新URL
				$data['status'] = 1; //插入时状态设置为1
				$this->insert($data);
				$table->insert($data);
                $is_add = 1;
			} else {
				$data['id'] = $data['id'] ? $data['id'] : $id;
				$data['url'] = getUrl($data); //更新URL
				unset($data['id']);
				$data['status'] = $data['status'] > 0 ? 1 : 0; //修改时，非0状态设置为1
				$this->update($data,  'id=' . $id);
				$table->update($data, 'id=' . $id);
				$status			= 0; //修改时不作为积分处理
				$data['userid'] = $_data['userid'];
			}
        } else { //添加
			$data['id'] = $id = $this->get_content_id();
			if (empty($id)) {
                return lang('m-con-36');
            }
			$data['url'] = getUrl($data); //更新URL
			$data['status'] = 1; //插入时状态设置为1
			$this->insert($data);
			$table->insert($data);
            $is_add = 1;
		}
		//积分处理 非系统添加且（增加时，文档状态等于1）
		if (!$data['sysadd'] && $status == 1) {
            $this->credits($data['userid'], 1);
        }
		//处理内容扩展数据
		$this->set_extend_data($id, $data);
        // 添加数据的处理
        if ($is_add) {
            // 回调函数
            $table = str_replace($this->prefix, '', $table->table_name);
            $function = 'callback_'.$table;
            $file = MODEL_DIR .'callback/'. $table . '.php';
            if (is_file($file)) {
                include_once $file;
                if (function_exists($function)) {
                    $function($data);
                }
            }

            // 执行任务表
			if (defined('SITE_BDPING') && SITE_BDPING) {

				$table = $this->db->dbprefix."sb";
				$this->db->query("CREATE TABLE IF NOT EXISTS `".$table."` (
				`id` int(10) AUTO_INCREMENT NOT NULL,
				`type` varchar(50) NOT NULL,
				`value` text NOT NULL,
				PRIMARY KEY (`id`),
				KEY `type` (`type`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
				$this->db->insert($table, array(
					'type' => 'content_add',
					'value' => array2string($data)
				));
			}
            // 推送微信
            //$this->post_weixin($id);
        }
        return $id;
    }
    
	/**
     * 删除
     */
    public function del($id, $catid) {
        $cat = get_category_data();
        $table = $cat[$catid]['tablename'];
        if (empty($table) || empty($id)) {
            return false;
        }
		$data = $this->find($id);
        if (empty($data)) {
            return false;
        }
        $this->delete('id=' . $id);
        $this->query('delete from ' . $this->prefix . $table . ' where id=' . $id);
		if (empty($data['sysadd']) && $data['username'] && is_numeric($data['userid'])) {
            $this->credits($data['userid'], 0);
        }
		$file = substr($data['url'], strlen(Controller::get_base_url())); //去掉主域名
		$file = substr($file, 0, 9) == 'index.php' ? null : $file; //是否为动态链接
		//删除关联表单数据
		$form = $this->execute('SELECT * FROM `' . $this->prefix . 'model` WHERE `joinid`=' . (int)$cat[$catid]['modelid']);
		if ($form) {
			foreach ($form as $t) {
				$this->query('DELETE FROM `' . $this->prefix . $t['tablename'] . '` WHERE `cid`=' . $id);
			}
		}
		if ($file && file_exists($file)) {
            @unlink($file);
        }
		//删除扩展表
		$this->query('delete from ' . $this->prefix . 'content_' . App::get_site_id() . '_extend where id=' . $id);
		//删除审核表
		$this->query('delete from ' . $this->prefix . 'content_' . App::get_site_id() . '_verify where id=' . $id);
		//删除推荐位信息
		$this->query('delete from ' . $this->prefix . 'position_data where contentid=' . $id);
    }
    
	/**
     * 更新URL地址
     */
    public function url($id, $url) {
        $this->update(array('url' => $url), 'id=' . $id);
    }
	
	/**
     * 审核文档
     */
    public function verify($id, $status) {
	    if (empty($id)) {
            return false;
        }
		$verify	= $this->from('content_' . App::get_site_id() . '_verify')->where('id=' . $id)->select(false);	//获取数据
		if (empty($verify)) {
            return false;
        }
		$data = string2array($verify['content']);
		$data['status'] = $status;
		if ($status == 1) {	//审核通过
			//更新地址
			$data['id']	 = $id;
			$this->set($id, $verify['tablename'], $data);
			//删除审核表中数据
			$this->query('delete from `' . $this->prefix . 'content_' . App::get_site_id() . '_verify` where id=' . $id);
		}
		$this->set_verify_data($id, $verify['tablename'], $data);
    }
    
    /**
     * 相关文档
     */
    public function relation($ids, $num) {
        return $this->from('content_' . App::get_site_id())->where('id in (' . $ids . ')')->order('listorder desc, updatetime desc')->limit($num)->select();
    }
	
	/**
     * 积分处理
     */
	public function credits($userid, $action) {
	    if (empty($userid)) {
            return false;
        }
	    $member = $this->from('member')->where('id=' . $userid)->select(false);
		if (empty($member)) {
            return false;
        }
		$cache = new cache_file();
		$config = $cache->get('member');
		if (isset($config['postcredits']) && $config['postcredits'] && $action == 1) {
		    //增加积分
			$credit = $member['credits'] + (int)$config['postcredits'];
		} elseif (isset($config['delcredits']) && $config['delcredits'] && $action == 0) {
		    //删除积分
			$credit = $member['credits'] - (int)$config['delcredits'];
		}
		if (isset($credit) && $credit != '') $this->query('update ' . $this->prefix . 'member set credits=' . (int)$credit . ' where id=' . $userid);
	}
    
	/**
     * 下载远程图片
     */
	private function download_images($imgs, $uid=0, $size=0) {
		$imgs = array_unique($imgs);	//去除重复图片
		$regex = $replace = array();
		$path = $uid ? 'uploadfiles/member/' . $uid . '/image/' . date('Ym') . '/' : 'uploadfiles/image/' . date('Ym') . '/';
		$this->mkdirs($path);
		//水印
		$config = App::get_config();
		if ($config['SITE_WATERMARK']) $image = Controller::instance('image_lib');
		foreach ($imgs as $img) {
			if ($uid && $size && count_member_size($uid) > $size * 1024 * 1024) continue;
			if (strpos($img, SITE_URL) !== false || substr($img, 0, 7) != 'http://') continue;
			//下载图片
			$fileext = strtolower(trim(substr(strrchr($img, '.'), 1, 10))); //扩展名
			$name	 = $path . md5($img . time()) . '.' . $fileext;
			$content = ia_geturl($img);
			if (empty($content)) continue;
			if (file_put_contents($name, $content)) {
				if ($config['SITE_WATERMARK']) {
					$image = Controller::instance('image_lib');
					if ($config['SITE_WATERMARK'] == 1) {
						$image->set_watermark_alpha($config['SITE_WATERMARK_ALPHA']);
						$image->make_image_watermark($name, $config['SITE_WATERMARK_POS'], $config['SITE_WATERMARK_IMAGE']);
					} else {
						$image->set_text_content($config['SITE_WATERMARK_TEXT']);
						$image->make_text_watermark($name, $config['SITE_WATERMARK_POS'], $config['SITE_WATERMARK_SIZE']);
					}
				}
				$regex[]   = $img;
				$replace[] = $name;
			}
		}
		return count($regex) > 0 ? array('regex' => $regex, 'replace' => $replace) : null;
	}
	
	/**
     * 递归创建目录
     */
    private function mkdirs($dir) {
        if (!is_dir($dir)){
            $this->mkdirs(dirname($dir));
            mkdir($dir);
        }
    }
	
	/**
     * 处理内容扩展数据
     */
    public function set_extend_data($id, $data) {
		$table	= 'content_' . App::get_site_id() . '_extend';
		$row 	= $this->from($table)->where('id=' . (int)$id)->select(false);
        if (empty($data['relation']) && empty($data['verify']) && empty($data['position']) && empty($row)) return false;
		if (empty($row)) {
			//添加数据
			$this->query("insert into " . $this->prefix . $table . " values ($id," . $data['catid'] . ",'" . $data['relation'] . "','" . $data['verify'] . "','" . $data['position'] . "')");
		} else {
			//更新数据
			$this->query("update " . $this->prefix . $table . " set `catid`=" . $data['catid'] . ",`relation`='" . $data['relation'] . "',`verify`='" . $data['verify'] . "',`position`='" . $data['position'] . "' where `id`=$id");
		}
		return true;
    }
	
	/**
     * 获取内容扩展数据
     */
	public function get_extend_data($id, $data=null) {
		$extend = $this->from('content_' . App::get_site_id() . '_extend')->where('id=' . $id)->select(false);
		return empty($extend) ? $data : (empty($data) ? $extend : array_merge($data, $extend));
	}
	
	/**
     * 获取内容审核数据
     */
	public function get_verify_data($id, $data) {
		if ($data['status'] == 1) return $data;
		$verify = $this->from('content_' . App::get_site_id() . '_verify')->where('id=' . $id)->select(false);
		if (empty($verify)) return $data;
		$_data	= string2array($verify['content']);
		return array_merge($data, $_data);
	}
	
	/**
     * 处理内容审核数据
     */
    public function set_verify_data($id, $table, $data) {
		if ($data['status'] != 2 && $data['status'] != 3 )	return false;
		if ($id) {	//修改内容
			$this->update(array('status'=>$data['status']), 'id=' . $id);	//更新内容表状态
			$this->set_table_name('content_' . App::get_site_id() . '_verify');		//设置为审核表对象
			$this->get_table_fields(true);
			$row	= $this->find($id);
			$verify = array(
				'id'			=> $id,
				'catid'			=> $data['catid'],
				'title'			=> $data['title'],
				'userid'		=> $data['userid'],
				'status'		=> $data['status'],
				'modelid'		=> $data['modelid'],
				'content'		=> array2string($data),
				'username'		=> $data['username'],
				'tablename'		=> $table,
				'updatetime'	=> time()
			);
			if ($row) {	//如果存在则修改
				$this->update($verify, 'id=' . $id);
			} else {	//不存在则插入新数据
				$this->insert($verify);
			}
		} else {	//增加内容
			$id	= $this->get_content_id();	//生成内容id
			$verify = array(
				'id'			=> $id,
				'catid'			=> $data['catid'],
				'title'			=> $data['title'],
				'userid'		=> $data['userid'],
				'status'		=> $data['status'],
				'modelid'		=> $data['modelid'],
				'content'		=> array2string($data),
				'username'		=> $data['username'],
				'tablename'		=> $table,
				'updatetime'	=> time()
			);
			$this->set_table_name('content_' . App::get_site_id() . '_verify');		//设置为审核表对象
			$this->get_table_fields(true);
			$this->insert($verify);
			$this->query('UPDATE ' . $this->prefix . 'member_count SET post=post+1 WHERE id=' . $data['userid']);	//增加会员统计数量
		}
		$this->set_extend_data($id, $data);	//处理内容扩展数据
		return $id;
    }
	
	/**
     * 插入新的内容id
     */
	private function get_content_id() {
        $this->get_table_name();
		$this->query('insert into `' . $this->prefix . 'content` values (NULL)');
		$id = $this->get_insert_id();
        if ($this->count(str_replace($this->prefix, '', $this->table_name), '', 'id='.$id)) {
            return $this->get_content_id();
        }
        return $id;
	}
	
	/**
     * 清理缓存内容id
     */
	public function clear_cache_id() {
		$this->query('DELETE FROM `' . $this->prefix . 'content` WHERE 1');
	}
	
}