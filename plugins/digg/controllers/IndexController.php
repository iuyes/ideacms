<?php

class IndexController extends Plugin {
	
    public function __construct() {
        parent::__construct();
    }
	
	//显示
	public function showAction() {
	    $cid   = $this->get('id');
		$cdata = $this->content->find($cid, 'title');
		if (empty($cdata))  exit('');
		$data  = $this->digg->where('contentid=' . $cid)->select(false);
		if (empty($data)) {
		    $data = array('contentid'=>$cid, 'title'=>$cdata['title'], 'cai'=>0, 'ding'=>0);
		}
		if($data['ding'] + $data['cai'] == 0) {
			$data['dingper'] = $data['caiper'] = 0;
		} else {
			$data['dingper'] = number_format($data['ding'] / ($data['ding'] + $data['cai']), 3) * 100;
			$data['caiper']  = 100 - $data['dingper'];
		}
		$data['dingper'] = trim(sprintf("%4.2f", $data['dingper']));
        $data['caiper']  = trim(sprintf("%4.2f", $data['caiper']));
		$setting = string2array($this->data['setting']);
		$this->assign(array(
		    'data' => $data,
			'ding' => $setting['dingname'] ? $setting['dingname'] : '顶一下',
			'cai'  => $setting['cainame']  ? $setting['cainame']  : '踩一下',
		));
		ob_start();
		$this->display('show');
		$html = ob_get_contents();
		ob_clean();
		$html = addslashes(str_replace(array("\r", "\n", "\t", chr(13)), array('', '', '', ''), $html));
	    echo 'document.write("' . $html . '");';
	}
	
	//提交
	public function addAction() {
	    $cid   = $this->get('id');
		$type  = $this->get('type');
		if (empty($cid) || $type == '')     exit(json_encode(array('status'=>0, 'data'=>'参数不完整')));
		$cdata = $this->content->find($cid, 'title');
		if (empty($cdata))                  exit(json_encode(array('status'=>0, 'data'=>'信息出错')));
		if (cookie::is_set('digg_' . $cid)) exit(json_encode(array('status'=>0, 'data'=>'请不要重复操作哦，休息一会儿吧')));
		$data  = $this->digg->getOne('contentid=' . $cid);
		if (empty($data)) {
		    $data = array('contentid'=>$cid, 'title'=>$cdata['title'], 'addtime'=>time(), 'ding'=>0, 'cai'=>0);
		    if ($type) {
				$data['ding'] ++;
			} else {
				$data['cai'] ++;
			}
		    $id = $this->digg->insert($data);
			if (empty($id)) exit(json_encode(array('status'=>0, 'data'=>'Insert出错')));
		} else {
		    if ($type) {
				$data['ding'] ++;
			} else {
				$data['cai'] ++;
			}
			$id = $data['id'];
			unset($data['id']);
			$data['addtime'] = time();
			$this->digg->update($data, 'id=' . $id);
		}
		cookie::set('digg_' . $cid, 1, 360); //间隔360秒
		if($data['ding'] + $data['cai'] == 0) {
			$data['dingper'] = $data['caiper'] = 0;
		} else {
			$data['dingper'] = number_format($data['ding'] / ($data['ding'] + $data['cai']), 3) * 100;
			$data['caiper']  = 100 - $data['dingper'];
		}
		$data['dingper'] = trim(sprintf("%4.2f", $data['dingper']));
        $data['caiper']  = trim(sprintf("%4.2f", $data['caiper']));
		$result = array('status'=>1, 'ding'=>$data['ding'], 'dingper'=>$data['dingper'], 'cai'=>$data['cai'], 'caiper'=>$data['caiper']);
		exit(json_encode($result));
	}

}