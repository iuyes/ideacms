<?php

class CategoryModel extends Model {
	
	public function get_primary_key() {
		return $this->primary_key = 'catid';
	}
	
	public function getSiteId($site = 0) {
		$site = $site ? $site : App::get_site_id();
		$sites = App::get_site();
		return $sites[$site]['SITE_EXTEND_ID'] ? $sites[$site]['SITE_EXTEND_ID'] : $site;
	}
	
	public function getData($site = 0) {
		return $this->where('site=' . $this->getSiteId($site))->order('listorder ASC,catid ASC')->select();
	}
	
	public function set($catid, $data) {
	    unset($data['catid']);
		$data['site'] = $this->getSiteId();
	    if ($catid) {
	        unset($data['typeid'], $data['modelid']);
			if ($data['synpost']) {
				//同步子栏目
				$childs = $this->child($catid);
				$childs = explode(',', $childs);
				if (count($childs) > 2) {
					foreach ($childs as $id) {
						if (empty($id) || $id == $catid) {
                            continue;
                        }
						$cdata = $this->find($id);
						$cset = string2array($cdata['setting']);
						$cset['memberpost'] = $data['setting']['memberpost'];
						$cset['modelpost'] = $data['setting']['modelpost'];
						$cset['adminpost'] = $data['setting']['adminpost'];
						$cset['rolepost'] = $data['setting']['rolepost'];
						$cset['grouppost'] = $data['setting']['grouppost'];
						$cset['guestpost'] = $data['setting']['guestpost'];
						$cset['verifypost']	= $data['setting']['verifypost'];
						$cset['verifyrole']	= $data['setting']['verifyrole'];
						$this->update(array('setting' => array2string($cset)), 'catid=' . $id);
					}
				}
			}
			unset($data['synpost']);
			$data['setting'] = array2string($data['setting']);
			$this->update($data, 'catid=' . $catid);
	        $this->repair();
	        return $catid;
	    } else {
			//继承父栏目权限配置
			if (!empty($data['parentid']) && empty($data['child'])) {
				$pdata = $this->find($data['parentid']);
				$pset = string2array($pdata['setting']);
				$data['setting']['memberpost'] = $data['setting']['memberpost'] ? $data['setting']['memberpost'] : ($pset['memberpost'] ? $pset['memberpost'] : null);
				$data['setting']['modelpost'] = $data['setting']['modelpost']  ? $data['setting']['modelpost']  : ($pset['modelpost']  ? $pset['modelpost']  : null);
				$data['setting']['adminpost'] = $data['setting']['adminpost']  ? $data['setting']['adminpost']  : ($pset['adminpost']  ? $pset['adminpost']  : null);
				$data['setting']['rolepost'] = $data['setting']['rolepost']   ? $data['setting']['rolepost']   : ($pset['rolepost']   ? $pset['rolepost']   : null);
				$data['setting']['grouppost'] = $data['setting']['grouppost']  ? $data['setting']['grouppost']  : ($pset['grouppost']  ? $pset['grouppost']  : null);
				$data['setting']['guestpost'] = $data['setting']['guestpost']  ? $data['setting']['guestpost']  : ($pset['guestpost']  ? $pset['guestpost']  : null);
				$data['setting']['verifypost'] = $data['setting']['verifypost'] ? $data['setting']['verifypost'] : ($pset['verifypost'] ? $pset['verifypost']  : null);
				$data['setting']['verifyrole'] = $data['setting']['verifyrole'] ? $data['setting']['verifyrole'] : ($pset['verifyrole'] ? $pset['verifyrole']   : null);
				unset($pdata, $pset);
			}
			$data['modelid'] = (int)$data['modelid'];
			$data['pagesize'] = (int)$data['pagesize'];
            if ($data['typeid'] == 4) {
                $data['modelid'] = (int)$data['modelid2'];
                if (empty($data['showtpl'])) {
                    $data['showtpl'] = 'post_form.html';
                }
            }
            unset($data['modelid2']);
			unset($data['synpost']);
			$data['setting'] = array2string($data['setting']);
			$data['child'] = 0;
			$data['arrchildid'] = '';
			$data['arrparentid'] = '';
	        $this->insert($data);
	    }
	    $catid = $this->get_insert_id();
	    $this->repair();
	    return empty($catid)? lang('failure') : $catid;
	}
	
