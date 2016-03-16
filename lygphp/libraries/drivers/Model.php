<?php

/**
 * 临时兼容性文件 （Free v2.0.0 以下版本有效）
 */

if (!defined('IN_IDEACMS')) exit();

abstract class Model {
    
	public	$ci;
	public	$prefix;
	public	$cache_dir;
	public	$primary_key;
    public	$db;
    public	$_parts;
    public	$dbname;
    public	$table_name;
    public	$field_type;
    public	$table_field;
	
	/**
	 * 构造函数
	 */
	public function __construct() {		
		//加载数据库配置文件
        $ci = &get_instance();
        $params = $ci->load_config('database');
        $this->db = $ci->db;
        $this->dbname = $params['dbname'];
        $this->prefix = $ci->db->dbprefix;
        $this->cache_dir = APP_ROOT . 'cache' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;
		return true;
	}
		
	/**
	 * 获取当前model所对应的数据表的名称
	 * 
	 * 注:若数据表有前缀($prefix)时，将自动加上数据表前缀。
	 * @access protected
	 * @return string	数据表名
	 */
	protected function get_table_name() {
		//当$this->table_name不存在时
		if (!$this->table_name) {
			//获取当前model的类名
			$model_id = substr(strtolower(get_class($this)), 0, -5);
			//分析数据表名，当有前缀时，加上前缀
			$this->table_name = !empty($this->prefix) ? $this->prefix . $model_id : $model_id;
		}
		return $this->table_name;
	}
	
	/**
	 * 获取数据表字段信息
	 * 
	 * 主键及所有字段信息,返回数据类型为数组
	 * @access protected
	 * @return array	字段信息
	 */
	protected function get_table_info() {
		$this->get_table_name();
		//查询数据表字段信息
		$sql = "SHOW FIELDS FROM {$this->table_name}";
		return $this->db->query($sql)->result_array();
	}
	
	/**
	 * 获取数据表主键
	 * 
	 * @access protected
	 * @return string	数据表主键
	 */
	protected function get_primary_key() {
		//当$this->primary_key内容为空时
		if (!$this->primary_key) {			
			//加载缓存文件不存在时,则创建缓存文件
			if (!$this->load_cache()) {
                $this->create_cache();
            }
		}
		return $this->primary_key;
	}
	
	/**
	 * 获取数据表字段信息
	 * 
	 * @access public
	 * @return array	数据表字段信息
	 */
	public function get_table_fields($is_news = false) {
        $this->load_cache();
		//当$this->table_field内容为空时,则加载model缓存文件
		if (!$this->table_field || $is_news) {
			//加载model缓存文件失败或缓存文件不存在时,创建model缓存文件
			if (!$this->load_cache()) {
                $this->create_cache();
            }
		}
		return $this->table_field;
	}
	
	/**
	 * 获取数据表字段类型
	 * 
	 * @access public
	 * @return array	数据表字段类型
	 */
	public function get_field_type($is_news = false) {
		//当$this->field_type,则加载model缓存文件
		if (!$this->field_type || $is_news) {
			//加载model缓存文件失败或缓存文件不存在时,创建model缓存文件
			if (!$this->load_cache()) $this->create_cache();
		}
		return $this->field_type;
	}

	
	/**
	 * 创建当前model的缓存文件
	 * 
	 * 用于创建当前model的缓存文件，用于减轻数据反复查询数据表字段信息的操作，从而提高程序的运行效率
	 * @access protected
	 * @return boolean
	 */
	protected function create_cache() {
		//获取当前model的缓存文件路径
		$cache_file = $this->parse_cache_file();
		//获取数据表字段信息
		$table_info = $this->get_table_info();
		$fields = array();
		$primary_key = array();
		foreach ($table_info as $lines) {
			//获取主键信息		
			if ($lines['Key'] == 'PRI') $primary_key[] = $lines['Field'];
			//获取字段信息
			$fields[] = $lines['Field'];
		}
		$this->primary_key = empty($primary_key) ? '' : $primary_key[0];
		$this->table_field = $fields;
		$this->field_type = $this->get_fields_type($this->table_name);
		//缓存文件内容整理
		$cache_data_array = array(
			'types'  => $this->field_type,
			'fields' => $this->table_field,
			'primary_key' => $this->primary_key,
		);
		$cache_content = "<?php\r\nif (!defined('IN_IDEACMS')) exit();\r\nreturn " . var_export($cache_data_array, true) . ";";		
		//分析model缓存文件目录
		if (!is_dir($this->cache_dir)) {
			//生成目录							
			@mkdir($this->cache_dir);
		}
		//将缓存内容写入缓存文件
		@file_put_contents($cache_file, $cache_content, LOCK_EX);
		return true;
	}


