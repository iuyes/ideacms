<?php

if (!defined('IN_IDEACMS')) exit();

/**
 * 静态页面处理信息函数
 */
function html_to_data($data, $file) {
	$data = is_array($data) ? $data : array('idea_html_to_data' => $data);
	$data = str_replace('=', '', base64_encode(fn_authcode($data, 'ENCODE')));	//加密数据
	return '<script type="text/javascript" src="' . url('api/data', array('data' => $data, 'file' => $file)) . '"></script>';
}

/**
 * authcode函数(用于数组)
 */
function fn_authcode($data, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$string	= $operation == 'DECODE' ? $data : array2string($data);
	$key	= md5($key ? $key : SITE_MEMBER_COOKIE);
	$keya	= md5(substr($key, 0, 16));
	$keyb	= md5(substr($key, 16, 16));
	$keyc	= $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey	= $keya . md5($keya . $keyc);
	$key_length = strlen($cryptkey);

	$string	= $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
	$string_length	= strlen($string);

	$result = '';
	$box	= range(0, 255);

	$rndkey = array();
	for($i	= 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
			return string2array(substr($result, 26));
		} else {
			return '';
		}
	} else {
		return $keyc . str_replace('=', '', base64_encode($result));
	}
}

/**
 * 内容审核状态
 */
function get_content_status($status) {
	switch ($status) {
		case 3:	//未审核
			return '<font color="#F00">[' . lang('a-con-32') . ']</font>';
		break;
		case 2:	//拒绝
			return '<font color="#0000FF">[' . lang('a-con-33') . ']</font>';
		break;
		case 0:	//回收站
			return '<font color="#6666">[' . lang('a-con-34') . ']</font>';
		break;
	}
}

/**
 * 内容审核状态
 */
function get_form_status($status) {
	switch ($status) {
		case 0:	//未审核
			return '<font color="#F00">[' . lang('a-con-32') . ']</font>';
		break;
		case 2:	//拒绝
			return '<font color="#0000FF">[' . lang('a-con-33') . ']</font>';
		break;
		case 4:	//回收站
			return '<font color="#6666">[' . lang('a-con-34') . ']</font>';
		break;
	}
}

/**
 * 读取栏目缓存数据
 */
function get_category_data($site = 0) {
	$cfg  	= App::get_config();
	$cache 	= new cache_file();
	$site	= empty($site) ? App::get_site_id() : $site;
    return $cfg['SITE_EXTEND_ID'] ? $cache->get('category_' . $cfg['SITE_EXTEND_ID']) : $cache->get('category_' . $site);
}

/**
 * 读取模型缓存数据(非会员)
 */
function get_model_data($name = 'content', $site = 0) {
	$cache = new cache_file();
	$site = empty($site) ? App::get_site_id() : $site;
	$data = $cache->get('model_' . $name . '_' . $site);
	$sites = App::get_site();
	if ($sites[$site]['SITE_EXTEND_ID']) { //属于继承
		$now = $cache->get('model_' . $name . '_' . $sites[$site]['SITE_EXTEND_ID']);
		if (empty($now)) {
            return $data;
        }
		foreach ($now as $mid => $t) {
			if (isset($data[$mid])) {
				unset($t['tablename'], $t['site']);
				$data[$mid] += $t;
			} else {
				$data[$mid]	 = $t;
			}
		}
	}
	return $data;
}

/**
 * 读取推荐信息函数
 */
function position($site, $posid, $catid = 0, $num = 0) {
    $cache = new cache_file();
	$site  = empty($site) ? App::get_site_id() : $site;
    $data  = $cache->get('position_' . $site);
    if (!isset($data[$posid]) || empty($data[$posid])) return array();
    $i     = 0;
    $row   = array();
    $list  = $data[$posid];
	$num   = $num ? $num : $list['maxnum'];
    if ($catid) {
        $category = get_category_data();
        $catids   = $category[$catid]['arrchilds'] ? explode(',', $category[$catid]['arrchilds']) : array();
    }
    foreach ($list as $key => $t) {
        if (!is_numeric($key)) break;
        if ($catid && $t['catid']) {
            if (in_array($t['catid'], $catids)) {
                $row[] = $t;
                $i++;
            }
        } else {
            $row[] = $t;
            $i++;
        }
        if ($i >= $num) break;
    }
    return $row;
}

/**
 * 运行应用/验证应用
 */
function plugin($dir) {
    $cache  = new cache_file();
    $data   = $cache->get('plugin');
    if (empty($data)) return false;
    $plugin = $data[$dir];
    if (empty($plugin)) return false;
    if ($plugin['typeid'] == 1) {
        //内置控制器应用，判断应用是否可用
		return $plugin['disable'] ? false : true;
    } else {
        //输出代码应用
        $runphp = APP_ROOT . 'plugins/' . $plugin['dir'] . '/run.php';
        $runhtm = APP_ROOT . 'plugins/' . $plugin['dir'] . '/run.html';
        if (!file_exists($runphp)) return false;
        extract($plugin['setting']);
        require $runphp;
        if (file_exists($runhtm)) require $runhtm;
    }
}

/**
 * 完整文件的路径
 */
function getfile($url) {
	if (empty($url)) return null;
    if (substr($url, 0, 7) == 'http://') return $url;
    if (strpos($url, SITE_PATH) !== false && SITE_PATH != '/') return $url;
    if (substr($url, 0, 1) == '/') $url = substr($url, 1);
    return SITE_PATH . $url;
}
function getImage($url) {
	if (empty($url)) return null;
    if (substr($url, 0, 7) == 'http://') return $url;
    if (strpos($url, SITE_URL) !== false && SITE_URL != '/') return $url;
    if (substr($url, 0, 1) == '/') $url = substr($url, 1);
    return SITE_URL.'/'. $url;
}

/**
 * 下载文件函数
 */
function downfile($url) {
	return url('api/down', array('file' => str_replace('=', '', base64_encode(fn_authcode(array('ideacms' => $url), 'ENCODE')))));
}

/**
 * 完整的图片路径
 */
function image($url) {
    if (empty($url) || strlen($url) == 1) return SITE_PATH . EXTENSION_PATH . '/null.jpg?' . $url;
    if (substr($url, 0, 7) == 'http://') return $url;
    if (strpos($url, SITE_PATH) !== false && SITE_PATH != '/') return $url;
    if (substr($url, 0, 1) == '/') $url = substr($url, 1);
    return SITE_PATH . $url;
}

/**
 * 图片缩略图地址
 */
