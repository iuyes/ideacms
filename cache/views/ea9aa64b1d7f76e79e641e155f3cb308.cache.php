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
<script type="text/javascript" src="<?php echo LANG_PATH; ?>lang.js"></script>
<title>admin</title>
</head>
<body style="font-weight: normal;">
<div class="subnav">
    <div class="content-menu ib-a blue line-x">
        <a href="<?php echo url('admin/plugin'); ?>" class="on"><em><?php echo lang('da015'); ?></em></a><span>|</span>
        <a href="<?php echo url('admin/plugin/cache'); ?>"><em><?php echo lang('a-cache'); ?></em></a><span>|</span>
        <a href="tencent://message/?uin=976510651" target="_blank"><em><?php echo lang('da016'); ?></em></a>
    </div>
	<div class="bk10"></div>
	<div class="table-list">
		<table width="100%" cellspacing="0">
		<thead>
		<tr>
			<th width="6%">ID</th>
			<th width="14%" align="left"><?php echo lang('a-plu-39'); ?></th>
			<th width="8%" align="left"><?php echo lang('a-plu-40'); ?></th>
			<th width="7%" align="left"><?php echo lang('a-plu-41'); ?></th>
			<th width="7%" align="left"><?php echo lang('a-plu-42'); ?></th>
			<th width="9%" align="left"><?php echo lang('a-plu-43'); ?></th>
			<th width="29%" align="left"><?php echo lang('a-option'); ?></th>
			<th width="20%" align="left">&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<?php if (is_array($list)) { $count=count($list);foreach ($list as $t) {  $url = $t['typeid'] ? url($t['dir'].'/admin/index/') : url('admin/plugin/set', array('pluginid'=>$t['pluginid'])); ?>
		<tr>
			<td align="center"><?php echo $t['pluginid']; ?></td>
			<td ><a href="<?php echo $url; ?>"><?php echo $t['name']; ?></a></td>
			<td align="left"><?php echo $t['author']; ?></td>
			<td align="left"><?php echo $t['version']; ?></td>
			<td align="left"><?php echo $t['dir']; ?></td>
			<td align="left"><?php if ($t['typeid']==1) {  echo lang('a-plu-44');  }  if ($t['typeid']==2) {  echo lang('a-plu-45');  }  if ($t['typeid']==0) {  echo lang('a-plu-46');  } ?></td>
			<td align="left">
			<?php if ($t['pluginid']) {  if (admin_auth($userinfo['roleid'], 'plugin-set')) { ?><a href="<?php echo url('admin/plugin/set',array('pluginid'=>$t['pluginid'])); ?>"><?php echo lang('a-plu-49'); ?></a>&nbsp;<?php }  if (admin_auth($userinfo['roleid'], 'plugin-disable')) { ?><a href="<?php echo url('admin/plugin/disable',array('pluginid'=>$t['pluginid'])); ?>"><?php if ($t['disable']) { ?><font color="#FF0000"><?php echo lang('a-open'); ?></font><?php } else {  echo lang('a-close');  } ?></a>&nbsp;<?php }  if (admin_auth($userinfo['roleid'], 'plugin-del')) { ?><a href="<?php echo url('admin/plugin/del',array('pluginid'=>$t['pluginid'])); ?>"><?php echo lang('a-plu-1'); ?></a>&nbsp;<?php }  if ($t['typeid']==2 || $t['typeid']==0) { ?><a href="javascript:;" onClick="getViewData(<?php echo $t['pluginid']; ?>);"><?php echo lang('a-plu-47'); ?></a>&nbsp;<?php }  } else {  if (admin_auth($userinfo['roleid'], 'plugin-add')) { ?><a href="<?php echo url('admin/plugin/add',array('dir'=>$t['dir'])); ?>"><font color="#FF0000"><?php echo lang('a-plu-48'); ?></font></a>&nbsp;<?php }  } ?>
			<!-- <?php if (admin_auth($userinfo['roleid'], 'plugin-unlink')) { ?><a href="<?php echo url('admin/plugin/unlink',array('dir'=>$t['dir'])); ?>"><?php echo lang('a-del'); ?></a><?php } ?> -->
			</td>
			<td width="35%" align="left"></td>
		</tr>
		<?php } } ?>
		</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
function testP(id) {
    $('#test_'+id).html("Checking...");
    $.post('<?php echo url('admin/plugin/ajaxtestp'); ?>&randid='+Math.random(), { id: id }, function(data){
        $('#test_'+id).html(data);
	});
}
function updateP(id) {
    $('#test_'+id).html("Checking...");
    $.post('<?php echo url('admin/plugin/ajaxupdate'); ?>&randid='+Math.random(), { id: id }, function(data){
        $('#test_'+id).html(data);
	});
}
function getViewData(pluginid) {
	var url = '<?php echo url("admin/plugin/ajaxview/",array("pluginid"=>"")); ?>'+pluginid;
	window.top.art.dialog(
	    {id:"ajaxview", okVal:idec_lang[6], cancelVal:idec_lang[7], iframe:url, title:'<?php echo lang('a-plu-37'); ?>', width:'260', height:'90', lock:true,
		button: [
            {
				name: '<?php echo lang('a-copy'); ?>',
				callback: function () {
					 var d = window.top.art.dialog({id:"ajaxview"}).data.iframe;
			         var c = d.document.getElementById('p_'+pluginid).value;
					 copyToClipboard(c);
					 return false;
				},
				focus: true
            }, {
                name: '<?php echo lang('a-close'); ?>'
            }
        ]

		}
	);
}

function copyToClipboard(meintext) {
    if (window.clipboardData){
        window.clipboardData.setData("Text", meintext);
    } else if (window.netscape){
        try {
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        } catch (e) {
            alert("<?php echo lang('a-att-18'); ?>");
		}
        var clip = Components.classes['@mozilla.org/widget/clipboard;1'].
        createInstance(Components.interfaces.nsIClipboard);
        if (!clip) return;
        var trans = Components.classes['@mozilla.org/widget/transferable;1'].
        createInstance(Components.interfaces.nsITransferable);
        if (!trans) return;
        trans.addDataFlavor('text/unicode');
        var len = new Object();
        var str = Components.classes["@mozilla.org/supports-string;1"].
        createInstance(Components.interfaces.nsISupportsString);
        var copytext=meintext;
        str.data=copytext;
        trans.setTransferData("text/unicode",str,copytext.length*2);
        var clipid=Components.interfaces.nsIClipboard;
        if (!clip) return false;
        clip.setData(trans,null,clipid.kGlobalClipboard);
    }
    alert("<?php echo lang('a-att-19'); ?>");
    return false;
}
</script>
</body>
</html>