    /**
     * 获取字段类型
     */
    public function get_fields_type($table_name) {
        if (!$table_name) {
            return false;
        }
        $_field = $this->db->query('SHOW FULL COLUMNS FROM '.$table_name)->result_array();
        $_types = array();
        foreach ($_field as $c) {
            $t = preg_replace('/\(.*\)/Ui', '', $c['Type']);
            $_types[$c['Field']] = $t;
        }
        return $_types;
    }
	
	/**
	 * 加载当前model的缓存文件内容
	 * 
	 * @access protected
	 * @return array	缓存文件内容
	 */
	protected function load_cache() {
		$cache_file = $this->parse_cache_file();
		//分析缓存文件是否存在		
		if (!is_file($cache_file)) {
            return false;
        }
		$cache_data_array = include $cache_file;
		$this->primary_key = $cache_data_array['primary_key'];
		$this->table_field = $cache_data_array['fields'];
		$this->field_type = $cache_data_array['types'];
		//清空不必要的内存占用
		unset($cache_data_array);
		return true;
	}
	
	/**
	 * 清空当前model的缓存文件
	 * 
	 * @access protected
	 * @return boolean
	 */
	protected function clear_cache() {
		//分析model缓存文件名
		$cache_file = $this->parse_cache_file();
		//当model缓存文件存在时
		if (is_file($cache_file)) unlink($cache_file);
		return true;
	}
	
	/**
	 * 分析当前model缓存文件的路径
	 * 
	 * @access protected
	 * @return string	缓存文件的路径
	 */
	protected function parse_cache_file() {
		$this->get_table_name();
		return $this->cache_dir . $this->table_name . '_model.data.php';
	}

    /**
     * 设置当前数据表的名称
     *
     * @access public
     * @param string $table_name 数据表名称
     * @return $this
     */
    public function set_table_name($table_name) {
        if (!$table_name) {
            return false;
        }
        $this->table_name = $this->prefix . trim($table_name);
        return $this;
    }

    /**
     * 设置数据表主键
     *
     * @access public
     * @param string $primary_key 数据表主键
     * @return $this
     */
    public function set_primary_key($primary_key) {
        if (!$primary_key) {
            return false;
        }
        $this->primary_key = trim($primary_key);
        return $this;
    }
	
	/**
	 * 组装SQL语句中的FROM语句
	 * 
	 * 用于处理 SELECT fields FROM table之类的SQL语句部分
	 * @access public
	 * @param mixed $table_name  所要查询的数据表名，参数支持数组
	 * @param mixed $columns	   所要查询的数据表字段，参数支持数组，默认为null, 即数据表全部字段
	 * @return $this
	 * 
	 */
	public function from($table_name = null, $fields = null) {
		if (!$table_name) {
			$this->get_table_name();
			$table_name   = str_replace($this->prefix, '', $this->table_name);
		}
		if (is_array($table_name)) {
			$option_array = array();
			foreach ($table_name as $key=>$value) {
				//当有数据表前缀时
				if (!empty($this->prefix)) {
					$option_array[] = is_int($key) ? ' ' .$this->prefix . trim($value) : ' ' . $this->prefix . trim($value) . ' AS ' . $key;  
				} else {
					$option_array[] = is_int($key) ? ' ' . trim($value) : ' ' . trim($value) . ' AS ' . $key;
				}
			}
			$table_str = implode(',', $option_array);
			unset($option_array);
		} else {
			$table_str = (!empty($this->prefix)) ? $this->prefix . trim($table_name) : trim($table_name);
		}
		//对数据表字段的分析
		$item_str      = $this->_parse_fields($fields);	
		//组装SQL中的FROM片段
		$this->_parts['from'] = 'SELECT ' . $item_str . ' FROM ' . $table_str;
		return $this;
	}
	
