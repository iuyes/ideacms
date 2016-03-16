<?php

/**
 * Common.php for v1.5 +
 * 应用控制器公共类 
 */

class Plugin extends Common {
    
    protected $plugin; //应用模型
	protected $data;   //应用数据
    
    public function __construct() {
        parent::__construct();
		$this->plugin  = $this->model('plugin');
        $this->data    = $this->plugin->where('dir=?', $this->namespace)->select(false);
		if (empty($this->data)) $this->adminMsg('应用尚未安装', url('admin/plugin'));
		if ($this->data['disable']) $this->adminMsg('应用尚未开启', url('admin/plugin'));
		$this->assign(array(
		    'viewpath' =>  SITE_PATH . $this->site['PLUGIN_DIR'] . '/' . $this->data['dir'] . '/views/',  
		));
    }
	
	public function pluginMsg($msg, $url='', $time=1) {
	    $this->assign(array(
		    'msg'  => $msg,
			'url'  => $url,
			'time' => $time,
		));
		$this->display('msg');
		exit;
	}
    
}

/**
 * 远程下载图片
 * @param $url 远程图片地址
 * @param $filename 保存图片地址
 * @return boolean
 */
function loadImage($url, $filename) {
	if ($url == '') return false; //地址为空,退出
	$ext = strtolower(strrchr($url, '.'));
	//格式判断
	if ($ext != '.gif' && $ext != '.jpg' && $ext != '.png' && $ext != '.gif' && $ext != '.jpeg') return false;
	ob_start ();
	$img = fn_geturl($url);
	for($i = 0; $i < 3; $i ++) {
		$data = $img;
		if ($data) break;
	}
	if ($img && function_exists ( 'curl_init' )) {
		$ch = curl_init ();
		$timeout = 30; // set to zero for no timeout
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		$img = curl_exec ( $ch );
		curl_close ( $ch );
	}
	ob_end_clean ();
	$size = strlen($img);
	if (($size * 1024) < 1) {
		return false;
	} else {
	    if (!is_dir(dirname($filename))) {
		    mkdir(dirname($filename));
		}
		file_put_contents($filename, $img);
		return true;
	}

}
