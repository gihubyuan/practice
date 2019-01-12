<?php
namespace Home\Controller;

use Think\Controller;

class HomeController extends Controller
{
    public function _initialize()
    {       
        define('CAPTCHA_REGISTER', 64);
        define('CAPTCHA_LOGIN', 128);
        define('CAPTCHA_COMMENT', 256);
        define('CAPTCHA_LOGIN_FAIL', 512); 
		$configs = api('Config/lists');
        $configs['limit_count'] = 10;
		$configs['confirm_addto_cart'] = 3;
		C($configs);
        if(empty(session('user_auth.uid')))
        {
            session('discount', 1);
            session('rank_id', 0);
        }
		if(C('SITE_CLOSED') == 1)
        {
			exit("网站已关闭,请稍后在访问~");
		}

       $this->assign('navs', get_navs());
    }   
}