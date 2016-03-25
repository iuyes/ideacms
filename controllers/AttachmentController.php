<?php

class AttachmentController extends Common {



    private $dir;

    public function __construct() {
		parent::__construct();
		if (!in_array($this->action, array('ajaxswfupload', 'ueditor_upload')) &&
            (!$this->session->is_set('user_id') && !get_cookie('member_id'))
        ) {
            $this->attMsg(lang('att-13'));
        }
		$this->dir = 'uploadfiles/';
	}


	/**
	 * 目录浏览
	 */
	public function albumAction() {
		$admin = $this->get('admin');
		if (empty($admin) && $this->memberinfo) {
			$this->dir.= 'member/' . $this->memberinfo['id'] . '/';
		} elseif (!$this->session->is_set('user_id')) {
		    $this->attMsg(lang('att-0'));
		}
	    $iframe = $this->get('iframe') ? 1 : 0;
        $dir = $this->get('dir') ? base64_decode($this->get('dir')) : '';
		if ($this->checkFileName($dir)) {
            $this->attMsg(lang('m-con-20'));
        }
        $dir = substr($dir, 0, 1) == '/' ? substr($dir, 1) : $dir;
        $dir = str_replace('//', '/', $dir);
        $data = file_list::get_file_list($this->dir . $dir);
        $list = array();
        if ($data) {
            foreach ($data as $t) {
                if ($t == 'index.html') {
                    continue;
                }
				if ($admin && $t == 'member') {
                    continue;
                }
				if (empty($admin) && strpos($t, '.thumb.') !== false) {
                    continue;
                }
                $path = $dir . $t . '/';
                $ext = is_dir($this->dir . $path) ? 'dir' : strtolower(trim(substr(strrchr($t, '.'), 1, 10)));
                $ico = file_exists(basename(VIEW_DIR) . '/admin/images/ext/' . $ext . '.gif') ? $ext . '.gif' : $ext . '.png';
                $fileinfo = array();
                if (is_file($this->dir . $dir . $t)) {
                    $file = $this->dir . $dir . $t;
                    $fileinfo = array(
                        'ext' => $ext,
                        'path' => $file,
                        'time' => date('Y-m-d H:i:s', filemtime($file)),
                        'size' => formatFileSize(filesize($file))
                    );
                }
                $list[] = array(
                    'ico' => $ico,
                    'dir' => base64_encode($path),
                    'url' => is_dir($this->dir . $path) ? url('attachment/album', array('dir'=>base64_encode($path), 'iframe'=>$iframe, 'admin'=>$admin)) : '',
                    'name' => $t,
                    'path' => $this->dir . $path,
                    'isimg' => in_array($ext, array('gif','jpg','png','jpeg','bmp')) ? 1 : 0,
                    'isdir'	=> is_dir($this->dir . $path) ? 1 : 0,
                    'fileinfo' => $fileinfo
                );
            }
        }
        $this->view->assign(array(
            'dir' => $this->dir . $dir,
            'pdir' => url('attachment/album', array('dir' => base64_encode(str_replace(basename($dir), '', $dir)), 'iframe' => $iframe, 'admin' => $admin)),
            'list' => $list,
            'istop' => $dir ? 1 : 0,
            'iframe' => $iframe
        ));
        $this->view->display('../admin/attachment_album');
	}

