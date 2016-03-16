<style type="text/css">
div, a { color: #777777;}
</style>
<div style="font-size:12px;padding-top:15px;">
<div><?php echo $msg?></div>
<div style="padding-top:5px;">
<?php
if ($url) {
?>
<a href="<?php echo $url?>">如果您的浏览器没有自动跳转，请点击这里</a>
<meta http-equiv="refresh" content="<?php echo $time?>; url=<?php echo $url?>">
<?php } ?>
</div>
</div>