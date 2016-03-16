<?php

class PositionModel extends Model {
	
	public function get_primary_key() {
		return $this->primary_key = 'posid';
	}
	
	public function set($posid, $data) {
		$data['site'] = App::get_site_id();
	    if ($posid) {
	        $this->update($data, 'posid=' . $posid);
	        return true;
	    }
	    $this->insert($data);
	    if ($this->get_insert_id()) return true;
	    return false;
	}
	
	public function del($posid) {
	    $this->delete('posid=' . $posid . ' AND site=' . App::get_site_id());
	    $table = $this->prefix . 'position_data';
	    $this->query('delete from ' . $table . ' where posid=' . $posid);
	}
	
}