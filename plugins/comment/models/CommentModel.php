<?php

class CommentModel extends Model {
    
	public function get_primary_key() {
		return $this->primary_key = 'id';
	}
	
}