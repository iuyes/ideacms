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
	foreach ($menu as $i=>$t) {
	    $class = $_GET['a'] == $t[0] ? ' class="on"' : '';
		$span  = $i >= count($menu)  ? '' : '<span>|</span>';
	    echo '<a href="' . purl('admin/' . $t[0]) . '" ' . $class . '><em>' . $t[1] . '</em></a>' . $span;
	}
	?>
    <a class="on" href="javascript:;"><em>订单操作</em></a>
	</div>
    <div class="bk10"></div>
	<div class="table-list">
	<form action="" method="post">
		<table width="100%" class="table_form">
		<tbody>
			<tr>
				<th width="200">订单编号：</th>
				<td><?php echo $data['order_sn']?></td>
			</tr>
			<tr>
				<th>商品信息：</th>
				<td>
                <table width="300" border="0">
                  <tr>
                    <td width="162">商品</td>
                    <td width="48">数量</td>
                    <td width="76">单价</td>
                  </tr>
                <?php 
                $items = string2array($data['items']);
                foreach($items as $t) {
                ?>
                  <tr>
                    <td><a href="<?php echo url('content/show', array('id'=>$t['id']))?>" target="_blank"><?php echo $t['title']?></a></td>
                    <td><?php echo $t['num']?></td>
                    <td><?php echo $t['item_price']?></td>
                  </tr>
                <?php
                }
                ?>
                </table>
				</td>
			</tr>
			<tr>
				<th>收货信息：</th>
				<td>
                <table width="400" border="0">
                  <tr>
                    <td width="102">姓名：<?php echo $data['name']?></td>
                    <td width="171">电话：<?php echo $data['tel']?></td>
                    <td width="113">邮政编码：<?php echo $data['zip']?></td>
                  </tr>
                  <tr>
                    <td colspan="3">地址：<?php echo $data['address']?></td>
                    </tr>
                </table>
				</td>
			</tr>
			<tr>
			  <th>配送方式：</th>
			  <td><?php echo $data['shipping_name']?></td>
		  </tr>
			<tr>
				<th>商品金额：</th>
				<td><input type="text" name="price" value="<?php echo $data['price']?>" size="15" class="input-text" <?php if ($data['status']) echo 'disabled="disabled"';?>></td>
			</tr>
			<tr>
			<th>订单状态：</th> 
			<td>
            <?php
            if ($data['status'] == 0) {
				if ($pay) { echo '未付款'; } else { echo '无需付款,请联系买家确认';}
			} elseif ($data['status'] == 1) {
				echo '等待配货';
			} elseif ($data['status'] == 2) {
				echo '正在配货';
			} elseif ($data['status'] == 3) {
				echo '已经发货，等待卖家确认';
			} elseif ($data['status'] == 4) {
				echo '交易关闭';
			} elseif ($data['status'] == 9) {
				echo '交易成功';
			}
			?>
            </td>
			</tr>
            <?php if ($data['status'] !=4) {?>
            <tr>
				<th>订单操作：</th>
				<td>
                <?php
                if ($data['status'] ==0 && !$pay) {
					?>
                    <input name="action" type="radio" value="3" onclick="$('#note').hide();" />&nbsp;确认订单
                    &nbsp;
                    <?php
				}
				?>
                <input name="action" type="radio" value="2" onclick="$('#note').hide();" />&nbsp;
                <?php
				if ($data['status'] == 0) {
					echo '改价';
				} elseif ($data['status'] == 1) {
					echo '配货';
				} elseif ($data['status'] == 2) {
					echo '发货';
				} elseif ($data['status'] == 3) {
					echo '确认收货';
				}
				?>
                <?php if ($data['status'] <3) { ?>
                &nbsp;
                <input name="action" type="radio" value="1" onclick="close_order()" />&nbsp;关闭交易
                <input type="text" name="note" id="note" value="<?php echo $data['note']?>" size="35" class="input-text">
                <? } ?>
                </td>
			</tr>
            <?php } if ($data['status']==2) {?>
            <tr>
				<th>发货编号：</th>
				<td><input type="text" name="shipping_id" value="<?php echo $data['shipping_id']?>" size="30" class="input-text"></td>
			</tr>
            <?php } ?>
            <tr>
				<th>操作备注：</th>
				<td><textarea style="width:255px;height:50px;" name="admintext"></textarea></td>
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
function close_order() {
	$('#note').show();
}
$('#note').hide();
</script>