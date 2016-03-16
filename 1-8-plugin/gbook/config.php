<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    'name'    => '留言本',
    'author'  => 'lirui',
    'version' => '1.3',
    'typeid'  => 1,
    'description' => "用户留言系统，前台模板调用代码:<br>{list table=gbook.gbook order=id_desc}<br>内容区域<br>{/list}<br><br>前台提交页面url(\'gbook/index/post\');",
    'fields'  => array(
       'status'  => array('field'=>'status', 'name'=>'留言审核', 'tips'=>'如果开启必须审核之后才能显示。', 'formtype'=>'radio', 'setting'=>"array('content'=>'开启|1' . chr(13) . '关闭|0')"),
       'emailto' => array('field'=>'emailto', 'name'=>'邮件通知', 'tips'=>'管理员回复时邮件通知留言用户。', 'formtype'=>'radio', 'setting'=>"array('content'=>'开启|1' . chr(13) . '关闭|0')"),
	   'code'    => array('field'=>'code', 'name'=>'验证码', 'tips'=>'', 'formtype'=>'radio', 'setting'=>"array('content'=>'开启|1' . chr(13) . '关闭|0')"),
    )
);