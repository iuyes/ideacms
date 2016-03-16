<?php

/**
 * Common.php
 * 控制器公共类 
 */

if (!defined('IN_IDEACMS')) exit();

class Common extends CI_Controller {

    public $cache;
    public $siteid;
    public $action;
	public $session;
    public $namespace;
    public $controller;

    public $member;
    public $memberinfo;
    public $memberedit;
    public $membermodel;
    public $membergroup;
    public $memberconfig;

    public $db;
    public $cats;
    public $site;
    public $content;
    public $cats_dir;
	public $category;

    public $_options;
    public $template;

    public function __construct() {
        parent::__construct();
		if (!file_exists(APP_ROOT.'./cache/install.lock')) {
            $this->redirect(url('install/'));
        }

        $system_cms = $this->load_config('version');
        require_once SYS_ROOT.'libraries/cache_file.class.php';
        $this->cache = new cache_file();

        App::$namespace = $this->namespace = trim($this->router->directory, '/');
        App::$controller = $this->controller = $this->router->class;
        App::$action = $this->action = $this->router->method;
        App::$plugin = APP_DIR;

        $this->_init();

		$this->load->model('category');
		$this->cats = $this->get_category();
		$this->cats_dir = $this->get_category_dir();

        $this->content = Controller::model('content');
        $this->content->table_name = $this->content->prefix.'content_'.$this->siteid;

		//定义网站常量
        define('IS_POST',	$this->post('submit'));
        define('SITE_PATH',	$this->get_base_url());
		define('SITE_URL', $this->get_server_name() . $this->get_base_url());
        define('CMS_CMS', $system_cms['cms']);
        define('CMS_NAME', $system_cms['name']);
        define('CMS_COM', $system_cms['company']);
        define('CMS_VERSION', $system_cms['version']);
        define('CMS_UPDATE', $system_cms['update']);
        define('SITE_THEME', $this->get_theme_url());
		define('PUBLIC_THEME', SITE_PATH . 'statics/public/');//共用样式
		define('WEB_THEME', SITE_PATH . 'statics/web/');//网页样式
		define('MOBILE_THEME', SITE_PATH . 'statics/mobile/');//移动样式
		//define('ADMIN_THEME', SITE_PATH . basename(VIEW_DIR) . '/admin/');
		define('ADMIN_THEME', SITE_PATH . 'statics/admin/');
		define('EXT_PATH', SITE_PATH . EXTENSION_PATH . '/');
		define('LANG_PATH', SITE_PATH . EXTENSION_PATH . '/language/' . SYS_LANGUAGE . '/');

		//禁止访问
		$ips = $this->cache->get('ip');
		$uip = $this->input->ip_address();
		if ($uip && $ips && is_array($ips)) {
		    if (isset($ips[$uip])
				&& (empty($ips[$uip]['endtime']) || ($ips[$uip]['endtime'] - $ips[$uip]['addtime']) >= 0)) {
				$this->adminMsg(lang('a-aip-6'));
			}
			foreach ($ips as $ip => $t) {
			    if (empty($ip) || strpos($ip, '*') === false) {
                    continue;
                }
				if (preg_match('/^' . str_replace(array('*', '.'), array('[0-9]+', '\.'), $ip) . '$/', $uip)) {
                    $this->adminMsg(lang('a-aip-6'));
                }
			}
			unset($ips, $ip);
		}

        $this->template = $this->view = new View();

		//载入会员系统缓存
		if (!$this->site['SYS_MEMBER'] && is_dir(CONTROLLER_DIR . 'member')) {
			$this->member = Controller::model('member');
			$this->membergroup  = $this->cache->get('membergroup');
			$this->membermodel  = $this->cache->get('model_member');
			$this->memberconfig	= $this->cache->get('member');
			if ($this->memberconfig['uc_use'] == 1 && $this->namespace != 'admin') {
				include EXTENSION_DIR . 'ucenter' . DIRECTORY_SEPARATOR . 'config.inc.php';
				include EXTENSION_DIR . 'ucenter' . DIRECTORY_SEPARATOR . 'uc_client' . DIRECTORY_SEPARATOR . 'client.php';
			}
			$this->memberinfo = $this->getMember();
			$this->view->assign(array(
				'memberinfo' => $this->memberinfo,
				'membergroup' => $this->membergroup,
				'membermodel' => $this->membermodel,
				'memberconfig' => $this->memberconfig
			));
		}

        define('IS_ADMIN', $this->session->userdata('user_id'));
		$this->view->assign($this->site);
		$this->view->assign(array(
			's' => $this->namespace,
			'c' => $this->controller,
			'a' => $this->action,
			'cats' => $this->cats,
			'param' => $this->getParam(),
			'sites' => App::get_site(),
			'siteid' => $this->siteid,
			'is_admin' => IS_ADMIN,
		));

		//加载系统函数库和自定义函数库
        require EXTENSION_DIR.'function.php';
        require EXTENSION_DIR.'custom.php';
		date_default_timezone_set(SYS_TIME_ZONE);
        $this->load->library('user_agent');
        //
        if (defined('SITE_BDPING') && SITE_BDPING &&
            date('G') < 6 && $this->user_agent->is_robot()
            && !get_cookie('sb') && $this->db->query("SHOW TABLES LIKE '".$this->db->dbprefix."sb'")->row_array()) {
            $data = $this->db->limit(5)->order_by('id desc')->get('sb')->result_array();
            if ($data) {
                set_cookie('sb', 1, 600);
                $delete = array();
                foreach ($data as $t) {
                    $value = string2array($t['value']);
                    if ($t['type'] == 'content_add') {
                        // 文章推送到baidping
                        if (function_exists('curl_init')) {
                            $url = 'http://ping.baidu.com/ping/RPC2';
                            $xml = "
                            <?xml version=\"1.0\" encoding=\"UTF-8\"?>
                            <methodCall>
                                <methodName>weblogUpdates.extendedPing</methodName>
                                <params>
                                    <param>
                                        <value><string>".$value['title']."</string></value>
                                    </param>
                                    <param>
                                        <value><string>".SITE_URL."</string></value>
                                    </param>
                                    <param>
                                        <value><string>".$value['url']."</string></value>
                                    </param>
                                    <param>
                                        <value><string></string></value>
                                    </param>
                                </params>
                            </methodCall>";
                            $ch = curl_init();
                            $head = array(
                                "POST ".$url." HTTP/1.0",
                                "Content-type: text/xml;charset=\"utf-8\"",
                                "Accept: text/xml",
                                "Content-length: ".strlen($xml)
                            );
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                            $res = curl_exec ($ch);
                            curl_close ($ch);
                            if (strpos($res, "<int>0</int>")) {
                                $delete[] = $t['id'];
                            }
                        }
                    }
                    if ($delete) {
                        $this->db->where_in('id', $delete)->delete('sb');
                    }
                }
            }
        }
    }
	
