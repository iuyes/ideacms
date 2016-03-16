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
	<form action="" method="post">
		<table width="100%" class="table_form">
		<tbody>
			<tr>
				<th width="200">会员名称：</th>
				<td><input type="text" class="input-text" style="width:100px;" id="username" onblur="checkuser()" name="username">
				<div class="onShow" id="username_result"></div>
				</td>
			</tr>
			<tr>
				<th>充值额度：</th>
				<td>
				<input type="radio" checked="" value="1" name="pay_unit"> 增加&nbsp;&nbsp;  
				<input type="radio" value="0" name="pay_unit"> 减少&nbsp;&nbsp;
				<input type="text" value="" size="10" name="money" class="input-text">
				<div class="onShow">填写金额！</div></td>
			</tr>
			<tr>
				<th>操作描述：</th>
				<td><textarea name="adminnote" style="width:295px;height:50px;"></textarea></td>
			</tr>
			<tr>
			<th>提醒操作：</th> 
			<td><label><input type="checkbox" checked="" value="1" name="sendemail" id="sendemail"> 发送e-mail通知会员</label></td>
			</tr>
			<tr>
				<th></th>
				<td><input type="submit" class="button" value="提交" name="submit"></td>
			  </tr>
			<tr>
		</tbody>
		</table>
	</form>
	</div>
</div>
</body>
</html>
<script language="javascript">
function checkuser() {
	$('#username_result').html('');
	$.get('<?php echo purl('admin/checkuser')?>&id='+Math.random(), { username:$('#username').val()}, function(data){ 
	    $('#username_result').html(data);
	});
}
</script>