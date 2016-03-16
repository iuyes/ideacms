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
	<a href="<?php echo purl('admin/index')?>" class="on"><em>文章踩顶</em></a><span>|</span>
	<a href="<?php echo url('admin/plugin/set', array('pluginid'=>$pluginid))?>"><em>应用配置</em></a>
	</div>
	<div class="explain-col">
	<form action="" method="post">
    <input name="form" id="list_form" type="hidden" value="search">
	搜索标题：<input type="text" class="input-text" size="20" name="kw"><input type="submit" class="button" value="搜  索" name="submit">
	</form>
	</div>
<div class="bk10"></div>
<div class="table-list">
<form action="" method="post">
<input name="form" id="list_form" type="hidden" value="del">
<table width="100%">
	<thead>
	<tr>
	  <th width="33" align="right"><input name="deletec" id="deletec" type="checkbox" onClick="setC()"></th>
		<th width="400" align="left">标题</th>
		<th width="86" align="left">踩</th>
		<th width="86" align="left">顶</th>
		<th width="155" align="left">更新时间</th>
		<th width="" align="left">&nbsp;</th>
	</tr>
    </thead>
    <tbody>
    <?php foreach ($list as $t) { ?>
	<tr height="25">
	  <td align="right"><input name="dels[]" value="<?php echo $t['id']?>" type="checkbox" class="deletec"></td>
	  <td align="left"><a href="<?php echo url('content/show', array('id'=>$t['contentid']));?>" target="_blank"><?php echo $t['title']?></a></td>
	  <td align="left"><?php echo $t['cai']?></td>
	  <td align="left"><?php echo $t['ding']?></td>
	  <td align="left"><?php echo date('Y-m-d H:i:s', $t['addtime'])?></td>
	  <td align="left">&nbsp;</td>
	  </tr>
    <?php } ?>
	<tr height="25">
	  <td colspan="11" align="left">
      <input type="submit" class="button" value="删  除" name="submit">&nbsp;
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