	/**
	 * 获取会员信息
	 */
	protected function getMember() {
	    if (get_cookie('member_id') && get_cookie('member_code')) {
            $uid  = (int)get_cookie('member_id');
			$code = get_cookie('member_code');
		    if (!empty($uid) && $code == substr(md5(SITE_MEMBER_COOKIE . $uid), 5, 20)) {
			    $_memberinfo = $this->db->where('id', $uid)->get('member')->row_array();
				$member_table = $this->membermodel[$_memberinfo['modelid']]['tablename'];
				if ($member_table) {
				    $memberdata = $this->db->where('id', $uid)->get($member_table)->row_array();
					if ($memberdata) {
					    $_memberinfo = array_merge($_memberinfo, $memberdata);
						$this->memberedit	= 1; //不需要完善会员资料
					}
					if ($this->memberconfig['uc_use'] == 1 && function_exists('uc_api_mysql')) {
					    $uc = uc_api_mysql('user', 'get_user', array('username'=> $_memberinfo['username']));
						if ($uc != 0) {
                            $_memberinfo['uid'] = $uc[0];
                        }
					}
					return $_memberinfo;
				}
			}
        }
		return false;
	}
    
    /**
     * 后台提示信息
	 * msg    消息名称
	 * url    返回地址
	 * time   等待时间
	 * i      是否显示返回文字
	 * result 返回结果是否成功
     */
    protected function adminMsg($msg, $url = '', $time = 3, $i = 1, $result = 0) {
	    $this->view->assign(array(
			'i'      => $i,
		    'msg'    => $msg,
			'url'    => $url,
			'time'   => $time,
			'result' => $result
		));
		$tpl = 'admin/msg';
		if ($this->namespace != 'admin') $tpl = '../' . $tpl;
        $this->view->display($tpl);
        exit;
    }
	
	/**
     * 会员提示信息
	 * msg    消息名称
	 * url    返回地址
	 * result 返回结果是否成功
	 * time   等待时间
     */
    protected function memberMsg($msg, $url = '', $result = 0, $time = 3) {
        $this->view->assign(array(
		    'msg'    => $msg,
			'url'    => $url,
			'time'   => $time,
			'result' => $result
		));
        $this->view->display('member/msg');
        exit;
    }
    
    /**
     * 前台提示信息
	 * msg    消息名称
	 * url    返回地址
	 * i      是否显示返回文字
	 * time   等待时间
     */
    protected function msg($msg, $url = '', $i = 0, $time = 3) {
        $this->view->assign(array(
			'i'    => $i,
		    'msg'  => $msg,
			'url'  => $url,
			'time' => $time
		));
        $this->view->display('msg');
        exit;
    }
    
    /**
     * 栏目URL
     */
    protected function getCaturl($data, $page = 0) {
         return getCaturl($data, $page);
    }
    
    /**
     * 内容页URL
     */
    protected function getUrl($data, $page = 0) {
        return getUrl($data, $page);
    }
    
    /**
     * 递归创建目录
     */
    protected function mkdirs($dir) {
	    if (empty($dir)) return false;
        if (!is_dir($dir)) {
            $this->mkdirs(dirname($dir));
            mkdir($dir);
        }
    }

    /**
     * 后台操作界面中的顶部导航菜单
     *
     * @param	array	$menu
     * @return	string
     */
    protected function get_menu($menu) {

        if (!$menu) {
            return NULL;
        }

        $_i = 0;
        $_str = '';
        $_mark = TRUE;
        $_uri = $_GET['s'].'/'.$_GET['c'].'/'.$_GET['a'];

        foreach ($menu as $name => $uri) {
            $uri = trim($uri, '/');
            if (!$name && !$uri) {
                continue;
            }
            $class = '';
            $url = url($uri);
            $class = ' class="onloading"';
            $mark = $_i == 0 ? '{MARK}' : '';
            // 判断选中
            if ($this->get_menu_calss($menu, $uri, $_uri)) {
                $_mark = FALSE;
                $class = ' class="onloading on"';
            }
            $_str.= '<a href="'.$url.'" '.$class.$mark.'><em>'.$name.'</em></a><span>|</span>';
            $_i ++;
        }

        if ($_mark && $this->router->method == 'edit') {
            $_str.= '<a href="javascript:;" class="on"><em>'.lang('edit').'</em></a><span>|</span>';
            $_mark = FALSE;
        }

        return $_mark ? str_replace('{MARK}', ' class="on"', $_str) : str_replace('{MARK}', '', $_str);
    }

