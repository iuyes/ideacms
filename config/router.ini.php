<?php
if (!defined('IN_IDEACMS')) exit();

/**
 * 自定义URL地址伪静态规则(必须服务器支持伪静态,并将所有指向为index.php文件)
 * 越靠前的规则匹配越优先
 * 伪静态规则符合标准正则表达式
 */
 
return array(
    //栏目: http://网站/栏目目录/ 分页: http://网站/栏目目录/分页id/
    '^([a-zA-Z0-9]+)[/]?$'          => 'c=content&a=list&catdir=${1}',
    '^([a-zA-Z0-9]+)/([0-9]+)[/]?$' => 'c=content&a=list&catdir=${1}&page=${2}',

    //内容: http://网站/内容id.html 分页: http://网站/栏目目录/内容id-分页id.html
    '^([a-zA-Z0-9]+)/([0-9]+).html$'           => 'c=content&a=show&id=${2}',
    '^([a-zA-Z0-9]+)/([0-9]+)\-([0-9]+).html$' => 'c=content&a=show&id=${2}&page=${3}',
	
	/*
	 * 以上伪静态规则仅供示例参考，具体规则按照自己定义的网站URL结构为准
	 */
    
	
);