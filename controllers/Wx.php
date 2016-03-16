<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * IdeaCMS
 *
 * @since		version 2.5.0
 * @author		连普创想 <976510651@qq.com>
 * @copyright   Copyright (c) 2015-9999, 连普创想, Inc.
 */

class Wx extends Common {

    public $wx;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $file = FCPATH.'config/weixin.php';
        $this->wx = is_file($file) ? string2array(file_get_contents($file)) : array();
        define("TOKEN", $this->wx['token']);
        define('WECHAT_THEME', SITE_PATH . basename(VIEW_DIR) . '/weixin/');
        $this->cache->cache_dir = APP_ROOT.'cache/weixin/';
    }

    /**
     * 接入
     */
    public function index() {

        if ($this->_valid()) {
            // 判读是不是只是验证
            $echostr = $this->input->get('echostr');
            if (!empty($echostr)) {
                echo $echostr;exit;
            } else {
                // 实际处理用户消息
                $this->responseMsg();
            }
        } else {
            echo '此接口仅用于微信服务端请求';
        }
    }
    // 用于接入验证
    private function _valid() {
        $token = $this->wx['token'];
        $signature = $this->input->get('signature');
        $timestamp = $this->input->get('timestamp');
        $nonce = $this->input->get('nonce');
        $tmp_arr = array($token, $timestamp, $nonce);
        sort($tmp_arr);
        $tmp_str = implode($tmp_arr);
        $tmp_str = sha1($tmp_str);
        return ($tmp_str == $signature);
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $MsgType = $postObj->MsgType;

            $time = time();
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";


            if(!empty( $keyword ))
            {

                $msgType = "text";
                $data = $this-> _find_msg($keyword);
                $contentStr = "微信测试账号";
                if(empty($data)) {
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                }
                else {
                    $data = $this->getResultData($data,$toUsername,$fromUsername);
                    if($data)
                        $resultStr = $data;
                    else
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                }

                echo $resultStr;
            }else{
                echo "Input something...";
            }

        }else {
            echo "";
            exit;
        }
    }

    /**
     *
     * Return response data
     * @param $data
     * @param $fromUsername
     * @param $toUsername
     * @return bool|string
     */
    public function getResultData($data,$fromUsername, $toUsername)
    {
        switch($data['type'])
        {
            case '0' : $data['type'] = 'text';break;
            case '1' : $data['type'] = 'news';break;
            case '2' : $data['type'] = 'app';break;
            default:
                $data['type'] = 'text';
        }
        $data['from'] = $fromUsername;
        $data['to'] = $toUsername;
        if($data['type'] == 'news')
        {
            $cachename = 'wx-resource'.md5($data['cid']);
            $cache = $this ->cache->get($cachename);
            if(!$cache) {
                $resource = $this->db
                    ->where('id', $data['cid'])
                    ->get('wx_content')
                    ->row_array();
                $this->cache->set($cachename,$resource);
            }
            else
            {
                $resource = $cache;
            }

            if($resource) {
                $data['content']['url'] = $resource['url'];
                $data['content']['title'] = $resource['title'];
                $data['content']['description'] = $resource['description'];
                $data['content']['thumb'] = $resource['thumb'];
            }
            else {
                return false;
            }
        }

        return $this->response_msg($data);
    }
    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    //////////////////////////////////////////////////
    public function showResource()
    {

        $id = $this->input->get('id');
        $data = $this ->db
            ->where('id',$id)
            ->get('wx_content')
            ->row_array();


        if($data['url'])
            $data['url'] = 'http://'.str_replace('http://','',$data['url']);
        $this->template->assign(
            array(
                'data' => $data,
                'title' => $data['title'],
            )
        );
        $this->template->display('../weixin/show_resource.html');
    }
    ////////////////////////////////////////////////////////
    // 发送消息
    protected function response_msg($data) {
        $str = '';
        if ($data['type'] == 'news') {
            $str.= '<xml>'.PHP_EOL;
            $str.= '<ToUserName><![CDATA['.$data['to'].']]></ToUserName>'.PHP_EOL;
            $str.= '<FromUserName><![CDATA['.$data['from'].']]></FromUserName>'.PHP_EOL;
            $str.= '<CreateTime>'.time().'</CreateTime>'.PHP_EOL;
            $str.= '<MsgType><![CDATA['.$data['type'].']]></MsgType>'.PHP_EOL;
            $str.= '<ArticleCount>'.(count($data['content']['orther'])+1).'</ArticleCount>'.PHP_EOL;
            $str.= '<Articles>'.PHP_EOL;

            $url = $data['content']['url'];

            $str.= '<item>'.PHP_EOL;
            $str.= '<Title><![CDATA['.strcut($data['content']['title'], 28).']]></Title>'.PHP_EOL;
            $str.= '<Description><![CDATA['.$data['content']['description'].']]></Description>'.PHP_EOL;
            $str.= '<PicUrl><![CDATA['.getImage($data['content']['thumb']).']]></PicUrl>'.PHP_EOL;
            $str.= '<Url><![CDATA['.$url.']]></Url>'.PHP_EOL;
            $str.= '</item>'.PHP_EOL;

            if ($data['content']['orther']) {
                foreach ($data['content']['orther'] as $i => $t) {
                    $ourl = isset($t['url']) && $t['url'] ? $t['url'] : $url.'&page='.$i;
                    $str.= '<item>'.PHP_EOL;
                    $str.= '<Title><![CDATA['.strcut($t['title'], 28).']]></Title>'.PHP_EOL;
                    $str.= '<Description><![CDATA['.$t['content'].']]></Description>'.PHP_EOL;
                    $str.= '<PicUrl><![CDATA['.getImage($t['thumb']).']]></PicUrl>'.PHP_EOL;
                    $str.= '<Url><![CDATA['.$ourl.']]></Url>'.PHP_EOL;
                    $str.= '</item>'.PHP_EOL;
                }
            }

            $str.= '</Articles>'.PHP_EOL;
            $str.= '</xml>';
        } else {

            $str.= '<xml>'.PHP_EOL;
            $str.= '<ToUserName><![CDATA['.$data['to'].']]></ToUserName>'.PHP_EOL;
            $str.= '<FromUserName><![CDATA['.$data['from'].']]></FromUserName>'.PHP_EOL;
            $str.= '<CreateTime>'.time().'</CreateTime>'.PHP_EOL;
            $str.= '<MsgType><![CDATA['.$data['type'].']]></MsgType>'.PHP_EOL;
            $str.= '<Content><![CDATA['.$data['content'].']]></Content>'.PHP_EOL;
            $str.= '<MsgId>'.$data['id'].'</MsgId>'.PHP_EOL;
            $str.= '</xml>';
        }
        return $str;
    }

    // 查询关键字
    private function _find_msg($msg) {


        // 缓存查询结果
        $name = 'weixin-keyword'.md5($msg);

        $data = $this ->cache->get($name);
        if (!$data) {

            $data = $this->db->where('keyword', $msg)->get('wx_reply')->row_array();
            $this ->cache->set($name,$data);
        }

        // 更新统计量
        if ($data) {
            $this->db->where('id', $data['id'])->set('count', 'count+1', false)->update('wx_reply');
        }

        return $data;
    }

}
