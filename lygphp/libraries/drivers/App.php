<?php

/**
 * 临时兼容性文件 （Free v2.0.0 以下版本有效）
 */

if (!defined('IN_IDEACMS')) exit();

abstract class App {
    
	public static $namespace;
	public static $controller;
	public static $action;
	public static $plugin;
	public static $siteid;
	public static $config     = array();
	public static $language   = array();
	public static $_objects   = array();
	public static $site_info  = array();
	public static $_inc_files = array();
	
	/**
	 * 分析URL信息
	 */
	private static function parse_request() {

		return true;
	}
	

	
	/**
	 * 显示404错误提示
	 */
	private static function display_404_error($id=0) {
	    header('HTTP/1.1 404 Not Found');
		require SYS_ROOT . 'html/error404.php';
		exit();		
	}
	
	/**
     * 显示错误提示
     */
    public static function display_error($message, $back=0) {
        if (!$message) return false;
		require SYS_ROOT . 'html/message.php';
        exit();
    }
	
	/**
	 * 核心类引导数组
	 */
	public static $core_class_array = array(
		'mysql'			=> 'libraries/mysql.class.php',
		'mysql_slave'	=> 'libraries/mysql_slave.class.php',
		'html'			=> 'libraries/html.class.php',
		'cache_file'	=> 'libraries/cache_file.class.php',
		'pagelist'		=> 'libraries/pagelist.class.php',
		'cookie'		=> 'libraries/cookie.class.php',
		'session'		=> 'libraries/session.class.php',
		'file_list'		=> 'libraries/file_list.class.php',
		'image_lib'		=> 'libraries/image_lib.class.php',
		'check'			=> 'libraries/check.class.php',
		'file_upload'	=> 'libraries/file_upload.class.php',
		'client'		=> 'libraries/client.class.php',
		'pinyin'		=> 'libraries/pinyin.class.php',
		'tree'			=> 'libraries/tree.class.php',
		'loader'		=> 'libraries/loader.class.php',
		'auth'			=> 'libraries/auth.class.php',
		'mail'			=> 'libraries/mail.class.php',
		'captcha'		=> 'libraries/captcha.class.php',
		'pclzip'		=> 'libraries/pclzip.class.php',
		'linkage_tree'	=> 'libraries/linkage_tree.class.php',
	);
	
	/**
	 * 项目文件的自动加载
	 */
	public static function auto_load($class_name) {
		if (isset(self::$core_class_array[$class_name])) {				
			self::load_file(SYS_ROOT . self::$core_class_array[$class_name]);			
		} else if (substr($class_name, -5) == 'Model') {	
			if (is_file(MODEL_DIR . $class_name . '.php')) {
				self::load_file(MODEL_DIR . $class_name . '.php');
			} elseif ((is_file(PLUGIN_DIR . self::$namespace. DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $class_name . '.php'))) {
			    self::load_file(PLUGIN_DIR . self::$namespace. DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $class_name . '.php');
			} else {
                // 当没有找到这个模型时
                if (strpos($class_name, 'Content_') === 0 || strpos($class_name, 'Form_') === 0 || strpos($class_name, 'Member_') === 0) {
                    // 是内容模型
                    //echo '内容';
                } else {
                    Controller::halt('The Model file: ' . $class_name . ' is not exists!');
                }
			}
		} else {
			if (is_file(EXTENSION_DIR . $class_name . '.php')) {
				self::load_file(EXTENSION_DIR . $class_name . '.php');
			} else {
				//exit('The File:' . $class_name . '.php is not exists!');
			}		
		}
	}
	
	/**
	 * 获取当前运行的namespace名称
	 */
	public static function get_namespace_id() {
		return strtolower(self::$namespace);
	}
	
	/**
	 * 获取当前运行的controller名称
	 */
	public static function get_controller_id() {
		return strtolower(self::$controller);
	}
	
	/**
	 * 获取当前运行的action名称
	 */
	public static function get_action_id() {
		return self::$action;
	}
	
	/**
	 * 获取当前运行的siteID
	 */
	public static function get_site_id() {
		return self::$siteid;
	}
	
	/**
	 * 获取配置信息
	 */
	public static function get_config() {
	    return self::$config;
	}
	
	/**
	 * 获取语言文件信息
	 */
	public static function get_language() {
	    return self::$language;
	}
	
