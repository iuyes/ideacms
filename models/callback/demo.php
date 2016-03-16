<?php

if (!defined('IN_IDEACMS')) exit();

/**
 * 模型id.php 内容模型或表单模型回调处理函数（需要一定的开发基础）
 *
 * 这是一个示例文件
 *
 * 函数格式：function callback_模型表名称($data) {}
 * $data 就是表单的提交内容了
 */


function callback_demo($data) {

    // 由开发者二次开发

    // 用于发送邮件
    mail::set(App::$config);
    mail::sendmail('收件人地址', '发信标题', '发信内容');

}
