<?php

class IndexController extends Plugin {
	
	private $mood;
	
    public function __construct() {
        parent::__construct();
		$this->mood = $this->model('mood');
    }
	
	//显示心情投票
	public function showAction() {
	    $cid  = $this->get('id'); //内容表id
		$mood = $this->cache->get('mood');
		if (empty($cid))  exit('');
		if (empty($mood)) exit('');
		$data  = $this->mood->where('contentid=' . $cid)->select(false);
		//计算图形柱
		foreach ($mood as $name=>$v) {
			if (!isset($data[$name])) $data[$name] = 0;
			if (isset($data['total']) && !empty($data['total'])) {
				$mood[$name]['per'] = ceil(($data[$name]/$data['total']) * 60);
			} else {
				$data['total'] = $mood[$name]['per'] = 0;
			}
		}
		$this->assign(array(
			'cid'  => $cid,
			'per'  => $this->viewpath . 'images/bg.gif',
		    'mood' => $mood,
			'data' => $data,
		));
		ob_start();
		$this->display('show');
		$html = ob_get_contents();
		ob_clean();
		$html = addslashes(str_replace(array("\r", "\n", "\t"), array('', '', ''), $html));
	    echo 'document.write("' . $html . '");';
	}
	
	//提交投票
	public function voteAction() {
	    $id   = $this->get('id');
	    $cid  = $this->get('cid'); //内容表id
		$name = $this->get('name');
		if (empty($cid) || empty($name)) exit(json_encode(array('status' => 0, 'data' => '参数不完整')));
		if (cookie::is_set('mood_' . $id)) exit(json_encode(array('status' => 0, 'data' => '您已经表达过心情了，请您休息一会儿吧')));
		$mood = $this->cache->get('mood');
		if ($id && $row = $this->mood->find($id)) {
		    $data = array(
			    $name        => $row[$name]+1,
				'total'      => $row['total']+1,
				'lastupdate' => time(),
			);
		    $this->mood->update($data, 'id=' . $id);
		} else {
		    $data = array(
				$name        => 1,
				'total'      => 1,
			    'contentid'  => $cid,
				'lastupdate' => time(),
			);
		    $id   = $this->mood->insert($data);
			if (empty($id)) exit(json_encode(array('status' => 0, 'data' => '数据加入失败')));
		}
		$result = array('status' => 1, 'value' => $data[$name]);
		cookie::set('mood_' . $id, 1, 360); //投票间隔360秒
		exit(json_encode($result));
	}

}