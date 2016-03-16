<?php

class UserModel extends Model {
	
	public function get_primary_key() {
		return $this->primary_key = 'userid';
	}
	
	public function get_fields() {
        return $this->get_table_fields();
    }
	
	/**
	 * 登录验证
	 * @param  $username
	 * @param  $password
	 * @return boolean
	 */
	public function check_login($username, $password) {
	    $row = $this->where('username=?', $username)->select(false);
	    if ($row) {
		    if (md5(md5($password) . $row['salt'] . md5($password)) != $row['password']) return false;
		    $ip = client::get_user_ip();
			if (empty($row['loginip']) || $row['loginip'] != $ip) {
				$update = array(
					'lastloginip'   => $row['loginip'], 
					'lastlogintime' => (int)$row['logintime'],
					'loginip'       => $ip,
					'logintime'     => time(),
				);
				$this->update($update, 'userid=' . $row['userid']);
			}
	        return $row;
	    }
	    return false;
	}
	
	/**
	 * 角色列表
	 * @return multitype:
	 */
	public function get_role_list() {
	    return $this->from('role')->order('roleid ASC')->select();
	}
	
	/**
	 * 用户列表
	 * @return multitype:
	 */
	public function get_user_list($roleid=NULL, $admin=0) {
	    $model = $this->from(array('a'=>'user', 'b'=>'role'));
	    $model->where('a.roleid=b.roleid');
	    if ($roleid) $model->where('a.roleid=' . $roleid);
	    return $model->select();
	}
	
	/**
	 * 用户详细信息
	 * @param  $userid
	 */
	public function userinfo($userid) {
	    if (!$userid) return false;
	    return $this->from(array('a'=>'user','b'=>'role'))->where('a.roleid=b.roleid')->where('a.userid=' . $userid)->select(false);
	}
	
	/**
	 * 角色详细信息
	 * @param  $roleid
	 * @return multitype:
	 */
	public function roleinfo($roleid) {
	    return $this->from('role')->where('roleid=' . $roleid)->select(false);
	}
	
	/**
	 * 添加/修改角色数据 
	 * @param  $roleid
	 * @param  $rolename
	 * @param  $description
	 * @return number （0：已经存在，1：成功，-1：失败）
	 */
	public function set_role($roleid=0, $rolename, $description) {
	    if ($roleid) {
	        $row = $this->from('role')->where('roleid<>' . $roleid)->where('rolename=?', $rolename)->select(false);
	        if ($row) return 0;
	    } else {
	        $row = $this->from('role')->where('rolename=?', $rolename)->select(false);
	        if ($row) return 0;
	    }
	    $sql    = "replace into " . $this->prefix . "role (roleid, rolename, description) values (" . $roleid . ", '" . $rolename . "', '" . $description . "')";
	    $result = $this->query($sql);
	    return $result ? 1 : -1;
	}
	
	public function del_role($roleid) {
	    $sql = 'delete from ' . $this->prefix . 'role where roleid=' . $roleid;
	    $this->query($sql);
	}
}