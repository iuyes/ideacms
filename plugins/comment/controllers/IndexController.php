<?php

class IndexController extends Plugin {

	private $setting;

    public function __construct() {
        parent::__construct();
		$this->setting = string2array($this->data['setting']);
    }

	/*
	 * 显示评论
	 */
	public function listAction() {
	    $contentid = (int)$this->get('id');
		$data      = $this->comment->getOne('contentid=' . $contentid, 'total,id');
		$commentid = (int)$data['id'];
		$this->assign(array(
		    'contentid' => $contentid,
			'total'     => (int)$data['total'],
			'code'      => $this->setting['code'],
			'commentid' => $commentid,
			'nums'      => isset($this->setting['nums']) && $this->setting['nums'] ? $this->setting['nums'] : 5,
			'memberid'  => isset($this->memberinfo['id']) ? $this->memberinfo['id'] : 0,
			'memberinfo'=> isset($this->memberinfo) && $this->memberinfo ? $this->memberinfo : null,
		));
		ob_start();
		$this->display('list');
		$html = ob_get_contents();
		ob_clean();
		$html = addslashes(str_replace(array("\r", "\n", "\t"), array('', '', ''), $html));
	    echo 'document.write("' . $html . '");';
	}

	/*
	 * ajax评论
	 */
	public function clistAction() {
	    $commentid = (int)$this->get('id');
		$listdata  = array();
		if ($commentid) {
		    $limit = (int)$this->get('num') ? (int)$this->get('num') : ( isset($this->setting['nums']) && $this->setting['nums'] ? $this->setting['nums'] : 5);
			$count = $this->comment_data->count('comment_data', 'id', 'commentid=' . $commentid . ' AND `status`=1 AND `reply`=0');
		    $list  = $this->comment_data->where('commentid=' . $commentid . ' AND `status`=1 AND `reply`=0')->order('lasttime DESC')->limit($limit)->select();
			foreach ($list as $k=>$t) {
			    $listdata[$k] = $t;
				$listdata[$k]['reply'] = $this->comment_data->where('commentid=' . $t['id'] . ' AND `status`=1 AND `reply`=1')->order('addtime DESC')->limit(20)->select();
			}
			$this->assign(array(
				'listdata'  => $listdata,
				'ismore'    => $limit < $count ? 1 : 0,
				'limit'     => $limit,
				'countmore' => $count - $limit,
				'code'      => $this->setting['code'],
				'count'     => $count,
				'memberid'  => isset($this->memberinfo['id']) ? $this->memberinfo['id'] : 0,
				'memberinfo'=> isset($this->memberinfo) && $this->memberinfo ? $this->memberinfo : null,
			));
			$this->display('clist');
		} else {
		    echo '没有评论';
		}
	}

