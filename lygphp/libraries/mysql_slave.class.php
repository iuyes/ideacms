<?php
/**
 * mysql_slave数据库驱动(作用于读写分离)
 */

if (!defined('IN_IDEACMS')) exit();

class mysql_slave extends mysql {

	private $params;

	public function __construct($params, $slave_params) {
        parent::__construct($params);
		$this->params 	= $slave_params[array_rand($slave_params)];
		$this->params['dbname'] = $params['dbname'];
		$this->params['prefix'] = $params['prefix'];
	}
	
	public function query($sql) {
		if($this->params && strtoupper(substr($sql, 0 , 6)) == 'SELECT') {
			parent::getInstance_slave($this->params);
			return parent::query($sql, self::$instance_slave->db_link);
		} else {
			return parent::query($sql);
		}
		
	}
	
	/**
	 * 单例模式
	 */
	public static function getInstance($params, $slave_params) {
		if (!self::$instance) {			
			self::$instance = new self($params, $slave_params);
		}
		return self::$instance;
	}
	
}