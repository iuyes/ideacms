<?php 
    $l = $count;
	foreach ($listdata as $t) { ?>
    <div style="border-top: 1px solid #D7D7D7; margin-top: 10px; padding-top: 10px;">
        <div style="float: left; border-radius: 6px 6px 6px 6px; width: 50px; height: 50px; overflow: hidden; margin-right: 10px;">
             <img src="<?php echo get_member_avatar($t['userid'], 45);?>" style="width: 50px; height: 50px;">
        </div>
        <div style="padding-left: 60px;">
            <div style="color: #303030; font-size: 13px; line-height: 18px; height: 26px;">
                <span style="color: #3B5998;float: left; text-decoration: none; padding-right: 6px;">
                    <?php echo $t['username'] ? $t['username'] : '游客'?>
                </span>
                <span style="color: #aaaaaa; float: left;" class="idea_comment_cmt_ufromname">
                    (<?php echo $t['ip']?> [<a href="javascript:;" style="color: #aaaaaa;" onClick="get_ip_address('<?php echo $t['ip']?>')">查询归属地</a>])
                </span>
                <div style="float: right;">【第<?php echo $l?>楼】<div style="clear: both;"></div></div>
            </div>
            <div style="color: #303030; font-size: 14px; line-height: 18px; word-wrap: break-word; text-align: left;">
                <?php echo $t['content']?>
            </div>
            <div style="padding: 12px 5px 0 0; line-height: 14px;" class="idea_comment_cmt_exp">
                <a onClick="show_comment_reply(<?php echo $t['id']?>)" style="color: #303030; cursor: pointer; display: block; float: right; font-size: 12px; padding: 0 4px 0 0;" id="comment_reply_<?php echo $t['id']?>_name" name="1">
                    回复
                </a>
                <a style="border-right: 1px solid #E2D4E2; color: #303030; cursor: pointer;float: right; font-size: 12px; margin-right: 10px; padding: 0 14px 0 0;" onClick="comment_cd(<?php echo $t['id']?>, 0)">
                    踩
                    <span style="padding-left: 3px;" id="idea_comment_cai_<?php echo $t['id']?>">
                       <?php echo $t['opposition']?>
                    </span>
                </a>
                <a style="background: url('<?php echo $viewpath?>images/downDig2.png') no-repeat scroll 0 0 transparent; cursor: pointer;float: right; height: 13px; margin-right: 4px; margin-top: 3px; width: 13px;">
                </a>
                <a style="border-right: 1px solid #E2D4E2; color: #303030; cursor: pointer; display: block; float: right; font-size: 12px; margin-right: 10px; padding: 0 14px 0 0;" onClick="comment_cd(<?php echo $t['id']?>, 1)">
                    顶
                    <span style="padding-left: 3px;" id="idea_comment_ding_<?php echo $t['id']?>">
                        <?php echo $t['support']?>
                    </span>
                </a>
                <a style="background: url('<?php echo $viewpath?>images/upDig2.png') no-repeat scroll 0 0 transparent; cursor: pointer; display: block; float: right; height: 13px; margin-right: 4px; margin-top: 3px; width: 13px;">
                </a>
                <?php 
				if ($isadmin || (isset($memberinfo['id']) && ($memberinfo['id'] == $t['userid'] && $memberinfo['username'] == $t['username']))) {
				?>
                <a onClick="comment_del(<?php echo $t['id']?>)" style="color: #303030; cursor: pointer; display: block; float: right; font-size: 12px; padding: 0 14px 0 0;">
                    删除
                </a>
                <?php } ?>
                <div style="color: #303030; float: left; font-size: 12px; padding: 0 10px 0 0;">
                    <?php echo fnDate($t['addtime'])?>
                </div>
                <div style="clear: both;">
                </div>
            </div>
        </div>
        <div style="clear: both;">
        </div>
        <!--reply s-->
        <div style="padding-left: 60px; padding-top: 10px; display:none" id="idea_comment_reply_<?php echo $t['id']?>">
        <div style="height: 78px; overflow: hidden; padding: 0 0 20px 0; _padding: 0;">
            <div style="float: left; border-radius: 6px 6px 6px 6px; width: 50px; height: 50px; overflow: hidden; margin-right: 10px;">
                <img src="<?php echo get_member_avatar($memberid, 45);?>" style="width: 50px; height: 50px;">
            </div>
            <div style="padding: 0 0 0 60px; overflow: visible;">
                <div style="height: 60px; background: url('<?php echo $viewpath?>images/textareabg.png') repeat-x scroll 0 0 #FFFFFF; border: 1px solid #d7d7d7; border-radius: 4px 4px 4px 4px; padding: 7px; position: relative; overflow: hidden; cursor: text;">
                    <textarea style="background: none repeat scroll 0px 0px rgb(255, 255, 255); padding: 0px; box-shadow: 0px 0px; color: rgb(189, 187, 187); border: 0px none; z-index: 90; overflow: hidden; float: left; font-size: 13px; font-family: 'Microsoft YaHei'; resize: none; height: 60px; display: block; outline: medium none; word-wrap: break-word; margin: 0px; width:100%;" id="comment_reply_<?php echo $t['id']?>_content"></textarea>
                </div>
                <div style="clear: both;">
                </div>
            </div>
        </div>
        <div style="position: relative; height: 40px; overflow: hidden; margin-top: -20px; _margin-top: -15px;">
            <a style="background: url(&quot;<?php echo $viewpath?>images/buttonLarge.png&quot;) repeat-x scroll 0px 0px transparent; border: 1px solid rgb(208, 207, 207); border-radius: 5px 5px 5px 5px; color: rgb(102, 102, 102); cursor: pointer; float: right; font-size: 13px; font-weight: bold; height: 24px; line-height: 21px; margin: 4px 0px 0px; padding: 3px 25px 1px; text-align: center; text-shadow: 0px 1px 0px rgb(255, 255, 255);" onclick="comment_reply(<?php echo $t['id']?>, 0)">
                回&nbsp;&nbsp;复
            </a>
            <?php if ($code) { ?>
            <span style="color: #424242; padding: 8px 15px 0 2px; text-decoration: none; float: right; font-size: 12px;">
               <img id="reply_<?php echo $t['id']?>_code" src="<?php echo url("api/captcha/", array("width"=>70, "height"=>21))?>" align="absmiddle" title="看不清楚？换一张" onclick="document.getElementById('reply_<?php echo $t['id']?>_code').src='<?php echo url("api/captcha/", array("width"=>70, "height"=>21))?>&'+Math.random();" style="cursor:pointer;border: 1px solid #DDDDDD;">
            </span>
            <span style="color: #424242; padding: 11px 10px 0 1px; text-decoration: none; float: right; font-size: 12px;">
            验证码：<input type="text" id="comment_reply_<?php echo $t['id']?>_code" onkeydown="KeyDown(event,<?php echo $t['id']?>);" style="width:40px; border:none; border-bottom:#999 1px solid;" />
            </span>
            <?php } ?>
            <div style="float: right; padding: 14px 15px 0 3px;">
                <div style="color: #424242; float: right; font-size: 12px; padding-left: 5px;" id="comment_reply_<?php echo $t['id']?>_result">
                </div>
            </div>
            <div style="clear: both;">
            </div>
        </div>
        </div>

        <!--reply e-->
    </div>
    <div id="idea_comment_reply_<?php echo $t['id']?>_result">
    <?php foreach ($t['reply'] as $r) { ?>
    <div style="border-top: 1px solid #D7D7D7; margin-top: 10px; padding-top: 10px; margin-left: 60px;">
        <div style="float: left; border-radius: 6px 6px 6px 6px; width: 38px; height: 38px; overflow: hidden; margin-right: 10px;">
            <img src="<?php echo get_member_avatar($r['userid'], 45);?>" style="width: 38px; height: 38px;">
        </div>
        <div style="padding-left: 48px;">
            <div style="color: #303030; font-size: 13px; line-height: 18px; height: 26px;">
                <span style="color: #3B5998;float: left; text-decoration: none; padding-right: 6px;"">
                    <?php echo $r['username'] ? $r['username'] : '游客'?>
                </span>
                <span style="color: #aaaaaa; float: left;" class="idea_comment_cmt_ufromname">
                    (<?php echo $r['ip']?> [<a href="javascript:;" style="color: #aaaaaa;" onClick="get_ip_address('<?php echo $r['ip']?>')">查询归属地</a>])
                </span>
            </div>
            <div style="color: #303030; font-size: 14px; line-height: 18px; word-wrap: break-word; text-align: left;">
                 <?php echo $r['content']?>
            </div>
            <div style="padding: 12px 5px 0 0; line-height: 14px;" class="idea_comment_cmt_exp">
                <?php 
				if ($isadmin || (isset($memberinfo['id']) && ($memberinfo['id'] == $r['userid'] && $memberinfo['username'] == $r['username']))) {
				?>
                <a onClick="comment_del(<?php echo $r['id']?>)" style="color: #303030; cursor: pointer; display: block; float: right; font-size: 12px; padding: 0 14px 0 0;">
                    删除
                </a>
                <?php } ?>
                <div style="color: #303030; float: left; font-size: 12px; padding: 0 10px 0 0;">
                     <?php echo fnDate($r['addtime'])?>
                </div>
                <div style="clear: both;">
                </div>
            </div>
        </div>
        <div style="clear: both;">
        </div>
    </div>
    <?php } ?>
    </div>
<?php 
	$l --;
	} 
?>
<?php if ($ismore) { ?>
<div style="cursor: pointer; background: url(&quot;<?php echo $viewpath?>images/morebg.png&quot;) repeat-x scroll 0% 0% transparent; height: 28px; line-height: 28px; display: block; border: 1px solid rgb(220, 220, 220); margin-top: 15px; text-align: center; border-radius: 3px 3px 3px 3px; font-size: 13px; color: rgb(66, 66, 66); text-decoration: none;" onmouseout="change_more(1)" onmouseover="change_more(0)" onclick="load_more_comment()" id="idea_commnet_more" limit="<?php echo $limit;?>">查看更多评论(<?php echo $countmore;?>)<img style="padding-left: 4px; border: none; display: inline;" src="<?php echo $viewpath?>images/arrow.png"></div>
<?php } ?>