	/*
	 * 提交评论
	 */
	public function addAction() {
	    $contentid = (int)$this->get('contentid');
		$content   = $this->get('content');
		if (empty($content)) exit(json_encode(array('status'=>0, 'data'=>'请填写评论内容！')));
		if (!$this->setting['guest'] && !$this->memberinfo) exit(json_encode(array('status'=>0, 'data'=>'游客不允许评论！')));
		if ($this->setting['code'] && $this->get('code') != $this->session->get('captcha')) exit(json_encode(array('status'=>0, 'data'=>'验证码不正确！')));
		if (cookie::is_set('comment_' . $contentid)) exit(json_encode(array('status'=>0, 'data'=>'您已经评论过了，请您休息一会儿吧！')));
		$infodata  = $this->content->find($contentid, 'title,id,catid');
		if (empty($infodata)) exit(json_encode(array('status'=>0, 'data'=>'信息不存在！')));
		$data      = $this->comment->getOne('contentid=' . $contentid);
		if (empty($data)) {
		    /*录入评论数据*/
			$data  = array(
			    'contentid' => $contentid,
				'catid'     => $infodata['catid'],
				'title'     => $infodata['title'],
				'total'     => 0,
			);
			$data['id'] = $this->comment->insert($data);
			if (empty($data['id'])) exit(json_encode(array('status'=>0, 'data'=>'数据录入失败！')));
		}
		$id   = $data['id'];
		cookie::set('comment_' . $contentid, 1, 360); //间隔360秒
		$time = time();
		unset($data['id']);
		$comment = array(
		    'commentid' => $id,
			'contentid' => $contentid,
			'userid'    => isset($this->memberinfo['id']) && $this->memberinfo['id'] ? $this->memberinfo['id'] : 0,
			'username'  => isset($this->memberinfo['username']) && $this->memberinfo['username'] ? $this->memberinfo['username'] : 0,
			'addtime'   => $time,
			'ip'        => client::get_user_ip(),
			'status'    => isset($this->setting['status']) && $this->setting['status'] ? 0 : 1,
			'content'   => $content,
			'support'   => 0,
			'opposition'=> 0,
			'reply'     => 0,
			'lasttime'  => $time,
		);
		if (!$this->comment_data->insert($comment)) exit(json_encode(array('status'=>0, 'data'=>'评论失败！')));
		if ($comment['status']) $this->comment->update(array('total'=>$data['total']+1, 'lastupdate'=>$time), 'id=' . $id);
		$result = $comment['status'] ? '评论成功' : '评论成功，需审核之后才能显示';
		exit(json_encode(array('status'=>1, 'data'=>$result, 'verify'=>$comment['status'], 'id'=>$id)));
	}

	/*
	 * 回复评论
	 */
	public function replyAction() {
	    $commentid = (int)$this->get('commentid');
		$content   = $this->get('content');
		if (empty($content)) exit(json_encode(array('status'=>0, 'data'=>'请填写回复内容！')));
		if (!$this->setting['guest'] && !$this->memberinfo) exit(json_encode(array('status'=>0, 'data'=>'游客不允许评论！')));
		if ($this->setting['code'] && $this->get('code') != $this->session->get('captcha')) exit(json_encode(array('status'=>0, 'data'=>'验证码不正确！')));
		if (cookie::is_set('comment_reply_' . $commentid)) exit(json_encode(array('status'=>0, 'data'=>'您已经回复过了，请您休息一会儿吧！')));
		$data      = $this->comment_data->find($commentid, 'id,contentid');
		if (empty($data)) exit(json_encode(array('status'=>0, 'data'=>'评论数据(#' . $commentid . ')不存在，请刷新页面！')));
		$time      = time();
		$comment   = array(
		    'commentid' => $commentid,
			'contentid' => $data['contentid'],
			'userid'    => isset($this->memberinfo['id']) && $this->memberinfo['id'] ? $this->memberinfo['id'] : 0,
			'username'  => isset($this->memberinfo['username']) && $this->memberinfo['username'] ? $this->memberinfo['username'] : 0,
			'addtime'   => $time,
			'ip'        => client::get_user_ip(),
			'status'    => isset($this->setting['status']) && $this->setting['status'] ? 0 : 1,
			'content'   => $content,
			'support'   => 0,
			'opposition'=> 0,
			'reply'     => 1,
			'lasttime'  => $time,
		);
		if (!$this->comment_data->insert($comment)) exit(json_encode(array('status'=>0, 'data'=>'回复失败！')));
		cookie::set('comment_reply_' . $commentid, 1, 360); //间隔360秒
		$result = $comment['status'] ? '回复成功' : '回复成功，需审核之后才能显示';
		$reply  = '';
		if ($comment['status']) {
		    $reply = ' <div style="border-top: 1px solid #D7D7D7; margin-top: 10px; padding-top: 10px; margin-left: 60px;">
				<div style="float: left; border-radius: 6px 6px 6px 6px; width: 38px; height: 38px; overflow: hidden; margin-right: 10px;">
					<img src="' . get_member_avatar($comment['userid'], 45) . '" style="width: 38px; height: 38px;">
				</div>
				<div style="padding-left: 48px;">
					<div style="color: #303030; font-size: 13px; line-height: 18px; height: 26px;">
						<span style="color: #3B5998;float: left; text-decoration: none; padding-right: 6px;"">
							' . ($comment['username'] ? $comment['username'] : '游客') .'
						</span>
						<span style="color: #aaaaaa; float: left;" class="idea_comment_cmt_ufromname">
							(' . $comment['ip'] . ')
						</span>
					</div>
					<div style="color: #303030; font-size: 14px; line-height: 18px; word-wrap: break-word; text-align: left;">
						' . $comment['content'] . '
					</div>
					<div style="padding: 12px 5px 0 0; line-height: 14px;" class="idea_comment_cmt_exp">
						<div style="color: #303030; float: left; font-size: 12px; padding: 0 10px 0 0;">
							 ' . fnDate($comment['addtime']) . '
						</div>
						<div style="clear: both;">
						</div>
					</div>
				</div>
				<div style="clear: both;">
				</div>
			</div>';
		}
		exit(json_encode(array('status'=>1, 'data'=> $result, 'verify'=>$comment['status'], 'result'=>$reply)));
	}

