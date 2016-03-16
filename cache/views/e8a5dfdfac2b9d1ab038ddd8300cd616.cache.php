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
<script type="text/javascript" src="<?php echo ADMIN_THEME; ?>js/dialog.js"></script>
<script type="text/javascript" src="<?php echo LANG_PATH; ?>lang.js"></script>
<script type="text/javascript">
function get_avatar(filepath) {
	if (filepath) {
		var content = '<img src="'+filepath+'" />';
	} else {
		var content = idec_lang[0];
	}
	window.top.art.dialog({title:idec_lang[1],fixed:true, content: content});
}
function setC() {
	if($("#deletec").attr('checked')) {
		$(".deletec").attr("checked",true);
	} else {
		$(".deletec").attr("checked",false);
	}
}
</script>
<title>admin</title>
</head>
<body style="font-weight: normal;">
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
		<a href="<?php echo url('admin/member/index'); ?>" <?php if ($status==0) { ?>class="on"<?php } ?>><em><?php echo lang('a-mem-26'); ?>(<?php echo $count[0]; ?>)</em></a><span>|</span>
		<a href="<?php echo url('admin/member/index', array('status'=>1)); ?>" <?php if ($status==1) { ?>class="on"<?php } ?>><em><?php echo lang('a-con-20'); ?>(<?php echo $count[1]; ?>)</em></a><span>|</span>
		<a href="<?php echo url('admin/member/index', array('status'=>2)); ?>" <?php if ($status==2) { ?>class="on"<?php } ?>><em><?php echo lang('a-con-21'); ?>(<?php echo $count[2]; ?>)</em></a><span>|</span>
		<?php if (admin_auth($userinfo['roleid'], 'member-reg')) { ?><a href="<?php echo url('admin/member/reg'); ?>"><em><?php echo lang('a-mem-27'); ?></em></a><?php } ?>
	</div>

    <div class="bk10"></div>
	<div class="explain-col">
		<form action="" method="post">
			<input name="form" type="hidden" value="search" />
			<?php echo lang('a-mem-28'); ?>：<input type="text" class="input-text" size="20" name="kw" />
			<input type="submit" class="button" value="<?php echo lang('a-search'); ?>" name="submit" />
            <?php if ($is_syn) { ?>
                <div class="onShow"><?php echo lang('a-user-ex-1'); ?></div>
            <?php } ?>
		</form>
	</div>
    <div class="bk10"></div>
	<div class="table-list">
		<form action="" method="post">
		<input name="form" id="list_form" type="hidden" value="" />
		<table width="100%">
		<thead>
		<tr>
			<th width="15" align="right"><input name="deletec" id="deletec" type="checkbox" onClick="setC()" /></th>
			<th width="30" align="left">ID </th>
			<th width="130" align="left"><?php echo lang('a-user'); ?></th>
			<th width="70" align="left"><?php echo lang('a-mem-29'); ?></th>
			<th width="70" align="left"><?php echo lang('a-mem-30'); ?></th>
			<th width="55" align="left"><?php echo lang('a-mem-31'); ?></th>
			<th width="120" align="left"><?php echo lang('a-mem-32'); ?></th>
			<th width="120" align="left"><?php echo lang('a-mem-33'); ?></th>
			<th align="left"><?php echo lang('a-option'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php if (is_array($list)) { $count=count($list);foreach ($list as $t) {  $avatar = ''; if($t['avatar']) { $avatar = image($t['avatar']); } ?>
		<tr height="25">
			<td align="right"><input name="del_<?php echo $t['id']; ?>_<?php echo $t['modelid']; ?>" type="checkbox" class="deletec" /></td>
			<td align="left"><?php echo $t['id']; ?></td>
			<td align="left"><?php if (!$t['status']) { ?><font color="#FF0000">[<?php echo lang('a-con-32'); ?>]</font><?php } ?>
			<a href="javascript:;" onClick="get_avatar('<?php echo $avatar; ?>')"><?php echo $t['username']; ?></a></td>
			<td align="left"><a href="<?php echo url('admin/member/index', array('modelid'=>$t['modelid'])); ?>"><?php echo $membermodel[$t['modelid']]['modelname']; ?></a></td>
			<td align="left"><a href="<?php echo url('admin/member/index',array('groupid'=>$t['groupid'])); ?>"><?php echo $membergroup[$t['groupid']]['name']; ?></a></td>
			<td align="left"><?php echo $t['credits']; ?></td>
			<td align="left"><?php echo date(TIME_FORMAT, $t['regdate']); ?></td>
			<td align="left"><a href="http://www.baidu.com/baidu?wd=<?php echo $t['regip']; ?>" target=_blank><?php echo $t['regip']; ?></a></td>
			<td align="left">
			<?php if (admin_auth($userinfo['roleid'], 'member-edit')) { ?><a href="<?php echo url('admin/member/edit',array('id'=>$t['id'])); ?>"><?php echo lang('a-mem-34'); ?></a> | <?php }  if (is_array($memberextend)) { $count=count($memberextend);foreach ($memberextend as $m) { ?>
			<a href="<?php echo url('admin/member/extend',array('modelid'=>$m['modelid'],'touserid'=>$t['id'])); ?>"><?php echo $m['modelname']; ?></a> | 
			<?php } }  if (admin_auth($userinfo['roleid'], 'member-del')) { ?><a href="javascript:;" onClick="if(confirm('<?php echo lang('a-mem-35'); ?>')){ window.location.href='<?php echo url('admin/member/del/',array('modelid'=>$t['modelid'],'id'=>$t['id']));?>'; }"><?php echo lang('a-del'); ?></a> <?php } ?>
			</td>
		</tr>
		<?php } } ?>
		<tr height="25">
			<td colspan="9" align="left">
			<input <?php if (!admin_auth($userinfo['roleid'], 'member-edit')) { ?>disabled<?php } ?> type="submit" class="button" value="<?php echo lang('a-con-36'); ?>" name="submit_status_1" onClick="$('#list_form').val('status_1')" />&nbsp;
			<input <?php if (!admin_auth($userinfo['roleid'], 'member-edit')) { ?>disabled<?php } ?> type="submit" class="button" value="<?php echo lang('a-con-37'); ?>" name="submit_status_0" onClick="$('#list_form').val('status_0')" />&nbsp;
		</tr>    
		</tbody>
		</table>
		<?php echo $pagelist; ?>
		</form>
	</div>

</div>
</body>
</html>