    private function get_menu_calss($menu, $uri, $_uri) {

        if ($uri == $_uri) {
            return TRUE;
        }

        if (!in_array($_uri, $menu)) {
            if (@strpos($_uri, $uri) === FALSE) {
                return FALSE;
            }
            $uri_arr = explode('/', $_uri);
            $uri_arr = array_slice($uri_arr, 0, -2);
            $__uri = implode('/', $uri_arr);
            if (in_array($__uri, $menu) && $__uri == $uri) {
                return TRUE;
            }
            return $this->get_menu_calss($menu, $uri, $__uri);
        }
    }
     
	/**
	* 加载自定义字段
	* fields 字段数组
	* data   字段默认值
	* auth   字段权限（是否必填）
	*/
    protected function getFields($fields, $data = array()) {
	    App::auto_load('fields');
	    $data_fields = '';
	    if (empty($fields['data'])) return false;
	    foreach ($fields['data'] as $t) {
		    if ($this->namespace != 'admin' && !$t['isshow']) continue;
			if (!@in_array($t['field'], $fields['merge']) && !in_array($t['formtype'], array('merge', 'fields')) && empty($t['merge'])) {
			    //单独显示的字段。
			    $data_fields .= '<tr id="fine_' . $t['field'] . '">';
				$data_fields .= isset($t['not_null']) && $t['not_null'] ? '<th><font color="red">*</font> ' . $t['name'] . '：</th>' : '<th>' . $t['name'] . '：</th>';
				$data_fields .= '<td>';
				$func  = 'content_' . $t['formtype'];
				//防止出错，把字段内容转换成数组格式
				$content = array($data[$t['field']]);
				$content = var_export($content, true);
				$field_config = var_export($t, true);
				if (function_exists($func)) {
                    eval("\$data_fields .= " . $func . "(" . $t['field'] . ", " . $content . ", " . $field_config . ");");
                }
				$data_fields .= $t['tips'] ? '<div class="onShow">' . $t['tips'] . '</div>' : '';
				$data_fields .= '<span id="ck_' . $t['field'] . '"></span>';
				$data_fields .= '</td>';
				$data_fields .= '</tr>';
			} elseif ($t['formtype'] == 'merge') {
			    $data_fields .= '<tr id="fine_' . $t['field'] . '">';
				$data_fields .= '<th>' . $t['name'] . '：</th>';
				$data_fields .= '<td>' ;
				$setting = string2array($t['setting']);
				$string = $setting['content'];
				$regex_array  = $replace_array = array();
                if ($t['data']) {
                    foreach ($t['data'] as $field) {
                        $zhiduan  = $fields['data'][$field];
                        $str = '';
                        $func = 'content_' . $zhiduan['formtype'];
                        //防止出错，把字段内容转换成数组格式
                        $content = array($data[$field]);
                        $content = var_export($content, true);
                        $field_config = var_export($zhiduan, true);
                        if (function_exists($func)) {
                            eval("\$str = " . $func . "(" . $field . ", " . $content . ", " . $field_config . ");");
                        }
                        $regex_array[]	= '{' . $field . '}';
                        $replace_array[] = $str;
                    }
                }
				$data_fields .= str_replace($regex_array, $replace_array, $string);
				$data_fields .= '</td>';
				$data_fields .= '</tr>';
			} elseif ($t['formtype'] == 'fields') {
			    $data_fields .= '<tr id="fine_' . $t['field'] . '">';
				$data_fields .= '<th>' . $t['name'] . '：</th><td>';
				$data_fields .= '<script type="text/javascript" src="' . ADMIN_THEME . 'js/jquery-ui.min.js"></script>';
				$data_fields .= '<div class="fields-list" id="list_' . $t['field'] . '_fields"><ul id="' . $t['field'] . '-sort-items">';
				$merge_string = null;
				$contentdata  = empty($data[$t['field']]) ? array(0 => array()) : string2array($data[$t['field']]);
				$setting = string2array($t['setting']);
				$string = htmlspecialchars_decode($setting['content']);
				foreach ($contentdata as $i => $cdata) {
				    $data_fields .= '<li id="li_' . $t['field'] . '_' . $i . '_fields">';
				    $regex_array  = $replace_array = $o_replace_array = array();
					foreach ($fields['data'] as $field => $value) {
						if (in_array($value['field'], $t['data'])) {
							$str  = $o_str  = '';
							$func = 'content_' . $value['formtype'];
							//防止出错，把字段内容转换成数组格式
							$content = array($cdata[$field]);
							$content = var_export($content, true);
							$field_config = var_export($value, true);
							if (function_exists($func)) {
                                eval("\$str = " . $func . "(" . $field . ", " . $content . ", " . $field_config . ");");
                            }
							if (empty($merge_string) && function_exists($func)) {
                                eval("\$o_str = " . $func . "(" . $field . ", null, " . $field_config . ");");
                            }
							$regex_array[] = '{' . $field . '}';
							$replace_array[] = str_replace('data[' . $field . ']', 'data[' . $t['field'] . '][' . $i . '][' . $field . ']', $str);
							$o_replace_array[] = str_replace('data[' . $field . ']', 'data[' . $t['field'] . '][{idea_block_id}][' . $field . ']', $o_str);
						}
					}
					if (empty($merge_string)) {
					    $merge_string = '<li id="li_' . $t['field'] . '_{idea_block_id}_fields">' . str_replace($regex_array, $o_replace_array, $string) . '<div class="option"><a href="javascript:;" onClick="$(\'#li_' . $t['field'] . '_{idea_block_id}_fields\').remove()">' . lang('a-mod-129') . '</a> <a href="javascript:;" style="cursor:move;" title="' . lang('a-mod-131') . '">' . lang('a-mod-130') . '</a></div></li>';
						$merge_string = str_replace(array("\r", "\n", "\t", chr(13), PHP_EOL), array('', '', '', '', ''), $merge_string);
					}
					$data_fields .= str_replace($regex_array, $replace_array, $string);
					$data_fields .= '<div class="option"><a href="javascript:;" onClick="$(\'#li_' . $t['field'] . '_' . $i . '_fields\').remove()">' . lang('a-mod-129') . '</a> <a href="javascript:;" style="cursor:move;" title="' . lang('a-mod-131') . '">' . lang('a-mod-130') . '</a></div></li>';
				}
				$data_fields .= '</ul>
				<div class="bk10"></div>
				<div class="picBut cu"><a href="javascript:;" onClick="add_block_' . $t['field'] . '()">' . lang('a-add') . '</a></div> 
				<script type="text/javascript">
				function add_block_' . $t['field'] . '() {
				    var json  = ' . json_encode(array('echo' => $merge_string)) . ';
					var c = json["echo"];
					var id = parseInt(Math.random()*1000);
					c = c.replace(/{idea_block_id}/ig, id);
					$("#' . $t['field'] . '-sort-items").append(c);
				}
				$("#' . $t['field'] . '-sort-items").sortable();
				</script>
				</td>';
				$data_fields .= '</tr>';
			}
	    }
	    return $data_fields;
    }
	
