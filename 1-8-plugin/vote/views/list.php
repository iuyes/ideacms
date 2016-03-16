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
<script language="javascript">
function setc() {
	if ($('#selectids').attr('checked')) {
		$('.ids').attr('checked',true);
	} else {
		$('.ids').attr('checked',false);
	}	
}
</script>
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
<table width="100%">
	<thead>
	<tr>
		<th width="50" align="right">选择&nbsp;<input name="index" value="1" id="selectids" type="checkbox" onClick="setc()"></th>
		<th width="280" align="left">主题</th>
		<th width="80" align="left">投票数量</th>
		<th width="50" align="left">状态</th>
		<th width="130" align="left">添加时间</th>
		<th align="left">操作</th>
	</tr>
    </thead>
    <tbody>
    <?php foreach ($list as $t) { ?>
	<tr height="25">
	  <td align="right"><input class='ids' name="ids[]" value="<?php echo $t['id'];?>" type="checkbox"></td>
	  <td align="left"><?php echo $t['subject'] ?></td>
	  <td align="left"><?php echo $t['votenums']?></td>
	  <td align="left">
		<?php
		if ($t['status'] == 0) {
			echo '<span style="color:red">无效</span>';
		} else {
			echo '<span style="color:#C60">生效</span>';
		}
		?>
      </td>
	  <td align="left"><?php echo date('Y-m-d H:i:s', $t['addtime'])?></td>
	  <td align="left">
	    <a href="<?php echo purl('index/post/',array('id'=>$t['id']))?>" target="_blank">投票</a> | 
	    <a href="<?php echo purl('index/show/',array('id'=>$t['id']))?>" target="_blank">查看</a> | 
		<a href="<?php echo purl('admin/edit/',array('id'=>$t['id']))?>">修改</a>
	  </td>
	</tr>
    <?php } ?>
	<tr height="44">
	  <td colspan="6" align="left">
      <input class="button" type="submit" name="submit" value="删 除" />
      </td>
	</tr>
	</tbody>
</table>
<?php echo $pagelist?>
</form>
</div>
</div>
</body>
</html>
