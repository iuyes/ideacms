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
	<?php if (!$curl) { ?>
	<b>开放平台提示：请先开启curl支持</b><br /><br />
	开启php curl函数库的步骤(for windows)<br />
			1).去掉windows/php.ini 文件里;extension=php_curl.dll前面的; /*用 echo phpinfo();查看php.ini的路径*/<br />
			2).把php5/libeay32.dll，ssleay32.dll复制到系统目录windows/下<br />
			3).重启apache<br />
	<?php } ?><br />
	<b>操作步骤</b><br /><br />
	1、到“分享配置”中去设置各个开关<br /><br />
	2、最后一步很重要，“更新缓存”，完毕<br /><br />
	</div>
</div>
</body>
</html>