	/**
     *	发布文章执行的动作
	 *	$data			发布的数据
	 *	$event			事件名称，before表示之前，later表示之后
	 *	$action			动作名称，member表示会员，admin表示管理员
	 *	缓存文件格式	post_event_事件名称
	 *	缓存数据格式	array('动作名称'=>'函数文件', ... )
     */
	protected function postEvent($data, $event, $action) {
		$Events = $this->cache->get('post_event_' . $event);	//加载缓存
		if (empty($Events) || empty($Events[$action])) return true;
		foreach ($Events[$action] as $name => $file) {
			if (is_file($file)) {
				include_once $file;	//加载函数文件
				$function = $name . '_' . $event;
				if (function_exists($function)) {
					eval("\$result = " . $function . "('" . array2string($data) . "');");
					if ($result && $event == 'before') {	//发布前，有返回信息
						$action == 'member' ? $this->memberMsg($result) : $this->adminMsg($result);
					}
				}
			}
		}
		return true;
	}
	
	/**
     * 验证自定义字段
     */
	protected function checkFields($fields, $data, $msg = 1) {
	    if (empty($fields)) return false;
		foreach ($fields['data'] as $t) {
		    if ($this->namespace != 'admin' && !$t['isshow']) continue;
			if ($t['formtype'] != 'merge' && isset($t['not_null']) && $t['not_null']) {
			    if (is_null($data[$t['field']]) || $data[$t['field']] == '') {
				    if ($msg == 1) {
					    $this->adminMsg(lang('com-0', array('1' => $t['name'])));
					} elseif ($msg == 2) {
					    $this->memberMsg(lang('com-0', array('1' => $t['name'])));
					} elseif ($msg == 3) {
					    return lang('com-0', array('1' => $t['name']));
					}
				}
				if (isset($t['pattern']) && $t['pattern']) {
					$pattern = substr($t['pattern'], 0, 1) == '/' ? (substr(substr($t['pattern'], 1), -1, 1) == '/' ? substr(substr($t['pattern'], 1), 0, -1) : substr($t['pattern'], 1)) : $t['pattern'];
				    if (!preg_match('/' . $pattern . '/', $data[$t['field']])) {
					    $showmsg = isset($t['errortips']) && $t['errortips'] ? $t['errortips'] : lang('com-1', array('1' => $t['name']));
					    if ($msg == 1) {
					        $this->adminMsg($showmsg);
						} elseif ($msg == 2) {
							$this->memberMsg($showmsg);
						} elseif ($msg == 3) {
							return $showmsg;
						}
					}
				} 
			}
	    }
	}
    
    /**
     * 生成水印图片
     */
    protected function watermark($file) {
        if (!$this->site['SITE_WATERMARK'] || $this->site['SITE_THUMB_TYPE']) return false;
        $image = $this->instance('image_lib');
        if ($this->site['SITE_WATERMARK'] == 1) {
            $image->set_watermark_alpha($this->site['SITE_WATERMARK_ALPHA']);
            $image->make_image_watermark($file, $this->site['SITE_WATERMARK_POS'], $this->site['SITE_WATERMARK_IMAGE']);
        } else {
            $image->set_text_content($this->site['SITE_WATERMARK_TEXT']);
            $image->make_text_watermark($file, $this->site['SITE_WATERMARK_POS'], $this->site['SITE_WATERMARK_SIZE']);
        }
    }
    
    /**
     * 生成网站地图
     */
    protected function sitemap() {
        sitemap_xml();
    }
	
	/**
     * 删除目录及文件
     */
    protected function delDir($filename) {
        if (empty($filename)) return false;
        if (is_file($filename) && file_exists($filename)) {
            unlink($filename);
        } else if ($filename != '.' && $filename!='..' && is_dir($filename)) {
            $dirs = scandir($filename);
            foreach ($dirs as $file) {
                if ($file != '.' && $file != '..') {
                    $this->delDir($filename . '/' . $file);
                }
            }
            rmdir($filename);
        }
    }
	
