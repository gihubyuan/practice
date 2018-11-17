<?php
namespace Home\Controller;

use Think\Controller;

class GoodController extends \Home\Controller\HomeController
{
	public function index()
	{
		$id = I('get.id');
		if(!$id || (!$good = M('goods')->find($id)))
		{
			$this->redirect('Index/index');
			exit;
		}

		$good['good_name_style'] = getStyleName($good['good_name'], $good['good_name_style']);

		assign_comments($this->view, $good['id']);

		$properties = get_good_properties($id);
		$this->assign('properties', $properties['prop']);
		
		$this->assign('spec', $properties['spec']);

		$this->assign('good', $good);
		if(C('CAPTCHA') & CAPTCHA_COMMENT) {
			$this->assign('captcha_on', 'on');
		}
		$this->display();
	}


}