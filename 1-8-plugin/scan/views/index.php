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
<script language="javascript">
$(function(){
   $('#loading').hide();
});
function loadimg() {
    $('#loading').show();
}
</script>
</head>
<body>
<div class="subnav">
    <div class="content-menu ib-a blue line-x">
	<?php
	$_GET['a'] = isset($_GET['a']) ? $_GET['a'] : 'index';
	foreach ($menu as $i=>$t) {
		$class = $_GET['a'] == $t[0] ? ' class="on"' : '';
		$span  = $i >= count($menu)-1  ? '' : '<span>|</span>';
		echo '<a href="' . purl('admin/' . $t[0]) . '" ' . $class . '><em>' . $t[1] . '</em></a>' . $span;
	}
	?>
    </div>
</div>
<div class="pad_10">
    <div class="explain-col">本检测程以开发模式为标准，如果您的网站目录包含其它系统，此检测程序可能会产生错误判断</div>
    <div class="bk10"></div>
    <div class="table-list">
    <form method="post" name="myform" action="">
    <table width="100%" cellspacing="0">
    <tr>
        <td width="150" align="right">文件类型：</td>
        <td><input type="text" class="input-text" size="30" value="<?php echo $data['filetype']?>" name="data[filetype]"></td>
    </tr>
    <tr>
        <td align="right">代码特征：</td>
        <td><input type="text" class="input-text" size="70" value="<?php echo $data['codeinfo']?>" name="data[codeinfo]">
        <div class="onShow">多个非法字符以“|”分开</div>
        </td>
    </tr>
    <tr>
        <td></td>
        <td><input type="submit" name="submit" value="开始检测" onClick="loadimg()" class="button">&nbsp;
        <span id="loading"><img src="<?php echo $viewpath?>loading.gif" /></span>
        </td>
    </tr>
    </table>
    </form>
    </div>
</div>
</body>
</html>
