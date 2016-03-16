<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * IdeaCMS
 *
 * @since		version 2.5.0
 * @author		连普创想 <976510651@qq.com>
 * @copyright   Copyright (c) 2015-9999, 连普创想, Inc.
 */
	
class Wx extends Admin {

    public $file;
    protected $wx_config;
    protected $expires_in;
    protected $cache_file;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->expires_in = 7200;
        $this->file = FCPATH.'config/weixin.php';
        $this->cache_file = 'wx_user';
        if(file_exists($this->file))
            $this->wx_config = unserialize(file_get_contents($this->file));
        $this->createTable();
    }
	
	/**
     * 配置
     */
    public function config() {

		if (IS_POST) {
			$data = $this->input->post('data');
			$size = file_put_contents($this->file, array2string($data));
			if (!$size) {
                $this->adminMsg('config目录无权限写入');
            }
			$this->adminMsg('保存成功', url('admin/wx/config'), 3, 1, 1);
		}

		$this->template->assign(array(
			'data' => is_file($this->file) ? string2array(file_get_contents($this->file)) : array(),
		));
		$this->template->display('admin/wx_config.html');
    }
///////////////////////////////MENU//////////////////////////////////////
    /**
     * 微信菜单管理主页
     */
    public function menu()
    {

        if(!file_exists($this->file))
            $this->adminMsg("请先配置好微信的基本设置",url('admin/wx/config'),3,1,0);



        if ($this->post('submit')) {
            foreach ($_POST as $var => $value) {
                if (strpos($var, 'order_') !== false) {
                    $this->db
                        ->where('id',(int)str_replace('order_', '', $var))
                        ->update('wx_menu',array('displayorder'=>$value));
                }
            }
            $this->adminMsg(lang('success'), url('admin/wx/menu/index'), 3, 1, 1);
        }
        if ($this->post('delete')) {
            $ids = $this->post('ids');
            if ($ids) {
                foreach($ids as $id) {
                    $this->delMenu($id, 1);
                }
            }
            $this->adminMsg(lang('success'), url('admin/wx/menu'), 3, 1, 1);
        }
        $menu = $this->db
            ->where('pid',0)
            ->order_by('displayorder asc')->get('wx_menu')
            ->result_array();
        if ($menu) {
            foreach ($menu as $i => $t) {
                $menu[$i]['data'] = $this->db
                    ->where('pid', (int)$t['id'])
                    ->order_by('displayorder asc')
                    ->get('wx_menu')
                    ->result_array();
            }
        }
        $this->template->assign(array(
            'data' => $menu,
        ));
        $this->template->display('admin/wx_menu.html');
    }

    /**
     * 添加微信菜单功能
     */
    public function addMenu()
    {
        if(!file_exists($this->file))
            $this->adminMsg("请先配置好微信的基本设置",url('admin/wx/config'),3,1,0);
        $pid = (int)$this->input->get('pid');
        if (IS_POST) {
            $data = $this->input->post('data');
            if (!$data['name']) {
                $this->adminMsg('名称不能为空！');
            }
            $data['displayorder'] = 0;
            $this->db->insert('wx_menu', $data);
            $this->adminMsg(lang('success'),url('admin/wx/menu'),3,1,1);
        } else {
            $data['type'] = 'click';
        }

        $this->template->assign(array(
            'pid' => $pid,
            'data' => $data,
            'app' => $this->db
                ->get('wx_app')
                ->result_array(),
            'top' => $this->db
                ->where('pid', 0)
                ->order_by('displayorder asc')
                ->get('wx_menu')
                ->result_array(),
        ));
        $this->template->display('admin/wx_addmenu.html');

	}

    /**
     *  删除菜单微信菜单
     * @param int $id 要删除的菜单id
     * @param int $all 是否批量删除
     */
    public function delMenu($id = 0, $all = 0)
    {
        $id = $id ? $id : (int)$this->get('id');
        $all = $all ? $all : $this->get('all');

        $result = $this->db
            ->where('id',$id)
            ->or_where('pid',$id)
            ->delete('wx_menu');

        if ($result) {
            $all or $this->adminMsg(lang('success'), url('admin/wx/menu'), 3, 1, 1);
        } else {
            $all or $this->adminMsg(lang('a-cat-8'));
        }

    }

    /**
     * 编辑菜单
     */
    public function editMenu()
    {
        $id = $this->input->get('id');

        if(IS_POST)
        {
            $data = $this->input->post('data');
            if (!$data['name']) {
                $this->adminMsg('名称不能为空！');
            }
            $data['displayorder'] = 0;
            $this->db->where('id',$id)->update('wx_menu', $data);
            $this->adminMsg(lang('success'),url('admin/wx/add_menu'),3,1,1);
        }
        $this->template->assign(
            array(
                'data' => $this->db
                ->where('id',$id)
                ->get('wx_menu')
                ->row_array(),
            )
        );
        $this->template->display('admin/wx_addmenu.html');
    }

    /**
     * 同步菜单
     */
    public function syncMenu()
    {
        $app = array();
        $data = $this->db->where('pid', 0)->order_by('displayorder asc')->get('wx_menu')->result_array();
        if ($data) {
            $json = array();
            foreach ($data as $i => $t) {
                $list = $this->db->where('pid', (int)$t['id'])->order_by('displayorder asc')->get('wx_menu')->result_array();
                if ($list) {
                    $val = array();
                    foreach ($list as $c) {
                        // 按应用来判断类别
                        if ($c['type'] == 'click') {
                            $c['type'] = 'view';
                            $c['url'] = SITE_URL.'/index.php?c=wx&m=click&action='.$c['key'];
                        }
                        $value = array(
                            'name' => $c['name'],
                            'type' => $c['type']
                        );
                        if ($c['type'] == 'click') {
                            $value['key'] = $c['key'];
                        } else {
                            $value['url'] = $c['url'];
                        }
                        $val[] = $value;
                    }
                    $json[] = array(
                        'name' => $t['name'],
                        'sub_button' => $val
                    );
                } else {
                    // 按应用来判断类别
                    if ($t['type'] == 'click') {
                        $c['type'] = 'view';
                        $c['url'] = SITE_URL.'/index.php?c=wx&m=click&action='.$c['key'];
                    }
                    $value = array(
                        'name' => $t['name'],
                        'type' => $t['type']
                    );
                    if ($t['type'] == 'click') {
                        $value['key'] = $t['key'];
                    } else {
                        $value['url'] = $t['url'];
                    }
                    $json[] = $value;
                }
            }
            //$body = json_encode(array('button'=>$json), JSON_UNESCAPED_UNICODE);
            $body = $this->_en_json(array('button'=>$json));

            $result = json_decode($this->_post(
                'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->_get_access_token(),
                $body
            ), true);
            if ($result['errcode']) {
                $this->adminMsg('（错误代码#'.$result['errcode'].'）'.$result['errmsg']);
            } else{
                $this->adminMsg('同步成功，需要24小时微信客户端才会展现出来。', url('admin/wx/menu'),3,3,1);
            }
        } else {
            $this->adminMsg('你还没有创建菜单呢！');
        }
    }