	/**
	 * 分析数据表字段信息
	 * 
	 * @access 	protected
	 * @param	array	$fields	数据表字段信息.本参数为数组
	 * @return 	string
	 */
	protected static function _parse_fields($fields = null) {
		if (is_null($fields)) return '*';
		if (is_array($fields)) {
			$fields_array = array();
			foreach($fields as $key=>$value) {
				$fields_array[] = is_int($key) ? $value : $value . ' AS ' . $key; 
			}
			$fields_str = implode(',', $fields_array);
			//清空不必要的内存占用
			unset($fields_array);
		} else {
			$fields_str = $fields;
		}
		return $fields_str;
	}
	
	/**
	 * 组装SQL语句的WHERE语句
	 * 
	 * 用于处理 WHERE id=3721 诸如此类的SQL语句部分
	 * @access public
	 * @param string $where WHERE的条件
	 * @param string $value 数据参数，一般为字符或字符串
	 * @return $this
	 */
	public function where($where, $value = null) {
		return $this->_where($where, $value, true);
	}
	
	/**
	 * 组装SQL语句的ORWHERE语句
	 * 
	 * 用于处理 ORWHERE id=2011 诸如此类的SQL语句部分
	 * @access public
	 * @param string $where WHERE的条件
	 * @param string $value 数据参数，一般为字符或字符串
	 * @return $this
	 */
	public function orwhere($where, $value = null) {
		return $this->_where($where, $value, false);
	}
	
	/**
	 * 组装SQL语句中WHERE及ORWHERE语句
	 * 
	 * 本方法用来为方法where()及orwhere()提供"配件"
	 * @access protected
	 * @param string $where SQL中的WHERE语句中的条件.
	 * @param string $value 数值（数据表某字段所对应的数据，通常都为字符串或字符）
	 * @param boolean $is_where 注:为true时是为where()， 反之 为orwhere()
	 * @return $this
	 */
	protected function _where($where, $value = null, $is_where = true) {
		if (!$where) return false;
		if (is_array($where)) {			
			$where_array = array();
			foreach ($where as $string) {
				$where_array[] = trim($string);
			}
			$where = implode(' AND ', $where_array);
			unset($where_array);
		}
		//当$model->where('name=?', 'ideacms');操作时
		if (!is_null($value)) {
			$value = $this->quote_into($value);
			$where = str_replace('?', $value, $where);
		}
		//处理where或orwhere.
		if ($is_where == true) {
			$this->_parts['where']    = (isset($this->_parts['where']) && $this->_parts['where']) ? $this->_parts['where'] . ' AND ' . $where   : ' WHERE ' . $where;
		} else {
			$this->_parts['or_where'] = (isset($this->_parts['or_where']) && $this->_parts['or_where']) ? $this->_parts['or_where'] . ' OR ' . $where : ' OR ' . $where;
		}
		return $this;
	}
	
	/**
	 * 组装SQL语句排序(ORDER BY)语句
	 * 
	 * 用于处理 ORDER BY post_id ASC 诸如之类的SQL语句部分
	 * @access public
	 * @param mixed $string 排序条件。注：本参数支持数组
	 * @return $this
	 */
	public function order($string) {
		if (!$string) return false;
		if (is_array($string)) {
			$order_array = array();
			foreach ($string as $lines) {
				$order_array[] = trim($lines);
			}
			$string = implode(',', $order_array);
			unset($order_array);			
		}
		$string = trim($string);		
		$this->_parts['order'] = (isset($this->_parts['order']) && $this->_parts['order']) ? $this->_parts['order'] . ', ' . $string : ' ORDER BY ' . $string;
		return $this;		
	}
	
