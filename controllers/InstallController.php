<?php
/**
 * 系统安装
 * @author Administrator
 */

class InstallController extends Controller {

    public $mysqli;

	public function __construct() {

        define('SYS_LANGUAGE',	'zh-cn');	//网站语言设置
        define('LANGUAGE_DIR',	EXTENSION_DIR . 'language' . DIRECTORY_SEPARATOR . SYS_LANGUAGE . DIRECTORY_SEPARATOR);	//网站语言文件
        $language = require LANGUAGE_DIR . 'lang.php';
        if (file_exists(LANGUAGE_DIR . 'custom.php')) {	//如果存在自定义语言包，则引入
            $custom_lang = require LANGUAGE_DIR . 'custom.php';
            $language = array_merge($language, $custom_lang);	//若有重复，自定义语言会覆盖系统语言
        }
        App::$language = $language;

		if (!is_writable(APP_ROOT . './cache')) {
		    $message = '系统缓存目录（/cache/）没有读写权限，安装程序无法进行！';
		    include SYS_ROOT . 'html/message.php';
		    exit;
		}
        $this->view = new View();
        $cms = Controller::load_config('version');
        define('SITE_PATH',  self::get_base_url());
        define('CMS_NAME', $cms['name']);
        define('CMS_VERSION', $cms['version']);
		define('SITE_THEME', self::get_theme_url());
		define('SYS_THEME_DIR', 'admin/');
		define('ADMIN_THEME', SITE_PATH . basename(VIEW_DIR) . '/admin/');
        define('LANG_PATH',	SITE_PATH . EXTENSION_PATH . '/language/' . SYS_LANGUAGE . '/');
		if (file_exists(APP_ROOT . './cache/install.lock')) {
            $message = '安装程序已经被锁定，<br/>如果需要解除锁定继续安装<br/>请删除 ./cache/install.lock 文件';
            include SYS_ROOT . 'html/message.php';
            exit;
        }
		App::auto_load('function');

        if (function_exists('mysqli_init')) {
            $this->mysqli = mysqli_init();
        } else {
            $this->mysqli = 0;
        }
	}
	
