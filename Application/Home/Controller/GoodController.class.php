<?php
namespace Home\Controller;

use Think\Controller;

class GoodController extends \Home\Controller\HomeController
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

}