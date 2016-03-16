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
	foreach ($menu as $i=>$t) {
	    $class = $_GET['a'] == $t[0] ? ' class="on"' : '';
		$span  = $i >= count($menu)-1  ? '' : '<span>|</span>';
	    echo '<a href="' . purl('admin/' . $t[0]) . '" ' . $class . '><em>' . $t[1] . '</em></a>' . $span;
	}
	?>
	</div>
    <div class="bk10"></div>
	<div class="table-list">
		<form method="post" action="" id="myform" name="myform">
		<table width="100%" class="table_form">
		<tr>
			<th width="200">使用须知： </th>
			<td>需要安装“在线充值”应用</td>
		</tr>
		<tr>
			<th>价格字段绑定： </th>
			<td>
			<table>
			<?php
			foreach($mods as $mod) {
			?>
			<tr>
			<th width="80"><?php echo $mod['modelname']?>： </th>
			<td>
			<select name="data[field][<?php echo $mod['modelid']?>]">
			<option value=""> - 绑定字段 - </option>
			<?php
			foreach($mod['fields']['data'] as $t) {
				$select = $data['field'][$mod['modelid']] == $t['field'] ? ' selected' : '';
				echo '<option value="' . $t['field'] . '" '. $select . '>' . $t['name'] . ' (' . $t['field'] . ')</option>';
			}
			?>
			</select>
			</td>
			</tr>
			<?php
			}
			?>
			</table>
			<div class="onShow" style="clear:both;margin-top:4px;">
			所绑定的“价格字段”就是阅读收费订单的价格，最好该字段是整型或者浮点型。
			</div>
			</td>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<td><input type="submit" class="button" value="提交" name="submit"></td>
		</tr>
		</table>
		</form>
	</div>
</div>
</body>
</html>