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
<input name="list_form" id="list_form" type="hidden" value="">
<table width="100%" cellspacing="0">
    <thead>
       <tr>
           <th width="50" align="right">&nbsp;</th>
           <th width="150" align="left">表名</th>
           <th width="50" align="left">类型</th>
           <th width="80" align="left">编码</th>
           <th width="90" align="left">记录数</th>
           <th width="90" align="left">使用空间</th>
           <th width="90" align="left">碎片</th>
           <th align="left">操作</th>
       </tr>
    </thead>
    <tbody>
	<?php foreach($data as $v){?>
	<tr height="25"<?php if (!$v['fc']) echo ' style="background-color:#FFC"';?>>
	<td align="right"><input class="selectform" type="checkbox" name="table[]" value="<?php echo $v['Name']?>"/></td>
	<td align="left"><?php echo $v['Name']?></td>
	<td align="left"><?php echo $v['Engine']?></td>
	<td align="left"><?php echo $v['Collation']?></td>
	<td align="left"><?php echo $v['Rows']?></td>
	<td align="left"><?php echo formatFileSize($v['Data_length']+$v['Index_length'])?></td>
	<td align="left"><?php echo formatFileSize($v['Data_free'])?></td>
	<td align="left">
    <a href="<?php echo purl("admin/repair", array("name"=>$v['Name']))?>">修复</a> | 
    <a href="<?php echo purl("admin/optimize", array("name"=>$v['Name']))?>">优化</a> | 
    <a href="javascript:void(0);" onclick="showcreat('<?php echo $v['Name']?>')">结构</a>
    </td>
	</tr>
	<?php }
	if (is_array($data)) {
	?>
    <tr height="28">
	<td align="right">全选&nbsp;&nbsp;<input name="selectform" class="cselectform" type="checkbox" onClick="setC()"></td>
	<td colspan="7" align="left"><input type="submit" class="button" value="批量修复" name="submit" onClick="$('#list_form').val('repair')">&nbsp;<input type="submit" class="button" value="批量优化" name="submit" onClick="$('#list_form').val('optimize')">&nbsp;</td>
	</tr>
    <?php }?>
	</tbody>
</table>
<div class="btn">&nbsp;</div>
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
function showcreat(tblname) {
	window.top.art.dialog({
		title:tblname+'表结构', 
		id:'show', 
		iframe:'<?php echo purl("admin/table", null, 1)?>&name='+tblname,
		width:'500px',
		height:'350px'
	});
}

</script>
</body>
</html>
