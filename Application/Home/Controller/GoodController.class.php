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
		$this->rank_prices = get_rank_prices($id, $shop_price);
		$this->volume_prices = get_volume_prices($id);
		if(C('CAPTCHA') & CAPTCHA_COMMENT)
		{
			$this->assign('captcha_on', 'on');
		}
		$this->display();
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
		->select();
	 $list = array();
	 foreach($prices as $k => $p)
	 {
	 	$list[$k] = array();
	 	$list[$k]['volume_number'] = $p['volume_number']; 
	 	$list[$k]['volume_price'] = sprintf('￥%d元', round($p['volume_price'])); 
	 }
	 return $list;
}