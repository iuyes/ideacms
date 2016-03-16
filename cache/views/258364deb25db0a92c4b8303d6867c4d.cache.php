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
<style> 
#ui-upload-holder{ position:relative;width:60px;height:25px;border:1px solid silver; overflow:hidden;float: left;} 
#ui-upload-input{ position:absolute;top:0px;right:0px;height:100%;cursor:pointer; opacity:0;filter:alpha(opacity:0);z-index:999;float:left;} 
#ui-upload-txt{ position:absolute;top:0px;left:0px;width:100%;height:100%;line-height:25px;text-align:center;} 
#ui-upload-button {position:relative;padding-left:10px;padding-top:1px;height:25px;overflow:hidden;float: left;}
#ui-upload-filepath{ position:relative; border:1px solid silver; width:200px; height:25px; overflow:hidden; float:left;border-right:none;} 
#ui-upload-filepathtxt{ position:absolute; top:0px;left:0px; width:100%;height:25px; border:0px; line-height:25px; } 
.uploadlay{padding-left:25px;} 
</style>
</head>
<body style="font-weight: normal;">
<div class="subnav">
	<div class="table-list">
		<form method="post" action="" id="myform" name="myform" enctype="multipart/form-data">
		<input name="size" id="size" type="hidden" value="<?php echo $size; ?>" />
		<input name="admin" id="admin" type="hidden" value="<?php echo $admin; ?>" />
		<input name="ofile" id="ofile" type="hidden" value="<?php echo $ofile; ?>" />
		<input name="filename" id="filename" type="hidden" value="<?php echo $fielname; ?>" />
		<input name="document" id="document" type="hidden" value="<?php echo $document; ?>" />
		<div class="pad-10">
			<div class="col-tab">
				<table width="100%">
				<tr>
				    <td align="center">
					<div class="uploadlay"> 
						<div id="ui-upload-filepath"> 
							<input type="text" id="ui-upload-filepathtxt" class="filepathtxt" disabled /> 
						</div> 
						<div id="ui-upload-holder"> 
							<div id="ui-upload-txt"><?php echo lang('a-mod-208'); ?></div> 
							<input type="file" id="ui-upload-input" name="file" /> 
						</div>
						<div id="ui-upload-button"> 
							<input type="submit" class="button" value="<?php echo lang('a-upload'); ?>" name="submit" align="absmiddle" <?php if ($isimage) { ?>onClick="this.value='uploading'"<?php } else { ?>onClick="uploading()"<?php } ?> />
						</div>
					</div> 
					<script> 
					document.getElementById("ui-upload-input").onchange=function(){ 
						document.getElementById("ui-upload-filepathtxt").value = this.value; 
					}
					function uploading() {
						$("#result").html("<img src='<?php echo ADMIN_THEME; ?>images/loading.gif'>");
					}
					</script>
					</td>
				</tr>
				<tr>
					<td align="center" id="result">
					<?php if ($isimage) {  echo lang('a-con-51'); ?>：<input type="text" class="input-text" style="width:32px;" name="width" value="<?php if ($w) {  echo $w;  } ?>" />&nbsp;X&nbsp;&nbsp;<input type="text" class="input-text" style="width:32px;" name="height" value="<?php if ($h) {  echo $h;  } ?>" />&nbsp;
					<?php echo lang('a-con-52'); ?> <input name="type" type="radio" value="0" checked /> &nbsp; <?php echo lang('a-con-53'); ?> <input name="type" type="radio" value="1" />
					&nbsp;
					<?php } ?>
					</td>
				</tr>
				</table>
				<div class="onShow"><?php echo $note; ?></div>
			</div>
		</div>
		</form>
	</div>
</div>
</body>
</html>