    /**
	 * 上传图片(单)
	 */
	public function imageAction() {
	    $this->memberCheck();
	    if ($this->post('submit')) {
		    $mark = $this->memberinfo ? false : true;
			$w = (int)$this->post('width');
			$h = (int)$this->post('height');
	        $img = array('w' => $w, 'h' => $h, 't' => $this->post('type'));
			$size = (int)$this->post('size');
	        $data = $this->upload('file', array('jpeg', 'jpg', 'gif', 'png'), $size, $img, $mark, $this->post('admin'), null, $this->post('ofile'), $this->post('document'));
            if ($data['result']) {
                $row = array(
                    'msg' => $data['result'],
                    'error' => 1,
                    'filename' => ''
                );
            } else {
                $row = array(
                    'msg' => lang('att-12'),
                    'error' => 0,
                    'filename' => $data['path'] //全路径
                );
            }
	        $this->view->assign(array(
				'url' => url('attachment/image', array('w' => $w, 'h' => $h, 'size' => $size, 'admin' => $this->post('admin'))),
				'note' => lang('att-11', array('1' => $size)),
			    'data' => $row
			));
	        $this->view->display('../admin/content_upload_result');
	    } else {
		    $w = (int)$this->get('w');
			$h = (int)$this->get('h');
			$s = (int)$this->get('size') ? (int)$this->get('size') : 2;
		    if ($w && empty($h)) {
				$this->view->assign(array(
				    'w' => $this->site['SITE_THUMB_WIDTH'],
					'h' => $this->site['SITE_THUMB_HEIGHT']
				));
			} else {
			    $this->view->assign(array(
				    'w' => $w,
					'h' => $h
				));
			}
		    $this->view->assign(array(
				'note' => lang('att-11', array('1'=>$s)),
				'size' => $s,
			    'admin' => $this->getAdmin(),
				'isimage' => 1, //如果是图片上传，就显示高宽输入框
				'document' => $this->get('document')
			));
	        $this->view->display('../admin/content_upload');
	    }
	}

    /**
     * 多文件（图片）上传
     */
    public function filesAction() {
	    $this->memberCheck();
        $setting = urldecode($this->get('setting'));
        list($type, $size) = explode('|', $setting);
        if (empty($type) || empty($size)) {
            $this->attMsg(lang('att-10'));
        }
        $type = base64_decode($type);
        $data = '';
        $_type = explode(',', $type);
        foreach ($_type as $t) {
            $data.= '*.' . $t . ';';
        }
        $this->view->assign(array(
            'size' => (int)$size,
            'type' => $type,
            'data' => $data,
			'admin' => $this->getAdmin(),
            'filesize' => 1024 * $size, //转换成MB
            'sessionid' => session_id(),
			'document' => $this->get('document')
        ));
        $this->view->display('../admin/attachment_swfupload');
    }

	/**
	 * 上传文件(单)
	 */
    public function fileAction() {
	    $this->memberCheck();
	    $type = urldecode($this->get('type'));
	    $type = base64_decode($type);
		$size = (int)$this->get('size');
	    $this->view->assign('note', lang('att-9', array('1' => $type, '2' => $size)));
	    if ($this->post('submit')) {
	        $data = $this->upload('file', explode(',', $type), $size, null, null, $this->post('admin'), null, $this->post('ofile'), $this->post('document'));
            if ($data['result']) {
                $row = array(
                    'msg' => $data['result'],
                    'error' => 1,
                    'filename' => ''
                );
            } else {
                $row = array(
                    'msg' => lang('att-8'),
                    'error' => 0,
                    'filename' => $data['path'] //全路径
                );
            }
	        $this->view->assign(array(
				'url' => url('attachment/file', array('size' => $size, 'type' => $this->get('type'), 'admin' => $this->post('admin'))),
			    'data' => $row
			));
	        $this->view->display('../admin/content_upload_result');
	    } else {
		    $this->view->assign(array(
				'admin' => $this->getAdmin(),
				'ofile' => $this->get('file'),
				'document' => $this->get('document')
			));
	        $this->view->display('../admin/content_upload');
	    }
	}