	/**
	 * 组装SQL语句LIMIT语句
	 * 
	 * limit(10,20)用于处理LIMIT 10, 20之类的SQL语句部分 
	 * @access public
	 * @param int $offset 启始id, 注:参数为整形
	 * @param int $count  显示的行数
	 * @return $this
	 */
	public function limit($offset, $count = null) {
		$count 	   = (int)$count;
		$offset    = (int)$offset;
		$limit_str = !empty($count) ? $offset . ', ' . $count : $offset;
		$this->_parts['limit'] = ' LIMIT ' . $limit_str;
		return $this;
	}
	
	/**
	 * 组装SQL语句的LIMIT语句
	 * 
	 * 注:本方法与$this->limit()功能相类，区别在于:本方法便于分页,参数不同
	 * @access public
	 * @param int $page 	当前的页数
	 * @param int $count 	每页显示的数据行数
	 * @return $this
	 */
	public function page_limit($page, $count) {
		$page = (int)$page;
		$count = (int)$count;
		$start_id = $count * ($page - 1);
		return $this->limit($start_id, $count);
	}
	
	/**
	 * 组装SQL语句中LEFT JOIN语句
	 * 
	 * jion('表名2', '关系语句')相当于SQL语句中LEFT JOIN 表2 ON 关系SQL语句部分
	 * @access public
	 * @param string $table_name	数据表名，注：本参数支持数组，主要用于数据表的alias别名
	 * @param string $where			join条件，注：不支持数组
	 * @return $this
	 */
	public function join($table_name, $where) {
		if (!$table_name || !$where) {
            return false;
        }
		if (is_array($table_name)) {									
			foreach ($table_name as $key=>$string) {																
				if (!empty($this->prefix)) {
					$table_name_str = is_int($key) ? $this->prefix . trim($string) : $this->prefix . trim($string) . ' AS ' . $key;
				} else {
					$table_name_str = is_int($key) ? trim($string) :  trim($string) .' AS ' . $key;	
				}
				//数据处理，只处理一个数组元素
				break;				
			}			
		} else {			
			$table_name_str   = (!empty($this->prefix)) ? $this->prefix . trim($table_name) : trim($table_name);
		}
		//处理条件语句	
		$this->_parts['join'] = ' LEFT JOIN ' . $table_name_str . ' ON ' . trim($where);
		return $this;
	}
	
	/**
	 * 组装SQL的GROUP BY语句
	 * 
	 * 用于处理SQL语句中GROUP BY语句部分
	 * @access public
	 * @param mixed $group_name	所要排序的字段对象
	 * @return $this
	 */
	public function group($group_name) {
		if (!$group_name) {
            return false;
        }
		if (is_array($group_name)) {			
			$group_array = array();
			foreach ($group_name as $lines) {
				$group_array[] = trim($lines);
			}
			$group_name = implode(',', $group_array);
			unset($group_array); 
		}
		$this->_parts['group'] = ($this->_parts['group']) ? $this->_parts['group'] . ', ' . $group_name : ' GROUP BY ' . $group_name;
		return $this;
	}
	
	/**
	 * 组装SQL的HAVING语句
	 * 
	 * 用于处理 having id=2011 诸如此类的SQL语句部分
	 * @access pulbic
	 * @param string|array $where 条件语句
	 * @param string $value	数据表某字段的数据值
	 * @return $this
	 */
	public function having($where, $value = null) {
		return $this->_having($where, $value, true);
	}
	
	/**
	 * 组装SQL的ORHAVING语句
	 * 
	 * 用于处理or having id=2011 诸如此类的SQL语句部分
	 * @access pulbic
	 * @param string|array $where 条件语句
	 * @param string $value	数据表某字段的数据值
	 * @return $this
	 */
	public function orhaving($where, $value = null) {
		return $this->_having($where, $value, false);
	}
	
