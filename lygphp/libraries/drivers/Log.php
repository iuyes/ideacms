<?php

/**
 * 临时兼容性文件 （Free v2.0.0 以下版本有效）
 */

if (!defined('IN_IDEACMS')) exit();

class Log extends Fn_base {
	
	/**
	 * 写入日志
	 */
	public static function write($message, $level = 'Error', $log_file_name = null) {
		if (!$message) return false;
		if (is_null($log_file_name)) $log_file_name = APP_ROOT . 'logs/' . date('Y_m_d', $_SERVER['REQUEST_TIME']) . '.log';
		if (is_file($log_file_name) && filesize($log_file_name) >= 2097152) {			
			rename($log_file_name, APP_ROOT . 'logs/' . $_SERVER['REQUEST_TIME'] . '-' . basename($log_file_name));
		}
		error_log(date('[Y-m-d H:i:s]', $_SERVER['REQUEST_TIME']) . " {$level}: {$message}\r\n", 3, $log_file_name);
	}
	
	/**
	 * 显示日志内容
	 */ 
	public static function show($log_file_name = null) {
		$log_file_name =  is_null($log_file_name) ? APP_ROOT . 'logs/' . date('Y_m_d', $_SERVER['REQUEST_TIME']) . '.log' : APP_ROOT . 'logs/' . $log_file_name . '.log';
		$log_content = file_get_contents($log_file_name);
		$list_str_array = explode("\r\n", $log_content);
		unset($log_content);
		$total_lines = sizeof($list_str_array);
		echo '<table width="85%" border="0" cellpadding="0" cellspacing="1" style="background:#0478CB; font-size:12px; line-height:25px;">';
		foreach ($list_str_array as $key=>$lines_str) {
			if ($key == $total_lines - 1) continue;
			$bg_color = ($key % 2 == 0) ? '#FFFFFF' : '#C6E7FF';
			echo '<tr><td height="25" align="left" bgcolor="' . $bg_color .'">&nbsp;' . $lines_str . '</td></tr>';
		}
		echo '</table>';
	}
}