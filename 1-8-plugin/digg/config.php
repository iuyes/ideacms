<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    'name' => '文章踩顶',
    'author' => 'start',
    'version' => '1.1',
    'typeid' => 1,
    'description' => "必须有Jquery支持，将下面代码放在内容页面中<br>
	\{if plugin(\'digg\')\}\&lt;script type=\"text/javascript\" src=\"{url(\'digg/index/show\', array(\'id\'=>\$id))}\"&gt;\&lt;\/script&gt;\{/if\}<br>
	有问题请在论坛联系我（start）。",
    'fields' => array(
        array('field'=>'dingname', 'name'=>'“顶”名称设置', 'tips'=>'默认“顶一下”。', 'formtype'=>'input', 'setting'=>"array('size'=>'100')"),
		array('field'=>'cainame',  'name'=>'“踩”名称设置', 'tips'=>'默认“踩一下”。', 'formtype'=>'input', 'setting'=>"array('size'=>'100')"),
    )
);