	public function indexAction() {
	    $step = $this->post('step') ? $this->post('step') : 1;
		switch($step) {
			case '1'://说明
				$this->view->assign('percent', '0%');
				$this->view->display('../install/' . $step);
			break;
			case '2'://环境
				$pass = true;
				$PHP_VERSION = PHP_VERSION;
				if (version_compare($PHP_VERSION, '5.2.0', '<')) {
					$php_pass = $pass = false;
				} else {
					$php_pass = true;
				}

                $mysqli = $PHP_MYSQL = '';
				if (extension_loaded('mysql')) {
					$PHP_MYSQL = '支持';
					$mysql_pass = true;
				} elseif(function_exists('mysqli_init')) {
                    $PHP_MYSQL = 'mysqli';
                    $mysqli = mysqli_init();
                    $mysql_pass = true;
                } else {
                    $PHP_MYSQL = '不支持';
                    $mysql_pass = $pass = false;
                }

				$PHP_GD = '';
				if (function_exists('imagejpeg')) {
                    $PHP_GD .= 'jpg';
                }
				if (function_exists('imagegif')) {
                    $PHP_GD .= ' gif';
                }
				if (function_exists('imagepng')) {
                    $PHP_GD .= ' png';
                }
				$gd_pass = $PHP_GD ? true : false;
				$is_json = false;
				$json = '["ok","t2","t3"]';
				if (function_exists('json_decode')) {
				    $json_data = json_decode($json);
				    if ($json_data) {
					    $is_json = true;
					} else {
					    $pass = false;
					}
				} else {
				    $json_data = null;
				    $pass = false;
				}
				$this->view->assign(array(
				    'php_pass' => $php_pass,
					'PHP_MYSQL' => $PHP_MYSQL,
					'mysql_pass' => $mysql_pass,
					'PHP_GD' => $PHP_GD,
					'gd_pass' => $gd_pass,
					'pass'  => $pass,
					'percent' => '20%',
					'urlopen' => fn_check_url(),
					'is_json' => $is_json,
					'json_data' => $json_data,
				));
				$this->view->display('../install/' . $step);
			break;
			case '3'://属性
				$ISWIN = strpos(strtoupper(PHP_OS), 'WIN') === false ? false : true;
				$files = array(
				    'cache/',
					'config/',
					'models/',
					'uploadfiles/',
				);
				$FILES = array();
				$pass  = true;
				foreach($files as $k=>$v) {
					$FILES[$k]['name'] = $v;
					if(is_writable(APP_ROOT . $v)) {
						$FILES[$k]['write'] = true;
					} else {
						$FILES[$k]['write'] = $pass = false;
					}
				}
				$this->view->assign(array(
				    'ISWIN'   => $ISWIN,
					'files'   => $files,
					'FILES'   => $FILES,
					'pass'    => $pass,
					'percent' => '40%',
				));
				$this->view->display('../install/' . $step);
			break;
			case '4'://数据库
				$this->view->assign('percent', '60%');
				$this->view->display('../install/' . $step);
			break;
			case '5'://安装进度
			    list($tdb_host, $tdb_port) = explode(':', $this->post('db_host'));
                $tdb_port = $tdb_port ? $tdb_port : 3306;
				$tdb_user = $this->post('db_user');
				$tdb_pass = $this->post('db_pass');
				$tdb_name = $this->post('db_name');
				$ttb_pre = $this->post('tb_pre');
				$import = $this->post('import');
				$username = $this->post('username');
				$password = $this->post('password');
				$email = $this->post('email');
				function dexit($msg) {
					echo '<script>alert("' . $msg . '");window.history.back();</script>';
					exit;
				}
				if (!preg_match('/^[a-z0-9]+$/i', $username) || strlen($username) < 4) {
                    dexit('请填写正确的超级管理员户名');
                }
				if (strlen($password) < 4) {
                    dexit('超级管理员密码最少4位');
                }
                if ($this->mysqli) {
                    if (!@mysqli_real_connect($this->mysqli, $tdb_host, $tdb_user, $tdb_pass, '', $tdb_port)) {
                        dexit('无法连接到数据库服务器（'.$tdb_host.':'.$tdb_port.'），请检查配置');
                    }
                    $tdb_name or dexit('请填写数据库名');
                    if (!@mysqli_select_db($this->mysqli, $tdb_name)) {
                        if (!@mysqli_query('CREATE DATABASE '.$tdb_name)) {
                            dexit('指定的数据库不存在\n\n系统尝试创建失败，请通过其他方式建立数据库');
                        }
                        @mysqli_select_db($this->mysqli, $tdb_name);
                    }
                    @mysqli_query($this->mysqli, 'SET NAMES utf8');
                } else {
                    if (!@mysql_connect($tdb_host.':'.$tdb_port, $tdb_user, $tdb_pass)) {
                        dexit('无法连接到数据库服务器（'.$tdb_host.':'.$tdb_port.'），请检查配置');
                    }
                    $tdb_name or dexit('请填写数据库名');
                    if (!@mysql_select_db($tdb_name)) {
                        if (!@mysql_query('CREATE DATABASE $tdb_name')) {
                            dexit('指定的数据库不存在\n\n系统尝试创建失败，请通过其他方式建立数据库');
                        }
                    }
                    @mysql_query('SET NAMES utf8');
                }
				//保存配置文件
				$content  = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * 数据库配置信息" . PHP_EOL . " */" . PHP_EOL . "return array(" . PHP_EOL . PHP_EOL;
                $content .= "	'host'     => '" . $tdb_host . "', " . PHP_EOL;
				$content .= "	'username' => '" . $tdb_user . "', " . PHP_EOL;
				$content .= "	'password' => '" . $tdb_pass . "', " . PHP_EOL;
				$content .= "	'dbname'   => '" . $tdb_name . "', " . PHP_EOL;
				$content .= "	'prefix'   => '" . $ttb_pre . "', " . PHP_EOL;
				$content .= "	'charset'  => 'utf8', " . PHP_EOL;
				$content .= "	'port'     => '".$tdb_port."', " . PHP_EOL;
                $content .= PHP_EOL . ");";
                if (!file_put_contents(CONFIG_DIR . 'database.ini.php', $content)) {
                    dexit('数据库配置文件保存失败，请检查文件权限！');
                }
                //保存站点域名配置文件
                $site = "<?php" . PHP_EOL . "if (!defined('IN_IDEACMS')) exit();" . PHP_EOL . PHP_EOL . "/**" . PHP_EOL . " * 数据库配置信息" . PHP_EOL . " */" . PHP_EOL . "return array(" . PHP_EOL . PHP_EOL;
                $site.= "	'1'     => '" . strtolower($_SERVER['HTTP_HOST']) . "', " . PHP_EOL;
                $site.= PHP_EOL . ");";
                if (!file_put_contents(CONFIG_DIR . 'site.ini.php', $site)) {
                    dexit('站点配置文件保存失败，请检查文件权限！');
                }
				//导入表结构
				$salt = substr(md5(time()), 0, 10);
				$sql = file_get_contents(APP_ROOT . './cache/install/table.sql');
				$sql = str_replace(array('{username}', '{password}', '{salt}', '{pre}'), array($username, md5(md5($password).$salt.md5($password)), $salt, $ttb_pre), $sql);
				$this->installsql($sql);
                //导入演示数据
				if ($import) {
				    $sql = file_get_contents(APP_ROOT . './cache/install/data.sql');
					$sql = str_replace('{pre}', $ttb_pre, $sql);
				    $this->installsql($sql);
				}
				$this->view->assign(array(
				    'percent' => '80%',
					'username' => $username,
					'password' => $password,
					'msgs' => array(
						'保存系统配置..................',
						'数据库连接....................',
						'创建数据库....................',
						'创建数据表....................',
						'设置管理员....................',
						'安装系统模型..................',
						'更新模型缓存..................',
						'更新应用缓存..................',
						'更新会员模型..................',
					),
				));
				$this->view->display('../install/' . $step);
			break;
			case '6'://安装成功
				$cache = new cache_file();
				$cache->set('install', 1);
                file_put_contents(APP_ROOT . './cache/install.lock', time());
				$this->view->assign(array(
				    'percent'  => '100%',
					'username' => $this->post('username'),
					'password' => $this->post('password'),
				));
				$this->view->display('../install/' . $step);
			break;
			case 'db_test':
			    $tdb_host = $this->post('tdb_host');
				$tdb_user = $this->post('tdb_user');
				$tdb_pass = $this->post('tdb_pass');
				$tdb_name = $this->post('tdb_name');
				$ttb_pre  = $this->post('ttb_pre');
				$ttb_test = $this->post('ttb_test');
                if ($this->mysqli) {
                    if (!@mysqli_real_connect($this->mysqli, $tdb_host, $tdb_user, $tdb_pass)) {
                        exit("<script>alert('无法连接到数据库服务器，请检查配置');</script>");
                    }
                    if (!@mysqli_select_db($this->mysqli, $tdb_name)) {
                        if (!@mysqli_query('CREATE DATABASE '.$tdb_name)) {
                            exit("<script>alert('指定的数据库(".$tdb_name.")不存在，系统尝试创建失败，请通过其他方式建立数据库');</script>");
                        }
                        @mysqli_select_db($this->mysqli, $tdb_name);
                    }
                    @mysqli_query($this->mysqli, 'SET NAMES utf8');
                } else {
                    if (!mysql_connect($tdb_host, $tdb_user, $tdb_pass)) {
                        exit("<script>alert('无法连接到数据库服务器，请检查配置');</script>");
                    }
                    if (!mysql_select_db($tdb_name)) {
                        if (!mysql_query("CREATE DATABASE " . $tdb_name)) {
                            exit("<script>alert('指定的数据库(".$tdb_name.")不存在，系统尝试创建失败，请通过其他方式建立数据库');</script>");
                        }
                        mysql_select_db($tdb_name);
                    }
                }
				$tables = array();
				$query = mysql_list_tables($tdb_name);
				while ($r = mysql_fetch_row($query)) {
					$tables[] = $r[0];
				}
				if (is_array($tables) && in_array($ttb_pre . 'user', $tables)) {
					if($ttb_test) {
						exit('<script>alert("数据库设置正确，连接正常\n\n注意：系统检测到您已经安装过' . CMS_NAME . '，如果继续安装将会清空现有数据\n\n如果需要保留现有数据，请修改数据表前缀");</script>');
					} else {
						exit('<script>alert("警告：系统检测到您已经安装过' . CMS_NAME . '，如果继续安装将会清空现有数据\n\n如果需要保留现有数据，请修改数据表前缀");</script>');
					}			
				}
				if ($ttb_test) exit('<script>alert("数据库设置正确，连接正常");</script>');
			break;
		}
	} 
	
	//执行sql语句
	private function installsql($sql) {
		$sql = str_replace(array(PHP_EOL, chr(13), chr(10)), 'SQL_FINECMS_EOL', $sql);
		$ret = array();
		$num = 0;
		$data = explode(';SQL_FINECMS_EOL', trim($sql));
		foreach($data as $query){
			$queries = explode('SQL_FINECMS_EOL', trim($query)); 
			foreach($queries as $query) {
				$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query; 
			} $num++; 
		}
		unset($sql); 
		foreach($ret as $query) {  
			if(trim($query)) {
                if ($this->mysqli) {
                    mysqli_query($this->mysqli, $query);
                } else {
                    mysql_query($query) or die($this->halt('数据导入出错<hr>' . mysql_error() . '<br>SQL语句：<br>' . $query));
                }
			} 
		}
	}
	
}