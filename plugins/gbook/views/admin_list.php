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
<script language="javascript" src="<?php echo $admin_path?>js/jquery.min.js"></script>
<script language="javascript" src="<?php echo $admin_path?>js/dialog.js"></script>
<title>后台管理</title>
</head>
<body style="font-weight: normal;">
<div class="subnav">

<div class="content-menu ib-a blue line-x">
<a href='<?php echo url("gbook/admin")?>' <?php if($status==0) echo 'class="on"';?>><em>留言管理</em></a><span>|</span>
<a href='<?php echo url("gbook/admin/index", array("status"=>1))?>' <?php if($status==1) echo 'class="on"';?>><em>未审留言</em></a><span>|</span>
<a href='<?php echo url("admin/plugin/set", array("pluginid"=>$this->plugin['pluginid']))?>'><em>配置信息</em></a>
</div>
<div class="table-list">
<form action="" method="post">
<input name="form" id="list_form" type="hidden" value="del">
<table width="100%">
	<thead>
	<tr>
		<th width="33" align="right"><input name="deletec" id="deletec" type="checkbox" onClick="setC()"></th>
		<th width="122" align="left">姓名</th>
		<th width="153" align="left">电话</th>
		<th width="215" align="left">邮箱</th>
		<th width="151" align="left">留言时间</th>
		<th width="93" align="left">状态</th>
		<th width="129">管理操作</th>
		<th width="351">&nbsp;</th>
	</tr>
    </thead>
    <tbody>
    <?php foreach ($list as $t) { ?>
	<tr height="25">
	  <td align="right"><input name="id_<?php echo $t['id'];?>" type="checkbox" class="deletec"></td>
	  <td align="left"><?php echo $t['name'];?></td>
	  <td align="left"><?php echo $t['tel'];?></td>
	  <td align="left"><?php echo $t['email'];?></td>
	  <td align="left"><?php echo date("Y-m-d H:i:s", $t['addtime'])?></td>
	  <td align="left"><?php echo $t['status'] ? '<font color=green>通过</a>' : '<font color=red>未审</a>';?></td>
	  <td align="center">
      <a href="<?php echo url('gbook/admin/edit/',array('id'=>$t[id]));?>">查看/回复</a> |
      <a href="javascript:;" onClick="if(confirm('确定删除吗？')){ window.location.href='<?php echo url('gbook/admin/del/',array('id'=>$t[id]));?>'; }">删除</a></td>
	  <td>&nbsp;</td>
	  </tr>
      <?php } ?>
	<tr height="25">
	  <td colspan="9" align="left">
      <input type="submit" class="button" value="删  除" name="submit_del" onClick="$('#list_form').val('del')">
      <input type="submit" class="button" value="审  核" name="submit_status" onClick="$('#list_form').val('status')">
      </td>
	  </tr>
	<tr>
	  <td>      
	  </tbody>
</table>
<?php echo $pagelist?>

</form>
</div>
</div>
<script language="javascript">
function setC() {
	if($("#deletec").attr('checked')) {
		$(".deletec").attr("checked",true);
	} else {
		$(".deletec").attr("checked",false);
	}
}
</script>
</body>
</html>