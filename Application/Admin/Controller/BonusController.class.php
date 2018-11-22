<?php
namespace Admin\Controller;

use Admin\Controller\PublicController;

class BonusController extends PublicController
{
	public function index()
	{
		$arr = M('userBonus')
		  ->field(['bonus_type', 'count(*)'=>'used_num'])
		  ->where(['used' => ['gt', 0]])
			->group('bonus_type')
			->select();

		$bonus_arr = array();
		foreach($arr as $val)
		{
			 $bonus_arr[$arr['bonus_type']]['used_num'] = $arr['used_num'];
		}

		$arr = M('userBonus')
		  ->field(['bonus_type', 'count(*)'=>'num'])
			->group('bonus_type')
			->select();

		foreach($arr as $val)
		{
			 $bonus_arr[$arr['bonus_type']]['send_num'] = $arr['num'];
		}

		$arr = M('bonus')->select();

		foreach($arr as $val)
		{
			$bonus_arr[$val['id']] = $val;
		}
		$this->assign('bonus_arr', $bonus_arr);
		$this->display();
	}

	public function send()
	{
		$type = I('type');
		
	}
}