    /**
     * 文件上传
     * @param  $fields		上传字段 'file'
     * @param  $type		文件类型  array(jpg,gif)
     * @param  $size		文件大小  MB
     * @param  $img			图片配置参数
     * @param  $mark		图片水印
     * @param  $admin		是否来自后台
     * @param  $stype		上传方式  swf或者ke
     * @param  $ofile		原文件
     * @param  $document	后台栏目归档目录
     * @return Array		返回数组
     */
    private function upload($fields, $type, $size, $img=null, $mark=true, $admin=0, $stype=null, $ofile=null, $document=null) {
	    $path = 'uploadfiles/';
		$upload = $this->instance('file_upload');
		if (empty($admin) && $this->memberinfo) {
			$uid = $this->memberinfo['id']; //会员附件归类
			if ($uid) {
			    $path.= 'member/' . $uid . '/';
				if (isset($this->membergroup[$this->memberinfo['groupid']]['filesize']) && $this->membergroup[$this->memberinfo['groupid']]['filesize']) {
					$c = count_member_size($this->memberinfo['id']);
					if ($c > $this->membergroup[$this->memberinfo['groupid']]['filesize'] * 1024 * 1024) {
					    $this->attMsg(lang('att-7', array('1' => $this->membergroup[$this->memberinfo['groupid']]['filesize'], '2' => formatFileSize($c))), $stype);
					}
				}
			}
			$document = null;
		} elseif ($admin) {
            $uid = (int)get_cookie('member_id');
		} else {
            //$this->attMsg(lang('att-0'), $stype);
            $uid = 0;
            $patp = 'uploadfiles/guest/';
        }
	    $upload->set($_FILES[$fields])->set_limit_size(1024*1024*$size)->set_limit_type($type);
        //设置路径和名称
        $ext = $upload->fileext();
		if (stripos($ext, 'php') !== FALSE || stripos($ext, 'asp') !== FALSE || stripos($ext, 'aspx') !== FALSE) {
			return array('result' => '非法文件');
		}
        if (in_array($ext, array('jpg','jpeg','bmp','png','gif'))) {
            $dir = 'image';
            $upload->set_image($img['w'], $img['h'], $img['t']);
        } else {
            $dir = 'file';
        }
        $path.= $dir . '/' . (empty($document) || $document == 'undefined' || !preg_match('/^[a-zA-Z_0-9]+$/', $document) ? '' : $document . '/');
		if ($ofile && is_file($ofile) && strpos($path, dirname(dirname($ofile))) === 0) { //判断原文件
			$path = dirname($ofile) . '/';
			$file = $fname = basename($ofile);
		} else {
			$path.= date('Ym') . '/';
			$data = file_list::get_file_list($path);
			$name = count($data) + 1;
			$name = is_file($path.$name.'.'.$ext) ? $name.str_replace('0.', '_',(double)microtime()) : $name;
			$file = $upload->filename();
			$fname = $name . '.' . $ext;
		}
        $result = $upload->upload($path, $fname);
		//上传成功处理图片
        if (!$result && $dir == 'image') {
            $this->watermark($path . $fname);
        }
        return array('result' => $result, 'path' => $path . $fname, 'file' => $file , 'ext' => $dir == 'image' ? 1 : $ext);
    }

	/**
     * Swf上传
     */
    public function ajaxswfuploadAction() {
        if ($this->post('submit')) {
            $_type = explode(',', $this->post('type'));
            if (empty($_type)) {
                exit('0,' . lang('att-6'));
            }
            $size = (int)$this->post('size');
            if (empty($size)) {
                exit('0,' . lang('att-5'));
            }
            $data = $this->upload('Filedata', $_type, $size, null, null, $this->post('admin'), 'swf', null, $this->post('document'));
            if ($data['result']) {
                exit('0,' . $data['result']);
            }
            //唯一ID,文件全路径,扩展名,文件名称
            exit(time() . rand(0, 999) . ',' . $data['path'] . ',' . $data['ext'] . ',' . str_replace('|', '_', $data['file']));
        } else {
            exit('0,' . lang('att-4'));
        }
    }

