<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>admin</title>
<link href="<?php echo ADMIN_THEME?>images/reset.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/system.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/main.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/table_form.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/dialog.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="<?php echo ADMIN_THEME?>js/jquery.min.js"></script>
<script language="javascript" src="<?php echo ADMIN_THEME?>js/dialog.js"></script>
</head>
<body>
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
    <?php
	//以下php代码段是后台导航的处理，配合admin控制器中的$menu数组
	$_GET['a'] = isset($_GET['a']) ? $_GET['a'] : 'index';
	foreach ($menu as $i=>$t) {
	    $class = $_GET['a'] == $t[0] ? ' class="on"' : '';
		$span  = $i >= count($menu)-1  ? '' : '<span>|</span>';
	    echo '<a href="' . purl('admin/' . $t[0]) . '" ' . $class . '><em>' . $t[1] . '</em></a>' . $span;
	}
	?>
	</div>
	<div class="bk10"></div>
	<div class="table-list">
	<b>操作步骤</b><br /><br />
	1、安装“在线充值应用”，已经安装的可用跳过 <br /><br />
	2、到“收费配置”中去设置各个栏目的投稿费用和权限 <br /><br />
	3、最后一步很重要，“更新缓存”，完毕<br /><br />
	<b>数据调用（选用）</b><br /><br />
	$cache	= new cache_file('fees');	//实例化缓存<br />
	$config	= $cache->get('config');	//获取配置缓存<br />
	/******上面两条是必须的****/<br />
	$config[栏目id];	//该栏目的配置信息（包括金额、权限,数组格式,打印出来就知道了）<br />
	$config[栏目id]['money'];	//该栏目收费金额<br />
	$config[栏目id]['model'];	//该栏目不需要收费的会员模型id集合（数组格式）<br />
	$config[栏目id]['group'];	//该栏目不需要收费的会员组id集合（数组格式）<br />
	</div>
</div>
</body>
</html>
