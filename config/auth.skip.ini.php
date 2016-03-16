<?php 
if (!defined('IN_IDEACMS')) exit();

/**
 * 不需要权限验证的模块
 */
return array(
    'defalut'=>array(),
	'admin'=>array(
        'index-index',
        'index-main',
        'login'
    )
);

?>