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

		$used_arr = $send_arr = $bonus_arr = array();
		foreach($arr as $val)
		{
			 $used_arr[$val['bonus_type']] = $val['used_num'];
		}

		$arr = M('userBonus')
		    ->field(['bonus_type', 'count(*)'=>'num'])
			->group('bonus_type')
			->select();

		foreach($arr as $val)
		{
			 $send_arr[$val['bonus_type']] = $val['num'];
		}

		$arr = M('bonus')->select();

		foreach($arr as $val)
		{
			$val['send_num'] = isset($send_arr[$val['id']]) ? $send_arr[$val['id']] :0;
			$val['used_num'] = isset($used_arr[$val['id']]) ? $used_arr[$val['id']] :0;
			$bonus_arr[] = $val;
		}
		$this->assign('bonus_arr', $bonus_arr);
		$this->display();
	}

	public function toSend()
	{
		$type = I('send_type');
		if($type == 3)
		{
			$arr = M('bonus')->field(['id', 'bonus_name', 'bonus_money'])->where(['send_type'=>3])->select();
			$list = array();
			foreach($arr as $val)
			{
				$list[$val['id']] = $val['bonus_name'] . '[' . sprintf(C('CURRENCY_FORMAT'), $val['bonus_money']) .']';
			}
			$this->assign('list', $list);
			$this->assign('type', $type);
			$this->display();
		}

	}

	public function sendByPrint()
	{

		$type_id = I('type_id');
		$bonus_sum = empty(I('bonus_num')) ? 1 : intval(I('bonus_num'));
		$max_sn = M('userBonus')->field(['MAX(bonus_sn)'=>'max_sn'])->find();
		$seed = !empty($max_sn['max_sn']) ? floor($max_sn / 10000) : 100000;
	 	for($i=0; $i< $bonus_sum; $i++)
	 	{
	 		$max_sn = ($seed + $i + 1) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
			M('userBonus')->add([
				'bonus_type' => $type_id,
				'user_id' => 0,
				'bonus_sn' => $max_sn,
				'used' => 0
			]);	 		
	 	}
	 	$this->redirect('Bonus/index');
	}
}