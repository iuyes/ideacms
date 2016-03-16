<?php

/**
 * 临时兼容性文件 （Free v2.0.0 以下版本有效）
 */

if (!defined('IN_IDEACMS')) exit();

abstract class Controller extends Fn_base {
    
	public $view;
	public static $_options = array();

	
	/**
	 * 记录$_GET中的非法字符
	 */
	public static function check_Get($var) {
	    if ($_GET['c'] == 'api' && $_GET['a'] == 'search') return false;
		static $cfg = null, $get_var = null;
		if (isset($get_var[$var])) return false;
		$get_var[$var] = true; //防止多次判断同一参数
        $get = isset($_GET[$var]) ? $_GET[$var] : null;
		$cfg = is_array($cfg) ? $cfg : self::load_config('attackcode');
		$bad = $cfg['get'];
		if (empty($get) || empty($bad)) return null;
        foreach ($bad as $t) {
            if (substr_count(strtolower($get), $t) > 0) self::save_attack_log('GET', $get);
        }
	}
	
	/**
	 * 记录$_POST中的非法字符
	 */
	public static function check_Post($var, $a=0) {
	    static $cfg = null;
	    $post = $a ? $var : (isset($_POST[$var]) ? $_POST[$var] : null);
		$cfg  = is_array($cfg) ? $cfg : self::load_config('attackcode');
		$bad  = $cfg['post'];
		if (empty($post) || empty($bad)) return null;
        foreach ($bad as $t) {
            if (substr_count(strtolower($post), $t) > 0) self::save_attack_log('POST', $post);
        }
	}
	
	/**
	 * 保存非法字符攻击日志
	 */
	private static function save_attack_log($type, $val) {
		$cfg = App::get_config();
	    if ($cfg['SYS_ATTACK_LOG']) {
			if (SYS_DOMAIN) $_SERVER['REQUEST_URI'] = str_replace('/' . SYS_DOMAIN, '', $_SERVER['REQUEST_URI']);
			$data = array(
				'url'  => isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : $_SERVER['REQUEST_URI'],
				'ip'   => client::get_user_ip(),
				'uid'  => get_cookie('member_id'),
				'time' => time(),
				'type' => $type,
				'val'  => $val,
				'user' => $_SERVER['HTTP_USER_AGENT'],
			);
			$dir  = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'attack' . DIRECTORY_SEPARATOR;
			$file = $dir . date('Ymd') . '.log';
			if (!is_dir($dir)) mkdir($dir, 0777);
			$body = file_exists($file) ? file_get_contents($file) : null;
			if ($body) {
				$fdata = explode(PHP_EOL, $body);
				$idata = 0;
				foreach ($fdata as $v) {
					if (empty($v)) continue;
					$t = unserialize($v);
					if ($data['ip'] == $t['ip']) $idata ++;
					//若Ip出现10次以上，直接禁止不再保存提醒
					//相同地址在20秒内都含有非法字符，直接禁止不再保存提醒
					if ($idata >= 10 || ($data['time'] - $t['time'] < 20 && $data['user'] == $t['user'] && $data['ip'] == $t['ip'] && $data['url'] == $t['url'])) {
					    if ($cfg['SYS_ILLEGAL_CHAR']) App::display_error(lang('app-10') . '<pre>' . htmlspecialchars(self::strip_slashes($val)) . '</pre>', 1);
						unset($cfg);
						return false;
					}
				}
				unset($fadta);
			}
			$body = serialize($data) . PHP_EOL . $body;
			file_put_contents($file, $body, LOCK_EX);
			if ($data['ip'] && $cfg['SYS_ATTACK_MAIL'] && check::is_email($cfg['SITE_SYSMAIL'])) {
				//发送邮件至管理员
				mail::set($cfg);
				$body = '------------------------------------------------------------------------------------------<br>' .
				'SITE: ' . SITE_URL .
				'<br>URL: ' . $data['url'] .
				'<br>TYPE: ' . $data['type'] .
				'<br>VALUE: ' . $data['val'] .
				'<br>IP: ' . $data['ip'] .
				'<br>TIME: ' . date(TIME_FORMAT, $data['time']) .
				'<br>USER: ' . $data['user'] .
				'<br>------------------------------------------------------------------------------------------<br>' .
				lang('a-cfg-6') . '<br>';
				mail::sendmail($cfg['SITE_SYSMAIL'], lang('a-cfg-5') . '-' . $cfg['SITE_NAME'], $body);
			}
		}
		if ($cfg['SYS_ILLEGAL_CHAR']) App::display_error(lang('app-10') . '<pre>' . htmlspecialchars(self::strip_slashes($val)) . '</pre>', 1);
		unset($cfg);
	}
	
