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
	</div>
    <div class="bk10"></div>
	<div class="table-list">
		<table width="100%" class="table_form">
		<tbody>
			<tr>
				<th width="200">绑定价格字段：</th>
				<td>首先必须在“核心配置”中去某个模型的绑定价格字段</td>
			</tr>
			<tr>
			  <th>模板介绍：</th>
			  <td>
			  1、yuedu_buy.html ：文档购买提示页面<br />
			  2、yuedu_checkout.html：购买订单确认页面<br />
			  3、yuedu_show.html：文档需购买内容显示页面<br />
			  4、member/yuedu_info.html：会员订单信息页面<br />
			  5、member/yuedu_order.html：会员订单列表页面<br />
			  6、member/yuedu_pay.html：会员支付页面<br />
			  </td>
			</tr>
			<tr>
			  <th>文档内容模板链接：</th>
			  <td><?php echo htmlspecialchars('<script type="text/javascript" src="{url(\'yuedu/index/show\', array(\'id\'=>$id, \'modelid\'=>$modelid, \'title\'=>$title))}"></script>')?>
			  <br />将上面js代码放到需要显示购买信息的地方，比如"文章内容"处、“下载链接”处、“视频播放”等
			  </td>
			</tr>
		</tbody>
		</table>
	</form>
	</div>
</div>
</body>
</html>