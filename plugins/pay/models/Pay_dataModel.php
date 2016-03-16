<?php
class Pay_dataModel extends Model {
    
	protected function get_primary_key() {
		return $this->primary_key = 'userid';
	}
	
	public function getData($userid) {
	    $data = $this->find($userid);
		if (empty($data)) {
		    $user = $this->from('member', 'username')->where('id=' . $userid)->select(false);
			if (empty($user)) return false;
            $data = array(
			    'userid'    => $userid,
				'username'  => $user['username'],
				'money'     => 0,
				'freeze'    => 0,
				'available' => 0,
			);
			$this->insert($data);
		}
		return $data;
	}
	
	public function getDataName($username) {
	    $data = $this->where('username=?', $username)->select(false);
		if (empty($data)) {
		    $user = $this->from('member', 'id,username')->where('username=?', $username)->select(false);
			if (empty($user)) return false;
            $data = array(
			    'userid'    => $user['id'],
				'username'  => $user['username'],
				'money'     => 0,
				'freeze'    => 0,
				'available' => 0,
			);
			$this->insert($data);
		}
		return $data;
	}
	
	public function set($data, $userid) {
		$set = array(
			'available' => $data['available'],
			'freeze'    => $data['freeze'],
		);
		return $this->update($set, 'userid=' . $userid);
	}
	
}