function thumb($img, $width = null, $height = null) {
    if (empty($img) || strlen($img) == 1 ||  !@is_file($img)) {
        return image($img);
    }
    if (strpos($img, SITE_PATH) === 1) {
        $img = substr($img, strlen(SITE_PATH));
    }
    $config = App::get_config();
	if ($width && $height) {	//如果有宽高参数
		$thumb = $img . '.thumb.' . $width . 'x' . $height . '.' . substr(strrchr(trim($img), '.'), 1);
		if (empty($config['SITE_THUMB_TYPE']))	{	//静态模式，生成新图
			if (!file_exists($thumb)) {	//若文件不存在则生成新图
				$image = new image_lib();
				$image->set_image_size($width, $height)->make_limit_image($img, $thumb);
			}
			return image($thumb);
		} else {
			return _thumb($img, $width, $height);	//动态调用
		}
	} elseif ($config['SITE_THUMB_WIDTH'] && $config['SITE_THUMB_HEIGHT']) {	//存在系统缩略图
		$thumb = $img . '.thumb.' . $config['SITE_THUMB_WIDTH'] . 'x' . $config['SITE_THUMB_HEIGHT'] . '.' . substr(strrchr(trim($img), '.'), 1);
		if (file_exists($thumb)) {
			return image($thumb);
		} else {
			if (empty($config['SITE_THUMB_TYPE']))	{	//静态模式，生成新图
				$image = new image_lib();
				$image->set_image_size($config['SITE_THUMB_WIDTH'], $config['SITE_THUMB_HEIGHT'])->make_limit_image($img, $thumb);
				return image($thumb);
			} else {
				return _thumb($img, $config['SITE_THUMB_WIDTH'], $config['SITE_THUMB_HEIGHT']);	//动态调用
			}
		}
	}
	return image($img);
}

/**
 * 强制动态模式调用图片
 */
function _thumb($img, $width = null, $height = null) {
	return url('api/thumb', array('img' => str_replace('=', '', base64_encode(fn_authcode(array('ideacms' => $img), 'ENCODE'))), 'width' => $width, 'height' => $height));
}
 
/**
 * 提取关键字
 */
function getKw($data) {
    $data = fn_geturl('http://keyword.discuz.com/related_kw.html?ics=utf-8&ocs=utf-8&title=' . rawurlencode($data) . '&content=' . rawurlencode($data));
	if ($data) {
        $kws = array();
	    $parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $data, $values, $index);
		xml_parser_free($parser);
		foreach ($values as $valuearray) {
		    $kw = trim($valuearray['value']);
		    if(strlen($kw) > 5 && ($valuearray['tag'] == 'kw' || $valuearray['tag'] == 'ekw')) {
                $kws[] = $kw;
            }
		}
		return implode(',', $kws);
	}
}

/**
 * 字符截取 支持UTF8/GBK
 */
