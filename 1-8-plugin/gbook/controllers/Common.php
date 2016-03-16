<?php

class Plugin extends Common {
    
    protected $plugin;
    protected $gbook;
	protected $data;
    
    public function __construct() {
        parent::__construct();
        $plugin       = $this->model('plugin');
        $this->data   = $this->plugin = $plugin->where('dir=?', $this->namespace)->select(false);
		if (empty($this->data)) $this->adminMsg('应用尚未安装', url('admin/plugin'));
		if ($this->data['disable']) $this->adminMsg('应用尚未开启', url('admin/plugin'));
        $this->gbook  = $this->model("gbook");
		$this->assign(array(
            'admin_path'     => SITE_PATH . basename(VIEW_DIR) . '/admin/',
            'extension_path' => SITE_PATH . EXTENSION_PATH . '/',
        )); 
    }
    
}    