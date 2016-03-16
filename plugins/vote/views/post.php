<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $subject?>-投票系统</title>
</head>
<body>
<form action="" method="post">
<h2>模板演示</h2>
<?php
foreach ($options as $k=>$t) {
?>
<li>
<?php if ($ischeckbox) {?><input name="vote_id[]" value="<?php echo $k?>" type="checkbox"><?php } else {?> <input name="vote_id" type="radio" value="<?php echo $k?>"> <?php } ?>
<?php echo $t?>：<?php echo (int)$votedata[$k]?>
</li>
<?php } ?>
<br><br>
<input type="submit" value="提 交" name="submit">
</form>
<hr>
网站模板：<?php echo SITE_THEME?>vote_post.html
<br>
<h3>字段说明</h3>
投票主题：{$subject}<br>
投票选项：{$options}（数组格式）<br>
投票总数：{$votenums}<br>
主题描述：{$description}<br>
主题状态：{$status}，1为生效，0为失效<br>
支持多选：{$ischeckbox}
<h3>投票表单(以下代码为用户参考)</h3>
&lt;form action="" method="post"&gt;<br />
{loop $options $k=&gt;$t}<br />
&lt;li&gt;<br />
{if $ischeckbox}<br />
&lt;input name="vote_id[]" value="{$k}" type="checkbox"&gt;<br />
{else}<br />
&lt;input name="vote_id" type="radio" value="{$k}"&gt;<br />
{/if}<br />
{$t}<br />
&lt;/li&gt;<br />
{/loop}<br />
&lt;br&gt;&lt;br&gt;<br />
&lt;input type="submit" value="提交投票" name="submit"&gt;<br />
&lt;/form&gt;
</body>
</html>