	/**
     * 用户是否能够查看未审核信息
     */
	protected function userShow($data) {
	    if ($data['status'] != 0) return true;
	    if ($this->session->is_set('user_id') && $this->session->get('user_id')) return true;
		if (get_cookie('member_id') && get_cookie('member_id') == $data['userid'] && $data['sysadd'] == 0) return true;
		return false;
	}
	
    /**
     * 验证验证码
     */
	protected function checkCode($value) {
        if (isset($_POST['geetest_challenge'])
            && isset($_POST['geetest_validate'])) {
            require EXTENSION_DIR.'Geetestlib.php';
            $GtSdk = new GeetestLib();
            if ($this->session->userdata('gtserver') == 1) {
                $result = $GtSdk->validate(
                    $_POST['geetest_challenge'],
                    $_POST['geetest_validate'],
                    $_POST['geetest_seccode']
                );
                if ($result == TRUE) {
                    return TURE;
                } else if ($result == FALSE) {
                    return FALSE;
                } else {
                    return FALSE;
                }
            }else{
                if ($GtSdk->get_answer($_POST['geetest_validate'])) {
                    return TURE;
                }else{
                    return FALSE;
                }
            }
        } else {
            $code  = $this->session->get('captcha');
            $value = strtolower($value);
            $this->session->unset_userdata('captcha');
            return $code == $value ? true : false;
        }
	}
	
	/**
     * 模型栏目
     */
	protected function getModelCategory($modelid) {
	    $data = array();
		foreach ($this->cats as $cat) {
		    if ($modelid == $cat['modelid'] && $cat['typeid'] == 1 && $cat['child'] == 0) $data[$cat['catid']] = $cat;
		}
		return $data;
	}
	
	/**
     * 模型的关联表单
     */
	protected function getModelJoin($modelid) {
	    if (empty($modelid)) return null;
		$data   = $this->get_model('form');
		$return = null;
		if ($data) {
		    foreach ($data as $t) {
			    if ($t['joinid'] == $modelid) $return[] = $t;
			}
		}
		return $return;
	}
	
	/**
     * 可在会员中心显示的表单
     */
	protected function getFormMember() {
		$data   = $this->get_model('form');
		$join   = $this->cache->get('model_join_' . $this->siteid);
		$return	= null;
		if ($data) {
		    foreach ($data as $id=>$t) {
				if (isset($t['setting']['auth']['siteuser']) && $t['setting']['auth']['siteuser'] && $t['setting']['auth']['site'] && in_array($this->siteid, $t['setting']['auth']['site'])) continue;
			    if (isset($t['setting']['form']['member']) && $t['setting']['form']['member'] && !$this->memberPost($t['setting']['auth'])) {
				    $t['joinname'] = isset($join[$t['joinid']]['modelname']) && $join[$t['joinid']]['modelname'] ? $join[$t['joinid']]['modelname'] : '';
				    $return[$id]   = $t;
				}
			}
		}
		return $return;
	}
	
	/**
     * 格式化字段数据
     */
	protected function getFieldData($model, $data) {
	    if (!isset($model['fields']['data']) || empty($model['fields']['data']) || empty($data)) return $data;
	    foreach ($model['fields']['data'] as $t) {
			if (!isset($data[$t['field']])) continue;
			if ($t['formtype'] == 'editor') {
			    //把编辑器中的HTML实体转换为字符
				$data[$t['field']] = htmlspecialchars_decode($data[$t['field']]);
			} elseif (in_array($t['formtype'], array('checkbox', 'files', 'fields'))) {
				//转换数组格式
				$data[$t['field']] = string2array($data[$t['field']]);
			}
		}
		return $data;
	}
	
	/**
     * 检查文件/目录名称是否规范
     */
	protected function checkFileName($file) {
		if (strpos($file, '../') !== false || strpos($file, '..\\') !== false) return true;
		return false;
	}
	
	/**
     * 会员投稿权限判断
     */
	protected function memberPost($data) {
		if (isset($data['siteuser']) && $data['siteuser'] && $data['site'] && in_array($this->siteid, $data['site'])) return true;
		if (isset($data['memberpost']) && $data['memberpost']) {
		    if (isset($data['modelpost']) && in_array($this->memberinfo['modelid'], $data['modelpost'])) return true;
			if (isset($data['grouppost']) && in_array($this->memberinfo['groupid'], $data['grouppost'])) return true;
		}
		return false;
	}
	
