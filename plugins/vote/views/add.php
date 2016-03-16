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
</head>
<body>
<div class="subnav">
	<div class="content-menu ib-a blue line-x">
	<?php 
	foreach ($menu as $i=>$t) {
	    $class = $_GET['a'] == $t[0] || $_GET['a'] == 'edit' && $t[0] == 'add'? ' class="on"' : '';
		$span  = $i >= count($menu)-1  ? '' : '<span>|</span>';
	    echo '<a href="' . purl('admin/' . $t[0]) . '" ' . $class . '><em>' . $t[1] . '</em></a>' . $span;
	}
	?>
	</div>
    <div class="bk10"></div>
	<div class="table-list">
	<form action="" method="post">
		<table width="100%" class="table_form">
		<tbody>
			<tr>
				<th width="200">投票主题：</th>
				<td><input type="text" name="data[subject]" size=50 class="input-text" value="<?php echo $data['subject']?>"></td>
			</tr>
			<tr>
			  <th>是否多选：</th>
			  <td><input name="data[ischeckbox]" type="radio" value="0" <?php if ($data['ischeckbox']==0) echo "checked";?>> 单选 
					&nbsp;&nbsp;&nbsp;
					<input name="data[ischeckbox]" type="radio" value="1" <?php if ($data['ischeckbox']==1) echo "checked";?>> 多选
			  </td>
		    </tr>
			<tr>
				<th>投票选项：</th>
				<td>
				<table width="100%" class="table_form">
				<?php 
				if (isset($data['options']) && is_array($data['options'])) {
				foreach($data['options'] as $k=>$t) { ?>
				<tr id="o_<?php echo $k?>">
					<th width="200"><input type="text" name="data[options][<?php echo $k?>]" size=30 class="input-text" value="<?php echo $t?>"> </th>
					<td><?php if ($k == 0) { ?><input type="button" class="button" value=" 添加选项 " onclick="add_option()" name="submit"><?php }else{?>&nbsp;<a href="javascript:removediv(<?php echo $k?>);">移除</a><?php } ?></td>
				</tr>
				<?php }
				}else{
				?>
				<tr>
					<th width="200"><input type="text" name="data[options][]" size=30 class="input-text" value=""> </th>
					<td><input type="button" class="button" value=" 添加选项 " onclick="add_option()" name="submit"></td>
				</tr>
				<?php } ?>
				<tbody id='vote_options'>
				</tbody>
				</table>
				</td>
			</tr>
			<tr>
			  <th>投票状态：</th>
			  <td><input name="data[status]" type="radio" value="1" <?php if ($data['status']==1) echo "checked";?>> 生效 
					&nbsp;&nbsp;&nbsp;
					<input name="data[status]" type="radio" value="0" <?php if ($data['status']==0) echo "checked";?>> 失效
			  </td>
		    </tr>
            <tr>
				<th>投票描述：</th>
				<td><textarea style="width:400px;height:100px;" name="data[description]"><?php echo $data['description']?></textarea></td>
			</tr>
			<tr>
				<th></th>
				<td><input type="submit" class="button" value="提 交" name="submit"></td>
			  </tr>
			<tr>
		</tbody>
		</table>
	</form>
	</div>
</div>
</body>
</html>
<script language="javascript">
function add_option() {
    var id= parseInt(Math.random()*1000);
    var html = '<tr id=o_'+id+'><th width="200"><input type="text" name="data[options][]" size=30 class="input-text" value=""> </th><td>&nbsp;<a href="javascript:removediv(\''+id+'\');">移除</a></td></tr>';
	$('#vote_options').append(html);
}
function removediv(k) {
	$('#o_'+k).remove();
}
</script>