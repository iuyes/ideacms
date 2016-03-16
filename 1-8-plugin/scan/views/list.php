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
            <th width="30" align="right"><input name="selectform" class="cselectform" type="checkbox" onClick="setC()"></th>
            <th width="150" align="left">扫描时间</th>
            <th width="60" align="left">影响文件</th>
            <th width="150" align="left">文件类型</th>
            <th align="left">特征代码</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data as $id=>$v){?>
        <tr>
            <td align="right"><input class="selectform" type="checkbox" name="del[]" value="<?php echo $id?>"/></td>
            <td align="left"><a href="<?php echo purl('admin/show', array('id'=>$id));?>"><?php echo date('Y-m-d H:i:s', $v['time'])?></a></td>
            <td align="left"><a href="<?php echo purl('admin/show', array('id'=>$id));?>"><?php echo count($v['info'])?></a></td>
            <td align="left"><?php echo $v['file']?></td>
            <td align="left"><?php echo $v['code']?></td>
        </tr>
        <?php }
        if (is_array($data)) {
        ?>
        <tr>
            <td colspan="5"><input type="submit" class="button" value="删除" name="submit" /></td>
        </tr>
        <?php }?>
        </tbody>
        </table>
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
