<script type="text/javascript">
function comment_post() {
	var content = $('#comment_content').val();
	var code    = $('#comment_code').val();
	$('#comment_result').html('评论发布中...');
	$('#idea_comment_ispost').val('1');
	if (content != '') {
		$.getJSON('<?php echo purl('index/add/');?>&'+Math.random(),{contentid:<?php echo $contentid?>,content:content,code:code},
		function(data) {
			if (data.status == 1) {
				$('#comment_result').html('<b><font color=green>'+data.data+'</font></b>');
				if (data.verify == 1) {
					$.get('<?php echo purl('index/clist/');?>&'+Math.random(), {id:data.id, num:0},function(data){
	                    $('#idea_comment_list').html(data);
	                });
				}
				setTimeout("$('#comment_result').html('')", 5000);
			} else {
				$('#comment_result').html('<b><font color=red>'+data.data+'</font></b>');
			}
			$('#idea_comment_ispost').val('0');
		});
	} else {
		$('#comment_result').html('<b><font color=red>请填写评论内容！</font></b>');
		$("#comment_content").focus();
		$('#idea_comment_ispost').val('0');
	}
}
function load_comment(num) {
	$('#idea_comment_list').html('<div align=center style="padding-top:10px;font-size:12px;">评论加载中....</div>');
	$.get('<?php echo purl('index/clist/');?>&'+Math.random(), {id:<?php echo $commentid?>, num:num},function(data){
	    if (data) {
			$('#idea_comment_list').html(data);
		} else {
			$('#idea_comment_list').html('');
		}
	});
}
function KeyDown(event, id) {
	if (event.keyCode == 13) {
		if (id == '') {
		    if ($('#idea_comment_ispost').val() == 1) {
			    alert('亲，动作不要太快了哦~');
			} else {
			    comment_post();
			}
		} else {
		    if ($('#idea_comment_isreply').val() == 1) {
			    alert('亲，动作不要太快了哦~');
			} else {
			    comment_reply(id);
			}
		}
	}
}
function change_more(type) {
	if (type == 1) {
		$('#idea_commnet_more').attr('style', 'cursor: pointer; background:none repeat scroll 0 0 #e4e6e4;height: 28px; line-height: 28px; display: block; border: 1px solid rgb(220, 220, 220); margin-top: 15px; text-align: center; border-radius: 3px 3px 3px 3px; font-size: 13px; color: rgb(66, 66, 66); text-decoration: none;');
	} else {
		$('#idea_commnet_more').attr('style', 'cursor: pointer; background: url(&quot;<?php echo $viewpath?>images/morebg.png&quot;) repeat-x scroll 0% 0% transparent; height: 28px; line-height: 28px; display: block; border: 1px solid rgb(220, 220, 220); margin-top: 15px; text-align: center; border-radius: 3px 3px 3px 3px; font-size: 13px; color: rgb(66, 66, 66); text-decoration: none;');
	}
}
function load_more_comment() {
	var num = $('#idea_commnet_more').attr('limit');
	num = parseInt(num) + 3;
	load_comment(num);
}
function show_comment_reply(id) {
	if (id == '') {
		alert('参数不正确！');
		return false;
	}
	var name = $('#comment_reply_'+id+'_name').attr('name');
	if (name == 1) {
		$('#comment_reply_'+id+'_code').val('');
	    $('#comment_reply_'+id+'_content').val('');
	    $('#comment_reply_'+id+'_result').html('');
		$('#comment_reply_'+id+'_name').attr('name', 0);
		$('#comment_reply_'+id+'_name').html('关闭');
		$('#idea_comment_reply_'+id).show();
		$('#comment_reply_'+id+'_content').focus();
	} else {
		$('#comment_reply_'+id+'_name').attr('name', 1);
		$('#comment_reply_'+id+'_name').html('回复');
		$('#idea_comment_reply_'+id).hide();
	}
}
function comment_reply(id) {
	if (id == '') {
		alert('参数不正确！');
		return false;
	}
	var code    = $('#comment_reply_'+id+'_code').val();
	var content = $('#comment_reply_'+id+'_content').val();
	var result  = $('#comment_reply_'+id+'_result');
	$('#idea_comment_isreply').val('1');
	result.html('评论回复中...');
	if (content != '') {
		$.getJSON('<?php echo purl('index/reply/');?>&'+Math.random(),{commentid:id,content:content,code:code},
		function(data) {
			if (data.status == 1) {
				result.html('<b><font color=green>'+data.data+'</font></b>');
				if (data.verify == 1) {
					$('#idea_comment_reply_'+id+'_result').html(data.result+$('#idea_comment_reply_'+id+'_result').html());
					show_comment_reply(id);
				}
			} else {
				result.html('<b><font color=red>'+data.data+'</font></b>');
			}
			$('#idea_comment_isreply').val('0');
		});
	} else {
		result.html('<b><font color=red>请填写回复内容！</font></b>');
		$('#comment_reply_'+id+'_content').focus();
		$('#idea_comment_isreply').val('0');
	}
}
function comment_cd(id, type) {
	$.getJSON('<?php echo purl('index/cd/');?>&'+Math.random(),{commentid:id,type:type},
		function(data) {
			if (data.status) {
				if (type == 1) {
					$('#idea_comment_ding_'+id).html(data.result);
				} else {
					$('#idea_comment_cai_'+id).html(data.result);
				}
			} else {
				alert(data.result);
			}
		}
	);
}
function comment_del(id) {
	if (confirm('确认删除该评论吗？')) {
	    $.getJSON('<?php echo purl('index/del/');?>&'+Math.random(),{commentid:id},
			function(data) {
				if (data.status) {
					alert('删除成功！');
					load_comment(<?php echo $nums;?>);
				} else {
					alert(data.result);
				}
			}
		);
	}
}
function get_ip_address(ip) {
	$.get('<?php echo purl('index/ipaddress/');?>&ip='+ip+'&'+Math.random(),
		function(data) {
			alert(data);
		}
	);
}
$(function() {
    load_comment(<?php echo $nums;?>);
});
</script>
<div id="idea_comment_frame">
<input type="hidden" id="idea_comment_ispost" value="">
<input type="hidden" id="idea_comment_isreply" value="">
    <div style="width: 100%; font-family: 'Microsoft YaHei'; position: relative; float: left;">
        <div style="height: 30px; font-size: 12px; font-weight: bold;" id="idea_comment_top">
            <div style="font-size: 12px; color: #888888; float: left; font-weight: normal; padding: 5px 5px 0 0;">
                共有 <b><?php echo $total;?></b> 条评论
            </div>
            <?php if ($memberinfo) { ?>
            <a style="background-color: #999; border-radius: 3px 3px 3px 3px; color: #EEEEEE; cursor: pointer; float: right; font-size: 13px; font-weight: bold; height: 20px; margin-left: 5px; margin-top: 2px; height: 16px; line-height: 17px; padding: 3px 15px; text-shadow: 0 1px 0 #646464;">
                <?php echo empty($memberinfo['nickname']) ? $memberinfo['username'] : $memberinfo['nickname'];?>
            </a>
            <?php } else {?>
            <a style="background-color: #999; border-radius: 3px 3px 3px 3px; color: #EEEEEE; cursor: pointer; float: right; font-size: 13px; font-weight: bold; height: 20px; margin-left: 5px; margin-top: 2px; height: 16px; line-height: 17px; padding: 3px 15px; text-shadow: 0 1px 0 #646464;" href="<?php echo url('member/login', array('back'=>urlencode(url('content/show', array('id'=>$contentid)) . '#comment')));?>">
                登录
            </a>
            <?php } ?>
            <div style="clear: both;">
            </div>
        </div>
        <div style="height: 78px; overflow: hidden; padding: 0 0 20px 0; _padding: 0;">
            <div style="float: left; border-radius: 6px 6px 6px 6px; width: 50px; height: 50px; overflow: hidden; margin-right: 10px;">
                <img src="<?php echo get_member_avatar($memberid, 45);?>" style="width: 50px; height: 50px;">
            </div>
            <div style="padding: 0 0 0 60px; overflow: visible;">
                <div style="height: 60px; background: url('<?php echo $viewpath?>images/textareabg.png') repeat-x scroll 0 0 #FFFFFF; border: 1px solid #d7d7d7; border-radius: 4px 4px 4px 4px; padding: 7px; position: relative; overflow: hidden; cursor: text;">
                    <textarea style="background: none repeat scroll 0px 0px rgb(255, 255, 255); padding: 0px; box-shadow: 0px 0px; color: rgb(189, 187, 187); border: 0px none; z-index: 90; overflow: hidden; float: left; font-size: 13px; font-family: 'Microsoft YaHei'; resize: none; height: 60px; display: block; outline: medium none; word-wrap: break-word; margin: 0px; width:100%;" id="comment_content"></textarea>
                </div>
                <div style="clear: both;">
                </div>
            </div>
        </div>
       
        <div style="position: relative; height: 40px; overflow: hidden; margin-top: -20px; _margin-top: -15px;">
            <a style="background: url(&quot;<?php echo $viewpath?>images/buttonLarge.png&quot;) repeat-x scroll 0px 0px transparent; border: 1px solid rgb(208, 207, 207); border-radius: 5px 5px 5px 5px; color: rgb(102, 102, 102); cursor: pointer; float: right; font-size: 13px; font-weight: bold; height: 24px; line-height: 21px; margin: 4px 0px 0px; padding: 3px 25px 1px; text-align: center; text-shadow: 0px 1px 0px rgb(255, 255, 255);" onclick="comment_post()">
                发&nbsp;&nbsp;布
            </a>
            <?php if ($code) { ?>
            <span style="color: #424242; padding: 8px 15px 0 2px; text-decoration: none; float: right; font-size: 12px;">
               <img id="code" src="<?php echo url("api/captcha/", array("width"=>70, "height"=>21))?>" align="absmiddle" title="看不清楚？换一张" onclick="document.getElementById('code').src='<?php echo url("api/captcha/", array("width"=>70, "height"=>21))?>&'+Math.random();" style="cursor:pointer;border: 1px solid #DDDDDD;">
            </span>
            <span style="color: #424242; padding: 11px 10px 0 1px; text-decoration: none; float: right; font-size: 12px;">
            验证码：<input type="text" id="comment_code" onkeydown="KeyDown(event, 0, 0);" style="width:40px; border:none; border-bottom:#999 1px solid;" />
            </span>
            <?php } ?>
            <div style="float: right; padding: 14px 15px 0 3px;">
                <div style="color: #424242; float: right; font-size: 12px; padding-left: 5px;" id="comment_result">
                  
                </div>
            </div>
            <div style="clear: both;">
            </div>
        </div>
        <div id="idea_comment_list">
        
        </div>
        <div style="clear: both;">
        </div>
    </div>
</div>
