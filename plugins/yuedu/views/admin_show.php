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
		$span  = $i >= count($menu)  ? '' : '<span>|</span>';
	    echo '<a href="' . purl('admin/' . $t[0]) . '" ' . $class . '><em>' . $t[1] . '</em></a>' . $span;
	}
	?>
    <a class="on" href="javascript:;"><em>订单查看</em></a>
	</div>
    <div class="bk10"></div>
	<div class="table-list">
	<form action="" method="post">
		<table width="100%" class="table_form">
		<tbody>
			<tr>
				<th width="200">订单ID：</th>
				<td><?php echo $data['id']?></td>
			</tr>
			<tr>
			  <th>文档标题：</th>
			  <td><?php echo $data['title']?></td>
			</tr>
			<tr>
			  <th>会员名称：</th>
			  <td><?php echo $data['username']?></td>
			</tr>
			<tr>
				<th>商品金额：</th>
				<td><input type="text" name="price" value="<?php echo $data['price']?>" size="15" class="input-text" <?php if ($data['status']) echo 'disabled="disabled"';?>></td>
			</tr>
			<tr>
			<th>订单状态：</th> 
			<td>
            <?php echo $data['status'] ? '交易成功，时间：' . date('Y-m-d H:i:s', $data['paytime']) : '等待付款';?>
            </td>
			</tr>
		</tbody>
		</table>
	</form>
	</div>
</div>
</body>
</html>
<script language="javascript">
function close_order() {
	$('#note').show();
}
<?php if ($data['status']==4) { ?>
$('#note').show();
<?php } else { ?>
$('#note').hide();
<?php }?>
</script>