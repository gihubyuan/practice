<?php 
namespace Admin\Controller;

use Think\Controller;
class PublicController extends Controller
{
	public function _initialize()
	{
		 define('CAPTCHA_REGISTER', 64);
		 define('CAPTCHA_LOGIN', 128);
		 define('CAPTCHA_COMMENT', 256);
		 define('CAPTCHA_LOGIN_FAIL', 512);
		 $configs = api('Config/lists');
     C($configs);
	}

}