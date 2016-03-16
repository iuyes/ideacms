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
		<form method="post" action="" id="myform" name="myform">
		<div class="pad-10">
			<div class="col-tab">
				<ul class="tabBut cu-li">
				<li onClick="SwapTab('setting','on','',4,1);" class="on" id="tab_setting_1">支付宝配置</li>
				<li onClick="SwapTab('setting','on','',4,2);" id="tab_setting_2" class="">财付通配置</li>
				</ul>
				<div class="contentList pad-10" id="div_setting_1" style="display: block;">
				<table width="100%" class="table_form">
				<tr>
					<th width="200">支付接口开关： </th>
					<td><input name="data[alipay][use]" type="radio" value="1" <?php if ($data['alipay']['use']==1) echo "checked";?>> 开启 
					&nbsp;&nbsp;&nbsp;<input name="data[alipay][use]" type="radio" value="0" <?php if ($data['alipay']['use']==0) echo "checked";?>> 关闭
					&nbsp;&nbsp;&nbsp;
					<a target="_blank" href="https://b.alipay.com/order/productDetail.htm?productId=2012051600355662">接口申请地址</a>
					</td>
				</tr>
				<tr>
					<th>接口名称： </th>
					<td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data['alipay']['name'] ? $data['alipay']['name'] : '支付宝'?>" name="data[alipay][name]"></td>
				</tr>
				<tr>
					<th>支付宝帐户： </th>
					<td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data['alipay']['username']?>" name="data[alipay][username]"></td>
				</tr>
				<tr>
					<th>合作者身份(parterID)： </th>
					<td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data['alipay']['partner']?>" name="data[alipay][partner]"></td>
				</tr>
				<tr>
					<th>交易安全校验码(key)： </th>
					<td><input type="text" class="input-text" style="width:300px;" value="<?php echo $data['alipay']['key']?>" name="data[alipay][key]"></td>
				</tr>
				<tr>
					<th>选择接口类型： </th>
					<td>
						即时到账交易接口
				    </td>
				</tr>
				</table>
				</div>

				<div class="contentList pad-10 hidden" id="div_setting_2" style="display: none;">
				<table width="100%" class="table_form ">
				<tr>
					<th width="200">支付接口开关： </th>
					<td><input name="data[tenpay][use]" type="radio" value="1" <?php if ($data['tenpay']['use']==1) echo "checked";?>> 开启 
					&nbsp;&nbsp;&nbsp;<input name="data[tenpay][use]" type="radio" value="0" <?php if ($data['tenpay']['use']==0) echo "checked";?>> 关闭
					&nbsp;&nbsp;&nbsp;
					<a target="_blank" href="http://union.tenpay.com/mch/mch_register.shtml">接口申请地址</a>
					</td>
				</tr>
				<tr>
					<th>接口名称： </th>
					<td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data['tenpay']['name'] ? $data['tenpay']['name'] : '财付通'?>" name="data[tenpay][name]"></td>
				</tr>
				<tr>
					<th>商户号： </th>
					<td><input type="text" class="input-text" style="width:200px;" value="<?php echo $data['tenpay']['partner']?>" name="data[tenpay][partner]"></td>
				</tr>
				<tr>
					<th>密钥(key)： </th>
					<td><input type="text" class="input-text" style="width:300px;" value="<?php echo $data['tenpay']['key']?>" name="data[tenpay][key]"></td>
				</tr>
				<tr>
					<th>选择接口类型： </th>
					<td>
						使用即时到账收款接口
				    </td>
				</tr>
				</tbody>
				</table>
				</div>
				
			    <div class="bk15"></div>
			    <input type="submit" class="button" value="提交" name="submit">
			</div>
		</div>
		</form>
	</div>
</div>
</body>
</html>
<script language="javascript">
function SwapTab(name,cls_show,cls_hide,cnt,cur){
	for(i=1;i<=cnt;i++){
		if(i==cur){
			$('#div_'+name+'_'+i).show();
			$('#tab_'+name+'_'+i).attr('class',cls_show);
		}else{
			$('#div_'+name+'_'+i).hide();
			$('#tab_'+name+'_'+i).attr('class',cls_hide);
		}
	}
}
</script>