function strcut($string, $length, $dot = '...') {
    $charset = 'utf-8';
	if (strlen($string) <= $length) {
        return $string;
    }
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
	$strcut = '';
	if (strtolower($charset) == 'utf-8') {
		$n = $tn = $noc = 0;
		while ($n < strlen($string)) {
			$t = ord($string[$n]);
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif (194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif (224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif (240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif (248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif ($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}
			if($noc >= $length) {
                break;
            }
		}
		if ($noc > $length) {
            $n -= $tn;
        }
		$strcut = substr($string, 0, $n);
	} else {
		for ($i = 0; $i < $length; $i++) {
			$strcut.= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
		}
	}
	$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
	return $strcut . $dot;
}

/**
 * 清除HTML标记
 */
function clearhtml($str) {
    $str = str_replace(array('&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array(' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $str);
    $str = preg_replace("/\<[a-z]+(.*)\>/iU", "", $str);
    $str = preg_replace("/\<\/[a-z]+\>/iU", "", $str);
    $str = str_replace(array(' ','	', chr(13), chr(10), '&nbsp;'), array('', '', '', '', ''), $str);
    return $str;
}

/**
 * 栏目面包屑导航
 * @param $catid  栏目id
 * @param $symbol 栏目间隔符
 * @return NULL|string
 */
function catpos($catid, $symbol = ' > ') {
    $cats = get_category_data();
    $catids = catposids($catid, '', $cats);
    if (empty($catids)) {
        return null;
    }
    if (substr($catids, -1) == ',') {
        $catids = substr($catids, 0, -1);
    }
    $ids = explode(',', $catids);
    krsort($ids);
    $str = '';
    foreach ($ids as $cid) {
        $cat = $cats[$cid];
		$str.= "<a href=\"" . $cat['url'] . "\" title=\"". $cat['catname'] . "\">" . $cat['catname'] . "</a>";
        if ($catid != $cid) {
            $str.= $symbol;
        }
    }
    return $str;
}

/**
 * 栏目上级ID集合
 * @param  $catid
 * @param  $catids
 * @return string 返回栏目所有上级ID
 */
function catposids($catid, $catids = '', $category) {
    if (empty($catid)) {
        return false;
    }
    $row = $category[$catid];
    $catids = $catid . ','; 
    if ($row['parentid']) {
        $catids.= catposids($row['parentid'], $catids, $category);
    }
    return $catids;
}

/**
 * 栏目下级ID集合
 * @param  $catid
 * @param  $catids
 * @return string 返回栏目所有下级ID
 */
function _catposids($catid, $catids = '', $category) {
    if (empty($catid)) {
        return false;
    }
    $row = $category[$catid];
    $catids = $catid . ','; 
    if ($row['child'] && $row['arrchildid']) {
		$id = explode(',', $row['arrchildid']);
		foreach ($id as $t) {
			$catids.= _catposids($t, $catids, $category);
		}
	}
    return $catids;
}

/**
 * 当前栏目同级菜单
 * @param  $catid
 */
function getCatNav($catid) {
    $cats = get_category_data();
    $cat = $cats[$catid];
    if (!$cat['child'] && !$cat['parentid']) {
        return array();
    }
    //当前栏目有子菜单时，同级栏目则是所有子菜单；否则为其父级同级菜单
    $catids = $cat['child'] ? $cat['arrchildid'] : $cat['arrparentid'];
    if (empty($catids)) {
        return array();
    }
    $ids = explode(',', $catids);
    $data = array();
    foreach ($ids as $cid) {
        $data[] = $cats[$cid];
    }
    return $data;
}

/**
 * 递归查询所有父级栏目信息
 * @param  int $catid  当前栏目ID
 * @return array
 */
function getParentData($catid) {
    $cats= get_category_data();
    $cat = $cats[$catid];
    if ($cat['parentid']) {
        $cat = getParentData($cat['parentid']);
    }
    return $cat;
}

/**
 * 递归查询所有父级栏目名称
 * @param  int    $catid   当前栏目ID
 * @param  string $prefix  分隔符
 * @param  int    $sort    排序方式 1正序，0反序
 * @return string          返回格式：顶级栏目[分隔符]一级栏目[分隔符]二级栏目...[分隔符]当前栏目
 */
function getParentName($catid, $prefix = '-', $sort = 1) {
    $cats = get_category_data();
	$cids = catposids($catid, null, $cats);
	$ids  = explode(',', $cids);
	$prefix	= empty($prefix) ? '-' : $prefix;
    if ($sort) {
        krsort($ids);
    }
	$str = '';
    foreach ($ids as $cid) {
        if ($cid) {
            $str.= $cats[$cid]['catname'] . $prefix;
        }
    }
	return substr($str, -1) == $prefix ? substr($str, 0, -1) : $str;
}

/**
* 查询所有父级栏目目录名称
* @param  int    $catid   当前栏目ID
* @return string          返回格式：顶级栏目dir[分隔符]一级栏目dir[分隔符]二级栏目dir...[分隔符]当前栏目dir
*/
function getParentDir($catid, $prefix = '/') {
    $cats = get_category_data();
	$cids = catposids($catid, null, $cats);
	$ids = explode(',', $cids);
	$prefix	= empty($prefix) ? '/' : $prefix;
    krsort($ids);
	$str = '';
    foreach ($ids as $cid) {
        if ($cid) {
            $str.= $cats[$cid]['catdir'] . $prefix;
        }
    }
	return substr($str, -1) == $prefix ? substr($str, 0, -1) : $str;
}

/**
 * 表单内容URL
 */
function form_show_url($modelid, $data) {
    if (empty($data) || empty($modelid)) {
        return null;
    }
    $form = get_model_data('form');
	$config	= $form[$modelid]['setting']['form']['url'];
	if (isset($config['show']) && $config['show']) {
		$data['y'] = date('Y', $data['inputtime']);
		$data['m'] = date('m', $data['inputtime']);
		$data['d'] = date('d', $data['inputtime']);
		$data['mid'] = $modelid;
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $rep = new php5replace($data);
            $url = preg_replace_callback('#{([a-z_0-9]+)}#Ui', array($rep, 'php55_replace_data'), $config['show']);
        } else {
		    $url = preg_replace('#{([a-z_0-9]+)}#Uei', "\$data[\\1]", $config['show']);
        }
		if ($config['tohtml'] && $config['htmldir']) {
            $url = $config['htmldir'] . '/' . $url;
        }
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $rep = new php5replace($data);
            $url = preg_replace_callback('#{([a-z_0-9]+)\((.*)\)}#U', array($rep, 'php55_replace_function'), $url);
        } else {
		    $url = preg_replace('#{([a-z_0-9]+)\((.*)\)}#Uie', "\\1(safe_replace('\\2'))", $url);
        }
		return SITE_PATH . $url;
	} else {
		return url('form/show', array('id' => $data['id'], 'modelid' => $modelid));
	}
}

/**
 * 内容页URL地址
 */
function getUrl($data, $page = 0) {

    // 默认地址
    $url = url('content/show', array('id' => $data['id']));
    $cats = get_category_data();
	$cat = $cats[$data['catid']];
    $model = get_model_data();
    $field = $model[$cat['modelid']]['fields']['data'];
    if ($field) {
        foreach ($field as $t) {
            if ($t['formtype'] == 'wurl') {
                if (isset($data[$t['field']])) {
                    // 跳转外链
                    return $url;
                }
            }
        }
    }
	unset($cats);
	if (isset($cat['setting']['url']['use']) && $cat['setting']['url']['use'] == 1 && $cat['setting']['url']['show']) {
		$data['y'] = date('Y', $data['inputtime']);
		$data['m'] = date('m', $data['inputtime']);
		$data['d'] = date('d', $data['inputtime']);
		$data['dir'] = $cat['catdir'];
		$data['pdir'] = getParentDir($data['catid'], $cat['setting']['url']['catjoin']);
		$data['page'] = $page;
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $rep = new php5replace($data);
            $url = !is_numeric($page) || $page > 1 ? preg_replace_callback('#{([a-z_0-9]+)}#Ui', array($rep, 'php55_replace_data'), $cat['setting']['url']['show_page']) : preg_replace_callback('#{([a-z_0-9]+)}#Ui', array($rep, 'php55_replace_data'), $cat['setting']['url']['show']);
        } else {
		    $url = !is_numeric($page) || $page > 1 ? preg_replace('#{([a-z_0-9]+)}#Uei', "\$data[\\1]", $cat['setting']['url']['show_page']) : preg_replace('#{([a-z_0-9]+)}#Uei', "\$data[\\1]", $cat['setting']['url']['show']);
        }
		if ($cat['setting']['url']['tohtml'] && $cat['setting']['url']['htmldir']) {
            $url = $cat['setting']['url']['htmldir'] . '/' . $url;
        }
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $rep = new php5replace($data);
            $url = preg_replace_callback('#{([a-z_0-9]+)\((.*)\)}#U', array($rep, 'php55_replace_function'), $url);
        } else {
            $url = preg_replace('#{([a-z_0-9]+)\((.*)\)}#Uie', "\\1(safe_replace('\\2'))", $url);
        }
		return SITE_PATH . $url;
	}
	if ($page) {
        $url = url('content/show', array('id' => $data['id'], 'page' => $page));
    }
	return $url;
}

/**
 * 栏目URL
 */
function getCaturl($data, $page = 0) {
	$cats = get_category_data();
	if (is_numeric($data)) {
		$data = $cats[$data];
		unset($cats);
	}
	if ($data['typeid'] == 3) {
		// 外链地址处理
		if (strpos($data['urlpath'], '{') === 0) {
			$ii = intval(str_replace('{', '', $data['urlpath']));
			if ($cats[$ii]['url']) {
				return $cats[$ii]['url'];
			}
		}
        return $data['urlpath'];
    }
	$url = url('content/list', array('catid' => $data['catid']));
	if (!is_array($data['setting'])) $data['setting'] = string2array($data['setting']);
	if (isset($data['setting']['url']['use']) && $data['setting']['url']['use'] == 1 && $data['setting']['url']['list']) {
		$data['id'] = $data['catid'];
		$data['dir'] = $data['catdir'];
		$data['pdir'] = getParentDir($data['catid'], $data['setting']['url']['catjoin']);
		$data['page'] = $page;
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $rep = new php5replace($data);
            $url = !is_numeric($page) || $page > 1 ? preg_replace_callback('#{([a-z_0-9]+)}#Ui', array($rep, 'php55_replace_data'), $data['setting']['url']['list_page']) : preg_replace_callback('#{([a-z_0-9]+)}#Ui', array($rep, 'php55_replace_data'), $data['setting']['url']['list']);
        } else {
            $url = !is_numeric($page) || $page > 1 ? preg_replace('#{([a-z_0-9]+)}#Uei', "\$data[\\1]", $data['setting']['url']['list_page']) : preg_replace('#{([a-z_0-9]+)}#Uei', "\$data[\\1]", $data['setting']['url']['list']);
        }
		if ($data['setting']['url']['tohtml'] && $data['setting']['url']['htmldir']) {
            $url = $data['setting']['url']['htmldir'] . '/' . $url;
        }
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $rep = new php5replace($data);
            $url = preg_replace_callback('#{([a-z_0-9]+)\((.*)\)}#U', array($rep, 'php55_replace_function'), $url);
        } else {
            $url = preg_replace('#{([a-z_0-9]+)\((.*)\)}#Uie', "\\1(safe_replace('\\2'))", $url);
        }
		return SITE_PATH . $url;
	}
	if ($page) {
        $url = url('content/list', array('catid' => $data['catid'], 'page' => $page));
    }
	return $url;
}

