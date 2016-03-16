<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $subject?>-投票系统</title>
<script language="javascript" src="<?php echo ADMIN_THEME?>js/jquery.min.js"></script>
<style>
.vote {
    background: none repeat scroll 0 0 #EBEBEB;
    height: 10px;
    line-height: 10px;
    overflow: hidden;
}
.vote .vote_result {
    border: 1px solid #D28F49;
    display: block;
    height: 8px;
    line-height: 0;
    overflow: hidden;
}
.vote .vote_result em {
	background: rgb(251, 171, 89); 
	border-width: 1px 1px 0px; 
	border-style: solid solid none; 
	border-color: rgb(255, 202, 147) rgb(255, 202, 147) currentColor;
	height: 7px; 
	line-height: 7px; 
	overflow: hidden; 
	display: block;
}
</style>
</head>
<body>
<h3>模板演示</h3>
<table width="100%">
<?php
foreach ($options as $k=>$t) {
$per= isset($votedata[$k]) ? intval($votedata[$k]/$votenums*100) : 0;
?>
<tr>
	<th width="100"><?php echo $t?></th>
	<td width="200"><div class="vote"><span class="vote_result" style="width:<? echo $per?>%;"><em></em></span></div></td>
	<td>（<?php echo (int)$votedata[$k]?>）</td>
</tr>
<?php } ?>
</table>
<hr>
网站模板：<?php echo SITE_THEME?>vote_post.html
<br>
<h3>字段说明</h3>
投票主题：{$subject}<br>
投票选项：{$options}（数组格式）<br>
投票数据：{$votedata}（数组格式，与投票选项对应）<br>
投票总数：{$votenums}<br>
主题描述：{$description}<br>
主题状态：{$status}，1为生效，0为失效<br>
<h3>模板调用(以下代码为用户参考)</h3>
&lt;style&gt;<br />
.vote {<br />
&nbsp;&nbsp;&nbsp; background: none repeat scroll 0 0 #EBEBEB;<br />
&nbsp;&nbsp;&nbsp; height: 10px;<br />
&nbsp;&nbsp;&nbsp; line-height: 10px;<br />
&nbsp;&nbsp;&nbsp; overflow: hidden;<br />
}<br />
.vote .vote_result {<br />
&nbsp;&nbsp;&nbsp; border: 1px solid #D28F49;<br />
&nbsp;&nbsp;&nbsp; display: block;<br />
&nbsp;&nbsp;&nbsp; height: 8px;<br />
&nbsp;&nbsp;&nbsp; line-height: 0;<br />
&nbsp;&nbsp;&nbsp; overflow: hidden;<br />
}<br />
.vote .vote_result em {<br />
&nbsp;background: rgb(251, 171, 89); <br />
&nbsp;border-width: 1px 1px 0px; <br />
&nbsp;border-style: solid solid none; <br />
&nbsp;border-color: rgb(255, 202, 147) rgb(255, 202, 147) currentColor;<br />
&nbsp;height: 7px; <br />
&nbsp;line-height: 7px; <br />
&nbsp;overflow: hidden; <br />
&nbsp;display: block;<br />
}<br />
&lt;/style&gt;<br />
&lt;table width="100%"&gt;<br />
{loop $options $k=&gt;$t}<br />
{php $per= isset($votedata[$k]) ? intval($votedata[$k]/$votenums*100) : 0;}<br />
&lt;tr&gt;<br />
&nbsp;&lt;th width="100"&gt;{$t}&lt;/th&gt;<br />
&nbsp;&lt;td width="200"&gt;&lt;div class="vote"&gt;&lt;span class="vote_result" style="width:{$per}%;"&gt;&lt;em&gt;&lt;/em&gt;&lt;/span&gt;&lt;/div&gt;&lt;/td&gt;<br />
&nbsp;&lt;td&gt;（{intval($votedata[$k])}）&lt;/td&gt;<br />
&lt;/tr&gt;<br />
{/loop}<br />
&lt;/table&gt;
</body>
</html>