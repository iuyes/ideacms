<?php
if (!defined('IN_IDEACMS')) exit();

/**
 * 应用程序配置信息
 */
return array(

	/* 系统核心配置 */

	'ADMIN_NAMESPACE'         => 'admin',  //后台管理路径名字，默认admin
	'SYS_DOMAIN'              => '',  //域名目录，针对虚拟主机用户
	'SYS_DEBUG'               => true,  //调试模式开关，网站正式上线时建议关闭
	'SYS_LOG'                 => false,  //程序运行日志开关,日志目录（项目目录/logs/）
	'SYS_VAR_PREX'            => 'IDEAS_b1bf4_',  //Sessoin、Cookie变量前缀
	'SYS_GZIP'                => false,  //是否Gzip压缩后输出
	'SITE_MEMBER_COOKIE'      => '2967e68d382902a',  //Cookie随机字符串
	'SESSION_COOKIE_DOMAIN'   => '',  //多站点会员登录状态跨域保存域名，格式如.lygphp.com（只针对多站点域名全是二级域名否则请留空）
	'SYS_EDITOR'              => 'ueditor',  //系统编辑器设置，默认kindeditor（自定义编辑器直接填写目录名称，如ueditor）
	'SYS_CAPTCHA_MODE'        => '0',  //验证码输出模式：0,普通模式；1,兼容模式，若验证码显示不正常可在此调整输出模式

	/* 网站相关配置 */

	'SITE_COMMENT'            => false,  //
	'SYS_MODE'                => '0',  //版本模式
	'SITE_ADMINLOG'           => true,  //后台操作日志开关
	'SITE_BDPING'             => false,  //百度Ping推送
	'SYS_ILLEGAL_CHAR'        => false,  //禁止非法字符提交
	'SYS_MEMBER'              => false,  //禁止使用会员功能
	'SYS_ATTACK_LOG'          => true,  //系统攻击日志开关
	'SYS_ATTACK_MAIL'         => false,  //是否发送邮件通知管理员
	'SITE_ADMIN_CODE'         => true,  //后台登录验证码开关
	'SITE_ADMIN_PAGESIZE'     => '8',  //后台数据分页条数
	'SITE_SYSMAIL'            => '',  //用来接收网站的一些系统邮件
	'SITE_MAIL_TYPE'          => '1',  //邮件发送模式
	'SITE_MAIL_SERVER'        => '',  //邮件服务器
	'SITE_MAIL_PORT'          => '25',  //邮件端口号
	'SITE_MAIL_FROM'          => '',  //发送人地址
	'SITE_MAIL_AUTH'          => '1',  //是否AUTH LOGIN验证
	'SITE_MAIL_USER'          => 'admin',  //验证用户名
	'SITE_MAIL_PASSWORD'      => 'admin',  //验证密码
	'SITE_MAP_UPDATE'         => '30',  //更新周期，单位为分钟搜索引擎将遵照此周期访问该页面，使页面上的新闻更及时地出现在百度新闻中
	'SITE_MAP_TIME'           => '10',  //天之内
	'SITE_MAP_NUM'            => '30',  //条数据
	'SITE_MAP_AUTO'           => false,  //自动生成开关，开启之后系统更新内容时XML会自动更新
	'SITE_SEARCH_PAGE'        => '2',  //搜索列表页显示数量
	'SITE_SEARCH_DATA_CACHE'  => '',  //搜索结果缓存时间，单位秒
	'SITE_SEARCH_URLRULE'     => '',  //内容搜索URL规则
	'SITE_SEARCH_TYPE'        => '1',  //搜索类型，1：普通搜索，2：Sphinx
	'SITE_SEARCH_INDEX_CACHE' => '',  //搜索索引缓存时间，单位小时
	'SITE_SEARCH_KW_FIELDS'   => 'title,keywords,description',  //参数kw匹配字段，如title,keywords
	'SITE_SEARCH_KW_OR'       => true,  //针对多个字段匹配，默认AND条件筛选
	'SITE_SEARCH_SPHINX_HOST' => '',  //Sphinx服务器地址
	'SITE_SEARCH_SPHINX_PORT' => '',  //Sphinx服务器端口号
	'SITE_SEARCH_SPHINX_NAME' => '',  //Sphinx索引名称，默认title
	'SITE_KEYWORD_NUMS'       => '',  //关键词页面的信息数量
	'SITE_TAG_LINK'           => false,  //是否自动将TAG链接作为文档内链
	'SITE_KEYWORD_CACHE'      => '',  //关键词页面的信息缓存时间，单位小时
	'SITE_TAG_PAGE'           => '',  //TAG列表页显示数量
	'SITE_TAG_CACHE'          => '',  //TAG列表缓存时间，单位小时
	'SITE_TAG_URLRULE'        => '',  //TAG列表URL规则
	'SITE_TAG_URL'            => '',  //TAG的URL规则

);