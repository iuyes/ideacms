<?php
if (!defined('IN_IDEACMS')) exit('No permission resources');

return array(
    'name'			=> '自动发布',
    'author'    => '连普创想',
    'version'		=> '1.0',
    'typeid'		=> 0,
    'description'	=> "先把数据设置为“回收站”状态，这些数据就是自动发布的“数据源”。<br>再把“模板调用代码”放到模板中，放到index.html或者公共页面footer.html最底部就行了。<br>设置发布时间，发布条数，即使是十一或者五一长假也不怕网站不更新了。",
    'fields'		=> array(
        array('field'=>'start_time', 'name'=>'开始时间段', 'tips'=>'如：8，表示从8点开始发布。', 'formtype'=>'input', 'setting'=>"array('size'=>'100')"),
        array('field'=>'end_time', 'name'=>'结束时间段', 'tips'=>'如：10，表示10点结束发布。', 'formtype'=>'input', 'setting'=>"array('size'=>'100')"),
        array('field'=>'nums', 'name'=>'发布数量范围', 'tips'=>'每个栏目发布的数量范围，格式：10,30，表示随机发布10到30条。', 'formtype'=>'input', 'setting'=>"array('size'=>'100')"),
    )
);