	/**
	 * 删除栏目及数据
	 * @param int $catid
	 * @return boolean 
	 */
	public function del($catid) {
	    if (empty($catid)) {
            return false;
        }
	    $this->repair($catid); //修复该栏目数据
	    $catids	= $this->child($catid, true);
	    if (empty($catids)) {
            return false;
        }
		$catids	= substr($catids, -1) == ',' ? substr($catids, 0, -1) : $catids;
		//删除栏目数据
	    $this->delete('catid IN (' . $catids . ')');
		//查询内容id集合
		$ids = '';
		$data = $this->execute('SELECT id FROM `' . $this->prefix . 'content_' . App::get_site_id() . '` WHERE `catid` IN (' . $catids . ')');
		if ($data) {
			//删除内容扩展
			$this->query('DELETE FROM `' . $this->prefix . 'content_' . App::get_site_id() . '_extend` WHERE `catid` IN (' . $catids . ')');
			foreach ($data as $t) {
				$ids.= $t['id'] . ',';
			}
			$ids = substr($ids, -1) == ',' ? substr($ids, 0, -1) : $ids;
			$cats = get_category_data();
			$catids	= explode(',', $catids);
			foreach ($catids as $catid) {
				//删除内容表数据
				if ($cats[$catid]['tablename']) {
					$this->query('DELETE FROM `' . $this->prefix . 'content_' . App::get_site_id() . '` WHERE `catid`=' . $catid);
					$this->query('DELETE FROM `' . $this->prefix . $cats[$catid]['tablename'] . '` WHERE `catid`=' . $catid);
				}
				//删除关联表单数据
				$form = $this->execute('SELECT * FROM `' . $this->prefix . 'model` WHERE `joinid`=' . (int)$cats[$catid]['modelid']);
				if ($form) {
					foreach ($form as $t) {
						$this->query('DELETE FROM `' . $this->prefix . $t['tablename'] . '` WHERE `cid` IN (' . $ids . ')');
					}
				}
			}
		}
	    return true;
	}
	
	/**
	 * 递归查找所有子栏目ID
	 * @param int $catid
	 * @param boolean $parent
	 * @param int $typeid
	 * @return string 
	 */
	public function child($catid, $parent = false, $typeid = 0) {
	    $str = '';
	    $data = $this->find($catid);
	    if (empty($data)) {
            return false;
        }
	    if ($data['child'] && $data['arrchildid']) { //存在子栏目
	        if ($parent && ($typeid ? $typeid == $data['typeid'] : true)) {
                $str.= $catid . ',';
            }
	        $ids = array();
	        $arrchildid = $data['arrchildid'];
	        if ($arrchildid) {
                $ids = explode(',', $arrchildid);
            }
	        foreach ($ids as $id) {
	            $str.= $this->child($id, $parent, $typeid);
	        }
	    } else {
	        if ($typeid ? $typeid == $data['typeid'] : true) {
                $str.= $catid . ',';
            }
	    }
	    return $str;
	}
	
	/**
	 * 递归修复所有栏目的子类id和同级分类id
	 * @param int $parentid
	 */
	public function repair($parentid = 0) {
	    $data = $this->where('site=' . $this->getSiteId() . ' AND parentid=' . $parentid)->order('listorder ASC')->select();
	    foreach ($data as $t) {
	        //检查该栏目下是否有子栏目
	        $catid = $t['catid'];
	        $parentid = $t['parentid'];
	        //当前栏目的所有父栏目ID(arrparentid)
	        $arrparentid = array();
	        foreach ($data as $s) {
	            $arrparentid[] = $s['catid'];
	        }
	        //组合父栏目ID
	        $arrparentid = implode(',', $arrparentid);
	        //查询子栏目
	        $s_data = $this->where('parentid=?', $t['catid'])->order('listorder ASC')->select();
	        if ($s_data) { //存在子栏目
	            //当前栏目的所有子栏目ID($arrchildid)
	            $arrchildid = array();
	            foreach ($s_data as $s) {
	                $arrchildid[] = $s['catid'];
	            }
	            //组合子栏目ID
	            $arrchildid = implode(',', $arrchildid);
	            $this->update(array('child' => 1, 'arrchildid' => $arrchildid, 'arrparentid' => $arrparentid), 'catid=' . $catid);
	            $this->repair($catid); //递归调用
	        } else {
	            //没有子栏目
	            $this->update(array('child' => 0, 'arrchildid' => '', 'arrparentid' => $arrparentid), 'catid=' . $catid);
	        }
	    } 
	}
	
	/**
	 * 验证栏目路径是否存在
	 * @param int $catid
	 * @param string $catdir
	 * @return boolean
	 */
	public function check_catdir($catid = 0, $catdir) {
	    if (empty($catdir)) {
            return TRUE;
        }
	    $this->where('catdir=?', $catdir);
	    if ($catid) {
            $this->where('catid<>?', $catid);
        }
	    $data = $this->select(false);
	    return empty($data) ? FALSE : TRUE;
	}
	
	/**
	 * 设置栏目URL
	 */
    public function url($id, $url) {
        $this->update(array('url' => $url), 'catid=' . $id);
    }
	
	/**
     * 递归查询所有父级栏目信息
     * @param  int $catid  当前栏目ID
     * @return array
     */
    public function getParentData($catid) {
	    $cat = $this->find($catid);
        if ($cat['parentid']) {
            $cat = $this->getParentData($cat['parentid']);
        }
        return $cat;
    }
    
	/**
	 * 递归设置栏目所有子栏目组
	 */
	public function setChildData($parentid, $data) {
		foreach ($data as $catid => $t) {
			if ($t['child']) {
                $data[$catid]['arrchilds'] = $this->getArrchildid($t['catid'], $data);
            }
		}
		return $data;
	}
	
	/**
	 * 获取子栏目ID列表
	 */
	private function getArrchildid($catid, $data) {
		$arrchildid = $catid;
		foreach ($data as $m) {
			if ($m['catid'] != $catid && $m['parentid'] == $catid) {
				$arrchildid.= ',' . $this->getArrchildid($m['catid'], $data);
			}
		}
		return $arrchildid;
	}
}