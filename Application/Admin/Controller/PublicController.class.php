<?php 
namespace Admin\Controller;

use Think\Controller;
class PublicController extends Controller
{
	public function _initialize()
	{
		 if(empty(session('admin_id')))
		 {
		 	$this->redirect('Login/login');
		 }
		 $admin = M('myUsers')->find(session('admin_id'));
		 if(!$admin || $admin['is_admin'] != 1)
		 {
		 	session('admin_id', null);
		 	$this->redirect('Login/login');
		 }
		 define('CAPTCHA_REGISTER', 64);
		 define('CAPTCHA_LOGIN', 128);
		 define('CAPTCHA_COMMENT', 256);
		 define('CAPTCHA_LOGIN_FAIL', 512);
		 $configs = api('Config/lists');
     	 C($configs);
	}

}