/**
 * 网站地图XML文件生成
 */
function sitemap_xml() {
    $config	= App::get_config();
    $time = (int)$config['SITE_MAP_TIME'];
    $num = (int)$config['SITE_MAP_NUM'];
    $update	= (int)$config['SITE_MAP_UPDATE'];
	if (empty($time) || empty($num) || empty($update)) {
        return lang('fun-0');
    }
	$db = Controller::model('content_' . App::get_site_id());
	$data = $db->where('`status`=1')->where('updatetime >' . strtotime('-' . $time . ' day'))->order('updatetime DESC')->limit(0, $num)->select();
	baidunews($data, $update);
	sitemap($data);
	return count($data);
}

/**
 * 百度新闻协议
 */
function baidunews($data, $update) {
    if (empty($data)) {
        return false;
    }
	$sites = App::get_site();
	$cats = get_category_data();
	$filename = count($sites) > 1 ? 'baidunews_' . App::get_site_id() . '.xml' : 'baidunews.xml';
	$baidunews = '';
	$baidunews = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
	$baidunews.= "<document>\n";
	$baidunews.= "<webSite>http://" . $_SERVER['HTTP_HOST'] . SITE_PATH . "</webSite>\n";
	$baidunews.= "<updatePeri>" . $update . "</updatePeri>\n";
    foreach ($data as $item) {
		$baidunews.= "<item>\n";
		$baidunews.= "<title>" . htmlspecialchars(strip_tags(clearhtml($item['title']))) . "</title>\n";
		$baidunews.= "<link>http://" . $_SERVER['HTTP_HOST'] . htmlspecialchars(strip_tags($item['url'])) . "</link>\n";
		$baidunews.= "<description>" . htmlspecialchars(strip_tags(clearhtml($item['description']))) . "</description>\n";
		$baidunews.= "<text>" . htmlspecialchars(strip_tags(clearhtml($item['description']))) . "</text>\n";
		if ($item['thumb']) {
            $baidunews.= "<image>http://" . $_SERVER['HTTP_HOST'] . image($item['thumb']) . "</image>\n";
        }
		$baidunews.= "<keywords>" . htmlspecialchars(strip_tags($item['keywords'])) . "</keywords>\n";
		$baidunews.= "<category>" . htmlspecialchars(strip_tags($cats[$item['catid']]['catname'])) . "</category>\n";
		$baidunews.= "<pubDate>" .  date('Y-m-d', $item['updatetime']) . "</pubDate>\n";
		$baidunews.= "</item>\n";
    } 
    $baidunews .= "</document>\n";
	unset($data);
    file_put_contents(APP_ROOT . $filename, $baidunews, LOCK_EX);
}

/**
 * 网站地图sitemap
 */
function sitemap($data) {
    if (empty($data)) {
        return false;
    }
	$sites = App::get_site();
	$file = count($sites) > 1 ? 'sitemap_' . App::get_site_id() . '.xml' : 'sitemap.xml';
    $header = "<\x3Fxml version=\"1.0\" encoding=\"UTF-8\"\x3F>\n\t<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
    $footer = "\t</urlset>\n";
    $map = $header . "\n";
    foreach ($data as $item){
        $map .= "\t\t<url>\n\t\t\t<loc>http://" . $_SERVER['HTTP_HOST'] . htmlspecialchars(strip_tags($item['url'])) . "</loc>\n";
        $map .= "\t\t\t<lastmod>" . date('Y-m-d', $item['updatetime']) . "</lastmod>\n";
        $map .= "\t\t\t<changefreq>daily</changefreq>\n";
        $map .= "\t\t\t<priority>1.0</priority>\n";
        $map .= "\t\t</url>\n\n";
    }
    $map .= $footer . "\n";
	unset($data);
    file_put_contents(APP_ROOT . $file, $map, LOCK_EX);
}

/**
 * 栏目页SEO信息
 * @param int    $cat
 * @param int    $page
 * @param string $kw
 * @return array
 */
function listSeo($cat, $page = 1, $kw = NULL) {
    $config		= App::get_config();
    $meta_title = $meta_keywords = $meta_description = '';
    if ($kw) {
	    $meta_title = (empty($cat) ? lang('fun-2', array('1' => $kw)) : lang('fun-2', array('1' => $kw))) . '-' . $config['SITE_NAME'];
		$meta_title = $page > 1 ? lang('fun-1', array('1' => $page)) . '-' . $meta_title : $meta_title;
	} else {
	    $meta_title = empty($cat['meta_title']) ? getParentName($cat['catid'], '-', 0) : $cat['meta_title'];
		$meta_title = $meta_title ? $meta_title . '-' . $config['SITE_NAME'] : $config['SITE_NAME'];
		$meta_title	= isset($cat['stitle']) && $cat['stitle'] ? $cat['stitle'] . '-' . $meta_title : ($page > 1 ? lang('fun-1', array('1' => $page)) . '-' . $meta_title : $meta_title);
		$meta_keywords    = empty($cat['meta_keywords']) ? getParentName($cat['catid'], ',', 0) . ',' . $config['SITE_KEYWORDS'] : $cat['meta_keywords'];
		$meta_description = empty($cat['meta_description']) ? $config['SITE_DESCRIPTION'] : $cat['meta_description'];
	}
    return array('meta_title' => $meta_title, 'meta_keywords' => $meta_keywords, 'meta_description' => $meta_description);
}

/**
 * 内容页SEO信息
 * @param int $data
 * @param int $page
 * @return array
 */
