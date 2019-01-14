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
		 define('CAPTCHA_REGISTER', 64);
		 define('CAPTCHA_LOGIN', 128);
		 define('CAPTCHA_COMMENT', 256);
		 define('CAPTCHA_LOGIN_FAIL', 512);
		 define('RANGE_CAT', 1);
		 define('RANGE_BRAND', 2);
		 define('RANGE_GOOD', 3);
		 define('TYPE_GIFT', 0);
		 define('TYPE_DISCOUNT', 1);
		 define('TYPE_SLASH', 2);
		 $configs = api('Config/lists');
     	 C($configs);
	}

}