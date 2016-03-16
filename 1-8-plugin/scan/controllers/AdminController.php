<?php

class AdminController extends Plugin {
	
	private $files = array();
	private $config;
	
    public function __construct() {
        parent::__construct();
        //Admin控制器进行登录验证
        if (!$this->session->is_set('user_id') || !$this->session->get('user_id')) $this->adminMsg('请登录以后再操作', url('admin/login'));
		$this->assign('menu', array(
			array('index', '病毒扫描'),
			array('list',  '扫描记录'),
		));
		$this->config = $this->cache->get('scan_cfg');
		$this->config['filetype'] = !isset($this->config['filetype']) || empty($this->config['filetype']) ? 'html|php' : $this->config['filetype'];
		$this->config['codeinfo'] = !isset($this->config['codeinfo']) || empty($this->config['codeinfo']) ? 'com|system|escapeshell|cmd|passthru|gzuncompress' : $this->config['codeinfo'];
    }
	
    /*
	 * 病毒扫描
	 */
    public function indexAction() {
		if ($this->isPostForm()) {
			$data = $this->post('data');
			$this->cache->set('scan_cfg', $data);
			$this->adminMsg('正在扫描，请稍候...', purl('admin/scan'), 1, 1, 2);
		}
		$this->assign('data', $this->config);
	    $this->display('index');
    }
	
	/*
	 * 查看扫描
	 */
    public function listAction() {
		if ($this->isPostForm()) {
			$del  = $this->post('del');
			$data = $this->cache->get('scan_log');
			if ($del) {
			    foreach ($del as $t) {
				    unset($data[$t]);
				}
			}
			$this->cache->set('scan_log', $data);
		}
		$this->assign('data', $this->cache->get('scan_log'));
	    $this->display('list');
    }
	
	/*
	 * 查看文件
	 */
    public function showAction() {
		$id   = (int)$this->get('id');
	    $data = $this->cache->get('scan_log');
		if (!isset($data[$id])) $this->adminMsg('扫描记录不存在');
		$this->assign(array(
		    'data'   => $data[$id]['info'],
			'fileid' => $id,
		));
	    $this->display('show');
    }
	
	/*
	 * 查看详细文件
	 */
    public function fileAction() {
		$id   = (int)$this->get('id');
		$fid  = (int)$this->get('fid');
	    $data = $this->cache->get('scan_log');
		if (!isset($data[$fid])) $this->adminMsg('扫描记录不存在');
		$file = $data[$fid]['info'][$id];
		if (empty($file) || !file_exists($file['file'])) $this->adminMsg('文件不存在(#' . $file['file'] . ')');
		$body = file_get_contents($file['file']);
		$body = gbk_to_utf8($body);
		highlight_string($body);
    }
	
	/*
	 * 开始病毒扫描
	 */
    public function scanAction() {
		$log = $this->cache->get('scan_log');
		$now = $this->site_scandir(APP_ROOT);
		if ($now) {
			$log[] = array(
				'time' => time(),
				'file' => $this->config['filetype'],
				'code' => $this->config['codeinfo'],
				'info' => $now,
			);
			$this->cache->set('scan_log', $log);
			$this->adminMsg('扫描完成，发现可能病毒(#' . count($now) . ')', purl('admin/list'), 1, 1, 1);
		} else {
		    $this->adminMsg('扫描完成，尚未发现病毒', '', 1, 1, 1);
		}
    }
	
	/*
	 * 扫描目录函数
	 */
	private function site_scandir($path) {
		$data = file_list::get_file_list($path);
		if (empty($data)) return $this->files;
		foreach ($data as $p) {
		    if ($p == 'cache') continue; //跳过缓存目录
		    if (is_dir($path . $p)) {
			    $this->site_scandir($path . $p . DIRECTORY_SEPARATOR);
			} else {
			    //扩展名判断
				$ext  = strtolower(trim(substr(strrchr($p, '.'), 1, 10)));
				$exts = explode('|', $this->config['filetype']);
				if (in_array($ext, $exts)) {
				    //特征代码扫描
					$code = explode('|', $this->config['codeinfo']);
					$body = file_get_contents($path . $p);
					$info = null;
					foreach ($code as $c) {
					    if (strpos($body, $c . '(') !== false) $info[] = $c;
					}
					if ($info) $this->files[] = array('file' => $path . $p, 'info' => $info);
				}
			}
		}
		return $this->files;
	}
}