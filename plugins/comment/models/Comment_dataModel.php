<?php

class Comment_dataModel extends Model {
    
	public function get_primary_key() {
		return $this->primary_key = 'id';
	}
	
}