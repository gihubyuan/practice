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
	   $data = I('get.');		
		 $result = ['error'=>'', 'content'=>''];
		 if(empty($data['good_id']))
		 {
		 	 $result['error'] = '访问错误';
		 }
		 else
		 {
		 	 $volume_price = $promotion_price = $final_price =0;
		 	 $number = $data['num'] > 0 ? $data['num'] : 1;
		 	 $arr_prices = get_volume_prices($data['good_id']);
		 	 foreach($arr_prices as $val)
		 	 {
		 	 	 if($number >= $val['volume_number'])
		 	 	 {
		 	 	 	 $volume_price = $val['volume_price_orgin'];
		 	 	 	 break;
		 	 	 }
		 	 }
		 	 $good_info = M('goods')->field(['shop_price * '.session('discount') => 'user_price', 'promotion_price', 'promotion_start', 'promotion_end'])->find($data['good_id']);

		 	 $user_price = $good_info['user_price'];
		 	 if($good_info['promotion_price'] > 0)
		 	 {
		 	 	  $gtime = time();
		 	 	  if($gtime >= $good_info['promotion_start'] && $gtime <= $good_info['promotion_end'])
		 	 	  {
		 	 	  	$promotion_price = $good_info['promotion_price'];
		 	 	  }
		 	 	  else
		 	 	  {
		 	 	  	$promotion_price = 0;
		 	 	  }
		 	 }

		 	 $final_price = min(array_filter([$volume_price, $promotion_price, $user_price]));

		 	 if(!empty($data['ids']))
		 	 {
		 	 	 $ids = explode(',', $data['ids']);
		 	 	 $attr_prices = M('goodAttrs')->field(['attr_price'])->where(db_create_in($ids, 'id'))->select();
		 	 	 foreach($attr_prices as $val)
		 	 	 {
		 	 	 	 if(!empty($val['attr_price']))
		 	 	 	 {
		 	 	 	 	 $final_price += $val['attr_price'];
		 	 	 	 }
		 	 	 }
		 	 }
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

function get_volume_prices($id, $price_type = 1)
{
	$prices = M('volumePrice')
		->where(['good_id'=>$id, 'price_type'=>$price_type])
		->order('volume_number desc')
		->select();
	 $list = array();
	 foreach($prices as $k => $p)
	 {
	 	$list[$k] = array();
	 	$list[$k]['volume_number'] = $p['volume_number']; 
	 	$list[$k]['volume_price'] = sprintf('￥%d元', round($p['volume_price'])); 
	 	$list[$k]['volume_price_orgin'] = $p['volume_price']; 
	 }
	 return $list;
}