function showSeo($data, $page = 1) {
    $cats	= get_category_data();
    $cat	= $cats[$data['catid']];
    $listseo	= listSeo($cat);
    $meta_title	= $meta_keywords = $meta_description = '';
    $meta_title	= (isset($data['stitle']) && $data['stitle'] ? $data['stitle'] . '-' . $data['title'] . '-' : $data['title'] . '-' . ($page > 1 ? lang('fun-1', array('1' => $page)) . '-' : '')) . $listseo['meta_title'];
    $meta_keywords	= empty($data['keywords']) ? $listseo['meta_keywords']    : $data['keywords'] . ',' . $listseo['meta_keywords'];
    $meta_description	= empty($data['description']) ? $listseo['meta_description'] : $data['description'];
    return array('meta_title' => $meta_title, 'meta_keywords' => $meta_keywords, 'meta_description' => $meta_description);
}

/**
 * 格式SQL查询IN(ID序列)
 * @param  $str
 * @param  $glue
 * @return boolean|string
 */
function formatStr($str, $glue = ',') {
    $arr = explode($glue, $str);
    if (!is_array($arr)) return false;
    $arr = array_unique($arr);
    $ids = '';
    foreach ($arr as $id) { if ($id) $ids .= ',' . $id; }
    return substr($ids, 1);
}

/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string){
	if(!is_array($string)) return addslashes($string);
	foreach($string as $key => $val) $string[$key] = new_addslashes($val);
	return $string;
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
	if(!is_array($string)) return stripslashes($string);
	foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
	return $string;
}

/**
 * 返回经addslashe处理过的字符串或数组
 * @param $obj 需要处理的字符串或数组
 * @return mixed
 */
function new_html_special_chars($string) {
	if(!is_array($string)) return htmlspecialchars($string);
	foreach($string as $key => $val) $string[$key] = new_html_special_chars($val);
	return $string;
}

function ijson($status, $code = '', $id = 0) {
    return json_encode(array('status' => $status, 'code' => $code, 'id' => $id));
}

/**
 * 安全过滤函数
 * @param $string
 * @return string
 */
function safe_replace($string) {
	$string = str_replace('%20','',$string);
	$string = str_replace('%27','',$string);
	$string = str_replace('%2527','',$string);
	$string = str_replace('*','',$string);
	$string = str_replace('"','&quot;',$string);
	$string = str_replace("'",'',$string);
	$string = str_replace('"','',$string);
	$string = str_replace(';','',$string);
	$string = str_replace('<','&lt;',$string);
	$string = str_replace('>','&gt;',$string);
	$string = str_replace("{",'',$string);
	$string = str_replace('}','',$string);
	return $string;
}

/**
* 将字符串转换为数组
* @param	string	$data	字符串
* @return	array	返回数组格式，如果，data为空，则返回空数组
*/
function string2array($data) {
    if ($data == '') return array();
    if (is_array($data)) return $data;
    if (strpos($data, 'array') !== false && strpos($data, 'array') === 0) {
        // 存在安全性威胁
        if (IS_ADMIN) {
            @eval("\$array = $data;");
            return $array;
        } else {
            exit($data.'<hr>兼容性验证失败，请复制当前页面的URL和以上错误代码上报给管理员');
        }
    }
    return unserialize($data);
}

/**
* 将数组转换为字符串
* @param	array	$data		数组
* @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
* @return	string	返回字符串，如果，data为空，则返回空
*/
function array2string($data, $isformdata = 1) {
	if($data == '') return '';
	if($isformdata) $data = new_stripslashes($data);
	return serialize($data);
}

/**
 * 广告调用
 * @param  $id
 */
function adsense($id) {
    return '<script language="javascript" src="' . url('adsense/index/get', array('id' => $id)) . '"></script>';
}

/**
 * 内联链接
 * @param  $message
 * @param  $data
 * @return Ambiguous
 */
function relatedlink($message, $data = array()) {
    global $config;
    if (empty($data)) {
	    $cache = new cache_file();
        $data  = $cache->get('relatedlink');
	}
	if (isset($config['SITE_TAG_LINK']) && $config['SITE_TAG_LINK']) {
	    $tdata = $cache->get('tag');
		$data  = array_merge((array)$tdata, (array)$data);
	}
	if (!is_array($data)) return $message;
	foreach ($data as $t) {
        if ($t) {
            $message = @preg_replace("/(?!<[^>]*)(".preg_quote($t['name'], '/').")(?![^<]*>)/siU",tagfont('\\1', '',  $t['url']),$message,isset($config['SITE_KEYWORD_NUMS']) && $config['SITE_KEYWORD_NUMS'] ? $config['SITE_KEYWORD_NUMS'] : 1);
        }
	}
	return $message;
}

/**
 * 内链字串
 */
function tagfont($tag, $code, $url) {
	return "<a href=\"$url\" target=\"_blank\">$tag</a>$code";
}

/**
 * 文字块调用
 * @param  $id
 * @return NULL|string
 */
function block($id) {
    $cache = new cache_file();
    $data  = $cache->get('block');
    $row   = $data[$id];
    if (empty($row)) return null;
    return htmlspecialchars_decode($row['content']);
}

/**
 * 文字块名称调用
 * @param $id
 * @return null|string
 */
function block_name($id){
	$cache = new cache_file();
	$data  = $cache->get('block');
	$row   = $data[$id];
	if (empty($row)) return null;
	return htmlspecialchars_decode($row['name']);
}
function iavatar($uid, $size = 90) {
    return get_member_avatar($uid, $size);
}

/**
 * 会员头像调用
 * @param  $uid
 * @param  $size 180, 90, 45, 30
 * @return array|string
 */
function get_member_avatar($uid, $size = 90) {
    if (empty($uid)) return SITE_PATH . EXTENSION_PATH . '/null.jpg';
    $db    = Controller::model('member');
	$cache = new cache_file();
	$cfg   = $cache->get('member');
	if ($cfg['uc_use'] == 1) {
	    $size = empty($size) || $size == 90 ? 'middle' : $size;
	    return UC_API . '/avatar.php?uid=' . $uid . '&size=' . $size;
	}
	$data  = $db->find($uid, 'avatar');
    $dir   = 'uploadfiles/member/' . $uid . '/';
	if ($data['avatar'] && strpos($data['avatar'], $dir) !== false) {
	    if (file_exists($dir . '90x90.jpg')) {
			return image($dir . $size . 'x' . $size . '.jpg');
		} else {
		    return thumb($data['avatar'], $size, $size);
		}
	}
	return image($data['avatar']);
}

/**
 * 会员信息调用
 * @param  $uid
 * @param  $more
 * @return array
 */
function get_member_info($uid, $more = 0) {
    if (empty($uid))  return null;
    $member = Controller::model('member');
	$data   = $member->find($uid);
	if (empty($data)) return null;
	if ($more) { //会员附表
		$cache = new cache_file();
		$model = $cache->get('model_member');
		if (isset($model[$data['modelid']])) {
		    $d    = Controller::model($model[$data['modelid']]['tablename']);
			$r    = $d->find($uid);
			$data = array_merge($r, $data);
		}
	}
	unset($data['password']);
	return $data;
}

