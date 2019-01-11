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
		$shop_price = $good['shop_price'];
		assign_comments($this->view, $good['id']);
		$good['good_name_style'] = getStyleName($good['good_name'], $good['good_name_style']);
		$good['shop_price'] = get_good_price($id);
		$this->assign('good', $good);
		$this->assign('properties', $properties['prop']);
		$this->assign('specification', $properties['spec']);
		$this->assign('good', $good);
		$this->assign('good_id', $good['id']);
		$this->rank_prices = get_rank_prices($id, $shop_price);
		$this->volume_prices = get_volume_prices($id);

		if(C('CAPTCHA') & CAPTCHA_COMMENT)
		{
			$this->assign('captcha_on', 'on');
		}
		$this->display();
	}

	public function getPrice()
	{
	   $good_id = I('get.good_id');		
	   $number = I('get.num');		
	   $ids = I('get.ids');		
		 $result = ['error'=>'', 'content'=>''];
		 if(empty($good_id))
		 {
		 	 $result['error'] = '访问错误';
		 }
		 else
		 {
		 	 $good_price = getFinalPrice($good_id, $number, 0);
		 	 $spec_price = spec_price($ids);
		 	 $final_price = ($good_price * $number) + $spec_price;
		 	 $result['content'] = sprintf('￥%d元', round($final_price));
		 }
		$this->ajaxReturn($result);
	}
}

function get_rank_prices($id, $price)
{
    $ranks = M('userRank')
     ->alias('ur')
     ->field(['rank_name', 'ifnull(mp.member_price, '.($price / 100).' * ur.discount)' => 'rank_price'])
     ->join('member_price mp on ur.id=mp.user_rank and mp.good_id='.$id, 'left')
     ->where(['ur.show_price'=>1, '_logic'=>'or', 'ur.id'=>session('rank_id')])
     ->select();

    $list = array();
    foreach($ranks as $val)
    {
        $list[$val['rank_name']] = array();
        $list[$val['rank_name']]['rank_price'] = sprintf('￥%d元', round($val['rank_price']));
    }
    return $list;
}

function get_good_price($good_id)
{
	$min_price = getFinalPrice($good_id, 1, 0);
	$attr_arr =  M('goodAttrs')
	 ->where('good_id =' . $good_id . ' AND attr_price > 0')
	 ->select();
	 $res = array();
	 foreach($attr_arr as $attr)
	 {
	 	  $res[$attr['attr_id']][$attr['attr_price']] = $attr;
	 }

	 $tmp = array();
	 foreach($res as $value)
	 {
	 	  ksort($value);
	 	  $tmp[] = end($value);
	 }

	 $max_price = $min_price;
	 foreach($tmp as $v)
	 {
	 	 $max_price += $v['attr_price'];
	 }

	 if($max_price == $min_price)
	 {
			return sprintf("￥%d元", $max_price);	 	  
	 }
	 elseif($max_price > $min_price)
	 {
	 	  return sprintf("￥%d", $min_price) . ' ~ ' . sprintf("%d元", $max_price);
	 }
	 else
	 {
			return sprintf("￥%d元", $max_price);	 	  
	 }
}