	/**
	 * 组装SQL的HAVING,ORHAVING语句
	 * 
	 * 为having()及orhaving()方法的执行提供'配件'
	 * @access protected
	 * @param mixed $where 条件语句
	 * @param string $value	数据表某字段的数据值
	 * @param boolean $is_having 当参数为true时，处理having()，当为false时，则为orhaving()
	 * @return $this
	 */
	protected function _having($where, $value = null, $is_having = true) {
		if (!$where) return false;
		if (is_array($where)) {						
			$where_array = array();
			foreach ($where as $string) {
				$where_array[] = trim($string);
			}
			$where = implode(' AND ', $where_array);
			unset($where_array);
		}
		//当程序$model->where('name=?', 'ideacms');操作时
		if (!is_null($value)) {
			$value = $this->quote_into($value);
			$where = str_replace('?', $value, $where);
		}
		//分析having() 或 orhaving()
		if ($is_having == true) {
			$this->_parts['having'] 	= ($this->_parts['having']) ? $this->_parts['having'] . ' AND ' . $where : ' HAVING ' . $where;
		} else {
			$this->_parts['or_having'] 	= ($this->_parts['or_having']) ? $this->_parts['or_having'] . ' AND ' . $where : ' OR ' . $where;
		}
		return $this;
	}
	
	/**
	 * 执行SQL语句中的SELECT查询语句
	 * 
	 * 组装SQL语句并完成查询，并返回查询结果，返回结果可以是多行，也可以是单行
	 * @access public
	 * @param boolean $all_data 是否输出为多行数据，默认为true,即多行数据；当false时输出的为单行数据
	 * @return array
	 */
	public function select($all_data = true, $cache = 0) {
		if (!isset($this->_parts['from']) || !$this->_parts['from']) {
            $this->from($this->table_name ? substr($this->table_name, strlen($this->prefix)) : substr(strtolower(get_class($this)), 0, -5));
        }
		//组装完整的SQL查询语句
		$parts_name_array = array('from', 'join', 'where', 'or_where', 'group', 'having', 'or_having', 'order', 'limit');
		$sql_str = '';
		if (!isset($this->_parts['order'])) $this->_parts['order'] = ' ORDER BY NULL';
		foreach ($parts_name_array as $part_name) {			
			if (isset($this->_parts[$part_name]) && $this->_parts[$part_name]) {
				$sql_str.= $this->_parts[$part_name];
				unset($this->_parts[$part_name]);
			}
		}
		return $this->execute($sql_str, $all_data, $cache);
	}
	
	/**
	 * 字符串转义函数
	 * 
	 * SQL语句指令安全过滤,用于字符转义
	 * @access public
	 * @param mixed $value 所要转义的字符或字符串,注：参数支持数组
	 * @return string|string
	 */
	public static function quote_into($value) {
		if (is_array($value)) {			
			foreach ($value as $key=>$string) {
				$value[$key] = self::quote_into($string);
			}
		} else {
			//当参数为字符串或字符时
			if (is_string($value)) $value = '\'' . addslashes($value) . '\'';
		}
		return $value;
	}
	
	/*
	 * 获取数据的总行数
	 * 
	 * 获取某数据表满足一定条件的数据的总行数,分页程序常用
	 * @access public
	 * @param string $table_name	所要查询的数据表名
	 * @param string $field_name	所要查询字段名称
	 * @param string $where			查询条件
	 * @param string $value			数值（数据表某字段所对应的数据，通常都为字符串或字符）
	 * @param int	 $cache			缓存时间
	 * @return integer
	 */
	public function count($table_name, $field_name = null, $where = null, $value = null, $cache = 0) {
		if (!$table_name) return false;
		$select = $this->from($table_name, array('total_num' => 'count(*)'));
		if (!is_null($where)) $select->where($where, $value);
		$data   = $cache && is_numeric($cache) ? $select->select(false, $cache) : $select->select(false);
		return $data['total_num'];
	}
	
	
	