	/**
	 * 获取并分析$_GET数组某参数值
	 */
	public static function get($string) {
		$name = isset($_GET[$string]) ? $_GET[$string] : null;
		if (!is_array($name)) {
		    self::check_Get($string);
			return htmlspecialchars(trim($name));
		}
		return null;
	}
	
	/**
	 * 获取并分析$_POST数组某参数值
	 */
	public static function post($string, $a=0) {
		$name = $a ? $string : (isset($_POST[$string]) ? $_POST[$string] : null);
		if (is_null($name)) return null;
		if (!is_array($name)) {
		    self::check_Post($string, $a);
		    return htmlspecialchars(trim($name));
		}
	    foreach ($name as $key=>$value) {
            $post_array[$key] = self::post($value, 1);
		}
		return $post_array;
	}
	
	/**
	 * 验证表单是否POST提交
	 */
	public static function isPostForm($var='submit', $emp=0) {
		if ($emp) {
		    if (!isset($_POST[$var]) && empty($_POST[$var])) return false;
		} else {
		    if (!isset($_POST[$var])) return false;
		}
		return true;
	}
	
	/**
	 * 获取并分析 $_GET或$_POST全局超级变量数组某参数的值
	 */
	public static function get_params($string) {
		$param_value = self::post($string);
		//当$_POST[$string]值没空时
		return empty($param_value) ? self::get($string) : $param_value;
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
	public static function halt($message, $level = 'Error') {
		if (empty($message)) return false;
		$trace 			= debug_backtrace();
		$source_file 	= $trace[0]['file'] . '(' . $trace[0]['line'] . ')';
		$trace_string 	= '';
		foreach ($trace as $key=>$t) {
			$trace_string .= '#'. $key . ' ' . $t['file'] . '('. $t['line'] . ')' . $t['class'] . $t['type'] . $t['function'] . '(' . implode('.',  $t['args']) . ')<br/>';			
		}
		include_once SYS_ROOT . 'html/exception.php';	
		if (SYS_LOG === true) Log::write($message, $level);
		exit();
	}
	
	/**
	 * 获取当前运行程序的网址域名
	 */
	public static function get_server_name() {
		$server_name = strtolower($_SERVER['SERVER_NAME']);
		$server_port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':' . (int)$_SERVER['SERVER_PORT'];
		$secure      = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;		
		return ($secure ? 'https://' : 'http://') . $server_name . $server_port;
	}
	
	/**
	 * 获取当前项目的根目录的URL
	 */
	public static function get_base_url() {
		$url = str_replace(array('\\', '//'), '/', dirname($_SERVER['SCRIPT_NAME']));
		return (substr($url, -1) == '/') ? $url : $url . '/'; //URL以反斜杠("/")结尾
	}
	
	/**
	 * 获取当前运行的Action的URL
	 */
	public static function get_self_url() {
		return self::create_url(App::get_controller_id() . URL_SEGEMENTATION . App::get_action_id());
	}
	
	/**
	 * 获取当前Controller内的某Action的URL
	 */
	public static function get_action_url($action_name) {
		if (empty($action_name)) return false;
		return self::create_url(App::get_controller_id() . URL_SEGEMENTATION . $action_name);
	}
	
	/**
	 * 获取当前项目themes目录的URL
	 */
	public static function get_theme_url(){
		if (defined('SITE_DIR')) {
			return self::get_base_url();
		}
		return self::get_base_url() . basename(VIEW_DIR) .'/' . basename(SYS_THEME_DIR) . '/';
	}
	
	/**
	 * 网址(URL)跳转操作
	 */
	public function redirect($url){
		if (!$url) return false;
		if (!headers_sent()) {
			header("Location:" . $url);	
		}else {
			echo '<script type="text/javascript">location.href="' . $url . '";</script>';
		}
		exit();
	}
	
	/**
	 * 网址(URL)组装操作,组装绝对路径的URL
	 */
	public static function create_url($route, $params = null) {
		if (!$route) return false;
		$arr   = explode(URL_SEGEMENTATION, $route);
		$arr   = array_diff($arr, array(''));
		$count = count($arr);
		$url   = ENTRY_SCRIPT_NAME;
		if (is_dir(CONTROLLER_DIR . $arr[0]) || is_dir(PLUGIN_DIR . $arr[0])) {
			$url .= '?s=' . (strtolower($arr[0]) == 'admin' ? ADMIN_NAMESPACE : strtolower($arr[0]));
			if (isset($arr[1]) && $arr[1]) {
			    if ($arr[1] != strtolower(DEFAULT_CONTROLLER)) $url .= '&c=' . strtolower($arr[1]);
				if (isset($arr[2]) && $arr[2] && $arr[2] != DEFAULT_ACTION) {
				    $url .= '&a=' . strtolower($arr[2]);
				}
			}
		} else {
		    if (isset($arr[0]) && $arr[0]) {
			    if ($arr[0] != strtolower(DEFAULT_CONTROLLER)) $url .= '?c=' . strtolower($arr[0]);
				if (isset($arr[1]) && $arr[1] && $arr[1] != DEFAULT_ACTION) {
				    $url .= '&a=' . strtolower($arr[1]);
				}
			}
		}
		//参数$params变量的键(key),值(value)的URL组装
		if (!is_null($params) && is_array($params)) {
 			$params_url = array();							
			foreach ($params as $key=>$value) {
				$params_url[] = trim($key) . '=' . trim($value);
			}
			$url = ($url == 'index.php' ? 'index.php?' : $url . '&') . implode('&', $params_url);
		}
		$url = str_replace('//', URL_SEGEMENTATION, $url);	
		return self::get_base_url() . $url;
	}
	
	/**
	 * 类的单例实例化操作
	 */
	public static function instance($class_name) {
		if (!$class_name) return false;
		return App::singleton($class_name);
	}
	
	/**
	 * 单例模式实例化一个Model对象
	 */
	public static function model($table_name) {
		if (!$table_name) return false;
		$model_name = ucfirst(strtolower($table_name)) . 'Model';
		return App::singleton($model_name);
	}
	
	/**
	 * 单例模式实例化一个应用Model对象
	 */
    public static function plugin_model($plugin, $table_name) {
	    return App::plugin_model($plugin, $table_name);
	}
	
	/**
	 * 静态加载文件 
	 */
	public static function import($file_name) {
		if (!$file_name) return false;
		$file_url = (strpos($file_name, '/') !== false) ? realpath($file_name) : realpath(EXTENSION_DIR . $file_name . '.class.php');
		return App::load_file($file_url);
	}
	
	/**
	 * 静态加载项目设置目录(config目录)中的配置文件
	 */
	public static function load_config($file_name) {
		if (!$file_name) return false;
		static $_config = array();
		if (!isset($_config[$file_name]) || $_config[$file_name] == null) {					
			$file_url = CONFIG_DIR . $file_name . '.ini.php';
			if (!is_file($file_url)) App::display_error(lang('app-11', array('1'=>$file_name)));
			$_config[$file_name] = include $file_url;
		}
		return $_config[$file_name];
	}
	
	/**
	 * stripslashes
	 */
	public static function strip_slashes($string) {
		if (!$string) return false;
		if (!is_array($string)) return stripslashes($string);
		foreach ($string as $key=>$value) {					
			$string[$key] = self::strip_slashes($value);
		}
		return $string;
	}
	
	/**
	 * addslashes
	 */
	public static function add_slashes($string) {
		if (!$string && is_null($string)) return false;
		if (!is_array($string)) return addslashes($string);
		foreach ($string as $key=>$value) {				
			$string[$key] = self::add_slashes($value);
		}
		return $string;
	}
	
	/**
	 * 加载应用配置信息
	 */
    public static function load_plugin_setting($dir) {
	    $file = PLUGIN_DIR . $dir . DIRECTORY_SEPARATOR . 'config.php';
	    if (!file_exists($file)) return array();
	    return require $file;
	}
	

	
	/**
     * 获取URL参数
     */
	public static function getParam() {
	    $param = array();
		if (!isset($_GET) || empty($_GET)) return $param;
        $ci = &get_instance();
		foreach ($_GET as $n=>$v) {
			if (!in_array($n, array('s', 'a', 'c'))) {
				$param[$n] = $ci->input->get($n);
			}
		}
		return $param;
	}
	
}