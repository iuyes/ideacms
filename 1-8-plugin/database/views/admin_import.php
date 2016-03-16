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
</div>
<div class="pad_10">
<div class="table-list">
<form method="post" name="myform" id="myform" action="">
<table width="100%" cellspacing="0">
    <thead>
       <tr>
           <th width="33" align="right">&nbsp;<input name="selectform" class="cselectform" type="checkbox" onClick="setC()"></th>
           <th width="150" align="left">备份时间</th>
           <th width="50">备份版本</th>
           <th width="111">文件大小</th>
           <th align="left" >操作</th>
       </tr>
    </thead>
    <tbody>
	<?php 
	if ($data) {
	foreach($data as $v){?>
	<tr>
	<td align="right"><input class="selectform" type="checkbox" name="paths[]" value="<?php echo $v['path']?>"/></td>
	<td align="left"><?php echo date('Y-m-d H:i:s', $v['path'])?></td>
	<td align="left"><?php echo $v['version']?></td>
	<td align="center"><?php echo $v['size']?></td>
	<td  align="left">
	<?php if ($v['version'] == '未知') { ?>
	<a href="javascript:;" onClick="if(confirm('确定恢复数据吗？')){ window.location.href='<?php echo purl("admin/import", array("path"=>$v['path']))?>'; }">恢复数据</a>
	<?php } else { ?>
	<?php 
	if ($v['version'] != CMS_VERSION) {
		echo '<font color=red>与当前版本(' . CMS_VERSION . ')不一致无法恢复</font>';
	} else {
		?>
		<a href="javascript:;" onClick="if(confirm('确定恢复数据吗？')){ window.location.href='<?php echo purl("admin/import", array("path"=>$v['path']))?>'; }">恢复数据</a>
		<?php
	}
	?>
	<?php } ?>
    </td>
	</tr>
	<?php } }?>
	</tbody>
</table>
 <?php 
if($data){
?>
<div class="btn">
<input type="submit" class="button" value="批量删除" name="submit" >&nbsp;备份目录：/cache/bakup/
</div>
<?php 
}
?>
</form>
</div>
</div>
<script language="javascript">
function setC() {
	if($(".cselectform").attr('checked')) {
		$(".selectform").attr("checked",true);
	} else {
		$(".selectform").attr("checked",false);
	}
}
</script>
</body>
</html>