/**
 * 编码转换函数
 * @param  $str 
 * @param  $from 
 * @param  $to
 * @return string
 */
function convert($str, $from = 'gbk', $to = 'utf-8') {
	if (!$str) return '';
	if (strtolower($from) == strtolower($to)) return $str;
	$to   = str_replace('gb2312', 'gbk', strtolower($to));
	$from = str_replace('gb2312', 'gbk', strtolower($from));
	if ($form == 'gbk' && $to == 'utf-8') {
	    return gbk_to_utf8($str);
	} elseif ($form == 'utf-8' && $to == 'gbk') {
	    return utf8_to_gbk($str);
	} else {
	    return $str;
	}
}

/**
 * utf8转gbk
 * @param $utfstr
 */
function utf8_to_gbk($utfstr) {
	$filename	= EXTENSION_DIR . 'encoding' . DIRECTORY_SEPARATOR . 'gb-unicode.table';
	$UC2GBTABLE = array();
	$fp	= fopen($filename, 'rb');
	while($l = fgets($fp, 15)) {        
		$UC2GBTABLE[hexdec(substr($l, 7, 6))] = hexdec(substr($l, 0, 6));
	}
	fclose($fp);
	$okstr = '';
	$ulen  = strlen($utfstr);
	for($i=0; $i<$ulen; $i++) {
		$c  = $utfstr[$i];
		$cb = decbin(ord($utfstr[$i]));
		if(strlen($cb)==8) { 
			$csize = strpos(decbin(ord($cb)), '0');
			for($j = 0; $j < $csize; $j++) {
				$i++; 
				$c .= $utfstr[$i];
			}
			$c = utf8_to_unicode($c);
			if(isset($UC2GBTABLE[$c])) {
				$c = dechex($UC2GBTABLE[$c]+0x8080);
				$okstr .= chr(hexdec($c[0] . $c[1])) . chr(hexdec($c[2] . $c[3]));
			} else {
				$okstr .= '&#' . $c . ';';
			}
		} else {
			$okstr .= $c;
		}
	}
	$okstr = trim($okstr);
	return $okstr;
}

/**
 * gbk转utf8
 * @param $gbstr
 */
function gbk_to_utf8($gbstr) {
	$filename  = EXTENSION_DIR . 'encoding' . DIRECTORY_SEPARATOR . 'gb-unicode.table';
	$CODETABLE = array();
	$fp	= fopen($filename, 'rb');
	while ($l = fgets($fp, 15)) { 
		$CODETABLE[hexdec(substr($l, 0, 6))] = substr($l, 7, 6); 
	}
	fclose($fp);
	$ret  = '';
	$utf8 = '';
	while ($gbstr) {
		if (ord(substr($gbstr, 0, 1)) > 0x80) {
			$thisW = substr($gbstr, 0, 2);
			$gbstr = substr($gbstr, 2, strlen($gbstr));
			$utf8 = '';
			@$utf8 = unicode_to_utf8(hexdec($CODETABLE[hexdec(bin2hex($thisW)) - 0x8080]));
			if ($utf8 != '') {
				for ($i = 0; $i < strlen($utf8); $i += 3) $ret .= chr(substr($utf8, $i, 3));
			}
		} else {
			$ret .= substr($gbstr, 0, 1);
			$gbstr = substr($gbstr, 1, strlen($gbstr));
		}
	}
	return $ret;
}

/**
 * unicode转utf8
 * @param  $c
 */
function unicode_to_utf8($c) {
	$str = '';
	if ($c < 0x80) {
		$str .= $c;
	} elseif($c < 0x800) {
		$str .= (0xC0 | $c >> 6);
		$str .= (0x80 | $c & 0x3F);
	} elseif($c < 0x10000) {
		$str .= (0xE0 | $c >> 12);
		$str .= (0x80 | $c >> 6 & 0x3F);
		$str .= (0x80 | $c & 0x3F);
	} elseif($c < 0x200000) {
		$str .= (0xF0 | $c >> 18);
		$str .= (0x80 | $c >> 12 & 0x3F);
		$str .= (0x80 | $c >> 6 & 0x3F);
		$str .= (0x80 | $c & 0x3F);
	}
	return $str;
}

/**
 * utf8转unicode
 * @param  $c
 */
function utf8_to_unicode($c) {
	switch (strlen($c)) {
		case 1:
		  return ord($c);
		case 2:
		  $n = (ord($c[0]) & 0x3f) << 6;
		  $n += ord($c[1]) & 0x3f;
		  return $n;
		case 3:
		  $n = (ord($c[0]) & 0x1f) << 12;
		  $n += (ord($c[1]) & 0x3f) << 6;
		  $n += ord($c[2]) & 0x3f;
		  return $n;
		case 4:
		  $n = (ord($c[0]) & 0x0f) << 18;
		  $n += (ord($c[1]) & 0x3f) << 12;
		  $n += (ord($c[2]) & 0x3f) << 6;
		  $n += ord($c[3]) & 0x3f;
		  return $n;
	}
}

/**
 * 格式化输出文件大小
 */