	/**
	 * 生成内容html
	 */
	protected function createShow($data, $page = 1) {
	    if (empty($data)) {
            return false;
        }
		ob_start();
		$id	= $data['id'];
	    $catid	= $data['catid'];
	    $cat = $this->cats[$catid];
		if ($cat['setting']['url']['use'] == 0 || $cat['setting']['url']['tohtml'] == 0  || $cat['setting']['url']['show'] == '') {
            return false;
        }
	    $table = $this->model($cat['tablename']);
	    $_data = $table->find($id);
	    $data = array_merge($data, $_data);
	    $model = $this->get_model();
	    $data = $this->getFieldData($model[$cat['modelid']], $data);
	    $data = $this->get_content_page($data, 1, $page);
	    $url = substr($this->getUrl($data, $page), strlen(Controller::get_base_url())); //去掉域名部分
        if (strpos($url, 'index.php') !== false || strpos($url, 'http://') != false) {
            return false;
        }
	    if (substr($url, -5) != '.html') { 
			$file = 'index.html'; //文件名
			$dir = $url; //目录
		} else {
			$file = basename($url);
			$dir = str_replace($file, '', $url);
		}
		$this->mkdirs($dir);
		$dir = substr($dir, -1) == '/' ? substr($dir, 0, -1) : $dir;
		$htmlfile = $dir ? $dir . '/' . $file : $file;
		if ($data['status'] == 0) {
		    @unlink($htmlfile);
			if (isset($pagelist) && is_array($pagelist)) {
		        foreach ($pagelist as $p=>$u) {
				    $file = str_replace(Controller::get_base_url(), '', $u);
				    @unlink($file);
				}
			}
			return false;
		}
		$prev_page = $this->content->getOne("`catid`=$catid AND `id`<$id AND `status`<>0 ORDER BY `id` DESC", null, 'title,url,hits');
		$next_page = $this->content->getOne("`catid`=$catid AND `id`>$id AND `status`<>0", null, 'title,url,hits');
	    $data['content'] = relatedlink($data['content']);
	    $this->view->assign(array(
	        'cat' => $cat,
		    'cats' => $this->cats,
	        'page' => $page,
	        'pageurl' => urlencode(getUrl($data, '{page}')),
	        'prev_page'	=> $prev_page,
	        'next_page'	=> $next_page
	    ));
	    $this->view->assign($data);
	    $this->view->assign(showSeo($data, $page));
		if ($this->namespace == 'admin') {
            $this->view->setTheme(true);
        }
	    $this->view->display(substr($cat['showtpl'], 0, -5));
		if ($this->namespace == 'admin') {
            $this->view->setTheme(false);
        }
		if (!file_put_contents($htmlfile, ob_get_clean(), LOCK_EX)) {
            $this->adminMsg(lang('a-com-11', array('1' => $htmlfile)));
        }
		$htmlfiles = $this->cache->get('html_files');
		$htmlfiles[] = $htmlfile;
		$this->cache->set('html_files', $htmlfiles);
		if (isset($_data['content']) && strpos($_data['content'], '{-page-}') !== false) {
			$content = explode('{-page-}', $_data['content']);
			$pageid	= $page <= 0 ? 1 : $page;
			$nextpage = $pageid + 1;
			if ($nextpage <= count($content)) {
                $this->createShow($data, $nextpage);
            }
		}
		return true;
	}
	
	/**
	 * 生成表单html
	 */
	protected function createForm($mid, $data) {
	    if (empty($data) || empty($mid)) return false;
		if ($data['status']	!= 1) return false;
		$url = substr(form_show_url($mid, $data), strlen(Controller::get_base_url())); //去掉域名部分
        if (strpos($url, 'index.php') !== false || strpos($url, 'http://') != false) {
            return false;
        }
	    if (substr($url, -5) != '.html') { 
			$file	= 'index.html'; //文件名 
			$dir	= $url; //目录
		} else {
			$file	= basename($url);
			$dir	= str_replace($file, '', $url);
		}
		$this->mkdirs($dir);
		$dir = substr($dir, -1) == '/' ? substr($dir, 0, -1) : $dir;
		$htmlfile	= $dir ? $dir . '/' . $file : $file;
		ob_start();
		$model = $this->get_model('form');
		$form  = $model[$mid];
	    if (empty($form)) return false;
		if (!isset($form['setting']['form']['url']['tohtml']) || empty($form['setting']['form']['url']['tohtml'])) return false;
	    if (isset($form['fields']) && $form['fields']) $data = $this->getFieldData($form, $data);
		$this->view->assign($data);
	    $this->view->assign(array(
			'table'				=> $form['tablename'],
			'modelid'			=> $mid,
			'form_name'			=> $form['modelname'],
	        'meta_title'		=> $form['setting']['form']['meta_title'],
	        'meta_keywords'		=> $form['setting']['form']['meta_keywords'], 
	        'meta_description'	=> $form['setting']['form']['meta_description']
	    ));
		if ($this->namespace == 'admin') $this->view->setTheme(true);
		$this->view->display(substr($form['showtpl'], 0, -5));
		if ($this->namespace == 'admin') $this->view->setTheme(false);
		if (!file_put_contents($htmlfile, ob_get_clean(), LOCK_EX)) {
            $this->adminMsg(lang('a-com-11', array('1' => $htmlfile)));
        }
		$htmlfiles		= $this->cache->get('html_files');
		$htmlfiles[]	= $htmlfile;
		$this->cache->set('html_files', $htmlfiles);
		return true;
	}
	
	/**
	 * 获取栏目缓存
	 */
	protected function get_category() {
		$cats = $this->cache->get('category_' . $this->siteid);
		if ($this->site['SITE_EXTEND_ID']) {
			$data = $this->cache->get('category_' . $this->site['SITE_EXTEND_ID']);
			if (empty($data)) {
                return $cats;
            }
			foreach ($data as $catid => $t) {
				$t['items'] = $cats[$catid]['items'];
				$cats[$catid] = $t;
			}
		}
		return $cats;
	}
	
	/**
	 * 获取栏目缓存目录名称
	 */
	protected function get_category_dir() {
		$cats = $this->cache->get('category_dir_' . $this->siteid);
		if ($this->site['SITE_EXTEND_ID']) {
			$data = $this->cache->get('category_dir_' . $this->site['SITE_EXTEND_ID']);
			return empty($data) ? $cats : $data;
		}
		return $cats;
	}
	
	/**
	 * 获取模型缓存(非会员模型)
	 */
	protected function get_model($name = 'content', $site = 0) {
		return get_model_data($name, $site);
	}
	
	/**
	 * 检查会员名是否符合规定
	 */
	protected function is_username($username) {
		$strlen  = strlen($username);
		$pattern = $this->memberconfig['username_pattern'] ? $this->memberconfig['username_pattern'] : '/^[a-zA-Z0-9_][a-zA-Z0-9_]+$/';
		if(!preg_match($pattern, $username)){
			return false;
		} elseif ( 20 < $strlen || $strlen < 2 ) {
			return false;
		}
		return true;
    }
	