	/**
	 * 对数据表的主键查询
	 * 
	 * 根据主键，获取某个主键的一行信息,主键可以类内设置。默认主键为数据表的物理主键
	 * 如果数据表没有主键，可以在model中定义
	 * @access public
	 * @param int|string|array $id 所要查询的主键值.注：本参数支持数组，当参数为数组时，可以查询多行数据
	 * @param array	$fields	返回数据的有效字段(数据表字段)
	 * @return string	所要查询数据信息（单行或多行）
	 * 
	 */
	public function find($id, $fields = null) {
		if (!$id) return false;
		//获取主键及数据表名
		$this->get_table_name();
		$this->get_primary_key();
		//分析查询的字段信息		
		$fields_str = $this->_parse_fields($fields);
		$sql_str    = 'SELECT ' . $fields_str . ' FROM ' . $this->table_name . ' WHERE ' . $this->primary_key;
		if (is_array($id)) {			
			$sql_str .= ' IN (' . implode(',', $id) . ')';
			$myrow    = $this->db->query($sql_str . ' ORDER BY NULL')->result_array();
		} else{			
			$sql_str .= '=' . $id;
			$myrow    = $this->db->query($sql_str . ' ORDER BY NULL')->row_array();
		}
		return $myrow;
	}
	
	/**
	 * 获取数据表的全部数据信息
	 * 
	 * 以主键为中心排序，获取数据表全部数据信息. 注:如果数据表数据量较大时，慎用此函数，以免数据表数据量过大，造成数据库服务器内存溢出,甚至服务器宕机
	 * @access public
	 * @param array		$fields	返回的数据表字段,默认为全部.即SELECT * FROM table_name
	 * @param  boolean	$order_asc数据排序,若为true时为ASC,为false时为DESC, 默认为ASC
	 * @param integer	$offset	limit启起ID
	 * @param integer	$count	显示的行数
	 * @return array	数据表数据信息
	 */
	public function findAll($fields = null, $order_asc = true, $offset = null, $count = null) {
		//获取主键及数据表名
		$this->get_table_name();
		$this->get_primary_key();
		//分析查询的字段信息		
		$fields_str = $this->_parse_fields($fields);
		$sql_str  = 'SELECT ' . $fields_str . ' FROM ' . $this->table_name . ' ORDER BY ' . $this->primary_key . (($order_asc == true) ? ' ASC' : ' DESC');
		if (!is_null($offset)) {
			$this->_parts['limit'] = '';
			$this->limit($offset, $count);
			$sql_str .= $this->_parts['limit'];
			unset($this->_parts['limit']);
		}
		return $this->db->query($sql_str)->result_array();
	}
	
	/**
	 * 查询数据表单行数据
	 * 
	 * 根据一个查询条件，获取一行数据，返回数据为数组型，索引为数据表字段名
	 * @access public
	 * @param mixed 	$where 查询条件
	 * @param sring  	$value 数值
	 * @param array		$fields	返回数据的数据表字段,默认为全部字段.注：本参数为数组
	 * @return array 	所要查询的数据表数据
	 */
	public function getOne($where, $value = null, $fields = null) {
		if (!$where) return false;
		//获取数据表名
		$this->get_table_name();
		//分析查询的字段信息		
		$fields_str = $this->_parse_fields($fields);
		//处理查询的SQL语句
		$this->_parts['where'] = '';
		$this->where($where, $value);
		$where_str = $this->_parts['where'];
		unset($this->_parts['where']);
		$sql_str = 'SELECT ' . $fields_str . ' FROM ' . $this->table_name . $where_str;
		if (stripos($sql_str, 'order by') === false) $sql_str .= ' ORDER BY NULL';
		return $this->db->query($sql_str)->row_array();
	}
	
