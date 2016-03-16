<?php
if (!defined('IN_IDEACMS')) exit();

/**
 * 连普创想配置
 */
return array(

	'SITE_EXTEND_ID'          => '0',  //所继承的网站id
	'SITE_LANGUAGE'           => 'zh-cn',  //系统语言设置，默认zh-cn
	'SITE_TIMEZONE'           => '8',  //时区常量，默认时区为东8区时区
	'SITE_THEME'              => 'default',  //模板风格,默认default
	'SITE_NAME'               => '连普创想',  //网站名称，将显示在浏览器窗口标题等位置
	'SITE_TITLE'              => 'ideacms',  //网站首页SEO标题
	'SITE_KEYWORDS'           => 'ideacms',  //网站SEO关键字
	'SITE_DESCRIPTION'        => 'ideacms',  //网站SEO描述信息
	'SITE_BOTTOM_INFO'        => '&lt;p&gt;ideacms&lt;/p&gt;',  //网站底部信息
	'SITE_WATERMARK'          => '0',  //水印功能
	'SITE_WATERMARK_ALPHA'    => '',  //图片水印透明度
	'SITE_WATERMARK_TEXT'     => '',  //文字水印
	'SITE_WATERMARK_SIZE'     => '',  //单位像素，默认14
	'SITE_WATERMARK_IMAGE'    => '',  //Png格式图片，水印图片目录：/extensions/watermark/
	'SITE_WATERMARK_POS'      => '',  //水印位置
	'SITE_THUMB_TYPE'         => '0',  //图片显示模式
	'SITE_THUMB_WIDTH'        => '',  //内容缩略图默认宽度
	'SITE_THUMB_HEIGHT'       => '',  //内容缩略图默认高度
	'SITE_TIME_FORMAT'        => 'Y-m-d',  //网站时间显示格式，参数与PHP的date函数一致，默认Y-m-d H:i:s
	'SITE_MOBILE'             => false,  //移动设备访问网站开关，打开之后需要设计移动端模板（默认mobile或者mobile_站点id）
	'SITE_MURL'               => '',  //域名格式为：m.lygphp.com，不要加http://
	'SITE_ICP'                => 'ICP备案序号',  //ICP备案序号
	'SITE_JS'                 => '',  //第三方统计代码

);