<?php 
namespace Admin\Controller;

use Admin\Controller\PublicController;

class IndexController extends PublicController
{

	public function index()
	{
		$this->display();
	}

	public function regFields()
	{
		  $this->assign('fields', M('registerFields')->where(['enabled'=>1])->select());
			$this->display();
	}

	public function affiliateHandle()
	{
		 if(IS_POST) {
		 	 $data = I('post.');
		 	 $data['invitation_points'] = !empty($data['invitation_points']) ? intval($data['invitation_points']) : 0;
		 	 $data['invitation_points_up'] = !empty($data['invitation_points_up']) ? intval($data['invitation_points_up']) : 0;
		 	 $data['on'] = intval($data['on']) == 1 ? 1 : 0;
		 	 put_affiliate($data);
		 	 return true;
		 }
	}

	public function captcha()
	{

		 $captcha = C('CAPTCHA');
		 if($captcha & CAPTCHA_REGISTER) {
		 	 $this->assign(strtolower('CAPTCHA_REGISTER'), 1);
		 }else {
		 	 $this->assign(strtolower('CAPTCHA_REGISTER'), 0);
		 }

		 if($captcha & CAPTCHA_LOGIN) {
		 	 $this->assign(strtolower('CAPTCHA_LOGIN'), 1);
		 }else {
		 	 $this->assign(strtolower('CAPTCHA_LOGIN'), 0);
		 }

		 if($captcha & CAPTCHA_COMMENT) {
		 	 $this->assign(strtolower('CAPTCHA_COMMENT'), 1);
		 }else {
		 	 $this->assign(strtolower('CAPTCHA_COMMENT'), 0);
		 }
		 if($captcha & CAPTCHA_LOGIN_FAIL) {
		 	 $this->assign(strtolower('CAPTCHA_LOGIN_FAIL'), 1);
		 }else {
		 	 $this->assign(strtolower('CAPTCHA_LOGIN_FAIL'), 0);
		 }
		 $this->display();
	}

	public function captchaSave()
	{
		 if(IS_POST) {
		 	 $data = I('post.');
		 	 $captcha = 0;
		 	 $captcha = !empty($data['captcha_register']) ? $captcha | CAPTCHA_REGISTER : $captcha;
		 	 $captcha = !empty($data['captcha_login']) ? $captcha | CAPTCHA_LOGIN : $captcha;
		 	 $captcha = !empty($data['captcha_comment']) ? $captcha | CAPTCHA_COMMENT : $captcha;
		 	 $captcha = !empty($data['captcha_login_fail']) ? $captcha | CAPTCHA_LOGIN_FAIL : $captcha;
		 	 
		 	 M('systemConfig')->where(['config_name'=>'captcha'])->delete();
		 	 if(M('systemConfig')->add([
		 	 	 'config_name' => 'captcha',
		 	 	 'config_title' => '验证码', 
		 	 	 'config_value' => $captcha 
		 	 ])) {
		 	 	 $this->success('修改成功', U('captcha'));
		 	 }else {
		 	 	 $this->error('修改失败');
		 	 }
		 }
	}
	
	public function affiliate()
	{
		$this->assign('affiliate', unserialize(C('affiliate')));
		$this->display();
	}

	public function aRegField()
	{
		$type = I('get.type');
		$is_add = $type== 'add' ? true : false; 
		$is_edit = $type== 'edit' ? true : false; 
		if($type != $is_add && $type !=$is_edit) {
			$this->error('错误');
		}
		if($is_edit) {
			empty(I('id')) && $this->error('错误'); 
		}

		if($is_add) {
			$this->assign('act_type', 'act_insert');
			$this->assign('field', ['field_name'=>'', 'type'=> 1,'field_title'=>'', 'enabled'=>1, 'field_values'=>'']);
		}else {
			$this->assign('act_type', 'act_update');
			$this->assign('field', M('registerFields')->find(I('id')));
		}

		$this->display();
	}

	function regFieldHandle()
	{
		$type = I('post.act_type');
		$is_insert = $type== 'act_insert' ? true : false; 
		$is_update = $type== 'act_update' ? true : false; 
		if($type != $is_insert && $type != $is_update) {
			$this->error('错误');
		}
		$data = I('post.');
		$data['enabled'] = isset($data['enabled']) ? 1: 0;
		if(empty($data['id'])) {
			unset($data['id']);
		}
		unset($data['__hash__'], $data['act_type']);

		if($is_insert) {
			M('registerFields')->add($data);
		}else {
			M('registerFields')->save($data);
		}
	}

}