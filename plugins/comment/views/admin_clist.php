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
	<a href="<?php echo purl('admin/index', array('commentid'=>$commentid, 'status'=>1))?>" <?php if ($status==1) echo 'class="on"';?>><em>评论管理</em></a><span>|</span>
	<a href="<?php echo purl('admin/index', array('commentid'=>$commentid, 'status'=>0))?>" <?php if ($status==0) echo 'class="on"';?>><em>未审评论</em></a><span>|</span>
	<a href="<?php echo url('admin/plugin/set', array('pluginid'=>$pluginid))?>"><em>应用配置</em></a>
	</div>
<div class="bk10"></div>
<div class="table-list">
<form action="" method="post">
<input name="form" id="list_form" type="hidden" value="del">
<table width="100%">
	<thead>
	<tr>
	  <th width="46" align="right">选择&nbsp;<input name="deletec" id="deletec" type="checkbox" onClick="setC()"></th>
		<th width="" align="left">评论内容</th>
		<th width="60" align="left">用户名</th>
		<th width="100" align="left">ip地址</th>
		<th width="155" align="left">评论时间</th>
		<th width="50" align="left">状态</th>
		<th width="100" align="left">&nbsp;</th>
	</tr>
    </thead>
    <tbody>
    <?php foreach ($list as $t) { ?>
	<tr height="25">
	  <td align="right"><input name="dels[]" value="<?php echo $t['id']?>" type="checkbox" class="deletec"></td>
	  <td align="left"><?php echo $t['reply'] ? '[回复] ' : '[主题] ';?><?php echo $t['content']?></td>
	  <td align="left"><?php echo $t['username'] ? $t['username'] : '游客'?></td>
	  <td align="left"><?php echo $t['ip']?></td>
	  <td align="left"><?php echo date('Y-m-d H:i:s', $t['addtime'])?></td>
	  <td align="left"><?php echo $t['status'] ? '通过' : '<font color=red>未审</font>';?></td>
	  <td align="left"><a href="<?php echo url('content/show', array('id'=>$t['contentid']));?>#comment" target="_blank">查看评论</a></td>
	  </tr>
    <?php } ?>
	<tr height="25">
	  <td colspan="11" align="left">
      <input type="submit" class="button" value="删  除" name="submit" onClick="$('#list_form').val('del')">&nbsp;
	  <?php if ($status==0) { ?><input type="submit" class="button" value="审  核" name="submit" onClick="$('#list_form').val('status')">&nbsp;<?php } ?>
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