	/**
	 * 查询数据表多行数据
	 * 
	 * 根据一个查询条件，获取多行数据。并且支持数据排序
	 * @access public
	 * @param mixed	$where 查询条件
	 * @param sring	$value 数值
	 * @param array $fields	返回数据的数据表字段.默认为全部字段.注:本参数为数组
	 * @param mixed $order 排序条件
	 * @param integer	$offset	limit启起ID
	 * @param integer	$count	显示的行数 
	 * @return array 
	 */
	public function getAll($where, $value=null, $fields = null, $order = null, $offset = null, $count = null) {
		if (!$where) return false;
		//获取数据表名
		$this->get_table_name();
		//分析查询的字段信息		
		$fields_str = $this->_parse_fields($fields);
		$sql_str    = 'SELECT ' . $fields_str . ' FROM ' . $this->table_name;
		//处理查询的SQL语句
		$this->_parts['where'] = '';
		$this->where($where, $value);
		$sql_str   .= $this->_parts['where'];
		unset($this->_parts['where']);
		//处理排序的SQL语句
		if (!is_null($order)) {
			$this->_parts['order'] = '';
			$this->order($order);
			$sql_str .= $this->_parts['order'];
			unset($this->_parts['order']);
		} else {
		    $sql_str .= ' ORDER BY NULL';
		}
		//处理limit语句
		if (!is_null($offset)) {
			$this->_parts['limit'] = '';
			$this->limit($offset, $count);
			$sql_str .= $this->_parts['limit'];
			unset($this->_parts['limit']);
		}
		return $this->db->query($sql_str)->result_array();
	}
	
	/**
	 * 数据表写入操作
	 * 
	 * 向当前model对应的数据表插入数据
	 * @access public
	 * @param array $data 所要写入的数据内容。注：数据必须为数组
	 * @return boolean
	 */
	public function insert($data) {
		if (!is_array($data) || !$data)  {
            return false;
        }
		//获取数据表名及字段信息
		$this->get_table_name();
		$this->get_table_fields();
		//处理数据表字段与数据的对应关系
		$field_array = array();
		$content_array 	= array();
		foreach ($data as $key=>$value) {
			if (in_array($key, $this->table_field)) {
				$field_array[] = '`' . trim($key) . '`';
				$content_array[] = '\'' . trim($this->_gl($value)) . '\'';
			}
		}
		$field_str = implode(',', $field_array);
		$content_str = implode(',', $content_array);
		if (empty($field_str)) {
            return false;
        }
		//清空不必要的内存占用
		unset($field_array);
		unset($content_array);
		$sql_str = 'INSERT INTO ' . $this->table_name . ' (' . $field_str . ') VALUES (' . $content_str . ')';
		return $this->db->query($sql_str) ? $this->db->insert_id() : false;
	}
	
	/**
	 * 数据表更改操作
	 * 
	 * 更改当前model所对应的数据表的数据内容
	 * @access public
	 * @param array 	$data 所要更改的数据内容
	 * @param mixed		$where 更改数据所要满足的条件
	 * @param string	$$params 数值，对满足更改的条件的进一步补充
	 * @return boolean
	 */
	public function update($data, $where, $params = null) {
		if (!is_array($data) || !$data || !$where) {
            return false;
        }
		//获取数据表名及字段信息
		$this->get_table_name();
		$this->get_table_fields();
		$this->get_field_type();
		$content_array = array();
		foreach ($data as $key=>$value) {
			if (in_array($key, $this->table_field)) {
			    $value = trim($value);
				if (strpos($value, $key) !== false && strpos($value, $key) === 0 && $this->field_type[$key] == 'int') {
				    $v = trim(str_replace($key, '', $value));
					if (preg_match('/[\+|\-]{1}[ 0-9]+/U', $v)) {
					    $content_array[] = '`' . $key . '` = `' . $key . '`' . $v;
					} else {
					    $content_array[] = '`' . $key . '` = \'' . $this->_gl($value) . '\'';
					}
				} else {
				    $content_array[] = '`' . $key . '` = \'' . $this->_gl($value)  . '\'';
				}
			}
		}
		$content_str = implode(',', $content_array);
		unset($content_array);
		if (empty($content_str)) {
            return false;
        }
		//组装SQL语句
		$sql_str = 'UPDATE ' . $this->table_name . ' SET ' . $content_str;
		//条件查询SQL语句的处理
		$this->_parts['where'] = '';
		$this->where($where, $params);
		$sql_str.= $this->_parts['where'];
		unset($this->_parts['where']);
		return $this->db->query($sql_str);
	}
	
	/**
	 * 数据表删除操作
	 */
	public function delete($where, $value = null) {
		//获取数据表名及字段信息
		$this->get_table_name();
		$sql_str  = 'DELETE FROM ' . $this->table_name;
		//处理SQL的条件查询语句
		$this->_parts['where'] = '';
		$this->where($where, $value);
		$sql_str .= $this->_parts['where'];
		unset($this->_parts['where']);
		return $this->db->query($sql_str);
	}
	
