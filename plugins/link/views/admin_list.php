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
<script language="javascript" type="text/javascript" src="<?php echo $admin_path?>js/dialog.js"></script>
<script charset="utf-8" src="<?php echo $extension_path?>kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="<?php echo $extension_path?>kindeditor/lang/zh_CN.js"></script>
<title>后台管理</title>
</head>
<body style="font-weight: normal;">
<div class="subnav">
<div class="content-menu ib-a blue line-x">
<a href='<?php echo url("link/admin")?>' class="on"><em>友情链接列表</em></a><span>|</span>
<a href='<?php echo url("link/admin/add")?>'><em>添加友情链接</em></a>
</div>
<div class="table-list">
<form action="" method="post">
<input name="form" id="list_form" type="hidden" value="order">
<table width="100%">
	<thead>
	<tr>
	  <th width="33" align="right"><input name="deletec" id="deletec" type="checkbox" onClick="setC()"></th>
		<th width="62">排序</th>
		<th width="136" align="left">网站名称</th>
		<th width="325" align="left">网站地址</th>
		<th width="113" align="left">链接类型</th>
		<th width="195" align="left">添加时间</th>
		<th width="244" align="left">管理操作</th>
		<th width="126">&nbsp;</th>
	</tr>
    </thead>
    <tbody>
    <?php foreach ($list as $t) { ?>
	<tr height="25">
	  <td align="right"><input name="del_<?php echo $t['id'];?>" type="checkbox" class="deletec"></td>
	  <td align="center"><input type="text" name="order_<?php echo $t['id'];?>" class="input-text" style="width:25px; height:15px;" value="<?php echo $t['listorder'];?>"></td>
	  <td align="left"><?php echo $t['name'];?></td>
	  <td align="left"><a href="<?php echo $t['url'];?>" target="_blank"><?php echo $t['url'];?></a></td>
	  <td align="left"><?php echo $t['typeid'] ? "LOGO链接" : "文字链接";?></td>
	  <td align="left"><?php echo date("Y-m-d H:i:s", $t[addtime])?></td>
	  <td align="left">
      <a href="<?php echo $t[url]?>" target="_blank">访问</a> | 
      <a href="<?php echo url('link/admin/edit/',array('id'=>$t[id]));?>">修改</a> | 
      <a href="javascript:;" onClick="if(confirm('确定删除吗？')){ window.location.href='<?php echo url('link/admin/del/',array('id'=>$t[id]));?>'; }">删除</a> 
      </td>
	  <td>&nbsp;</td>
	  </tr>
      <?php } ?>
	<tr height="25">
	  <td colspan="9" align="left"><input type="submit" class="button" value="排  序" name="submit_order" onClick="$('#list_form').val('order')">&nbsp;
      <input type="submit" class="button" value="删  除" name="submit_del" onClick="$('#list_form').val('del')">&nbsp;<div class="onShow">友情提示：排序方式为“从小到大”</div></td>
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