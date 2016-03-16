<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="<?php echo $admin_path?>images/reset.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $admin_path?>images/system.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $admin_path?>images/dialog.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $admin_path?>images/main.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $admin_path?>images/switchbox.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $admin_path?>images/table_form.css" rel="stylesheet" type="text/css" />
<script src="<?php echo $admin_path?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $admin_path?>js/dialog.js"></script>
<title>后台管理</title>
</head>
<body style="font-weight: normal;">
<div class="subnav">
<div class="content-menu ib-a blue line-x">
<a href='<?php echo url("gbook/admin")?>'><em>留言管理</em></a><span>|</span>
<a href='<?php echo url("gbook/admin/index", array("status"=>1))?>'><em>未审留言</em></a><span>|</span>
<a href='javascript:;' class="on"><em>查看留言</em></a><span>|</span>
<a href='<?php echo url("admin/plugin/set", array("pluginid"=>$this->plugin['pluginid']))?>'><em>配置信息</em></a>
</div>
<div class="table-list">
<form action="" method="post">
<input name="data[id]" type="hidden" id="id" value="<?php echo $data[id]?>">
<div class="pad-10">
<div class="col-tab">
<table width="100%" class="table_form ">
	<tr>
        <th width="200">姓名：</th>
        <td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data[name]?>" name="data[name]"></td>
      </tr>
     <tr>
        <th>电话：</th>
        <td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data[tel]?>" name="data[tel]"></td>
     </tr>
     <tr>
        <th>邮箱：</th>
        <td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data[email]?>" name="data[email]"></td>
     </tr>
     <tr>
        <th>地址：</th>
        <td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data[address]?>" name="data[address]"></td>
     </tr>
     <tr>
        <th>内容：</th>
        <td><textarea name="data[content]" cols="40" rows="4"><?php echo $data[content]?></textarea></td>
     </tr>
     <tr>
        <th>状态：</th>
        <td><input name="data[status]" type="radio" value="0" <?php if ($data[status]==0) echo "checked";?>>&nbsp;未审&nbsp;&nbsp;
        <input name="data[status]" type="radio" value="1" <?php if ($data[status]==1) echo "checked";?>>&nbsp;已审
        </td>
     </tr>
     <tr>
        <th>回复信息&nbsp;</th>
        <td></td>
     </tr>
     <tr>
        <th>回复姓名：</th>
        <td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data[r_name]?>" name="data[r_name]"></td>
     </tr>
     <tr>
        <th>回复内容：</th>
        <td><textarea name="data[r_content]" cols="40" rows="4"><?php echo $data[r_content]?></textarea></td>
     </tr>
	<tr>
        <th></th>
        <td><input type="submit" class="button" value="提交" name="submit"></td>
      </tr>
	<tr>
</table>



</div>

</div>

</form>
</div>
</div>
<script language="javascript">
function preview(obj) {
	var filepath = $('#'+obj).val();
	if (filepath) {
		var content = '<img src="'+filepath+'" />';
	} else {
		var content = '图片地址为空';
	}
	window.top.art.dialog({title:'预览',fixed:true, content: content});
}
function getField(obj) {
	var tid = obj.value;
	$.get("<?php echo url('adsense/admin/ajaxfield');?>", {tid:tid}, function(data) {
		if (data) $("#type_field").html(data);															 
	});
}
</script>
</body>
</html>