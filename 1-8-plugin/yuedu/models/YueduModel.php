<?php
class YueduModel extends Model {
    
	protected function get_primary_key() {
		return $this->primary_key = 'id';
	}
	
	public function check($id, $userid) {
		$data = $this->where('cid=' . $id)->where('userid=' . $userid)->select(false);
		return empty($data) ? false : true;
	}
	
}