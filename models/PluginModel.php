<?php

class PluginModel extends Model {
	
	public function get_primary_key() {
		return $this->primary_key = 'pluginid';
	}
	
}