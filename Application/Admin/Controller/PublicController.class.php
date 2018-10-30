<?php 
namespace Admin\Controller;

use Think\Controller;
class PublicController extends Controller
{
	public function _initialize()
	{

		 $configs = api('Config/lists');
     C($configs);
    
	}

}