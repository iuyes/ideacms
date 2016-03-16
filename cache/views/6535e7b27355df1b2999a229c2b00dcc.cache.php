<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<title>IdeaCMS-<?php echo lang('a-msg-0'); ?></title>
<style type="text/css">
<!--
*{ 
padding:0; margin:0; font-size:12px
}
a:link,a:visited{
	text-decoration:none;color:#0068a6
}
a:hover,a:active{
	color:#ff6600;text-decoration: underline
}
.showMsg{
	border: 1px solid #1e64c8; zoom:1; width:450px; height:172px;position:absolute;top:44%;left:50%;margin:-87px 0 0 -225px
}
.showMsg h5{
	background-image: url(<?php echo ADMIN_THEME; ?>images/msg.png);background-repeat: no-repeat; color:#fff; padding-left:35px; height:25px; line-height:26px;*line-height:28px; overflow:hidden; font-size:14px; text-align:left;
}
.showMsg .content{ 
    padding:46px 12px 10px 45px; font-size:14px; height:64px; text-align:left
}
.showMsg .bottom{
	background:#e4ecf7; margin: 0 1px 1px 1px;line-height:26px; *line-height:30px; height:26px; text-align:center
}
.showMsg .ok, .showMsg .guery, .showMsg .no{
	background: url(<?php echo ADMIN_THEME; ?>images/msg_bg.png) no-repeat 0px -560px;
}
.showMsg .guery{
	background-position: left -460px;
}
.showMsg .no{
	background-position: left -360px;
}
-->
</style>
<script type="text/javaScript" src="<?php echo ADMIN_THEME; ?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_THEME; ?>js/dialog.js"></script>
</head>
<body>
<div class="showMsg" style="text-align:center">
	<h5><?php echo lang('a-msg-0'); ?></h5>
    <div class="content <?php if ($result==1) { ?>ok<?php } else if ($result==2) { ?>guery<?php } else { ?>no<?php } ?>" style="display:inline-block;display:-moz-inline-stack;zoom:1;*display:inline;max-width:330px">
	<?php echo $msg; ?>
	</div>
    <div class="bottom">
    <?php if ($url) { ?>
    <a href="<?php echo $url; ?>"><?php echo lang('a-msg-1'); ?></a>
	<meta http-equiv="refresh" content="<?php echo $time; ?>; url=<?php echo $url; ?>">
    <?php } else {  if ($i) { ?><a href="javascript:history.back();" >[<?php echo lang('a-msg-2'); ?>]</a><?php }  } ?> 
	</div>
</div>
<script type="text/javascript">
$(function(){
    window.top.art.dialog({id:'clz'}).close();
}); 
</script>
</body>
</html>