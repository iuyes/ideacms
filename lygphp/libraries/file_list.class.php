<?php
/**
 * file_list class file
 * 用于文件夹内容的读取,复制,剪切等操作
 */

if (!defined('IN_IDEACMS')) exit();

class file_list extends Ia_base {
	
	/**
	 * 构造函数
	 * 
	 * @return void
	 */
	public function __construct() {
		
	}
	
	/**
	 * 分析文件夹是否存在
	 * 
	 * @param string $dir_name 所要操作的文件目录名
	 * @return string
	 */
	protected static function parse_dir($dir_name) {	
		if (!is_dir($dir_name)) return false;
		if (substr($dir_name, -1) !== '/' || substr($dir_name, -1) !== '\\') return $dir_name . DIRECTORY_SEPARATOR;
		return $dir_name;
	}
	
	/**
	 * 分析目标目录的读写权限
	 * 
	 * @access public
	 * @param string $dir_name	目标目录
	 * @return string
	 */
	public static function make_dir($dir_name) {
		if (!is_dir($dir_name)) {			
			mkdir($dir_name, 0777);
		} else {
			if (!is_writable($dir_name)) {
				chmod($dir_name, 0777);
			}
		}
		return $dir_name;
	}
	
	/**
	 * 获取目录内文件
	 * 
	 * @param string $dir_name	所要读取内容的目录名
	 * @return string
	 */
	public static function get_file_list($dir_name) {
		$dir    = self::parse_dir($dir_name);
		if (!$dir) return null;
		$handle = opendir($dir);
		$files  = array();
		while (false !== ($file = readdir($handle))) {			
			if ($file == '.' || $file == '..' || $file == '.cvs' || $file == '.svn') continue;
            $id = filectime($dir . $file);
			$id && !isset($files[$id]) ? $files[$id] = $file : $files[] = $file;
		}
		closedir($handle);
		asort($files);
		return $files;
	}

	/**
	 * 将一个文件夹内容复制到另一个文件夹
	 * 
	 * @param string $source	被复制的文件夹名
	 * @param string $dest		所要复制文件的目标文件夹
	 * @return boolean
	 */
	public static function copy_dir($source, $dest) {
		if (!$source || !$dest) return false;
		$parse_dir = self::parse_dir($source);
		$dest_dir  = self::make_dir($dest);
		$file_list = self::get_file_list($parse_dir);
		foreach ($file_list as $file) {			
			if (is_dir($parse_dir . '/' . $file)) {				
				self::copy_dir($parse_dir . '/' . $file, $dest_dir . '/' . $file);
			} else {				
				copy($parse_dir . '/' . $file, $dest_dir . '/' . $file);
			}
		}
		return true;
	}

	/**
	 * 移动文件夹, 相当于WIN下的ctr+x(剪切操作)
	 * 
	 * @param string $source	原目录名
	 * @param string $dest		目标目录
	 * @return boolean
	 */
	public static function move_dir($source, $dest) {
		if (!$source || !$dest) return false;
		$parse_dir = self::parse_dir($source);
		$dest_dir  = self::make_dir($dest);
		$file_list = self::get_file_list($parse_dir);
		foreach ($file_list as $file) {			
			if (is_dir($parse_dir . '/' . $file)) {				
				self::move_dir($parse_dir . '/' . $file, $dest_dir . '/' . $file);
			} else {				
				if (copy($parse_dir . '/' . $file, $dest_dir . '/' . $file)) {					
					unlink($parse_dir . '/' . $file);
				}	
			}
		}
		rmdir($parse_dir);
		return true;
	}
	
	/**
	 * 删除文件夹
	 * 
	 * @param string $file_dir	所要删除文件的路径
	 * @return boolean
	 */
	public static function delete_dir($file_dir) {
		if (!$file_dir) return false;
		$parse_dir = self::parse_dir($file_dir);
		$file_list = self::get_file_list($parse_dir);
		foreach ($file_list as $file) {			
			if (is_dir($parse_dir . '/' . $file)) {						
				self::delete_dir($parse_dir . '/' . $file);
				rmdir($parse_dir . '/' . $file);
			} else {			
				unlink($parse_dir . '/' . $file);
			}
		}	
		return true;
	}
}