	/**
	 * 获取所有站点信息
	 */
	public static function get_site() {
	    return self::$site_info;
	}
	
	/**
	 * 设置网站配置信息
	 */
	private static function set_config($config) {
		$site	= array_flip(require CONFIG_DIR . 'site.ini.php');
		$name	= strtolower($_SERVER['HTTP_HOST']) . ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . (int)$_SERVER['SERVER_PORT']);
		$info	= array();
		$path	= str_replace(array('\\', '//'), '/', dirname($_SERVER['SCRIPT_NAME']));
		$path	= (substr($path, -1) == '/') ? $path : $path . '/'; //URL以反斜杠("/")结尾
		$siteid	= isset($_GET['siteid']) ? (int)$_GET['siteid'] : (isset($site[$name]) ? $site[$name] : 1);
		foreach ($site as $url => $sid) {
			$f	= CONFIG_DIR . 'site' . DIRECTORY_SEPARATOR . $sid . '.ini.php';
			if (file_exists($f) && is_file($f)) {
				$info[$sid] = require $f;
				$info[$sid]['URL'] = 'http://' . $url . $path;
				$info[$sid]['DOMAIN'] = $url;
			} else {
				if ($siteid == $sid) $siteid = 1;
			}
		}
		$site	= isset($info[$siteid]) ? $info[$siteid] : array();
		$config = array_merge($config, $site);
		$config['PLUGIN_DIR'] = basename(PLUGIN_DIR);
		if (isset($config['SITE_MOBILE']) && $config['SITE_MOBILE'] == true && self::get_mobile()) {	//手机客服端修改系统模板目录
			$config['SITE_THEME'] = is_dir(VIEW_DIR . 'mobile_' . $siteid) ? 'mobile_' . $siteid : (is_dir(VIEW_DIR . 'mobile') ? 'mobile' : $config['SITE_THEME']);
		}
		define('TIME_FORMAT',	isset($config['SITE_TIME_FORMAT']) && $config['SITE_TIME_FORMAT'] ? $config['SITE_TIME_FORMAT'] : 'Y-m-d H:i:s'); //输出时间格式化
		define('SYS_LANGUAGE',	isset($config['SITE_LANGUAGE']) && $config['SITE_LANGUAGE']	? $config['SITE_LANGUAGE']	: 'zh-cn');	//网站语言设置
		define('LANGUAGE_DIR',	EXTENSION_DIR . 'language' . DIRECTORY_SEPARATOR . SYS_LANGUAGE . DIRECTORY_SEPARATOR);	//网站语言文件
		define('SYS_THEME_DIR',	$config['SITE_THEME'] . DIRECTORY_SEPARATOR);	//模板风格
		define('SYS_TIME_ZONE',	'Etc/GMT' . ($config['SITE_TIMEZONE'] > 0 ? '-' : '+') . (abs($config['SITE_TIMEZONE'])));	//时区
		date_default_timezone_set(SYS_TIME_ZONE);
		if (!file_exists(LANGUAGE_DIR)) exit('语言目录不存在：' . LANGUAGE_DIR);
		$language = require LANGUAGE_DIR . 'lang.php';
		if (file_exists(LANGUAGE_DIR . 'custom.php')) {	//如果存在自定义语言包，则引入
			$custom_lang = require LANGUAGE_DIR . 'custom.php';
			$language = array_merge($language, $custom_lang);	//若有重复，自定义语言会覆盖系统语言
		}
		self::$siteid = $siteid;
		self::$config = $config;
		self::$language = $language;
		self::$site_info = $info;
	}
	
	/**
	 * 判断客服端是否是手机客服端
	 */
	public static function get_mobile() {
		if (isset($_SERVER['HTTP_VIA'])) return stristr($_SERVER['HTTP_VIA'],'wap') ? true : false;
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$client = array(
				'nokia',
				'sony',
				'ericsson',
				'mot',
				'samsung',
				'htc',
				'sgh',
				'lg',
				'sharp',
				'sie-',
				'philips',
				'panasonic',
				'alcatel',
				'lenovo',
				'iphone',
				'blackberry',
				'meizu',
				'android',
				'netfront',
				'symbian',
				'ucweb',
				'windowsce',
				'palm',
				'operamini',
				'operamobi',
				'openwave',
				'nexusone',
				'cldc',
				'midp',
				'wap',
				'mobile'
			);
			if (preg_match("/(" . implode('|', $client) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) return true;
		}
		if (isset($_SERVER['HTTP_ACCEPT'])) {
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
				return true;
			}
		}
		return false;
	}
	
   /**
	 * 获取当前运行的plugin名称
	 */
	public static function get_plugin_id() {
		return self::$plugin;
	}
	
	/**
	 * 单例模式
	 */
	public static function singleton($class_name) {
		if (!$class_name) return false;
		$key = strtolower($class_name);
		if (isset(self::$_objects[$key])) return self::$_objects[$key];
        if (strpos($key, 'content_') === 0) {
            // 表示是内容模型
            self::$_objects[$key] = new ContentModel();
            self::$_objects[$key]->table_name = self::$_objects[$key]->prefix.str_replace('model', '', $key);
            return self::$_objects[$key];
        } elseif (strpos($key, 'form_') === 0) {
            // 表示是表单模型
            self::$_objects[$key] = new FormModel();
            self::$_objects[$key]->table_name = self::$_objects[$key]->prefix.str_replace('model', '', $key);
            return self::$_objects[$key];
        } elseif (strpos($key, 'member_') === 0) {
            // 表示是会员模型
            self::$_objects[$key] = new MemberModel();
            self::$_objects[$key]->table_name = self::$_objects[$key]->prefix.str_replace('model', '', $key);
            return self::$_objects[$key];
        } else {
		    return self::$_objects[$key] = new $class_name();
        }
	}
	
	/**
	 * 返回应用模型的唯一实例(单例模式)
	 */
    public static function plugin_model($plugin, $table_name) {
	    if (!$table_name || !$plugin) return false;
		$model_name = ucfirst(strtolower($table_name)) . 'Model';
	    $model_file = PLUGIN_DIR . $plugin . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $model_name . '.php';
	    if (!is_file($model_file)) Controller::halt('The pluginModel(#' . $plugin . ') file:' . $model_name . '.php is not exists!');
	    $key  = strtolower($model_name);
	    if (isset(self::$_objects[$key])) return self::$_objects[$key];
	    require $model_file;
		return self::$_objects[$key] = new $model_name();
	}
	
	/**
	 * 静态加载文件
	 */
	public static function load_file($file_name) {
		if (!$file_name) return false;
		if (!isset(self::$_inc_files[$file_name]) || self::$_inc_files[$file_name] == false) {
			if (!is_file($file_name)) Controller::halt('The file:' . $file_name . ' not found!');
			include_once $file_name;
			self::$_inc_files[$file_name] = true;
		}
		return self::$_inc_files[$file_name];
	}
}

