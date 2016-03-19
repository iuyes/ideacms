<?php
/**
 * mysql数据库驱动,完成对mysql数据库的操作
 */

if (!defined('IN_IDEACMS')) exit();

class mysql extends Ia_base {
	
	public $db_link;
	public static $instance;
	public static $instance_slave;
	
	/**
	 * 构造函数
	 */
	public function __construct($params) {
		if (!$params['host'] || !$params['username'] || !$params['dbname']) Controller::halt('Mysql数据库配置文件不完整'); //检测参数信息是否完整
		if ($params['port'] && $params['port'] != 3306) $params['host'] .= ':' . $params['port']; //处理数据库端口
		$this->db_link = @mysql_connect($params['host'], $params['username'], $params['password']); //实例化mysql连接ID
		if (!$this->db_link) Controller::halt('Mysql服务器(' . $params['host'] . ')连接失败 <br/>Error Message:' . (function_exists('iconv') ? iconv('GBK', 'UTF-8', mysql_error()) : mysql_error()) . '<br/>Error Code:' . mysql_errno(), 'Warning');
		if (!mysql_select_db($params['dbname'], $this->db_link)) Controller::halt('不能连接到数据库<br/>' . (function_exists('iconv') ? iconv('GBK', 'UTF-8', mysql_error()) : mysql_error()) . ' Error Message:' . mysql_error(), 'Warning');
		mysql_query("SET NAMES UTF8", $this->db_link); //设置数据库编码
		if (version_compare($this->get_server_info(), '5.0.2', '>=')) mysql_query("SET SESSION SQL_MODE=''", $this->db_link);
		return true;
	}
	
	/**
	 * 执行SQL语句
	 */
	public function query($sql, $link = null) {
		$link	= $link ? $link : $this->db_link;
		$result = mysql_query($sql, $link);
		//file_put_contents('sql.txt', $sql . PHP_EOL, FILE_APPEND);
		//日志操作,当调试模式开启时,将所执行过的SQL写入SQL跟踪日志文件,便于DBA进行MYSQL优化。若调试模式关闭,当SQL语句执行错误时写入日志文件
		if (SYS_DEBUG === false) {
			if ($result == false) {
				//获取当前运行的namespace、controller及action名称
				$action_id		= App::get_action_id();
				$namespace_id	= App::get_namespace_id();
				$controller_id	= App::get_controller_id();
				$namespace_code = $namespace_id ? '[' . $namespace_id . ']' : '';
				if (SYS_LOG === true) Log::write($namespace_code . '[' . $controller_id . '][' . $action_id . '] SQL execute failed :' . $sql . ' Error Code:' . $this->errno() . 'Error Message:' . $this->error());
			}			
		} else {
			//获取当前运行的namespace、controller及action名称
			$action_id		= App::get_action_id();
			$namespace_id	= App::get_namespace_id();
			$controller_id	= App::get_controller_id();
			$sql_log_file	= APP_ROOT . 'logs' . DIRECTORY_SEPARATOR . 'SQL_' . date('Y_m_d', $_SERVER['REQUEST_TIME']) . '.log';
			$namespace_code	= $namespace_id ? '[' . $namespace_id . ']' : '';
			if ($result == true) {
				if (SYS_LOG === true) Log::write($namespace_code . '[' . $controller_id . '][' . $action_id . ']:' . $sql, 'Normal', $sql_log_file);
			} else {
				Controller::halt($namespace_code . '[' . $controller_id . '][' . $action_id . '] SQL execute failed :' . $sql . '<br/>Error Message:' . $this->error() . '<br/>Error Code:'.$this->errno(). '<br/>Error SQL:' . $sql);
			} 
		}
		return $result;
	}
	
	/**
	 * 获取mysql数据库服务器信息
	 */
	public function get_server_info() {
		return mysql_get_server_info();
	}
	
	/**
	 * 获取mysql错误描述信息
	 */
	public function error() {
		return function_exists('iconv') ? iconv('GBK', 'UTF-8', mysql_error()) : mysql_error();
	}
	
	/**
	 * 获取mysql错误信息代码
	 */
	public function errno() {
		return mysql_errno();
	}
	
	/**
	 * 通过一个SQL语句获取一行信息(字段型)
	 */
	public function fetch_row($sql) {
		if (strtolower(substr($sql, 0, 6)) == 'select' && !stripos($sql, 'limit') !== false) $sql .= ' LIMIT 1';
		$result = $this->query($sql);
		if (!$result) return false;
		$rows   = mysql_fetch_assoc($result);
		mysql_free_result($result);
		return $rows;
	}
	
	/**
	 * 通过一个SQL语句获取全部信息(字段型)
	 */
	public function get_array($sql) {
		$result = $this->query($sql);
		if (!$result)return false;
		$myrow  = array();
		while ($row = mysql_fetch_assoc($result)) {
			$myrow[] = $row;
		}
		mysql_free_result($result);
		return $myrow;
	}
	
	/**
	 * 获取insert_id
	 */
	public function insert_id() {
		return ($id = mysql_insert_id($this->db_link)) >= 0 ? $id : mysql_result($this->query("SELECT last_insert_id()"));
	}
	
	/**
	 * 字段的数量
	 */
	public function num_fields($sql) {
		$result = $this->query($sql);
		return mysql_num_fields($result);
	}
	
	/**
	 * 结果集中的数量
	 */
	public function num_rows($sql) {
		$result = $this->query($sql);
		return mysql_num_rows($result);
	}
	
	/**
	 * 获取字段类型
	 */
	public function get_fields_type($table_name) {
	    if (!$table_name) return false;
		$res   = mysql_query("SELECT * FROM {$table_name} ORDER BY NULL LIMIT 1");
		$types = array();
		while ($row = mysql_fetch_field($res)) {
		    $types[$row->name] = $row->type;
		}
		mysql_free_result($res);
		return $types;
	}
	
	/**
	 * 析构函数
	 */
	public function __destruct() {
		if ($this->db_link) @mysql_close($this->db_link);
	}
	
	/**
	 * 单例模式
	 */
	public static function getInstance($params) {
		if (!self::$instance) {			
			self::$instance = new self($params);
		}
		return self::$instance;
	}
	
	/**
	 * 单例模式
	 */
	public static function getInstance_slave($params) {
		if (!self::$instance_slave) {			
			self::$instance_slave = new self($params);
		}
		return self::$instance_slave;
	}
}