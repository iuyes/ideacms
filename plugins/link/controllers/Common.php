<?php

class Plugin extends Common {
    
    protected $plugin;
    protected $link;
	protected $data;
    
    public function __construct() {
        parent::__construct();
        $this->plugin = $this->model("plugin");
        $this->data   = $this->plugin->where("dir=?", $this->namespace)->select(false);
		if (empty($this->data)) $this->adminMsg('应用尚未安装', url('admin/plugin'));
		if ($this->data['disable']) $this->adminMsg('应用尚未开启', url('admin/plugin'));
        $this->link   = $this->model("link");
		$this->assign(array(
            "admin_path"     => SITE_PATH . basename(VIEW_DIR) . "/admin/",
            "extension_path" => SITE_PATH . EXTENSION_PATH . "/",
        ));
    }
    
}    