/**
 * URL函数
 */
function url($route, $params = null) {

    if(file_exists(APP_ROOT.'cache\member.lock'))
    {
        $domain = explode(';',file_get_contents(APP_ROOT.'cache\member.lock'));
        if($domain[1] && $route == 'api/user') {
            $domain = 'http://' . str_replace('http://', '', $domain[1]) . '/member/index.php?c=api&m=userinfo';
            return $domain;
        }
    }

    if (!isset($params['siteid']) && isset($_GET['siteid']) && substr_count($route, '/') >= 2)
    {
        //站点判断(前端控制器不带该参数)
        $params['siteid'] = (int)$_GET['siteid'];
        $params = array_reverse($params);
    }
    return Controller::create_url($route, $params);
}

function iurl($route, $params) {
    return url($route, $params);
}

/**
 * 应用中的URL函数
 */
function purl($route, $params = '') {
	return url(App::get_namespace_id() . '/' . $route, $params);
}

/**
 * 语言调用函数
 */
function lang($name, $data = '') {
    $language = App::get_language();
	$string = isset($language[$name]) ? $language[$name] : $name;
	if ($data) {
		foreach ($data as $r => $t) {
			$string = str_replace('{' . $r . '}', $t, $string);
		}
	}
	return $string;
}

function ilang($name, $data = '') {
    return lang($name, $data);
}

/**
 * 程序执行时间
 */
function runtime() {
	$temptime = explode(' ', SYS_START_TIME);
	$time     = $temptime[1] + $temptime[0];
	$temptime = explode(' ', microtime());
	$now      = $temptime[1] + $temptime[0];
	return number_format($now - $time, 6);
}

spl_autoload_register(array('App', 'auto_load'));