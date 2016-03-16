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
<form method="post" action="" name="searchform">
<input type="hidden" name="form" value="add">
<div class="explain-col search-form">
名称：<input type="text" name="data[name]" size=10 class="input-text" value=""> 
价格：<input type="text" name="data[price]" size=10 class="input-text" value=""> 
描述：<input type="text" name="data[description]" size=30 class="input-text" value="">  
<input type="submit" name="submit" class="button" value="快速添加">
</div>
</form>
<form action="" method="post">
<input type="hidden" name="form" value="update">
<table width="100%">
	<thead>
	<tr>
		<th width="50" align="left">删除</th>
		<th width="120" align="left">物流名称</th>
		<th width="80" align="left">物流运费</th>
		<th align="left">描述</th>
	</tr>
    </thead>
    <tbody>
    <?php 
	if (is_array($data)) {
	foreach ($data as $t) { ?>
	<tr height="25">
	  <td align="left"><input type="checkbox" name="delete[]" value="<?php echo $t['id']?>"></td>
	  <td align="left"><input name="data[<?php echo $t['id']?>][name]" type="text" size="10" value="<?php echo $t['name']?>" class="input-text" /></td>
	  <td align="left"><input name="data[<?php echo $t['id']?>][price]" type="text" size="10" value="<?php echo $t['price']?>" class="input-text" /></td>
	  <td align="left"><input name="data[<?php echo $t['id']?>][description]" type="text" size="50" value="<?php echo $t['description']?>" class="input-text" /></td>
	  </tr>
    <?php }} ?>
	  </tbody>
</table>
<br>
<input type="submit" class="button" value="更 新" name="submit">
</form>
</div>
</div>
</body>
</html>