	/**
	 * 执行SQL查询语句
	 * $all_rows 是否显示全部数据开关，当为true时，显示全部数据，为false时，显示一行数据，默认为true
	 * $cache    缓存时间，单位秒。
	 */
	public function execute($sql, $all_data = true, $cache = 0) {
	    if ($cache) {
		    $file = $this->cache_dir . md5($sql) . '.db.php';
			if (is_file($file) && time() - filemtime($file) < $cache) {
			    return unserialize(file_get_contents($file));
			} else {
				$data = ($all_data == true) ? $this->db->query($sql)->result_array() : $this->db->query($sql)->row_array();
				@file_put_contents($file, serialize($data), LOCK_EX);
				return $data;
			}
		}
		return ($all_data == true) ? $this->db->query($sql)->result_array() : $this->db->query($sql)->row_array();
	}
	
	/**
	 * 执行SQL语句操作
	 */
	public function query($sql) {
		return $this->db->query($sql);
	}
	
	/**
	 * 获取insert_id操作
	 */
	public function get_insert_id() {
		return $this->db->insert_id();
	}
	
	/**
	 * 实例化model
	 */
	public function load_model($model_name) {
		if (!$model_name) return false;
		return Controller::model($model_name);
	}
	
	/**
	 * 检查表是否存在
	 */
	public function is_table_exists($table) {
	    $table = $this->prefix . $table;
		$tables = $this->get_tables();
		return in_array($table, $tables) ? true : false;
	}
	
	/**
	 * 当前数据库中的所有表
	 */
	public function get_tables() {
	    $result = $this->db->query("SHOW TABLES FROM `$this->dbname`")->result_array();
	    $tables = array();
		foreach ($result as $t) {
		    foreach ($t as $c) { $tables[] = $c; }
		}
		return $tables;
	}
	
    /**
	 * 获取mysql数据库服务器信息
	 */
	public function get_server_info() {
		return $this->db->get_server_info();
	}
	
	/**
	 * 字段的数量
	 */
	public function num_fields($sql) {
        if (preg_match('/`(.+)`/U', $sql, $data)) {
            $table = $data[1];
        } else {
            $table = $sql;
        }
        $field = $this->db->query('SHOW FULL COLUMNS FROM '.$table)->result_array();
		return count($field);
	}
	
	/**
	 * 结果集中的数量
	 */
	public function num_rows($sql) {

        $field = $this->db->query($sql)->result_array();
        return count($field);
	}
	
	/**
	 * 自动变量设置
	 */
	 public function __set($name, $value) {
	 	if (is_object($this->myrow)) {	 		
	 		$this->myrow->$name = addslashes($value);	 		
	 	} else {	 		
	 		//允许model对数据表名，数据表主键的自定义
	 		if (in_array($name, array('table_name', 'primary_key'))) {
	 			$this->$name = addslashes($value);
	 		}
	 	}
	 	return true; 	
	 }
	 
	 /**
	  * 输出类的实例化对象
	  */
	 public function __toString() {
	 	if ($this->_parts) {
	 		$parts_name_array = array('from', 'join', 'where', 'or_where', 'group', 'having', 'or_having', 'order', 'limit');	 		
			$sql_str = '';			
			foreach ($parts_name_array as $part_name) {			
				if ($this->_parts[$part_name]) {
					$sql_str .= $this->_parts[$part_name];	
					unset($this->_parts[$part_name]);				
				}
			}
			return (string)$sql_str;
	 	}
	 	return (string)'This is ' . get_class($this) . ' Class!';
	}

    // 字符转义处理
    public function _gl($str) {

        if (function_exists('mysqli_real_escape_string')) {
            $t = $str;
            $str = @mysqli_real_escape_string($this->db->conn_id, $str);
            if (!$str && $str != $t) {
                $str = addslashes($t);
            }
        } else {
            $str = addslashes($str);
        }

        return $str;
    }
	
}