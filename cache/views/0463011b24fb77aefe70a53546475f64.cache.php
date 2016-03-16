<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="<?php echo ADMIN_THEME; ?>images/reset.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/system.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/dialog.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/main.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/switchbox.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/table_form.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo ADMIN_THEME; ?>js/jquery.min.js"></script>
<title>admin</title>
</head>
<body style="font-weight: normal;">
<div class="subnav">
	<div class="table-list">
		<div class="pad-10">
			<div class="col-tab">
				<div class="explain-col mb10">
					<?php if ($ck_ob) { ?>
					<font color="red"><?php echo lang('a-con-116'); ?></font>
					<?php }  if ($check) { ?>
					<font color="red"><?php echo lang('a-con-117', array('1'=>APP_ROOT, '2'=>$check)); ?></font>
					<?php } else {  echo lang('a-con-118');  } ?>
				</div>
				<div class="contentList pad-10">
					<div class="step">
						<h4>第一步：</h4>
						<input type="button" class="button" value="第一步&nbsp;<?php echo lang('a-men-52'); ?>" name="submit" onClick="window.location.href='<?php echo url("admin/index/cache"); ?>';">&nbsp;&nbsp;
					</div>
					<div class="step">
						<h4>第二步：</h4>
						<input type="button" class="button" value="第二步&nbsp;<?php echo lang('a-men-54'); ?>" name="submit" onClick="window.location.href='<?php echo url("admin/content/updateurl/"); ?>';">&nbsp;&nbsp;
					</div>
					<div class="step">
						<h4>第三步：</h4>
						<?php if (!$ismb) { ?>
					<input type="button" class="button" value="<?php echo lang('a-con-119'); ?>" name="submit" onClick="window.location.href='<?php echo url("admin/html/indexc"); ?>';">&nbsp;&nbsp;
					<?php } ?>
					<input type="button" class="button" value="<?php echo lang('a-con-120'); ?>" name="submit" onClick="window.location.href='<?php echo url("admin/html/category"); ?>';">&nbsp;&nbsp;
					<input type="button" class="button" value="<?php echo lang('a-con-121'); ?>" name="submit" onClick="window.location.href='<?php echo url("admin/html/show"); ?>';">&nbsp;&nbsp;
					<input type="button" class="button" value="<?php echo lang('a-men-70'); ?>" name="submit" onClick="window.location.href='<?php echo url("admin/html/form"); ?>';">&nbsp;&nbsp;
					<input type="button" class="button" value="<?php echo lang('a-con-122'); ?>" name="submit" onClick="window.location.href='<?php echo url("admin/html/clear"); ?>';">&nbsp;&nbsp;
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>
