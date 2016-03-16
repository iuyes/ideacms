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
    <span>|</span>
    <a class="on" href="javascript:;"><em>详细信息</em></a>
    </div>
</div>
<div class="pad_10">
    <div class="table-list">
        <table width="100%" cellspacing="0">
        <thead>
        <tr>
            <th width="150" align="left">特征代码</th>
            <th align="left">文件地址</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data as $id=>$v){?>
        <tr>
            <td align="left"><?php echo implode('|', $v['info'])?></td>
            <td align="left"><a href="javascript:show_file(<?php echo $id;?>, '特征代码<?php echo implode('|', $v['info'])?>， 请检查代码是否被非法篡改');"><?php echo $v['file']?></a></td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
        <div class="btn">&nbsp;</div>
    </div>
</div>
<script language="javascript">
	function show_file(id, title) {
		var url   = '<?php echo purl('admin/file/',array('fid'=>$fileid))?>&id='+id;
		var winid = 'loadinfo';
		window.top.art.dialog({id:winid, iframe:url, title:title, width:'80%', height:'400', lock:true});
	}
</script>
</body>
</html>
