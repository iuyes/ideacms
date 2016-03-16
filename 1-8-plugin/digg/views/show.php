<style type="text/css"> 
#newdigg {
    clear: both;
    height: 51px;
    margin: 8px auto;
    overflow: hidden;
    padding-left: 8px;
    width: 406px;
}
#newdigg .digg_good {
    background: url("<?php echo $viewpath;?>images/newdigg-bg.png") no-repeat scroll left top transparent;
}
#newdigg .diggbox {
    cursor: pointer;
    float: left;
    height: 51px;
    margin-right: 8px;
    overflow: hidden;
    width: 195px;
}
#newdigg .digg_good .digg_act {
    color: #CC3300;
}
#newdigg .diggbox .digg_act {
    float: left;
    font-size: 14px;
    font-weight: bold;
    height: 29px;
    line-height: 31px;
    overflow: hidden;
    text-indent: 32px;
}
#newdigg .digg_good .digg_num {
    color: #CC6633;
}
#newdigg .diggbox .digg_num {
    float: left;
    line-height: 29px;
    text-indent: 5px;
}
#newdigg .diggbox .digg_percent {
    clear: both;
    overflow: hidden;
    padding-left: 10px;
    width: 180px;
}
#newdigg .diggbox .digg_percent .digg_percent_bar {
    background: none repeat scroll 0 0 #E8E8E8;
    border-right: 1px solid #CCCCCC;
    float: left;
    height: 7px;
    margin-top: 3px;
    overflow: hidden;
    width: 100px;
}
#newdigg .digg_good .digg_percent .digg_percent_bar span {
    background: none repeat scroll 0 0 #FFC535;
    border: 1px solid #E37F24;
	display: block;
    height: 5px;
    overflow: hidden;
}
#newdigg .diggbox .digg_percent .digg_percent_num {
    float: left;
    font-size: 10px;
    padding-left: 10px;
}
#newdigg .digg_bad {
    background: url("<?php echo $viewpath;?>images/newdigg-bg.png") no-repeat scroll right top transparent;
}
#newdigg .digg_bad .digg_percent .digg_percent_bar span {
    background: none repeat scroll 0 0 #94C0E4;
    border: 1px solid #689ACC;
	display: block;
    height: 5px;
    overflow: hidden;
}
</style>
<input type="hidden" id="idea_digg_ispost" value="0">
<div id="newdigg" class="newdigg">
    <div onclick="postDigg(1)" onmouseout="this.style.backgroundPosition='left top';" onmousemove="this.style.backgroundPosition='left bottom';" class="diggbox digg_good" style="background-position: left top;">
	    <div class="digg_act"><?php echo $ding;?></div>
		<div class="digg_num" id="digg_ding_num">(<?php echo $data['ding'];?>)</div>
		<div class="digg_percent">
			<div class="digg_percent_bar"><span id="digg_ding_style" style="width:<?php echo $data['dingper'];?>%"></span></div>
			<div class="digg_percent_num" id="digg_ding_per"><?php echo $data['dingper'];?>%</div>
		</div>
	</div>
	<div onclick="postDigg(0)" onmouseout="this.style.backgroundPosition='right top';" onmousemove="this.style.backgroundPosition='right bottom';" class="diggbox digg_bad" style="background-position: right top;">
		<div class="digg_act"><?php echo $cai;?></div>
		<div class="digg_num" id="digg_cai_num">(<?php echo $data['cai'];?>)</div>
		<div class="digg_percent">
			<div class="digg_percent_bar"><span id="digg_cai_style" style="width:<?php echo $data['caiper'];?>%"></span></div>
			<div class="digg_percent_num" id="digg_cai_per"><?php echo $data['caiper'];?>%</div>
		</div>
	</div>
</div>
		
<script type="text/javascript">
function postDigg(type) {
    var check = $('#idea_digg_ispost').val();
	if (check == 1) {
	    alert('亲，不要太快了哦~~');
	    return false;
	}
	$('#idea_digg_ispost').val('1');
	$.getJSON('<?php echo purl('index/add/', array('id'=>$data['contentid'], 'type'=>''), 1)?>'+type+'&'+Math.random(), function(data){
		if (data.status==1)	{
			$('#digg_ding_num').html('('+data.ding+')');
			$('#digg_ding_style').attr('style', 'width:'+data.dingper+'%');
			$('#digg_ding_per').html(data.dingper+'%');
			$('#digg_cai_num').html('('+data.cai+')');
			$('#digg_cai_style').attr('style', 'width:'+data.caiper+'%');
			$('#digg_cai_per').html(data.caiper+'%');
			$('#idea_digg_ispost').val('0');
		} else {
			alert(data.data);
			$('#idea_digg_ispost').val('0');
		}
	});
}

</script>                                                           