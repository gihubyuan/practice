<?php
namespace Home\Controller;

use Think\Controller;

class HomeController extends Controller
{
    
    public function _initialize()
    {        
    		$configs = api('Config/lists');
    		C($configs);
    		if(C('SITE_CLOSED') == 1) {
    			exit("网站已关闭,请稍后在访问~");
    		}

        $this->assign('navs', get_navs());
    }

   
}