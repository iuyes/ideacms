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
			<table width="100%" class="table_form">
			<tbody>
				<tr>
				  <th width="200">会员分享：</th>
				  <td><input name="data[member]" type="radio" value="0" <?php if (empty($data['member'])) echo "checked";?>> 关闭 
						&nbsp;&nbsp;&nbsp;
						<input name="data[member]" type="radio" value="1" <?php if ($data['member']==1) echo "checked";?>> 开启 
						<div class="onShow">若不使用“一键登录”功能，将不会生效</div>
				  </td>
				</tr>
				<tr>
				  <th>管理分享：</th>
				  <td><input name="data[admin]" type="radio" value="0" <?php if (empty($data['admin'])) echo "checked";?>> 关闭 
						&nbsp;&nbsp;&nbsp;
						<input name="data[admin]" type="radio" value="1" <?php if ($data['admin']==1) echo "checked";?>> 开启
						<div class="onShow">管理员在后台发布文档时，前台必须使用“一键登录”到会员中心</div>
				  </td>
				</tr>
				<tr>
					<th>网站名称：</th>
					<td><input name="data[name]" class="input-text" size=30 value="<?php echo $data['name']?>" />
					<div class="onShow">在分享中显示网站的名称，如“IdeaCMS官方网站”</div>
					</td>
				</tr>
				<tr>
					<th></th>
					<td><input type="submit" class="button" value="提 交" name="submit"></td>
				  </tr>
				<tr>
			</tbody>
			</table>
		</form>
	</div>
</div>
</body>
</html>