	/**
     * KE上传
     */
	public function kindeditor_uploadAction() {
	    $this->memberCheck();
	    //定义允许上传的文件扩展名
		$ext = array(
			'file'  => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
			'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
			'flash' => array('swf', 'flv'),
			'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb')
		);
		//检查目录
		$dir = $this->get('dir') ? $this->get('dir') : 'image';
		if (!isset($ext[$dir])) {
		    echo json_encode(array('error' => 1, 'message' => lang('att-3', array('1' => $dir))));exit;
		}
		$img = $dir == 'image' ? 1 : 0;
		$size = $img ? 2 : 100;
		//检查文件大小
		if (is_null($_FILES['imgFile']['size']) || $_FILES['imgFile']['size'] > $size * 1024 * 1024) {
		    echo json_encode(array('error' => 1, 'message' => lang('att-2', array('1' => $size))));exit;
		}
		$data = $this->upload('imgFile', $ext[$dir], $size, $img, null, $this->getAdmin(), 'ke', null, $this->get('document'));
		if ($data['result']) {
			echo json_encode(array('error' => 1, 'message' => $data['result']));exit;
		} else {
			echo json_encode(array('error' => 0, 'url' => $data['path']));exit;
		}
	}

	/**
     * KE浏览
     */
	public function kindeditor_managerAction() {
		$root_url = SITE_PATH . 'uploadfiles/';
		$root_path = APP_ROOT . 'uploadfiles/';
		//用户目录设定
		$admin = $this->getAdmin();
		if (empty($admin) && $this->memberinfo) {
			$id = $this->memberinfo['id'];
			if ($id) { //会员附件目录
			    $root_path.= 'member/' . $id . '/';
				$root_url.= 'member/' . $id . '/';
			}
		} elseif (!$this->session->is_set('user_id')) {
		    //属于游客
			exit;
		}
		//图片扩展名
		$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
		//目录名
		$dir_name = $this->get('dir') == 'image' ? 'image' : 'file';
		if ($dir_name !== '') {
			$root_path.= $dir_name . "/";
			$root_url.= $dir_name . "/";
			if (!file_exists($root_path)) {
				mkdir($root_path);
			}
		}
		//根据path参数，设置各路径和URL
		if (empty($_GET['path'])) {
			$current_path = realpath($root_path) . '/';
			$current_url = $root_url;
			$current_dir_path = '';
			$moveup_dir_path  = '';
		} else {
			$_GET['path'] = str_replace('%2F', '', $_GET['path']);
			$current_path = realpath($root_path) . '/' . $_GET['path'];
			$current_url = $root_url . $_GET['path'];
			$current_dir_path = $_GET['path'];
			$moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
		}
		echo realpath($root_path);
		//排序形式，name or size or type
		$order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);
		//不允许使用..移动到上一级目录
		if (preg_match('/\.\./', $current_path)) {
			echo 'Access is not allowed.';
			exit;
		}
		//最后一个字符不是/
		if (!preg_match('/\/$/', $current_path)) {
			echo 'Parameter is not valid.';
			exit;
		}
		//目录不存在或不是目录
		if (!file_exists($current_path) || !is_dir($current_path)) {
			echo 'Directory does not exist.';
			exit;
		}
		//遍历目录取得文件信息
		$file_list = array();
		if ($handle = opendir($current_path)) {
			$i = 0;
			while (false !== ($filename = readdir($handle))) {
				if ($filename{0} == '.' || strpos($filename, '.thumb.') !== false) continue;
				$file = $current_path . $filename;
				if (is_dir($file)) {
					$file_list[$i]['is_dir'] = true; //是否文件夹
					$file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
					$file_list[$i]['filesize'] = 0; //文件大小
					$file_list[$i]['is_photo'] = false; //是否图片
					$file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
				} else {
					$file_list[$i]['is_dir'] = false;
					$file_list[$i]['has_file'] = false;
					$file_list[$i]['filesize'] = filesize($file);
					$file_list[$i]['dir_path'] = '';
					$file_ext = strtolower(substr(strrchr($file, '.'), 1));
					$file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
					$file_list[$i]['filetype'] = $file_ext;
				}
				$file_list[$i]['filename'] = $filename; //文件名，包含扩展名
				$file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
				$i++;
			}
			closedir($handle);
		}

		//排序
		function cmp_func($a, $b) {
			global $order;
			if ($a['is_dir'] && !$b['is_dir']) {
				return -1;
			} else if (!$a['is_dir'] && $b['is_dir']) {
				return 1;
			} else {
				if ($order == 'size') {
					if ($a['filesize'] > $b['filesize']) {
						return 1;
					} else if ($a['filesize'] < $b['filesize']) {
						return -1;
					} else {
						return 0;
					}
				} else if ($order == 'type') {
					return strcmp($a['filetype'], $b['filetype']);
				} else {
					return strcmp($a['filename'], $b['filename']);
				}
			}
		}
		usort($file_list, 'cmp_func');
		$result = array();
		//相对于根目录的上一级目录
		$result['moveup_dir_path'] = $moveup_dir_path;
		//相对于根目录的当前目录
		$result['current_dir_path'] = $current_dir_path;
		//当前目录的URL
		$result['current_url'] = $current_url;
		//文件数
		$result['total_count'] = count($file_list);
		//文件列表数组
		$result['file_list'] = $file_list;
		//输出JSON字符串
		echo json_encode($result);
	}

	/**
     * Ueditor上传
     */
	public function ueditor_uploadAction() {
	    $this->memberCheck();
	    //定义允许上传的文件扩展名
		$ext = array(
			'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
			'file' => array("rar", "doc", "docx", "zip", "pdf", "txt", "swf", "wmv")
		);
		//检查目录
		$dir = $this->get('dir') ? $this->get('dir') : 'image';
		if (!isset($ext[$dir])) {
		    echo json_encode(array('state' => lang('att-3', array('1' => $dir))));exit;
		}
		$img = $dir == 'image' ? 1 : 0;
		$size = $img ? 2 : 100;
		//检查文件大小
		if (is_null($_FILES['upfile']['size']) || $_FILES['upfile']['size'] > $size * 1024 * 1024) {
		    echo json_encode(array('state' => lang('att-2', array('1' => $size))));exit;
		}
		$data = $this->upload('upfile', $ext[$dir], $size, $img, null, $this->getAdmin(), 'ue', null, $this->get('document'));
		if ($data['result']) {
			echo json_encode(array('state' => $data['result']));exit;
		} else {
			echo json_encode(array('state' => 'SUCCESS','fileType' => '.'.$data['ext'], 'url' => $data['path'], 'title' => $this->post('pictitle'), 'original' => $_FILES['upfile']['name']));exit;
		}
	}

	/**
     * Ueditor浏览
     */
	public function ueditor_managerAction() {
		$path = 'uploadfiles/';
		$admin = $this->getAdmin();
		//用户目录设定
		if (empty($admin) && $this->memberinfo) {
			$path.= 'member/' . $this->memberinfo['id'] . '/';
		} elseif (!$this->session->is_set('user_id')) {
		    //属于游客
			exit;
		}
		$dir = $this->get('dir') ? $this->get('dir') : 'image';
		$path.= $dir;
		$action = $this->post('action');
		/**
		 * 遍历获取目录下的指定类型的文件
		 * @param $path
		 * @param array $files
		 * @return array
		 */
		function getfiles( $path , &$files = array() )
		{
			if ( !is_dir( $path ) ) return null;
			$handle = opendir( $path );
			while ( false !== ( $file = readdir( $handle ) ) ) {
				if ( $file != '.' && $file != '..' ) {
					$path2 = $path . '/' . $file;
					if ( is_dir( $path2 ) ) {
						getfiles( $path2 , $files );
					} else {
						if ( preg_match( "/\.(gif|jpeg|jpg|png|bmp)$/i" , $file ) ) {
							$files[] = $path2;
						}
					}
				}
			}
			return $files;
		}
		if ( $action == "get" ) {
			$files = getfiles( $path );
			if ( !$files ) return;
			rsort($files, SORT_STRING);
			$str = "";
			foreach ( $files as $file ) {
				$str.= $file . "ue_separate_ue";
			}
			echo $str;
		}
	}

	/**
     * 会员组权限检测
     */
	private function memberCheck() {
	    if ($this->memberinfo && !$this->session->is_set('user_id')) {
			$group = $this->membergroup[$this->memberinfo['groupid']];
			if (empty($group)) {
                return false;
            }
			if (empty($group['allowattachment'])) {
				$this->attMsg(lang('att-1', array('1' => $group['name'])));
			}
        }
	}

	/**
     * 判断是否来自后台
     */
	private function getAdmin() {
	    if (isset($_SERVER['HTTP_REFERER'])
            && $_SERVER['HTTP_REFERER']
            && stripos($_SERVER['HTTP_REFERER'], 's=' . ADMIN_NAMESPACE) !== false) {
		    return 1;
		}
		return 0;
	}

	/**
     * 消息提示
     */
	private function attMsg($msg, $stype=null) {
	    if ($stype == 'swf') {
		    exit('0,' . $msg);
		} elseif ($stype == 'ke') {
		    echo json_encode(array('error' => 1, 'message' => $msg));exit;
		} elseif ($stype == 'ue') {
		    echo json_encode(array('state' => $msg));exit;
		}
	    exit("<div style='padding-top:40px;text-align:center;font-size:14px;'><font color=red>×</font>&nbsp;&nbsp;" . $msg . "</div>");
	}

}
