<?php

class FormModel extends Model {
    
    public function get_primary_key() {
        return $this->primary_key = 'id';
    }
    
    public function get_fields() {
        return $this->get_table_fields();
    }
	
    /**
     * 添加、修改内容数据
     */
    public function set($id, $data) {
        //数组转化为字符
		foreach ($data as $i=>$t) {
			if (is_array($t)) {
                $data[$i] = array2string($t);
            }
		}
		if ($id) {
		    //修改
			unset($data['id']);
            $this->update($data,  'id=' . $id);
        } else {
            //添加
			$id = $this->get_form_id();	//生成唯一id
			$data['id'] = $id;
			if (empty($id)) {
                return false;
            }
			$this->insert($data);
            // 回调函数
            $table = str_replace($this->prefix, '', $this->table_name);
            $function = 'callback_'.$table;
            $file = MODEL_DIR .'callback/'. $table . '.php';
            if (is_file($file)) {
                include_once $file;
                if (function_exists($function)) {
                    $function($data);
                }
            }
		}

		return $id;
    }
	
	/**
     * 插入新的表单id
     */
	public function get_form_id() {
		$this->query('insert into `' . $this->prefix . 'form` values (NULL)');
		return $this->get_insert_id();
	}
	
	/**
     * 清理缓存内容id
     */
	public function clear_cache_id() {
		$this->query('DELETE FROM `' . $this->prefix . 'form` WHERE 1');
	}
    
}