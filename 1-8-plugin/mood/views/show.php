<style type="text/css"> 
#mood{text-align: center;}
#mood ul,#mood ul li{marign:0;padding:0}
#mood ul li{
    display:inline-block;
	display:-moz-inline-stack;
	zoom:1;
	*display:inline;
    vertical-align: bottom;
    width:80px; 
    padding-bottom:10px;
	text-align: center;
}
#mood ul li span{ font-size:12px}
#mood .mood_result {
    background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #DADADA;
    height: 70px;
    left: 20px;
    margin-bottom: 5px;
	margin-left: 13px;
    padding: 0;
    position: relative;
    width: 10px;
}
#mood .mood_result .bg {
    background: url("<?php echo $per;?>") repeat scroll 0 0 transparent;
    bottom: 0;
    font-size: 0;
    height: 0;
    left: 0;
    line-height: 0;
    margin: 0;
    padding: 1px;
    position: absolute;
    width: 8px;
}
</style>
<!--[if IE 7]>
<style type="text/css">
#mood .mood_result {
    left: 0px;
	margin-left: 0px;
}
</style>
<![endif]-->
<!--[if IE 6]>
<style type="text/css">
#mood .mood_result {
    left: 0px;
	margin-left: 0px;
}
</style>
<![endif]-->
<div id="mood">
<ul>
<?php foreach ($mood as $name=>$v) {
if ($v['use']==1) {
?>
<li>
<span id="mood_<?php echo $name;?>_value"><?php echo (int)$data[$name];?></span>
<div class="mood_result"><div style="height: <?php echo $v['per'];?>px;" class="bg"></div></div>
<a href="javascript:;" onclick="vote('<?php echo $name;?>')"><?php if ($v['pic']) echo '<img border=0 src="' . $v['pic'] . '">';?><br />
<?php echo $v['name'];?></a>
</li>
<?php } }?>
</ul>
</div>
<script type="text/javascript">
function vote(name) {
	$.getJSON('<?php echo purl('index/vote/', array('cid'=>$cid, 'id'=>$data['id'], 'name'=>''), 1)?>'+name+'&'+Math.random(), function(data){
		if(data.status==1)	{
			$('#mood_'+name+'_value').html('<b>'+data.value+'</b>');
		}else {
			alert(data.data);
		}
	})
}

</script>                                                           