function formatFileSize($fileSize, $round = 2) {
    if (empty($fileSize)) return 0;
	$i    = 0;
	$inv  = 1 / 1024;
	$unit = array(' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
	while ($fileSize >= 1024 && $i < 8) {
		$fileSize *= $inv;
		++$i;
	}
	$fileSizeTmp = sprintf("%.2f", $fileSize);
	$value = $fileSizeTmp - (int)$fileSizeTmp ? $fileSizeTmp : $fileSize;
	return round($value, $round) . $unit[$i];
}

/**
 * 获取当前站点的联动菜单缓存数据
 */
function get_linkage_data() {
	$cache = new cache_file();
    $data  = $cache->get('linkage');
	$site  = $cache->get('linkage_' . App::get_site_id());
	if (empty($site)) return $data;
	if (empty($data)) return $site;
	return $data + $site;
}

/**
 * 调用联动菜单数据
 */
function linkagedata($keyid, $id = 0) {
	if (empty($id)) return false;
    $datas = get_linkage_data();
	$data  = $datas[$keyid];
	if (empty($data)) return false;
	if ($id == $keyid) {
	    unset($data['data']);
	    return $data;
	}
	if (isset($data['data'][$id])) {
	    $t = $data['data'][$id];
	    if ($t['arrchilds']) $t['arrchilds'] = explode(',', $t['arrchilds']);
	    return $t;
	}
	return $t;
}

/**
 * 调用联动菜单数据
 */
function linkagelist($keyid, $id = 0) {
    $datas = get_linkage_data();
	$data  = $datas[$keyid];
	$list  = array();
	if (empty($data)) return false;
	if (empty($id)) {
	    foreach ($data['data'] as $k => $t) {
			if (0 == $t['parentid']) {
			    if ($t['arrchilds']) $t['arrchilds'] = explode(',', $t['arrchilds']);
			    $list[$k] = $t;
			}
		}
	} elseif (isset($data['data'][$id])) {
	    if ($data['data'][$id]['arrchilds']) {
		    foreach ($data['data'] as $k => $t) {
				if ($id == $t['parentid']) {
			        if ($t['arrchilds']) $t['arrchilds'] = explode(',', $t['arrchilds']);
				    $list[$k] = $t;
				}
			}
		} else {
		    foreach ($data['data'] as $k => $t) {
				if ($data['data'][$id]['parentid'] == $t['parentid']) {
			        if ($t['arrchilds']) $t['arrchilds'] = explode(',', $t['arrchilds']);
				    $list[$k] = $t;
				}
			}
		}
	}
	return $list;
}

/**
 * 联动菜单面包屑导航
 */
function linkagepos($keyid, $id, $urlrule, $s = ' > ') {
	$ids  = linkageids($keyid, $id);
	foreach ($ids as $_id) {
	    $data = linkagedata($keyid, $_id);
		$str .= $urlrule ? "<a href=\"" . str_replace('{linkageid}', $data['id'], $urlrule) . "\">" . $data['name'] . "</a>" : $data['name'];
        if ($id != $_id) $str .= $s;
    }
	return $str;
}

/**
 * 调用该菜单的上级菜单id集合
 */
function linkage_ids($keyid, $id, $ids = '') {
    $datas = get_linkage_data();
    $data  = $datas[$keyid]['data'][$id];
    $ids   = $id . ','; 
    if ($data['parentid']) $ids .= linkage_ids($keyid, $data['parentid'], $ids);
    return $ids;
}

/**
 * 调用该菜单的上级菜单id集合(数组返回)
 */
function linkageids($keyid, $id) {
    $ids = linkage_ids($keyid, $id);
    if (empty($ids)) return null;
    if (substr($ids, -1) == ',') $ids = substr($ids, 0, -1);
    $ids  = explode(',', $ids);
    krsort($ids);
	return $ids;
}

/**
 * 表单调用联动菜单
 * @param $linkageid 联动菜单id
 * @param $id 生成联动菜单的样式id
 * @param $defaultvalue 默认值
 * @param $level 级数
 * required 必填
 */
function linkageform($linkageid = 0, $defaultvalue = 0, $id = 'linkage', $level = 3, $required) {
    $data = get_linkage_data();
	$datas = $data[$linkageid];
	$infos = $datas['data'];
    $string = '';
	if(!defined('FINECMS_LINKAGE_INIT_LD')) {
		define('FINECMS_LINKAGE_INIT_LD', 1);
		$string.= '<script type="text/javascript" src="' . ADMIN_THEME . 'js/jquery.ld.js"></script>';
	}
	$default_txt = '';
	$default_lev = 1;
	if($defaultvalue) {
		$default_txt = menu_linkage_level($defaultvalue, $linkageid, $infos);
		$default_lev = substr_count($default_txt, ' > ');
		$default_txt = '["' . str_replace(' > ', '","', $default_txt) . '"]';
	}
    if (!$infos) {
        $string.= '<font color="red">联动菜单值为空，请添加数据或更新缓存</font>';
    }
	$string.= $defaultvalue ? '<input type="hidden" name="data[' . $id . ']"  id="fc_' . $id . '" value="' . $defaultvalue . '">' : '<input type="hidden" name="data[' . $id . ']"  id="fc_' . $id . '" value="">';
	$is_jy = 0;
    for ($i = 1; $i <= $level; $i++) {
        if ($i > $default_lev) {
            $is_jy ++;
            $style = 'style="display:none"';
        } else {
            $style = '';
        }
		$required = $i == 1 && $required ? ' required' : '';
		$string.='<select class="ideacms-select-' . $id . '" name="' . $id . '-' . $i . '" id="' . $id . '-' . $i .'" width="100" ' . $style . $required . '><option value=""> -- </option></select>&nbsp;&nbsp;';
	}
    $string.= '<script type="text/javascript">
				$(function(){
					var $ld5 = $(".ideacms-select-' . $id . '");					  
					$ld5.ld({ajaxOptions:{"url":"' . SITE_URL . 'index.php?c=api&a=linkage&id=' . $linkageid . '"},defaultParentId:0})	 
					var ld5_api = $ld5.ld("api");
					ld5_api.selected(' . $default_txt . ');
					$ld5.bind("change",onchange);
					function onchange(e){
						var $target = $(e.target);
						var index = $ld5.index($target);
						$("#fc_' . $id . '-' . $i . '").remove();
						$("#fc_' . $id . '").val($ld5.eq(index).show().val());
						index ++;
						$ld5.eq(index).show();
					}
				})
	</script>';
	return $string;
}

/**
 * 联动菜单层级
 */
function menu_linkage_level($linkageid, $keyid, $infos, $result = array()) {
	if (@array_key_exists($linkageid, $infos)) {
		$result[] = $infos[$linkageid]['id'];
		return menu_linkage_level($infos[$linkageid]['parentid'], $keyid , $infos, $result);
	}
	krsort($result);
	return implode(' > ',$result);
}

/**
 * 百度地图调用
 */
function baiduMap($modelid, $name, $value, $width = 600, $height = 400) {
    if (empty($modelid) || empty($name) || empty($value)) return false;
    $cache  = new cache_file();
	$models = array('model', 'membermodel', 'formmodel');
	foreach ($models as $name) {
	    $m  = $cache->get($name);
		if (isset($m[$modelid])) {
		    $t = $m[$modelid];
			break;
		}
	}
	$set    = string2array($t['setting']);
    $apikey = isset($set['apikey']) ? $set['apikey'] : '';
	list($lngX, $latY, $zoom) = explode('|', $value);
	$str    = "<script type='text/javascript' src='http://api.map.baidu.com/api?v=1.2&key=" . $apikey . "'></script>";
	$str   .= '<div id="mapObj" class="view" style="width: ' . $width . 'px; height:' . $height . 'px"></div>';
	$str   .= '<script type="text/javascript">';
	$str   .= '
	var mapObj=null;
	lngX = "' . $lngX . '";
	latY = "' . $latY . '";
	zoom = "' . $zoom . '";		
	var mapObj = new BMap.Map("mapObj");
	var ctrl_nav = new BMap.NavigationControl({anchor:BMAP_ANCHOR_TOP_LEFT,type:BMAP_NAVIGATION_CONTROL_LARGE});
	mapObj.addControl(ctrl_nav);
	mapObj.enableDragging();
	mapObj.enableScrollWheelZoom();
	mapObj.enableDoubleClickZoom();
	mapObj.enableKeyboard();//启用键盘上下左右键移动地图
	mapObj.centerAndZoom(new BMap.Point(lngX,latY),zoom);
	drawPoints();
	';
	$str   .= '
	function drawPoints(){
		var myIcon = new BMap.Icon("' . ADMIN_THEME . 'images/mak.png", new BMap.Size(27, 45));
		var center = mapObj.getCenter();
		var point = new BMap.Point(lngX,latY);
		var marker = new BMap.Marker(point, {icon: myIcon});
		mapObj.addOverlay(marker);
	}';	
	$str   .='</script>';
	return $str;
}

/**
 * 判断能否调用/下载远程数据
 */
function fn_check_url() {
    if (ini_get('allow_url_fopen')) return null;
	if (function_exists('curl_init') && function_exists('curl_exec')) return null;
	return lang('app-13');
}

/**
 * 调用远程数据
 */
function fn_geturl($url) {
    if (substr($url, 0, 7) != 'http://') return file_get_contents($url);
    if (ini_get('allow_url_fopen')) {
	    return @file_get_contents($url);
	} elseif (function_exists('curl_init') && function_exists('curl_exec')) {
	    $ch   = curl_init($url);
	    $data = '';
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}

/**
 * 汉字转为拼音
 */
function word2pinyin($word) {
    if (empty($word)) return '';
    $pin = Controller::instance('pinyin');
	return str_replace('/', '', $pin->output(str_replace(' ', '', $word)));
}

/**
 * Tag标签URL
 */
function tag_url($word) {
    global $config;
    if (empty($word)) return '';
    return $config['SITE_TAG_URL'] ? str_replace('{tag}', word2pinyin($word), SITE_PATH . $config['SITE_TAG_URL']) : url('tag/list', array('kw' => word2pinyin($word)));
}

/**
 * 统计目录大小
 */
function count_dir_size($dir) {
    if (!is_dir($dir)) return 0;
	set_time_limit(0);
	$count  = 0;
    $handle = opendir($dir);
	while (false !== ($file = readdir($handle))) {
        if ($file == '.' || $file == '..') continue;
		$path = $dir . $file;
		if (is_dir($path)) {
			$count += count_dir_size($path . '/', $size);
		} elseif (@is_file($path)) {
			$count += filesize($path);
		}
	}
	closedir($handle);
	return $count;
}

/**
 * 统计会员附件目录大小
 */
function count_member_size($id, $path = null) {
    if (empty($id)) return 0;
	set_time_limit(0);
    $dir    = APP_ROOT . 'uploadfiles/member/' . $id . '/';
	if ($path) $dir .= $path . '/';
    if (!is_dir($dir)) return 0;
	$count  = 0;
    $handle = opendir($dir);
	while (false !== ($file = readdir($handle))) {
        if ($file == '.' || $file == '..' || strpos($t, '.thumb.') !== false) continue;
		$path = $dir . $file;
		if (is_dir($path)) {
			$count += count_dir_size($path . '/', $size);
		} elseif (@is_file($path)) {
			$count += filesize($path);
		}
	}
	closedir($handle);
	return $count;
}

/**
 * 获取标签关键字数据
 */
function get_tag_data($keyword) {
    if (empty($keyword)) return null;
	return strpos($keyword, ',') !== false ? explode(',', $keyword) : array(0 => $keyword);
}

/**
 * 后台权限验证
 */
function admin_auth($roleid, $action) {
	return auth::check($roleid, $action, 'admin');
}

/**
 * 后台投稿权限验证
 */
function admin_post_auth($roleid, $data) {
	if (isset($data['adminpost']) && $data['adminpost'] && $data['rolepost'] && in_array($roleid, $data['rolepost'])) {
		return true;
	} elseif (isset($data['siteuser']) && $data['siteuser'] && $data['site'] && in_array(App::get_site_id(), $data['site'])) {
		return true;
	} else {
		return false;
	}
}

// php 5.5 以上版本的正则替换方法
class php5replace {

    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    // 替换常量值 for php5.5
    public function php55_replace_var($value) {
        $v = '';
        @eval('$v = '.$value[1].';');
        return $v;
    }

    // 替换数组变量值 for php5.5
    public function php55_replace_data($value) {
        return $this->data[$value[1]];
    }

    // 替换函数值 for php5.5
    public function php55_replace_function($value) {

        if (function_exists($value[1])) {
            if ($value[2] == '$data') {
                return $value[1]($this->data);
            }
            return $value[1]($value[2]);
        }

        return $value[0];
    }

}

/**
 * 调用远程数据
 *
 * @param	string	$url
 * @return	string
 */
function icatcher_data($url) {

    // fopen模式
    if (ini_get('allow_url_fopen')) {
        $data = @file_get_contents($url);
        if ($data !== FALSE) {
            return $data;
        }
    }

    // curl模式
    if (function_exists('curl_init') && function_exists('curl_exec')) {
        $ch = curl_init($url);
        $data = '';
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    return NULL;
}


/**
 * 将对象转换为数组
 *
 * @param	object	$obj	数组对象
 * @return	array
 */
function iobject2array($obj) {
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if ($_arr && is_array($_arr)) {
        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? iobject2array($val) : $val;
            $arr[$key] = $val;
        }
    }
    return $arr;
}

/**
 * 将字符串转换为数组
 *
 * @param	string	$data	字符串
 * @return	array
 */
function istring2array($data) {
    return $data ? (is_array($data) ? $data : unserialize(stripslashes($data))) : array();
}

/**
 * 将数组转换为字符串
 *
 * @param	array	$data	数组
 * @return	string
 */
function iarray2string($data) {
    return $data ? addslashes(serialize($data)) : '';
}


// 发送短信
function fn_sendsms($mobile, $content) {

    if (!$mobile || !$content) {
        return FALSE;
    }

    $file = FCPATH.'config/sms.php';
    $config = @is_file($file) ? string2array(file_get_contents($file)) : array();

    $result = icatcher_data('http://www.lygphp.com/index.php?uid='.$config['uid'].'&key='.$config['key'].'&mobile='.$mobile.'&content='.$content.'【'.$config['note'].'】&domain='.trim(str_replace('http://', '', SITE_URL), '/').'&sitename='.CMS_NAME);
    if (!$result) {
        return FALSE;
    }

    $result = iobject2array(json_decode($result));

    @file_put_contents(FCPATH.'cache/sms.log', date('Y-m-d H:i:s').' ['.$mobile.'] ['.$result['msg'].'] （'.str_replace(array(chr(13), chr(10)), '', $content).'）'.PHP_EOL, FILE_APPEND);

    return $result;
}