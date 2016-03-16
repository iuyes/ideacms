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
<form method="post" action="" name="searchform">
<div class="explain-col search-form">
订单编号  <input type="text" name="order_sn" size=30 class="input-text" value=""> 
用户名  <input type="text" name="username" class="input-text" value="">  
订单状态  <select name="status">
        <option value="-1" <?php if ($status==-1) echo "selected"?>> - 选择状态 - </option>
        <option value="0" <?php if ($status==0) echo "selected"?>> - 未付款/未确认 - </option>
        <option value="1" <?php if ($status==1) echo "selected"?>> - 配货 - </option>
        <option value="2" <?php if ($status==2) echo "selected"?>> - 发货 - </option>
        <option value="3" <?php if ($status==3) echo "selected"?>> - 收货 - </option>
        <option value="4" <?php if ($status==4) echo "selected"?>> - 关闭 - </option>
        <option value="9" <?php if ($status==9) echo "selected"?>> - 完成 - </option>
        </select>
<input type="submit" name="submit" class="button" value="搜索">
</div>
<input type="hidden" name="form" value="search">
</form>
<form action="" method="post">
<table width="100%">
	<thead>
	<tr>
		<th width="130" align="left">订单编号</th>
		<th width="80" align="left">用户名</th>
		<th width="120" align="left">下单时间</th>
		<th width="80" align="left">商品价格</th>
		<th align="left">操作</th>
	</tr>
    </thead>
    <tbody>
    <?php foreach ($list as $t) { ?>
	<tr height="25">
	  <td align="left"><?php echo '<a href="' . purl('admin/show', array('id'=>$t['id'])) . '">' . $t['order_sn'] . '</a>';?></td>
	  <td align="left"><?php echo $t['username'] ?></td>
	  <td align="left"><?php echo date('Y-m-d H:i:s', $t['addtime'])?></td>
	  <td align="left"><?php echo $t['price']?></td>
	  <td align="left">
<?php
if ($t['status'] == 0) {
    if ($pay) {
	    if (!$t['paytime']) {
			echo '<span style="color:red">未付款</span> | <a href="' . purl('admin/edit', array('id'=>$t['id'])) . '">改价</a>';
		}
    } else {
		echo '<span style="color:#C60">无需付款</span> | ' . '<a href="' . purl('admin/edit', array('id'=>$t['id'])) . '">配货</a>';
	}
} else if ($t['status'] == 1) {
	echo '<a href="' . purl('admin/edit', array('id'=>$t['id'])) . '">配货</a>';
} else if ($t['status'] == 2) {
	echo '<a href="' . purl('admin/edit', array('id'=>$t['id'])) . '">发货</a>'; 
} else if ($t['status'] == 3) {
	echo '<a href="' . purl('admin/edit', array('id'=>$t['id'])) . '">等待收货确认</a>'; 
} else if ($t['status'] == 4) {
	echo '交易关闭(' . $t['note'] . ')'; 
} else if ($t['status'] == 9) {
	echo '<span style="color:#00F">交易成功</span>';
}
?>
      </td>
	  </tr>
    <?php } ?>
	  </tbody>
</table>
<?php echo $pagelist?>
</form>
</div>
</div>
</body>
</html>
