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
			<th width="200">是否在线付款： </th>
			<td>
			<input name="data[pay]" type="radio" value="1" <?php if ($data['pay']==1) echo "checked";?>> 付款（需要安装“在线充值”应用） 
			&nbsp;&nbsp;&nbsp;
			<input name="data[pay]" type="radio" value="0" <?php if ($data['pay']==0) echo "checked";?>> 不付款（只生成一个订单，联系客户线下交易）
			</td>
		</tr>
		<tr>
			<th>（必选）商品价格字段绑定： </th>
			<td>
			<table>
			<?php
			foreach($mods as $mod) {
			?>
			<tr>
			<th width="80"><?php echo $mod['modelname']?>： </th>
			<td>
			<select name="data[field][item_price][<?php echo $mod['modelid']?>]">
			<option value=""> - 绑定字段 - </option>
			<?php
			foreach($mod['fields']['data'] as $t) {
				$select = $data['field']['item_price'][$mod['modelid']] == $t['field'] ? ' selected' : '';
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
			所绑定的“价格字段”就是支付订单的价格，最好该字段是整型。
			</div>
			</td>
		</tr>
		<tr>
			<th>（可选）商品总数量字段绑定： </th>
			<td>
			<table>
			<?php
			foreach($mods as $mod) {
			?>
			<tr>
			<th width="80"><?php echo $mod['modelname']?>： </th>
			<td>
			<select name="data[field][item_total][<?php echo $mod['modelid']?>]">
			<option value=""> - 绑定字段 - </option>
			<?php
			foreach($mod['fields']['data'] as $t) {
				$select = $data['field']['item_total'][$mod['modelid']] == $t['field'] ? ' selected' : '';
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
			所绑定的“总数量字段”就是该商品的总数量，应用于统计。
			</div>
			</td>
		</tr>
		<tr>
			<th>（可选）商品已出售数量字段绑定： </th>
			<td>
			<table>
			<?php
			foreach($mods as $mod) {
			?>
			<tr>
			<th width="80"><?php echo $mod['modelname']?>： </th>
			<td>
			<select name="data[field][item_num][<?php echo $mod['modelid']?>]">
			<option value=""> - 绑定字段 - </option>
			<?php
			foreach($mod['fields']['data'] as $t) {
				$select = $data['field']['item_num'][$mod['modelid']] == $t['field'] ? ' selected' : '';
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
			所绑定的“已出售数量字段”就是该商品已经出售的数量，应用于订购判断，超出总数量将不会产生订单。
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