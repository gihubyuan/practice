<?php
namespace Home\Controller;

use Think\Controller;

class GoodController extends \Home\Controller\HomeController
{
	public function index()
	{
		$id = I('get.id');
		if(!$id || (!M('goods')->find($id)))
		{
			$this->redirect('Index/index');
			exit;
		}		

		$properties = get_good_properties($id);
		$good = get_good_info($id);
		assign_comments($this->view, $good['id']);
		$good['good_name_style'] = getStyleName($good['good_name'], $good['good_name_style']);
		$this->assign('good', $good);
		$this->assign('properties', $properties['prop']);
		$this->assign('specification', $properties['spec']);
		$this->assign('good', $good);
		// $rank_prices = get_rank_prices($id);
		// $volume_prices = get_volume_prices($id);
		if(C('CAPTCHA') & CAPTCHA_COMMENT)
		{
			$this->assign('captcha_on', 'on');
		}
		$this->display();
	}

}