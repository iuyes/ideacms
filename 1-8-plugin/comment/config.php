<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    'name' => '文章评论',
    'author'  => '连普创想',
    'version' => '1.3',
    'typeid' => 1,
    'description' => "必须有Jquery支持，将下面代码放在内容页面中<br>
	\{if plugin(\'comment\')\}\&lt;a name=\"comment\"\&gt;\&lt;/a\&gt;\&lt;script type=\"text/javascript\" src=\"{url(\'comment/index/list\', array(\'id\'=>\$id))}\"&gt;\&lt;\/script&gt;\{/if\}",
    'fields' => array(
	    array('field'=>'status', 'name'=>'评论审核', 'tips'=>'如果开启必须审核之后才能显示。', 'formtype'=>'radio', 'setting'=>"array('content'=>'开启|1' . chr(13) . '关闭|0')"),
		array('field'=>'code', 'name'=>'验证码', 'tips'=>'', 'formtype'=>'radio', 'setting'=>"array('content'=>'开启|1' . chr(13) . '关闭|0')"),
		array('field'=>'guest', 'name'=>'游客评论', 'tips'=>'默认允许游客评论。', 'formtype'=>'radio', 'setting'=>"array('content'=>'开启|1' . chr(13) . '关闭|0')"),
        array('field'=>'nums', 'name'=>'显示评论数量', 'tips'=>'默认10条。', 'formtype'=>'input', 'setting'=>"array('size'=>'100')"),
    )
);