<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<link href="<?php echo PUBLIC_THEME; ?>css/bootstrap.css" rel="stylesheet" type="text/css" />
<link href="<?php echo PUBLIC_THEME; ?>css/font-awesome.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/reset.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/system.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/dialog.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/main.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/switchbox.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ADMIN_THEME; ?>images/table_form.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo ADMIN_THEME; ?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_THEME; ?>js/dialog.js"></script>
</head>
<body>
<style type="text/css">
html{ _overflow-y:scroll }
tr { height:25px;}
</style>
<div id="main_frameid" class="pad-10" style="_margin-right:-12px;_width:98.9%;">
<script type="text/javascript">
$(function(){
	$.getScript("<?php echo url('admin/index/ajaxcount', array('type'=>'member')); ?>");
    $.getScript("<?php echo url('admin/index/ajaxcount', array('type'=>'size')); ?>");
	$.getScript("<?php echo url('admin/index/ajaxcount', array('type'=>'install')); ?>");
	if ($.browser.msie && parseInt($.browser.version) < 7) $('#browserVersionAlert').show();
});
</script>
<div class="explain-col mb10" style="display:none" id="browserVersionAlert"><?php echo lang('a-ie'); ?></div>
<div class="row">
<div class="col-md-6 col-sm-12">
	<div class="parts">
	<h6><?php echo lang('a-ind-6'); ?></h6>
	<div class="content" style="margin-top:18px;">
		<div class="row quick">
		<div class="col-sm-6 col-md-3">
			<a href="<?php echo url('admin/category'); ?>"><i class="fa fa-list"></i></a><br><br>
			<a href="<?php echo url('admin/category'); ?>" class="text"><?php echo lang('a-men-92'); ?></a>
		</div>
		<div class="col-sm-6 col-md-3">
			<a href="<?php echo url('admin/plugin/'); ?>"><i class="fa fa-cubes"></i></a><br><br>
			<a href="<?php echo url('admin/plugin/'); ?>" class="text"><?php echo lang('a-men-7'); ?>管理</a>
		</div>
		<div class="col-sm-6 col-md-3">
		<a href="<?php echo url('admin/site/config'); ?>"><i class="fa fa-cogs"></i></a><br><br>
		<a href="<?php echo url('admin/site/config'); ?>" class="text"><?php echo lang('a-men-12'); ?></a>
		</div>
		<div class="col-sm-6 col-md-3">
			<a href="<?php echo url('admin/member/'); ?>"><i class="fa fa-users"></i></a><br><br>
			<a href="<?php echo url('admin/member/'); ?>" class="text"><?php echo lang('a-men-5'); ?>管理</a>
		</div>
		</div>
	</div>
	</div>
</div>

<div class="col-md-6 col-sm-12">
	<div class="parts">
	<h6><?php echo lang('a-ind-0'); ?></h6>
	<div class="content">
	<p><?php echo lang('a-com-15'); ?>，<?php echo $userinfo['username']; ?>&nbsp;<?php if ($userinfo['realname']) { ?>(<?php echo $userinfo['realname']; ?>)<?php } ?> ，<?php echo lang('a-ind-1'); ?>：<?php echo $userinfo['rolename']; ?> </p>
	<p><?php echo lang('a-ind-2'); ?>：<?php echo date(TIME_FORMAT, $userinfo['lastlogintime']); ?> ，<?php echo lang('a-ind-3'); ?>：<a href="http://www.baidu.com/baidu?wd=<?php echo $userinfo['lastloginip']; ?>" target=_blank><?php echo $userinfo['lastloginip']; ?></a></p>
  <p><?php echo lang('a-ind-4'); ?>：<?php echo date(TIME_FORMAT, $userinfo['logintime']); ?> ，<?php echo lang('a-ind-5'); ?>：<a href="http://www.baidu.com/baidu?wd=<?php echo $userinfo['loginip']; ?>" target=_blank><?php echo $userinfo['loginip']; ?></a></p>
	<div class="bk20 hr"><hr></div>
	</div>
</div>
</div>


<div class="col-md-6 col-sm-12">
	<div class="parts">
    <h6><?php echo lang('a-ind-16'); ?></h6>
	<div class="content">
	    <table border="0" cellpadding="0" cellspacing="0">
		  <tr>
			<td width="261"><?php echo lang('a-ind-17'); ?>模块：&nbsp;<a href="<?php echo url('admin/member/'); ?>"><span id="member_1" class="btn btn-info btn-xs"><img src="<?php echo ADMIN_THEME; ?>images/onLoad.gif"></span></a></td>
			<td width="279"><?php echo lang('a-ind-18'); ?>：&nbsp;<a href="<?php echo url('admin/member/', array('status'=>2)); ?>"><span id="member_2" class="btn btn-warning btn-xs"><img src="<?php echo ADMIN_THEME; ?>images/onLoad.gif"></span></a></td>
		  </tr>
		</table>
	    <div class="bk20 hr"><hr></div>
		<table border="0" cellpadding="0" cellspacing="0">
		<?php if (is_array($model)) { $count=count($model);foreach ($model as $t) { ?>
		  <tr>
			<td width="261"><?php echo $t['modelname']; ?>模块：&nbsp;<a href="<?php echo url('admin/content/', array('modelid'=>$t['modelid'])); ?>"><span id="m_<?php echo $t['modelid']; ?>_1" class="btn btn-info btn-xs"><img src="<?php echo ADMIN_THEME; ?>images/onLoad.gif"></span></a></td>
			<td width="279"><?php echo lang('a-ind-18'); ?>：&nbsp;<a href="<?php echo url('admin/content/verify', array('modelid'=>$t['modelid'], 'status'=>3)); ?>"><span id="m_<?php echo $t['modelid']; ?>_2" class="btn btn-warning btn-xs"><img src="<?php echo ADMIN_THEME; ?>images/onLoad.gif"></span></a></td>
		  </tr>
		<script type="text/javascript">
		$(function(){
		　　$.getScript("<?php echo url('admin/index/ajaxcount', array('modelid'=>$t['modelid'])); ?>");
		});
		</script>
		<?php } } ?>
		</table>
	</div>
	</div>
</div>

<div class="col-md-6 col-sm-12">
	<div class="parts">
	<h6><?php echo lang('a-ind-19'); ?></h6>
	<div class="content">
  <p><?php echo lang('a-ind-10'); ?>：&nbsp;<?php echo CMS_NAME; ?>&nbsp;<i class="fa fa-vimeo-square"></i><?php echo CMS_VERSION; ?> <span id="idea_version"></span></p>
  <p>更新时间：&nbsp;<i class="fa fa-clock-o"></i>&nbsp;<?php echo CMS_UPDATE; ?></p>
  <div class="bk20 hr"><hr></div>
  <p>操作系统：&nbsp;<?php echo PHP_OS; ?></p>
  <p>PHP版本：&nbsp;PHP<?php echo PHP_VERSION; ?></p>
  <p>软件环境：&nbsp;<?php echo strcut($_SERVER['SERVER_SOFTWARE'], 20); ?></p>
  <div class="bk20 hr"><hr></div>
	</div>
</div>
</div>
</div><!--end row-->

</div>
</body>
</html>
