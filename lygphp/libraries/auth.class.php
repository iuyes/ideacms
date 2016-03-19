<?php
/**
 * auth class file
 * 权限验证
 */

if (!defined('IN_IDEACMS')) {
	exit();
}

class auth extends Ia_base {
	
	public static function check($groupid, $action, $namespace="defalut") {
        $action = strtolower($action);
        $namespace = strtolower($namespace);
        if ($groupid == 1) {
            return true;
        }
	    //跳过不需要验证的模块
	    if (self::skip($action, $namespace)) {
            return true;
        }
	    $rules = self::get_role($groupid);
	    if (empty($rules)) {
            false;
        }
	    list($c, $m) = explode("-", $action);
	    if (@in_array($c, $rules)) {
	        return true;
	    } elseif (@in_array($action, $rules)) {
	        return true;
	    } else {
	        //无权限操作
	        return false;
	    }
	}
	
	public static function get_role($groupid) {
	    //加载权限分配文件
	    $config_file = CONFIG_DIR . "auth.role.ini.php";
	    if (!is_file($config_file)) {
            return null;
        }
	    $config = require $config_file;
	    return $groupid && isset($config[$groupid]) ? $config[$groupid] : null;
	}
	
	public static function skip($action, $namespace="defalut") {
	    //controller和action
	    list($c, $m) = explode("-", $action);
	    if (stripos($m, "ajax")!==false) {
            return true;
        }
	    //加载不需要权限验证的配置文件
	    $config_file = CONFIG_DIR . "auth.skip.ini.php";
	    if (!is_file($config_file)) {
            return false;
        }
	    $config = require $config_file;
	    $skip = $namespace && isset($config[$namespace]) ?  $config[$namespace] : $config['defalut'];
	    if (empty($skip)) {
            return true;
        } //配置文件中没有内容，直接跳过验证
	    if (in_array($c, $skip)) {
	        //跳过
	        return true;
	    } elseif (in_array($action, $skip)) {
	        //跳过
	        return true;
	    } else {
	        return false;
	    }
	}
	
}