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
<script language="javascript">
$(function(){
   $('#loading').hide();
});
function loadimg() {
    $('#loading').show();
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
</div>
<div class="pad_10">
<div class="table-list">
<form method="post" name="myform" id="myform" action="">
<table width="100%" cellspacing="0">
  <thead>
  <tr>
      <th align="left" colspan=4>执行SQL</th>
  </tr>
  </thead>
  <tr>
      <td width="100">&nbsp;</td>
      <td colspan=3><textarea style="width:490px;height:100px;" id="sql" name="sql"></textarea></td>
  </tr>
  <tr>
      <td></td>
      <td colspan=3><input type="submit" name="submit" value="  执行  " onClick="loadimg()" class="button">&nbsp;
	  <span id="loading"><img src="<?php echo $viewpath?>loading.gif" /></span>
	  </td>
  </tr>
</table>
<?php if ($data) { ?>
<table width="100%" cellspacing="0">
  <thead>
  <tr>
      <th align="left" colspan=4>执行结果(#<?php echo count($data); ?>)：<?php echo $sql;?></th>
  </tr>
  </thead>
  <tr>
      <td colspan=4>
	  <pre><?php print_r($data); ?></pre>
	  </td>
  </tr>
</table>
<?php } ?>
</form>
</div>
</div>
</body>
</html>
