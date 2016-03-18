<style type="text/css"> 
#newdigg_<?php echo $data['contentid']?> {
    clear: both;
    height: 51px;
    margin: 8px auto;
    overflow: hidden;
    padding-left: 8px;
    width: 406px;
}
#newdigg_<?php echo $data['contentid']?> .digg_good {
    background: url("<?php echo $viewpath;?>images/newdigg-bg.png") no-repeat scroll left top transparent;
}
#newdigg_<?php echo $data['contentid']?> .diggbox {
    cursor: pointer;
    float: left;
    height: 51px;
    margin-right: 8px;
    overflow: hidden;
    width: 195px;
}
#newdigg_<?php echo $data['contentid']?> .digg_good .digg_act {
    color: #CC3300;
}
#newdigg_<?php echo $data['contentid']?> .diggbox .digg_act {
    float: left;
    font-size: 14px;
    font-weight: bold;
    height: 29px;
    line-height: 31px;
    overflow: hidden;
    text-indent: 32px;
}
#newdigg_<?php echo $data['contentid']?> .digg_good .digg_num {
    color: #CC6633;
}
#newdigg_<?php echo $data['contentid']?> .diggbox .digg_num {
    float: left;
    line-height: 29px;
    text-indent: 5px;
}
#newdigg_<?php echo $data['contentid']?> .diggbox .digg_percent {
    clear: both;
    overflow: hidden;
    padding-left: 10px;
    width: 180px;
}
#newdigg_<?php echo $data['contentid']?> .diggbox .digg_percent .digg_percent_bar {
    background: none repeat scroll 0 0 #E8E8E8;
    border-right: 1px solid #CCCCCC;
    float: left;
    height: 7px;
    margin-top: 3px;
    overflow: hidden;
    width: 100px;
}
#newdigg_<?php echo $data['contentid']?> .digg_good .digg_percent .digg_percent_bar span {
    background: none repeat scroll 0 0 #FFC535;
    border: 1px solid #E37F24;
	display: block;
    height: 5px;
    overflow: hidden;
}
#newdigg_<?php echo $data['contentid']?> .diggbox .digg_percent .digg_percent_num {
    float: left;
    font-size: 10px;
    padding-left: 10px;
}
#newdigg_<?php echo $data['contentid']?> .digg_bad {
    background: url("<?php echo $viewpath;?>images/newdigg-bg.png") no-repeat scroll right top transparent;
}
#newdigg_<?php echo $data['contentid']?> .digg_bad .digg_percent .digg_percent_bar span {
    background: none repeat scroll 0 0 #94C0E4;
    border: 1px solid #689ACC;
	display: block;
    height: 5px;
    overflow: hidden;
}
</style>
<input type="hidden" id="idea_digg_ispost_<?php echo $data['contentid']?>" value="0">
<div id="newdigg_<?php echo $data['contentid']?>" class="newdigg">
    <div onclick="postDigg_<?php echo $data['contentid']?>(1)" onmouseout="this.style.backgroundPosition='left top';" onmousemove="this.style.backgroundPosition='left bottom';" class="diggbox digg_good" style="background-position: left top;">
	    <div class="digg_act"><?php echo $ding;?></div>
		<div class="digg_num" id="digg_ding_num_<?php echo $data['contentid']?>">(<?php echo $data['ding'];?>)</div>
		<div class="digg_percent">
			<div class="digg_percent_bar"><span id="digg_ding_style_<?php echo $data['contentid']?>" style="width:<?php echo $data['dingper'];?>%"></span></div>
			<div class="digg_percent_num" id="digg_ding_per_<?php echo $data['contentid']?>"><?php echo $data['dingper'];?>%</div>
		</div>
	</div>
	<div onclick="postDigg_<?php echo $data['contentid']?>(0)" onmouseout="this.style.backgroundPosition='right top';" onmousemove="this.style.backgroundPosition='right bottom';" class="diggbox digg_bad" style="background-position: right top;">
		<div class="digg_act"><?php echo $cai;?></div>
		<div class="digg_num" id="digg_cai_num_<?php echo $data['contentid']?>">(<?php echo $data['cai'];?>)</div>
		<div class="digg_percent">
			<div class="digg_percent_bar"><span id="digg_cai_style_<?php echo $data['contentid']?>" style="width:<?php echo $data['caiper'];?>%"></span></div>
			<div class="digg_percent_num" id="digg_cai_per_<?php echo $data['contentid']?>"><?php echo $data['caiper'];?>%</div>
		</div>
	</div>
</div>
		
<script type="text/javascript">
function postDigg_<?php echo $data['contentid']?>(type) {
    var check = $('#idea_digg_ispost_<?php echo $data['contentid']?>').val();
	if (check == 1) {
	    alert('亲，不要太快了哦~~');
	    return false;
	}
	$('#idea_digg_ispost_<?php echo $data['contentid']?>').val('1');
	$.getJSON('<?php echo purl('index/add/', array('id'=>$data['contentid'], 'type'=>''), 1)?>'+type+'&'+Math.random(), function(data){
		if (data.status==1)	{
			$('#digg_ding_num_<?php echo $data['contentid']?>').html('('+data.ding+')');
			$('#digg_ding_style_<?php echo $data['contentid']?>').attr('style', 'width:'+data.dingper+'%');
			$('#digg_ding_per_<?php echo $data['contentid']?>').html(data.dingper+'%');
			$('#digg_cai_num_<?php echo $data['contentid']?>').html('('+data.cai+')');
			$('#digg_cai_style_<?php echo $data['contentid']?>').attr('style', 'width:'+data.caiper+'%');
			$('#digg_cai_per_<?php echo $data['contentid']?>').html(data.caiper+'%');
			$('#idea_digg_ispost_<?php echo $data['contentid']?>').val('0');
		} else {
			alert(data.data);
			$('#idea_digg_ispost_<?php echo $data['contentid']?>').val('0');
		}
	});
}

</script>                                                           