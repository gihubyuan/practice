<?php
namespace Home\Controller;

use Think\Controller;

class CommentController extends \Home\Controller\HomeController
{
	public function index()
	{
		empty(I('id')) && $this->error("错误");
	    $good = M('goods')->find(I('id'));		
		if(!$good) {
			$this->error("错误");
			exit;
		}

		$good['good_name_style'] = getStyleName($good['good_name'], $good['good_name_style']);
		$this->assign('good', $good);
		$this->display();
	}

	public function addComment()
	{	
		if(IS_POST) {
			$data = I('post.');
			$msg = [];
			$result = ['status'=>1];
			if(empty($data['reply_id'])) {
				$result['status'] = 0;
				$msg[] = '参数错误';
			}
			if(empty($data['email']) || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]+\@[a-zA-Z][a-zA-Z0-9_]+(\.com|\.cn|\.edu\.gov)+$/', $data['email'])) {
				$result['status'] = 0;
				$msg[] = '邮箱格式错误';
			}
			if(empty($data['content'])) {
				$result['status'] = 0;
				$msg[] = '评论内容不得为空';
			}
			
			if(C('CAPTCHA') & CAPTCHA_COMMENT) {
				if(empty($data['vcode']) || !check_verify($data['vcode'])) {
					$result['status'] = 0;
				    $msg[] = '验证码错误';
				}
				
				$factor = C('COMMENT_FACTOR');
				if($data['comment_type'] ==0  && intval($factor) > 0) {
					switch($factor) {
						case 1:
							if(!is_login()) {
								$result['status'] = 0;
								$msg[] = '请登录在发表评论';
							}
							// 购买才能发表评论
							break;
						case 2:
							break;
					}
				}
				if($result['status'] == 0) {
					$this->ajaxReturn(array_merge($result, ['msg'=>$msg]));
				}
				if(!addComment($data)) {
					$this->ajaxReturn(['status'=>0, 'msg'=> ['添加失败,请重新尝试']]);
				}else {
					$this->ajaxReturn(['status'=>1, 'msg'=> '评论成功,等待管理员审核']);
				}

			}else {
				if(session('post_comment_time') == null) {
					session('post_comment_time', 0);
				}

				if(time() - session('post_comment_time') < 30) {
					$result['status'] = 0;
					$msg[] = '间隔30秒';
				}else {
					$factor = C('COMMENT_FACTOR');
					if($data['comment_type'] ==0  && intval($factor) > 0) {
						switch($factor) {
							case 1:
								if(!is_login()) {
									$result['status'] = 0;
									$msg[] = '请登录在发表评论';
								}
								// 购买才能发表评论
								break;
							case 2:
								break;
						}
					}
				}
				if($result['status'] == 0) {
					$this->ajaxReturn(array_merge($result, ['msg'=>$msg]));
				}
				if(!addComment($data)) {
					$this->ajaxReturn(['status'=>0, 'msg'=> ['添加失败,请重新尝试']]);
				}else {
					session('post_comment_time',time()) ;
					$this->ajaxReturn(['status'=>1, 'msg'=> '评论成功,等待管理员审核']);
				}
			}

		}else {
			$this->ajaxReturn(['status'=>0, 'msg'=>'非法访问']);
		}
	}
	
}

function addComment($data)
{
	$data['status'] = 1 - C('COMMENT_CHECK');
	$data['username'] = !empty(session('user_auth.username')) ? session('user_auth.username') : '';
	$data['uid'] = !empty(session('user_auth.uid')) ? session('user_auth.uid') : 0;
	$data['comment_type'] = !empty(intval($data['comment_type'])) ? intval($data['comment_type']) : 0;
	$data['pid'] = !empty(intval($data['pid'])) ? intval($data['pid']) : 0;
	$data['add_time'] = time();
	$data['ip_address'] = get_client_ip();
	return ($last_id = M('comments')->add($data)) ? true : false;
}