	/**
	 * 内容分页
	 */
	protected function get_content_page($data, $type, $page) {
		if (strpos($data['content'], '{-page-}') !== false) {	//内容分页判断
			$content  = explode('{-page-}', $data['content']);
			$pageid   = count($content) >= $page ? ($page - 1) : (count($content) - 1);
			$page_id  = 1;
			$pagelist = $pagename = array();
			foreach ($content as $k => $t) {
				if (preg_match('/\[stitle\](.*)\[\/stitle\]/', $t, $stitle)) {	//子标题判断
					$content[$k] = str_replace($stitle[0], '', $t);
					$pagename[$page_id]	= $stitle[1];
					if ($k == $pageid) $data['stitle'] = $stitle[1];
				} else {
					$pagename[$page_id]	= $page_id;
				}
				$pagelist[$page_id] = $type == 0 ? getCaturl($data, $page_id) : getUrl($data, $page_id);
				$page_id ++ ;
			}
			$data['content'] = $content[$pageid];
			$this->view->assign(array(
				'page'			=> $page,
				'contentname'	=> $pagename,
				'contentpage'	=> $pagelist
			));
		}
		return $data;
	}
	
	/**
	 * 更新登录信息
	 */
	public function update_login_info($data) {
		$userip	= client::get_user_ip();
		if (empty($data['loginip']) || $data['loginip'] != $userip) {	//如果会员表中的登录ip不一致，则重新记录
			$update = array(
				'lastloginip'   => $data['loginip'], 
				'lastlogintime' => $data['logintime'],
				'loginip'       => $ip,
				'logintime'     => time(),
			);
			$this->member->update($update, 'id=' . $data['id']);
		}
	}



    //======================兼容性处理=====================//

    /**
     * 设置网站配置信息
     */
    private function _init() {
        $config = require CONFIG_DIR . 'config.ini.php';
        $site = array_flip(require CONFIG_DIR . 'site.ini.php');
        $mb = array();
        if (is_file(CONFIG_DIR . 'mobile.ini.php')) {
            $mb = array_flip(require CONFIG_DIR . 'mobile.ini.php');
        }
        $name = strtolower($_SERVER['HTTP_HOST']) . ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . (int)$_SERVER['SERVER_PORT']);
        $info = array();
        $path = str_replace(array('\\', '//'), '/', dirname($_SERVER['SCRIPT_NAME']));
        $path = (substr($path, -1) == '/') ? $path : $path . '/'; //URL以反斜杠("/")结尾
        $is_mb = 0;
        if (isset($_GET['siteid'])) {
            $siteid	= (int)$_GET['siteid'];
        } elseif (isset($site[$name])) {
            $siteid = (int)$site[$name];
        } elseif (isset($mb[$name])) {
            $siteid = (int)$mb[$name];
            $is_mb = 1;
        }
        $siteid = max($siteid, 1);
        foreach ($site as $url => $sid) {
            $f = CONFIG_DIR . 'site' . DIRECTORY_SEPARATOR . $sid . '.ini.php';
            if (file_exists($f) && is_file($f)) {
                $info[$sid] = require $f;
                $info[$sid]['URL'] = 'http://' . $url . $path;
                $info[$sid]['DOMAIN'] = $url;
            } else {
                if ($siteid == $sid) $siteid = 1;
            }
        }

        $site = isset($info[$siteid]) ? $info[$siteid] : array();
        $config = array_merge($config, $site);
        $config['PLUGIN_DIR'] = basename(PLUGIN_DIR);

        // 移动判断
        if (isset($config['SITE_MOBILE']) && $config['SITE_MOBILE'] == true) {
            if ($is_mb || App::get_mobile()) {
                //手机客服端修改系统模板目录
                $config['SITE_THEME'] = is_dir(VIEW_DIR . 'mobile_' . $siteid) ? 'mobile_' . $siteid : (is_dir(VIEW_DIR . 'mobile') ? 'mobile' : $config['SITE_THEME']);
            }
        }
        define('TIME_FORMAT',	isset($config['SITE_TIME_FORMAT']) && $config['SITE_TIME_FORMAT'] ? $config['SITE_TIME_FORMAT'] : 'Y-m-d H:i:s'); //输出时间格式化
        define('SYS_LANGUAGE',	isset($config['SITE_LANGUAGE']) && $config['SITE_LANGUAGE']	? $config['SITE_LANGUAGE']	: 'zh-cn');	//网站语言设置
        define('LANGUAGE_DIR',	EXTENSION_DIR . 'language' . DIRECTORY_SEPARATOR . SYS_LANGUAGE . DIRECTORY_SEPARATOR);	//网站语言文件
        if (defined('SITE_DIR')) {
            define('SYS_THEME_DIR',	SITE_DIR . DIRECTORY_SEPARATOR);	//模板风格
        } else {
            define('SYS_THEME_DIR',	$config['SITE_THEME'] . DIRECTORY_SEPARATOR);	//模板风格
        }

        define('SYS_TIME_ZONE',	'Etc/GMT' . ($config['SITE_TIMEZONE'] > 0 ? '-' : '+') . (abs($config['SITE_TIMEZONE'])));	//时区
        date_default_timezone_set(SYS_TIME_ZONE);
        if (!file_exists(LANGUAGE_DIR)) {
            exit('语言目录不存在：' . LANGUAGE_DIR);
        }
        $language = require LANGUAGE_DIR . 'lang.php';
        if (file_exists(LANGUAGE_DIR . 'custom.php')) {	//如果存在自定义语言包，则引入
            $custom_lang = require LANGUAGE_DIR . 'custom.php';
            $language = array_merge($language, $custom_lang);	//若有重复，自定义语言会覆盖系统语言
        }
        $this->siteid = $siteid;
        define('SITE_ID', $this->siteid);
        define('SYS_MODE', isset($config['SYS_MODE']) ? $config['SYS_MODE'] : 0);
        define('IS_AJAX', $this->input->is_ajax_request());
        $this->site = $this->config = $config;
        $this->language = $language;
        $this->site_info = $info;

