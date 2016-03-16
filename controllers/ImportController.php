<?php

/**
 * @name		火车采集器接口程序
 * @author		976510651@qq.com
 * @version		v2.0
 * @description	采集数据可以不再手动修改接收参数，程序自动处理POST数据
 */

class ImportController extends Common {

	public function __construct() {
        parent::__construct();
	}
	
	public function categoryAction() {
	    echo "<select name='catid'>";
		foreach ($this->cats as $cat) {
		    if ($cat['typeid']==1 && $cat['child']==0) echo "<option value='" . $cat['catid'] . "'>" . $cat['catname'] . "</option>";
		}
		echo "</select>";
	}
	
	public function contentAction() {
		//参数接收
		$data = array();
		if (!isset($_POST) || empty($_POST)) exit('您没有此权限');
		foreach ($_POST as $n=>$v) {
			$data[$n] = $this->post($n);
		}
	    //验证权限
		$user = $this->model('user');
		if (!$user->check_login($data['username'], $data['password'])) exit('您没有此权限');
	    //参数判断
		if (!isset($data['title']) || empty($data['title'])) 	 exit('标题不能为空');
	    if (!isset($data['catid']) || empty($data['catid'])) 	 exit('栏目不能为空');
	    if (!isset($data['content']) || empty($data['content'])) exit('内容不能为空');
	    //内容模型
	    $model	 = $this->get_model();
	    $modelid = $this->cats[$data['catid']]['modelid'];
	    $table	 = $model[$modelid]['tablename'];
		if (empty($table)) exit('模型不存在');
	    //数据处理
		$data['status']    = 0;	//文档状态，0回收站
		$data['keywords']  = getKw($data['title']);	//从标签获取关键字
		$data['sysadd']    = 1;	//作为管理员录入
		$data['modelid']   = $modelid;	//内容模型id
		$data['inputtime'] = $data['updatetime'] = time();	//入库时间
		unset($data['password']);
		//数据入库
		$result  = $this->content->getOne('title=?', $data['title']);
		if ($result) {
			exit('已经存在');
			/*
			注释段是更新文档
			unset($data['inputtime']);
			$result = $this->content->set($result['id'], $table, $data);
			if (!is_numeric($result)) exit('添加失败');
			*/
		} else {
			$result = $this->content->set(0, $table, $data);
			if (!is_numeric($result)) exit('添加失败');
		}
		//更新URL地址
	    $data['id'] = $result;
	    $this->content->url($data['id'], $this->getUrl($data));
	    exit('发布成功');
	}
}