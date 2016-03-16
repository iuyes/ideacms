<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>admin</title>
<link href="<?php echo ADMIN_THEME?>images/reset.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/system.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/main.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/table_form.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="<?php echo ADMIN_THEME?>js/jquery.min.js"></script>
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
      <th align="left" colspan=4>备份选项</th>
  </tr>
  </thead>
  <tr>
      <td width="200">每个分卷文件大小</td>
      <td colspan=3><input type=text class="input-text" name="size" value="5120" size=5> K</td>
  </tr>
  <tr>
      <td width="200">系统表选择 <input name="sf" class="cf" type="checkbox" onClick="setC('f')"></td>
      <td colspan=3>
          <table width="600">
          <?php 
		  $i = 0;
		  foreach($data as $v) {
	      if ($v['fc']) {
		  if ($i%4==0) echo "<tr>";
		  ?>
          <td><input class="cff" type="checkbox" name="table[]" value="<?php echo $v['Name']?>"/></td>
          <td><?php echo $v['Name']?></td>
          <?php 
		  if ($i%4==3) echo "</tr>";
		  $i++;
		  }
		  }
		  ?>
          </table>
      </td>
  </tr>
   <tr>
      <td width="200">其他表选择 <input name="so" class="co" type="checkbox" onClick="setC('o')"></td>
      <td colspan=3>
          <table width="600">
          <?php 
		  $i = 0;
		  foreach($data as $v) {
	      if (!$v['fc']) {
		  if ($i%4==0) echo "<tr>";
		  ?>
          <td><input class="cfo" type="checkbox" name="table[]" value="<?php echo $v['Name']?>"/></td>
          <td><?php echo $v['Name']?></td>
          <?php 
		  if ($i%4==3) echo "</tr>";
		  $i++;
		  }
		  }
		  ?>
          </table>
      </td>
  </tr>
  <tr>
      <td></td>
      <td colspan=3>
      <input type="submit" name="submit" value="  开 始 备 份 数 据  " class="button"></td>
  </tr>
</table>
</form><div class="btn">&nbsp;</div>
<script language="javascript">
function setC(c) {
	if($('.c'+c).attr('checked')) {
		$('.cf'+c).attr('checked',true);
	} else {
		$('.cf'+c).attr('checked',false);
	}
}
</script>
</div>
</div>
</body>
</html>
