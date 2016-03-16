<?php

/**
 * 文件名称: Common.php for v1.6 +
 * 应用控制器公共类
 */

class Plugin extends Common {
    
    protected $plugin;   //应用模型
	protected $data;     //应用数据
	protected $viewpath; //视图目录
	protected $comment;  //当前应用模型实例
	protected $comment_data;
    
    public function __construct() {
        parent::__construct();
		$this->plugin   = $this->model('plugin');
        $this->data     = $this->plugin->where('dir=?', $this->namespace)->select(false);
		if (empty($this->data)) $this->adminMsg('应用尚未安装', url('admin/plugin'));
		if ($this->data['disable']) $this->adminMsg('应用尚未开启', url('admin/plugin'));
		$this->comment  = $this->model('comment'); 
		$this->viewpath = SITE_PATH . $this->site['PLUGIN_DIR'] . '/' . $this->data['dir'] . '/views/';
		$this->comment_data = $this->model('comment_data'); 
		$this->assign(array(
		    'viewpath' => $this->viewpath,
            'pluginid' => $this->data['pluginid'],
            'isadmin'  => ($this->session->is_set('user_id') && $this->session->get('user_id')) ? 1 : 0,			
		));
    }
    
}

/**
 * 人性化时间显示
 */
function fnDate($timestamp) {
    if (!is_numeric($timestamp) || empty($timestamp)) return false;
    $now  = time();
	$timeoffset = 8;
	$timestamp += $timeoffset * 3600;
	$todaytimestamp = $now - ($now  + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
	$s    = gmdate('Y-n-j H:i:s', $timestamp);
	$time = $now + $timeoffset * 3600 - $timestamp;
	if($timestamp >= $todaytimestamp) {
		if($time > 3600) {
			return intval($time / 3600) . '&nbsp;小时前';
		} elseif($time > 1800) {
			return '半小时前';
		} elseif($time > 60) {
			return intval($time / 60).'&nbsp;分钟前';
		} elseif($time > 0) {
			return $time . '&nbsp;秒前</span>';
		} elseif($time == 0) {
			return '刚刚';
		} else {
			return $s;
		}
	} elseif(($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
		if($days == 0) {
			return '昨天&nbsp;' . gmdate('H:i:s', $timestamp);
		} elseif($days == 1) {
			return '前天&nbsp;' . gmdate('H:i:s', $timestamp);
		} else {
			return ($days + 1) . '&nbsp;天前';
		}
	} else {
		return $s;
	}
}
