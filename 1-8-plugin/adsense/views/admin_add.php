<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="<?php echo ADMIN_THEME?>images/reset.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/system.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/dialog.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/main.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/switchbox.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME?>images/table_form.css" rel="stylesheet" type="text/css" />
<script src="<?php echo ADMIN_THEME?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_THEME?>js/dialog.js"></script>
<link href="<?php echo $extpath?>calendar/jscal2.css" type="text/css" rel="stylesheet">
<link href="<?php echo $extpath?>calendar/border-radius.css" type="text/css" rel="stylesheet">
<link href="<?php echo $extpath?>calendar/win2k.css" type="text/css" rel="stylesheet">
<script src="<?php echo $extpath?>calendar/calendar.js" type="text/javascript"></script>
<script src="<?php echo $extpath?>calendar/lang/en.js" type="text/javascript"></script>
<script language="javascript">var sitepath = "<?php echo SITE_PATH;?>";</script>
<script language="javascript" src="<?php echo ADMIN_THEME?>js/core.js"></script>
<title>后台管理</title>
</head>
<body style="font-weight: normal;">
<div class="subnav">
<div class="content-menu ib-a blue line-x">
<a href='<?php echo purl("admin")?>'><em>广告位管理</em></a><span>|</span>
<a href='<?php echo purl("admin/add")?>' class="on"><em>添加广告位</em></a><span>|</span>
<a href='<?php echo purl("admin/cache")?>'><em>更新广告缓存</em></a>
</div>
<div class="bk10"></div>
<div class="table-list">
<form action="" method="post">
<input name="data[id]" type="hidden" id="id" value="<?php echo $data[id]?>">
<table width="100%" class="table_form ">
	<tr>
        <th width="200">广告位名称：</th>
        <td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data['adname']?>" name="data[adname]"></td>
      </tr>
     <tr>
        <th>广告尺寸：</th>
        <td>宽：<input type="text" class="input-text" value="<?php echo $data['width']?>" name="data[width]" style="width:40px;">px&nbsp;&nbsp;
        高：<input type="text" class="input-text" value="<?php echo $data['height']?>" name="data[height]" style="width:40px;">px
        </td>
     </tr>
     <tr>
        <th>显示类型：</th>
        <td>
        <select name="data[showtype]">
        <option value="0">≡ 选择类型 ≡</option>
        <?php
		$type = array(1=>'顺序显示', 0=>'随机显示');
        foreach ($type as $tid=>$t) {
			$selected = isset($data['showtype']) ? ($tid==$data['showtype'] ? ' selected' : '') : '';
			echo '<option value="' . $tid . '" ' . $selected . '>≡ ' . $t . ' ≡</option>';
		}
		?>
        </select>
        </td>
     </tr>
	<tr>
        <th></th>
        <td><input type="submit" class="button" value="提交" name="submit"></td>
      </tr>
	<tr>
</table>
</form>
</div>
</div>
</body>
</html>