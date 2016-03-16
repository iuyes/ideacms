<?php

/**
 * index.php 入口文件
 */
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_STRICT);


@date_default_timezone_set('Asia/Shanghai'); // 解决时区
define('IN_IDEACMS', true);
define('APP_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('EXT', '.php');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('FCPATH', str_replace(SELF, '', __FILE__));
define('APPPATH', FCPATH.'extensions/');
define('BASEPATH', FCPATH.'lygphp/');
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

$config = require FCPATH.'config/config.ini.php';

/**
 * 配置
 */

define('SYS_ROOT', FCPATH.'lygphp'.DIRECTORY_SEPARATOR);  //核心文件所在路径
define('SYS_START_TIME', microtime(true));  //设置程序开始执行时间
define('CONTROLLER_DIR', APP_ROOT . 'controllers' . DIRECTORY_SEPARATOR);  //controller目录的路径
define('MODEL_DIR', APP_ROOT . 'models' . DIRECTORY_SEPARATOR);  //model目录的路径
define('VIEW_DIR', APP_ROOT . 'views' . DIRECTORY_SEPARATOR); //view目录的路径
define('CONFIG_DIR', APP_ROOT . 'config' . DIRECTORY_SEPARATOR);  //config目录的路径
define('EXTENSION_PATH', 'extensions'); //extension目录文件夹
define('EXTENSION_DIR', APP_ROOT . EXTENSION_PATH . DIRECTORY_SEPARATOR);  	//extension目录的路径
define('PLUGIN_DIR', APP_ROOT . 'plugins' . DIRECTORY_SEPARATOR);       	//应用目录文件夹
define('DEFAULT_CONTROLLER', 'Index');	//设置系统默认的controller名称,默认为:Index
define('DEFAULT_ACTION', 'index'); //设置系统默认的action名称,默认为index
define('SYS_LOG', $config['SYS_LOG']); //设置是否开启运行日志
define('SYS_DEBUG',	 $config['SYS_DEBUG']);  //设置是否开启调试模式.开启后,程序运行出现错误时,显示错误信息
define('URL_SEGEMENTATION', '/');  	//定义网址路由的分割符
define('ENTRY_SCRIPT_NAME', 'index.php');  	//定义入口文件名
define('SITE_MEMBER_COOKIE', $config['SITE_MEMBER_COOKIE']);  //会员登录Cookie随机字符码
define('SYS_COOKIE_DOMAIN', $config['SESSION_COOKIE_DOMAIN']);	 //跨域
define('SYS_DOMAIN', $config['SYS_DOMAIN']); //域名目录，针对虚拟主机用户
define('SYS_ATTACK_LOG', isset($config['SYS_ATTACK_LOG']) && $config['SYS_ATTACK_LOG'] ? $config['SYS_ATTACK_LOG'] : false);	//系统攻击日志开关
define('ADMIN_NAMESPACE', isset($config['ADMIN_NAMESPACE']) && $config['ADMIN_NAMESPACE'] ? $config['ADMIN_NAMESPACE'] : 'admin'); //定义后台管理路径的名字
define('SYS_VAR_PREX', isset($config['SYS_VAR_PREX']) && $config['SYS_VAR_PREX'] ? $config['SYS_VAR_PREX'] : 'idea_');	//SESSION和COOKIE变量前缀

/**
 * 环境参数
 */

if (function_exists('ini_set'))	{
    SYS_DEBUG ? ini_set('display_errors', true) : ini_set('display_errors', false);
    ini_set('memory_limit', '1024M');
    if (SYS_COOKIE_DOMAIN) {
        ini_set('session.cookie_domain', SYS_COOKIE_DOMAIN);
    }
}

require_once BASEPATH.'core/CodeIgniter.php';