        define('SITE_BDPING', $config['SITE_BDPING']);


        App::$siteid = $siteid;
        App::$config = $config;
        App::$language = $language;
        App::$site_info	= $info;

        $config = require CONFIG_DIR.'database.ini.php';
        $config['hostname'] = $config['host'];
        $config['database'] = $config['dbname'];
        $config['dbdriver'] = function_exists('mysqli_init') ? "mysqli" : "mysql";
        $config['dbprefix'] = $config['prefix'];
        $config['pconnect'] = FALSE;
        $config['db_debug'] = TRUE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = "";
        $config['char_set'] = "utf8";
        $config['dbcollat'] = "utf8_general_ci";

        $this->load->database($config);

    }

    /**
     * 记录$_GET中的非法字符
     */
    public function check_Get($var) {
        return Controller::check_Get($var);
    }

    /**
     * 记录$_POST中的非法字符
     */
    public function check_Post($var, $a=0) {
        return Controller::check_Post($var, $a);
    }

    /**
     * 保存非法字符攻击日志
     */
    private static function save_attack_log($type, $val) {
        return Controller::save_attack_log($type, $val);
    }

    /**
     * 获取并分析$_GET数组某参数值
     */
    public function get($string) {
        return Controller::get($string);
    }

    /**
     * 获取并分析$_POST数组某参数值
     */
    public function post($string, $a=0) {
        return Controller::post($string, $a);
    }

    /**
     * 验证表单是否POST提交
     */
    public function isPostForm($var='submit', $emp=0) {
        return Controller::isPostForm($var, $emp);
    }

    /**
     * 获取并分析 $_GET或$_POST全局超级变量数组某参数的值
     */
    public function get_params($string) {
        return Controller::get_params($string);
    }

    /**
     * trigger_error()的简化函数
     *
     * 用于显示错误信息. 若调试模式关闭时(即:SYS_DEBUG为false时)，则将错误信息并写入日志
     * @access public
     * @param string $message   所要显示的错误信息
     * @param string $level     日志类型. 默认为Error. 参数：Warning, Error, Notice
     * @return void
     */
    public function halt($message, $level = 'Error') {
        return Controller::halt($message, $level);
    }

    /**
     * 获取当前运行程序的网址域名
     */
    public function get_server_name() {
        return Controller::get_server_name();
    }

    /**
     * 获取当前项目的根目录的URL
     */
    public function get_base_url() {
        return Controller::get_base_url();
    }

    /**
     * 获取当前运行的Action的URL
     */
    public function get_self_url() {
        return Controller::get_selfself_url();
    }

    /**
     * 获取当前Controller内的某Action的URL
     */
    public function get_action_url($action_name) {
        return Controller::get_action_url($action_name);
    }

    /**
     * 获取当前项目themes目录的URL
     */
    public function get_theme_url(){
        return Controller::get_theme_url();
    }

    /**
     * 网址(URL)跳转操作
     */
    public function redirect($url){
        return Controller::redirect($url);
    }

    /**
     * 网址(URL)组装操作,组装绝对路径的URL
     */
    public function create_url($route, $params = null) {
        return Controller::create_url($route, $params);
    }

    /**
     * 类的单例实例化操作
     */
    public function instance($class_name) {
        return Controller::instance($class_name);
    }

    /**
     * 单例模式实例化一个Model对象
     */
    public function model($table_name) {
        return Controller::model($table_name);
    }

    /**
     * 单例模式实例化一个应用Model对象
     */
    public function plugin_model($plugin, $table_name) {
        return Controller::plugin_model($plugin, $table_name);
    }

    /**
     * 静态加载文件
     */
    public function import($file_name) {
        return Controller::import($file_name);
    }

    /**
     * 静态加载项目设置目录(config目录)中的配置文件
     */
    public function load_config($file_name) {
        return Controller::load_config($file_name);
    }

    /**
     * stripslashes
     */
    protected static function strip_slashes($string) {
        return Controller::strip_slashes($string);
    }

    /**
     * addslashes
     */
    protected static function add_slashes($string) {
        return Controller::add_slashes($string);
    }

    /**
     * 加载应用配置信息
     */
    protected function load_plugin_setting($dir) {
        return Controller::load_plugin_setting($dir);
    }

    /**
     * 设置应用视图变量
     */
    public function assign($key, $value = null) {
        if (!$key) return false;
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->_options[$k] = $v;
            }
        } else {
            $this->_options[$key] = $value;
        }
        return true;
    }

    /**
     * 应用调用模板文件（文件为PHP文件）
     */
    public function display($file) {
        $filename = PLUGIN_DIR . App::get_plugin_id() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $file . '.php';
        if (!file_exists($filename)) {
            App::display_error(lang('app-12', array('1'=>$file)));
        }
        //分析视图变量
        $this->_options['viewpath'] = SITE_PATH . basename(PLUGIN_DIR) . '/' . APP_DIR . '/views/';
        extract($this->_options, EXTR_PREFIX_SAME, 'data');
        //清空不必要的内存占用
        require $filename;
    }


    /**
     * 获取URL参数
     */
    protected function getParam() {
        return Controller::getParam();
    }

}
