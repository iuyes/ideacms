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
  <a href="<?php echo purl("admin/index")?>"><em>文章心情</em></a><span>|</span>
  <a href="<?php echo purl("admin/config")?>" class="on"><em>配置信息</em></a>
  </div>
</div>
<div class="pad-lr-10">
<form name="myform" action="" method="post">
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th  align="left" width="40">开关</th>
			<th align="left" width="200">名称</th>
			<th align="left">图片</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($data as $id=>$t) { ?>
    <tr>
		<td align="left"><input type="checkbox" value="1" name="data[<?php echo $id?>][use]" <?php if($t['use']==1){echo 'checked';}?>></td>
		<td align="left"><input class="input-text" type="text" name="data[<?php echo $id?>][name]" value="<?php echo $t['name']?>"></td>
		<td align="left"><input class="input-text" size=80 type="text" name="data[<?php echo $id?>][pic]" value="<?php echo $t['pic']?>"><?php if ($t['pic']) { ?>&nbsp;<img src="<?php echo $t['pic']?>"><?php } ?></td>
    </tr>
	<?php } ?>
    </tbody>
</table>
<div class="btn">
<input type="submit" class="button" name="submit" value=" 保存配置 "/>
</div>
</div>
</form>
</div>
</body>
</html>