	/*
	 * 踩顶
	 */
	public function cdAction() {
	    $commentid = (int)$this->get('commentid');
		$typeid    = $this->get('type');
		if (empty($commentid)) exit(json_encode(array('status'=>0, 'result'=>'参数不正确！')));
		if (cookie::is_set('comment_cd_' . $commentid))  exit(json_encode(array('status'=>0, 'result'=>'亲，不要太快哦！')));
		$data      = $this->comment_data->find($commentid, 'id,support,opposition');
		if (empty($data))  exit(json_encode(array('status'=>0, 'result'=>'评论数据不存在，请刷新页面！')));
		if ($typeid == 1) {
		    $result = $data['support']+1;
		    $update = array('support'=>$result);
		} else {
		    $result = $data['opposition']+1;
		    $update = array('opposition'=>$result);
		}
		if (!$this->comment_data->update($update, 'id=' . $commentid))  exit(json_encode(array('status'=>0, 'result'=>'操作失败！')));
		cookie::set('comment_cd_' . $commentid, 1, 360); //间隔360秒
		exit(json_encode(array('status'=>1, 'result'=>$result)));
	}

	/*
	 * 删除
	 */
	public function delAction() {
	    $cid  = (int)$this->get('commentid');
		if (empty($cid)) {
            exit(json_encode(array('status'=>0, 'result'=>'参数不正确！')));
        }
		$data = $this->comment_data->find($cid, 'id,commentid,reply,userid,username,contentid');
		if (empty($data)) {
            exit(json_encode(array('status'=>0, 'result'=>'评论信息不存在！')));
        }
        if ($data['userid'] == 0 && $data['ip'] != client::get_user_ip()) {
            exit(json_encode(array('status'=>0, 'result'=>'无权限！')));
        }
		if (IS_ADMIN || (isset($this->memberinfo['id']) && ($this->memberinfo['id'] == $data['userid'] && $this->memberinfo['username'] == $data['username']))) {
			if ($data['reply']) {
				$this->comment_data->delete('id=' . $cid);
			} else {
				$this->comment_data->delete('id=' . $cid);
				$this->comment_data->delete('commentid=' . $cid . ' AND `reply`=1');
				$this->comment->update(array('total'=>'total-1'), 'id=' . $data['commentid']);
			}
			exit(json_encode(array('status'=>1)));
		} else {
		    exit(json_encode(array('status'=>0, 'result'=>'无权限！')));
	    }
	}

	/*
	 * IP查询
	 */
	public function ipaddressAction() {
        exit('官方接口已经失效，官方服务器死机了！~~~');
	}


}
