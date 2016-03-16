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
	<form action="" method="post" target='result'>
		<table width="100%" class="table_form">
		<tbody>
			<tr>
				<th width="200">充值金额：</th>
				<td><input type="text" class="input-text" style="width:100px;" name="data[money]" value="10" required /><div class="onShow">单位：元</div></td>
			</tr>
			<tr>
				<th>生成数量：</th>
				<td><input type="text" class="input-text" style="width:100px;" name="data[num]" value="10" required /></td>
			</tr>
			<tr>
				<th>有效期：</th>
				<td><input type="text" class="input-text" style="width:100px;" name="data[time]" value="365" required /><div class="onShow">单位：天</div></td>
			</tr>
			<tr>
				<th></th>
				<td><input type="submit" class="button" value=" 生成充值卡 " name="submit"></td>
			  </tr>
			<tr>
		</tbody>
		</table>
	</form>
	</div>
	<iframe name="result" frameborder="0" id="result" width="100%" height="200"></iframe>
</div>
</body>
</html>