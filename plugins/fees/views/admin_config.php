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
		<form action="" method="post">
		<table width="100%">
		<thead>
		<tr>
			<th width="44" align="left">id</th>
			<th width="222" align="left">栏目名称</th>
			<th width="80" align="left">价格(元)</th>
			<th align="left">会员权限设定(选中项将不会收费，你就慢慢选吧)</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($list as $t) { ?>
		<tr>
			<td align="left"><?php echo $t['catid'];?></td>
			<td align="left"><?php echo $t['prefix'] ?></td>
			<td align="left"><?php if ($t['typeid']==1 && $t['child']==0) {?><input type="text" size=7 name="data[<?php echo $t['catid'];?>][money]" class="input-text" value="<?php echo $data[$t['catid']]['money'] ?>" /><?php } ?></td>
			<td align="left">
			<?php if ($t['typeid']==1 && $t['child']==0) {?>
				模型：
				<?php foreach ($model as $c) { ?>
				&nbsp;<?php echo $c['modelname'];?>
				<input name="data[<?php echo $t['catid'];?>][model][]" type="checkbox" value="<?php echo $c['modelid']?>" <?php if (@in_array($c['modelid'], $data[$t['catid']]['model'])) echo "checked";?> />
				<?php } ?>
				|&nbsp;会员组：
				<?php foreach ($group as $c) { ?>
				&nbsp;<?php echo $c['name'];?>
				<input name="data[<?php echo $t['catid'];?>][group][]" type="checkbox" value="<?php echo $c['id']?>" <?php if (@in_array($c['id'], $data[$t['catid']]['group'])) echo "checked";?> />
				<?php } ?>
			<?php } ?>
			</td>
		</tr>
		<?php } ?>
		</tbody>
		</table>
		<div class="bk10"></div>
		<input type="submit" class="button" value=" 保 存 " name="submit" />
		</form>
	</div>
</div>
</body>
</html>