////////////////////////////////////USER////////////////////////////////////
    /**
     * 关注者管理主页
     */
    public function user()
    {

        $page = (int)$this->get('page');
        $data  = $this->_pagelist(10,'wx_user',$page,'admin/wx/user','subscribe_time DESC');
        $this->template->assign(
            array(
                'data'  => $data['data'],
                'pages' => $data['pages']
            )
        );
        $this->template->display('admin/wx_member_list');
    }

    /**
     * 发送消息给关注用户
     */
    public function userSend()
    {

        $action = $this->input->post('action');
        $ids = $this->input->post('ids');
        if (!$ids && $action != 'all') {
            $this->adminMsg('未选择任何用户！');
        }

        $resources = $this->db
            ->get('wx_content')
            ->result_array();

        $data['ids'] = $ids;
        if($action == 'all')
        {
            if(IS_POST)
            {
                $data = $this->input->post('data');
                if(!$data['content'] && !$data['type'])
                    $this->adminMsg('回复内容不能为空');
                $this->replyUsers($data,'',true);
            }
            $data['to'] = '所有用户';
            $data['action'] = 'all';
        }
        else
        {

            $users = $this->db
                ->where_in('id',$ids)
                ->get('wx_user')
                ->result_array();
            if(IS_POST)
            {
                $data = $this->input->post('data');

                if(!$data['content'] && !$data['type'])
                    $this->adminMsg('回复内容不能为空');
                $this->replyUsers($data,$users);
            }

            foreach ($users as $user) {
                $nickname[] = $user['nickname'];
            }
            $data['to'] = strcut(join(' ; ',$nickname),80);
            $data['action'] = 'sel';
        }

        $this->template->assign(
            array(
                'data' => $data,
                'resources' => $resources
            )
        );
        $this->template->display('admin/wx_member_send');
    }

    /**
     * 推送文章给关注者
     */
    public function sendContent()
    {

        $ids = $this -> input -> get('ids');
        $catid = $this ->input->get('$catid');
        if(!$ids)
            $this->adminMsg('还没选择任何文章!');
        $ids = explode(',',$ids);
        $contents = $this->db
            ->where_in('id',$ids)
            ->order_by('updatetime DESC')
            ->get('content_'.SITE_ID)
            ->result_array();
        $data1 = $contents[0];
        foreach ($contents as $k => $d)
        {
            if($k)
                $data1['other'][] = $d;
        }
        $users = $this->db
            ->get('wx_user')
            ->result_array();

        if(!$users)
        {
            $this->adminMsg('微信公众号还没有用户关注！');
        }

        foreach ($users as $user)
        {
            $data['ids'][] = $user['openid'];
        }
        $data['content'] = array(
            'msgtype' => 'news',
            'news' => array(
                'articles' => array(
                    array(
                        'title' => $data1['title'],
                        'description' => $data1['description'],
                        'url' => SITE_URL.ltrim($data1['url'],'/'),
                        'picurl' => getImage($data1['thumb'])
                    ),
                )
            )
        );
        if ($data1['other']) {
            foreach ($data1['other'] as $i => $t) {
                $data['content']['news']['articles'][] = array(
                    'title' => $t['title'],
                    'description' => '',
                    'url' => SITE_URL.ltrim($t['url'],'/'),
                    'picurl' => getImage($t['thumb'])
                );
            }
        }

        $ok = $error = 0;
        foreach ($data['ids'] as $id) {
            $json = $data['content'];
            $json['touser'] = $id;
            $body = $this->_en_json($json);
            $result = json_decode($this->_post(
                'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->_get_access_token(),
                $body
            ), true);
            if ($result['errcode'] == 0 ) {
                $ok++;
            } else {
                $error++;
                $msg = $result['errmsg'];
            }
        }
        $this->adminMsg("成功发送{$ok}条，失败{$error}条。{$msg}", url('admin/content',array('modelid'=>1,'catid'=>$catid)), 3, 1,1);
    }

    /**
     *
     * 发送消息给用户方法
     * @param $data1 消息数据
     * @param $users 用户
     * @param bool|false $all 是否发送给所有用户
     */
    public function replyUsers($data1,$users,$all = false)
    {
        if($all)
        {
            $users = $this->db
                ->get('wx_user')
                ->result_array();
        }

        $open = array();
        foreach ($users as $t) {
            $open[] = $t['openid'];
        }
        $data = array(
            'ids' => $open,
        );

        $post = $data1;
        if ($post['type']) {
            // 素材
            $cdata = $this->db->where('id', (int)$post['cid'])->get('wx_content')->row_array();
            $cdata['url'] = SITE_URL.APP_DIR.'/index.php?c=wx&a=showResource&id='.$cdata['id'];
            $cdata['orther'] = istring2array($cdata['orther']);

            // 图文素材
            $data['content'] = array(
                'msgtype' => 'news',
                'news' => array(
                    'articles' => array(
                        array(
                            'title' => $cdata['title'],
                            'description' => $cdata['description'],
                            'url' => $cdata['url'],
                            'picurl' => getImage($cdata['thumb'])
                        ),
                    )
                )
            );
            if ($cdata['orther']) {
                foreach ($cdata['orther'] as $i => $t) {
                    $data['content']['news']['articles'][] = array(
                        'title' => $t['title'],
                        'description' => '',
                        'url' => $cdata['url'].'&page='.$i,
                        'picurl' => getImage($t['thumb'])
                    );
                }
            }

        } else {
            // 文本
            $data['content'] = array(
                'msgtype' => 'text',
                'text' => array(
                    'content' => $post['content']
                )
            );
        }

        $ok = $error = 0;
        foreach ($data['ids'] as $id) {
            $json = $data['content'];
            $json['touser'] = $id;
            $body = $this->_en_json($json);
            $result = json_decode($this->_post(
                'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->_get_access_token(),
                $body
            ), true);

            if ($result['errcode'] == 0 ) {
                $ok++;
            } else {
                $error++;
                $msg = $result['errmsg'];
            }
        }
        $this->adminMsg("成功发送{$ok}条，失败{$error}条。{$msg}", url('admin/wx/user'), isset($msg) ? 5 : 3 , 1,1);
    }

    /**
     * 同步用户方法
     */
    public function syncUsers()
    {
        $ok = $this->input->get('ok');
        if (!$ok) {
            $this->adminMsg("正在从微信服务端同步导入关注用户...", iurl('admin/wx/syncUsers', array('ok' => 1)), 2, 2,1);
        }
        $key = $this->_get_access_token();
        $data = json_decode(@icatcher_data('https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$key), true);
        if (!$data) {
            $this->adminMsg("微信服务端还没有关注的用户");
        }
        if (isset($data['errcode']) && $data['errcode']) {
            $this->adminMsg('错误代码：'.$data['errcode'].'，<a style="font-size: 14px;" href="http://mp.weixin.qq.com/wiki/17/fa4e1434e57290788bde25603fa2fcbd.html" target=_blank>点击查看错误详情</a>');
        }
        $userid = array();
        foreach ($data['data']['openid'] as $id) {
            $result = json_decode(@icatcher_data('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$key.'&openid='.$id), true);
            if (isset($result['errcode'])) {
                continue;
            }
            $user = $this->db->where('openid', $id)->get('wx_user')->row_array();
            if ($user) {
                // 修改
                $this->db
                    ->where('id',$user['id'])
                    ->update('wx_user', array(
                    'sex' => $result['sex'],
                    'city' => $result['city'],
                    'country' => $result['country'],
                    'nickname' => strip_tags($result['nickname']),
                    'province' => $result['province'],
                    'language' => $result['language'],
                    'headimgurl' => $result['headimgurl'],
                    'subscribe_time' => $result['subscribe_time'],
                ));
                $userid[] = $user['id'];
            } else {
                // 添加
                $this->db->insert('wx_user', array(
                    'uid' => 0,
                    'sex' => $result['sex'],
                    'city' => $result['city'],
                    'openid' => $id,
                    'country' => $result['country'],
                    'nickname' => $result['nickname'],
                    'province' => $result['province'],
                    'language' => $result['language'],
                    'headimgurl' => $result['headimgurl'],
                    'location_x' => '',
                    'location_y' => '',
                    'location_info' => '',
                    'subscribe_time' => $result['subscribe_time'],
                ));
                $userid[] = $this->db->insert_id();
            }
        }
        if ($userid) {
            $this->db->where('id not in('.implode(',', $userid).')')->delete('wx_user');
        }
        $this->adminMsg('共导入'.count($data['data']['openid']).'个用户', url('admin/wx/user'), 3, 1, 1);
    }
//////////////////////////////RESOURCE//////////////////////////////////////
    /**
     * 素材管理主页
     */
    public function index()
    {
        if ($this->post('delete')) {
            $ids = $this->post('ids');
            if ($ids) {
                foreach ($ids as $id) {
                    $this->delResource($id, 1);
                }
            }
            $this->adminMsg(lang('success'), url('admin/wx/index'), 3, 1, 1);
        }

        $data = $this->_pagelist(10,'wx_content',(int)$this->get('page'),'admin/wx/index');
        $this->template->assign(
            array(
                'data' => $data['data'],
                'pages'=> $data['pages'],
            )
        );
        $this->template->display('admin/wx_resources_list.html');
    }

    /**
     * 添加素材
     */
    public function addResource()
    {

        if(IS_POST)
        {
            $data = $this->input->post('data');
            $data['tid'] = 0;


            if($data['title'] == NUll or $data['content'] == NULL)
            {
                $this->adminMsg('素材标题或正文不能为空');
            }

            $arr = $data;

            $arr['inputtime'] = time();
            $this->db->insert('wx_content',$arr);

            $this->adminMsg('素材添加成功！',url('admin/wx/index'),2,1,1);

        }

        $this->assign(
            array(
                'data' => $data
            )
        );
        $this->template->display('admin/wx_add_resource.html');
    }

    /**
     * 编辑素材
     */
    public function editResource()
    {
        $id = $this->input->get('id');

        if(IS_POST)
        {
            $data = $this->input->post('data');


            if($data['title'] == NUll or $data['content'] == NULL)
            {
                $this->adminMsg('素材标题或正文不能为空');
            }

            $arr = $data;

            $arr['inputtime'] = time();
            $this->db
                ->where('id',$id)
                ->update('wx_content',$arr);

            $this->adminMsg('素材修改成功！',url('admin/wx/index'),2,1,1);
        }
        $list = $this->db
            ->where('id',$id)
            ->get('wx_content')->row_array();

        $list['images'] = unserialize($list['thumb']);
        $this->template->assign(
            array(
                'data' => $list,
            )
        );
        $this->template->display('admin/wx_add_resource.html');
    }

    /**
     *  删除素材
     * @param int $id
     * @param int $all
     */
    public function delResource($id = 0, $all = 0)
    {
        $id = $id ? $id : (int)$this->get('id');
        $all = $all ? $all : $this->get('all');

        $result = $this->db
            ->where('id',$id)
            ->delete('wx_content');

        if ($result) {
            $all or $this->adminMsg(lang('success'), url('admin/wx/index'), 3, 1, 1);
        } else {
            $all or $this->adminMsg(lang('a-cat-8'));
        }
    }
/////////////////////////////////REPLY////////////////////////////////////////

    /**
     * 回复关键字管理主页
     */
    public function keyword()
    {

        if ($this->post('delete')) {
            $ids = $this->post('ids');
            if ($ids) {
                foreach ($ids as $id) {
                    $this->delKeyword($id, 1);
                }
            }
            $this->adminMsg(lang('success'), url('admin/wx/keyword'), 3, 1, 1);
        }
        $data = $this->_pagelist(15,'wx_reply',(int)$this->get('page'),'admin/wx/keyword');
        $this->template->assign(
            array(
                'list' => $data['data'],
                'pages' => $data['pages'],
            )
        );
        $this->template->display('admin/wx_keyword.html');
    }


    /**
     * 添加回复关键字
     */
    public function addKeyword()
    {

        if(IS_POST)
        {
            $data = $this->input->post('data');

            if($data['content'] == null || $data['keyword'] == null)
                $this->adminMsg('添加关键字或内容不能为空！');
            $keywords = explode(' ',$data['keyword']);
            foreach ($keywords as $keyword){
                if($keyword) {
                    $data['keyword'] = $keyword;
                    if(!$this->db->where('keyword =',$keyword)->get('wx_reply')->row_array())
                        $this->db->insert('wx_reply', $data);
                    else
                        $this->adminMsg("关键字“{$keyword}”已经存在！");
                }
            }


            $this->adminMsg('添加成功！',url('admin/wx/keyword'),3,1,1);
        }
        $resources = $this->db->get('wx_content')->result_array();

        $this->template->assign(
            array(
                'resources' => $resources,

            )
        );
        $this->template->display('admin/wx_add_keyword');
    }

    /**
     * 编辑回复关键字
     */
    public function editKeyword()
    {
        $id = $this->input->get('id');

        if(IS_POST)
        {
            $data = $this->input->post('data');

            $this->db
                ->where('id',$id)
                ->update('wx_reply',$data);
            $this->adminMsg('修改成功！',url('admin/wx/keyword'),3,1,1);

        }
        $data1 = $this->db
            ->where('id',$id)
            ->get('wx_reply')
            ->row_array();
        $resources = $this->db->get('wx_content')->result_array();

        $this->template->assign(
            array(
                'resources' => $resources,
                'data'      => $data1

            )
        );
        $this->template->display('admin/wx_add_keyword');
    }

    /**
     *  删除回复关键字
     * @param int $id
     * @param int $all
     */
    public function delKeyword($id = 0, $all =0)
    {
        $id = $id ? $id : (int)$this->get('id');
        $all = $all ? $all : $this->get('all');

        $result = $this->db
            ->where('id',$id)
            ->delete('wx_reply');

        if ($result) {
            $all or $this->adminMsg(lang('success'), url('admin/wx/keyword'), 3, 1, 1);
        } else {
            $all or $this->adminMsg(lang('a-cat-8'));
        }
    }
    //////////////////////////RESOLVE METHOD////////////////////////////
    /**
     * 获取access_token
     * @return mixed
     */
    protected function _get_access_token() {

        $name = APP_ROOT.'cache/'.SITE_ID.'_access_token.txt';
        $data = @json_decode(@file_get_contents($name), true);
        if (isset($data['time']) && $data['time'] > time() && isset($data['access_token']) && $data['access_token']) {
            return $data['access_token'];
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->wx_config['appid'].'&secret='.$this->wx_config['appsecret'];
        $data = json_decode(@icatcher_data($url), true);
        if (!$data) {
            @unlink($name);
            $this->adminMsg('获取access_token失败，请检查服务器是否支持远程链接');
        }
        if (isset($data['errmsg']) && $data['errmsg']) {
            @unlink($name);
            $this->adminMsg('错误代码（'.$data['errcode'].'）：'.$data['errmsg']);
        }
        $data['time'] = time() + $data['expires_in'];
        @file_put_contents($name, json_encode($data));

        return $data['access_token'];
    }
    /**
     * POST请求
     */
    protected function _post($url, $params) {
        if (function_exists('curl_init')) { // curl方式
            $oCurl = curl_init();
            if (stripos($url, 'https://') !== FALSE) {
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            }
            $string = $params;
            if (is_array($params)) {
                $aPOST = array();
                foreach ($params as $key => $val){
                    $aPOST[] = $key.'='.urlencode($val);
                }
                $string = join('&', $aPOST);
            }
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POST, TRUE);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $string);
            $response = curl_exec($oCurl);
            curl_close($oCurl);
            return $response;
        } elseif (function_exists('stream_context_create')) { // php5.3以上
            $opts = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($params),
                )
            );
            $_opts = stream_context_get_params(stream_context_get_default());
            $context = stream_context_create(array_merge_recursive($_opts['options'], $opts));
            return file_get_contents($url, false, $context);
        } else {
            return FALSE;
        }
    }
    // 转化为json
    protected function _en_json($data) {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            return urldecode(json_encode(_url_encode($data)));
        } else {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 创建表
     */
    protected function createTable()
    {
        $this->db->query(" CREATE TABLE IF NOT EXISTS `".$this->db->dbprefix."wx_content` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `tid` tinyint(1) unsigned NOT NULL COMMENT '素材类型',
          `cid` varchar(255) DEFAULT NULL COMMENT '关联内容id',
          `title` varchar(255) NOT NULL COMMENT '标题',
          `thumb` varchar(255) NOT NULL COMMENT '图片',
          `description` varchar(255) DEFAULT NULL COMMENT '描述',
          `url` varchar(255) DEFAULT NULL COMMENT '更多阅读地址',
          `content` text NOT NULL COMMENT '详细内容',
          `orther` mediumtext COMMENT '其他数据信息',
          `inputtime` int(10) unsigned NOT NULL COMMENT '输入时间',
          PRIMARY KEY (`id`),
          KEY `tid` (`tid`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '素材内容表';
                ");
        $this->db->query("CREATE TABLE IF NOT EXISTS `".$this->db->dbprefix."wx_app` (
          `id` int(10) NOT NULL AUTO_INCREMENT,
          `type` tinyint(1) NOT NULL COMMENT '应用类型',
          `name` varchar(50) NOT NULL COMMENT '应用名称',
          `images` text NOT NULL,
          `filename` varchar(50) NOT NULL COMMENT '文件名称',
          `config` text COMMENT '应用本身信息',
          `setting` text COMMENT '应用配置信息',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '微信应用表';
                        ");
        $this->db->query("CREATE TABLE IF NOT EXISTS `".$this->db->dbprefix."wx_menu` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `pid` int(10) unsigned NOT NULL,
          `type` char(10) NOT NULL,
          `name` varchar(30) NOT NULL,
          `key` varchar(30) DEFAULT NULL,
          `url` varchar(255) DEFAULT NULL,
          `displayorder` tinyint(3) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `pid` (`pid`),
          KEY `displayorder` (`displayorder`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '微信菜单表'; ");
        $this->db->query(" CREATE TABLE IF NOT EXISTS `".$this->db->dbprefix."wx_reply` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '回复类型',
          `keyword` varchar(255) NOT NULL COMMENT '关键字',
          `app` varchar(30) DEFAULT NULL COMMENT '应用目录',
          `cid` int(10) NOT NULL DEFAULT '0' COMMENT '素材id',
          `content` text COMMENT '文本信息',
          `count` int(10) NOT NULL DEFAULT '0' COMMENT '总计回复次数',
          PRIMARY KEY (`id`),
          KEY `count` (`count`),
          KEY `keyword` (`keyword`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '微信回复规则表';");
        $this->db->query(" CREATE TABLE IF NOT EXISTS `".$this->db->dbprefix."wx_user` (
          `id` int(10) NOT NULL AUTO_INCREMENT,
          `uid` int(10) unsigned DEFAULT NULL COMMENT '会员id',
          `openid` varchar(50) NOT NULL COMMENT '唯一id',
          `nickname` varchar(50) NOT NULL COMMENT '微信昵称',
          `sex` tinyint(1) unsigned DEFAULT NULL COMMENT '性别',
          `city` varchar(30) DEFAULT NULL COMMENT '城市',
          `country` varchar(30) DEFAULT NULL COMMENT '国家',
          `province` varchar(30) DEFAULT NULL COMMENT '省',
          `language` varchar(30) DEFAULT NULL COMMENT '语言',
          `headimgurl` varchar(255) DEFAULT NULL COMMENT '头像地址',
          `subscribe_time` int(10) unsigned NOT NULL COMMENT '关注时间',
          `location_x` varchar(20) DEFAULT NULL COMMENT '坐标',
          `location_y` varchar(20) DEFAULT NULL COMMENT '坐标',
          `location_info` varchar(255) DEFAULT NULL COMMENT '坐标详情',
          `msg_today` INT( 10 ) NOT NULL DEFAULT '0' COMMENT '每日消息的发送时间',
          PRIMARY KEY (`id`),
          KEY `uid` (`uid`),
          KEY `msg_today` (`msg_today`),
          KEY `openid` (`openid`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT '微信会员表';");

    }

    /**
     * 分页
     * @param int $pagesize
     * @param $table
     * @param int $page
     * @param $url
     * @param string $order
     * @return array
     */
    protected function _pagelist($pagesize = 8,$table,$page = 1,$url,$order = 'id DESC')
    {
        $page     = (!$page) ? 1 : $page;
        $urlparam['page']   = '{page}';
        $pagelist = $this->instance('pagelist');
        $pagelist->loadconfig();
        $total = $this->db->count_all_results($table);
        $data     = $this->db->limit($pagesize,($page-1)*$pagesize)->order_by($order)->get($table)->result_array();
        $pagelist = $pagelist->total($total)->url(url($url, $urlparam))->num($pagesize)->page($page)->output();

        return array('data'=>$data,'pages'=>$pagelist);
    }

}
/**
 * url 编码
 */
function _url_encode($str) {
    if (is_array($str)) {
        foreach($str as $key=>$value) {
            $str[urlencode($key)] = _url_encode($value);
        }
    } else {
        $str = urlencode($str);
    }

    return $str;
}

