<?php

class IndexController extends Plugin {

    public function __construct() {
        parent::__construct();
    }
    
    public function gotoAction() {
        $id   = (int)$this->get('id');
        $data = $this->adsense_data->find($id);
        $set  = string2array($data['setting']);
        $url  = check::is_url($set['setting_url']) ? $set['setting_url'] : SITE_URL;
        $this->adsense_data->update(array('clicks'=>$data['clicks']+1), 'id=' . $id);
        header('Location: ' . $url);
    }
	
	public function getAction() {
	    $id   = (int)$this->get('id');
	    $html = $this->getData($id);
		$html = $html ? htmlspecialchars_decode($html) : 'Adsense Is NULL';
	    $html = addslashes(str_replace(array("\r", "\n", "\t", chr(13)), array('', '', '', ''), $html));
		$html = str_replace(array('<!--', '//-->'), '', $html);
	    echo 'document.write("' . $html . '");';
	}
	
	private function getData($id) {
	    $adsense = $this->cache->get('adsense');
		$adsense = $adsense[$id];
		if (empty($adsense['data']) || !is_array($adsense['data'])) return null;
		//判断广告的有效性
		$list    = array();
		foreach ($adsense['data'] as $t) {
			if ($t['disabled'] == 0) {
				if ($t['enddate'] == 0) {
					$list[] = $t;
				} elseif ($t['enddate'] - $t['startdate'] > 0) {
					$list[] = $t;
				}
			}
		}
		if ($adsense['showtype'] == 1) {
			//顺序显示
			$data = $list[0];
		} else {
			//随机显示
			$key  = array_rand($list, 1);
			$data = $list[$key];
		}
		if (empty($data)) return null;
		//输出广告
		$body  = '<div id="adsense_' . $id . '">';
		$set   = string2array($data['setting']);
		if ($data['typeid'] == 1) {
			//图片广告
			$url    = url('adsense/index/goto/', array('id'=>$id));
			$width  = $adsense['width']  ? 'width=' . $adsense['width']   : '';
			$height = $adsense['height'] ? 'height=' . $adsense['height'] : '';
			$body  .= '<a href="' . $url . '" target="_blank"><img src="' . image($set['setting_thumb']) . '" ' . $width . ' ' . $height . '></a>';
		} elseif ($data['typeid'] == 2) {
			//代码广告
			$body  .= $set['setting_content'];
		}
		$body .= '</div>';
		return $body;
	}
    
}