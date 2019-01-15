<?php
namespace Home\Controller;

use Think\Controller;

class FlowController extends \Home\Controller\HomeController
{
	public function addToCart()
	{
		  if(empty(session('user_auth.uid')))
		  {
		  	 $this->error('请先登录');
		  	 exit;
		  }
			$specs = empty(I('post.specs')) ? array() : I('post.specs');
			$id = I('post.id');
			$num = I('post.num');
			$parent_id = empty(I('post.parent_id')) ? 0 : I('post.parent_id');
			$num = intval($num) < 1 ? 1 : intval($num);
 
			if(empty($id))
			{
				$this->ajaxReturn(['error'=>'错误']);
			}

			$result = add_to_cart($parent_id, $id, $num, $specs);
			if($result['error'] == '')
			{
				$total = M('carts')->where(['user_id'=>session('user_auth.uid')])->getField('SUM(good_price * good_number)');
				$result['flow'] = round(floatval($total), 2); 
			}
			$this->ajaxReturn($result);
	}

	function cart()
	{
		$user_id = session('user_auth.uid');
		if(empty($user_id))
	  {
	  	 $this->error('请先登录');
	  	 exit;
	  }

	  $this->favors = favourable_list();
	  $this->range_text = array(
	 	0 => '所有商品',
	 	1 => '指定分类',
	 	2 => '指定品牌',
	 	3 => '指定商品',
	 );

	  $cart_items = cart_goods();
	  $this->assign('cart_items', $cart_items['goods']);
	  $this->assign('saving_mark', sprintf('您的购买价格%s比市场价%s节省%s元, 优惠率%f%%', $total['amount'], $total['market_price'], $total['saving'], $total['save_rate']));
	  $discount = compute_discount();
	  if($discount['discount'] > 0)
	  {
	  	$this->assign('your_discount', sprintf('从优惠活动%s抵扣%s', $discount['favor_name'], $discount['discount']));
	  }
	  $good_ids = M('carts')->field(['good_id'])->where(['user_id'=>$user_id])->select();
	  			get_fittings();

	  foreach($good_ids as $k => $good_id)
	  {
	  	 $good_ids[$k] = $good_id['good_id'];
	  }
	  $group_goods =  get_fittings($good_ids);
	  $this->assign('group_goods', $group_goods);

	  
	  $this->display();
	}

}

function cart_goods()
{
	$uid = session('user_auth.uid');
	$goods = M('carts')
	  ->alias('c')
	  ->field(['IF(c.parent_id, c.parent_id, c.good_id)' => 'pid', 'c.*'])
	  ->join('goods g on c.good_id=g.id', 'left')
	  ->where(['user_id'=>$uid, 'rec_type'=>1])
	  ->order('pid, c.parent_id')
	  ->select();
	 $total = array(
	 	'amount' => 0.00,
	 	'market_price' => 0.00,
	 );
	 $real_num = 0;
	 $virtual_num = 0;
	foreach($goods as $k => $good)
	{
		$total['amount'] += $good['good_number'] * $good['good_price'];
		$total['market_price'] += $good['good_number'] * $good['market_price'];
		$goods[$k]['subtotal'] = price_format($good['good_number'] * $good['good_price']);

		if($good['is_real'] == 1)
		{
			$real_num++;
		}
		else
		{
			$virtual_num++;
		}
	}
	$total['amount'] = price_format($total['amount']);
	if($total['market_price'] > 0)
	{
		$total['saving'] = $total['market_price'] - $total['amount'];
		$total['save_rate'] = round($total['saving'] / $total['market_price'], 2);
	}
	$total['real_num'] = $real_num;
	$total['virtual_num'] = $virtual_num;
	return array(['total'=>$total, 'goods'=>$goods]);
}

function get_fittings($parent_id)
{
  $group_goods = M('groupGoods')
   				->alias('gp')
   				->field(['gp.*', 'IFNULL(mp.member_price, g.shop_price * ' . session('discount') .')' => 'min_price'])
   				->join('goods g on gp.good_id=g.id', 'left')
   				->join('member_price mp on g.id=mp.good_id AND mp.user_rank='. session('rank_id'), 'left')
   				->where(db_create_in($parent_id, 'gp.parent_id'))
   				->select();
   return $group_goods;
}

function price_format($price)
{
	return sprintf('￥%s元', round($price, 2));
}

function favourable_list()
{
	$cart_favors = cart_favourable();
	$now = time();
	$user_rank = empty(session('rank_id')) ?  0 : session('rank_id');
	$favors = M('favourableActivity')
	 ->where("concat(',', act_recipient, ',') like '%,$user_rank,%' and act_start_time <= $now and act_end_time >= $now and act_type = '0'" )
	 ->select();
	 $description_arr = array(
	 	0 => '选择以下指定赠品%d件',
	 	1 => '总价打折%s%%',
	 	2 => '总价减免%s',
	 );
	 foreach ($favors as $key => $value) {
	 	  $favors[$key]['act_start_time'] = date('Y-m-d H:i', $favors[$key]['act_start_time']);
	 	  $favors[$key]['act_end_time'] = date('Y-m-d H:i', $favors[$key]['act_end_time']);
	 	  $favors[$key]['min_amount_formatted'] = sprintf('￥%s元', round($favors[$key]['act_min_amount'], 2));
	 	  $favors[$key]['max_amount_formatted'] = sprintf('￥%s元', round($favors[$key]['act_max_amount'], 2));
	 	  $favors[$key]['range_desc'] = favourable_range($value);
	 	  $favors[$key]['available'] = favourable_available($value);
	 	  $favors[$key]['description'] = sprintf($description_arr[$favors[$key]['act_type']], $favors[$key]['act_type_ext']);
	 	  if($favors[$key]['available'])
	 	  {
	 	  	$favors[$key]['available'] = !favourable_used($value, $cart_favors);
	 	  }
	 }
	 
	 return $favors;
}

function cart_favourable()
{
	$user_id = session('user_auth.uid');
	if(empty($user_id))
  {
  	 $this->error('请先登录');
  	 exit;
  }
	$gifts = M('carts')
	 ->field(['count(*)' => 'num'])
	 ->where('is_gift > 0 AND user_id = ' . $user_id . ' AND rec_type = 1')
	 ->group('is_gift')
	 ->select();
	 $return = array();
	foreach ($gifts as $key => $value) {
		$return[$value['is_gift']] = $value['num'];
	}
	return $return;
}

function favourable_available($favor)
{
	$amount = favourable_amount($favor);
	return $amount >= $favor['act_min_amount'] && ($amount <= $favor['act_max_amount'] || $favor['act_max_amount'] == 0);
}

function favourable_used($favor, $cart_favor)
{
	if($favor['act_type'] == 1)
	{
		return isset($cart_favor[$favor['id']]) && $cart_favor[$favor['id']] >= intval($favor['act_type_ext']);
	}
	else
	{
		
	}
}

function favourable_amount($favor)
{
	$where = '';
	if($favor['act_range'] == 1)
	{
		$ranges = explode(',', $favor['act_range_ext']);
	 	$cat_ids = array();
	 	foreach($ranges as $cat_id)
	 	{
	 		$cat_ids = array_merge($cat_ids, array_keys(getCategories($cat_id)));
	 	}
	 	$cat_ids = array_unique($cat_ids);
	 	$where = ' AND g.cat_id ' . db_create_in($cat_ids);
	}
	elseif($favor['act_range'] == 2)
	{
	 	$ranges = explode(',', $favor['act_range_ext']);
		$where = ' AND ' . db_create_in($ranges, 'g.brand_id');
	}
	elseif($favor['act_range'] == 3)
	{
	 	$ranges = explode(',', $favor['act_range_ext']);
		$where = ' AND ' . db_create_in($ranges, 'g.id');
	}
	else
	{
		$where = '';
	}

	return M('carts')
	 ->alias('c')
	 ->join('goods g on c.good_id=g.id', 'inner')
	 ->where('c.is_gift = 0 and c.user_id = '. session('user_auth.uid') . $where)
	 ->getField('SUM(c.good_price * c.good_number)');
}

function favourable_range($favor)
{
	 $desc = '';

	 if($favor['act_range'] == 1)
	 {
	 	$ranges = explode(',', $favor['act_range_ext']);
	 	$cat_ids = array();
	 	foreach($ranges as $cat_id)
	 	{
	 		$cat_ids = array_merge($cat_ids, array_keys(getCategories($cat_id)));
	 	}
	 	$cat_ids = array_unique($cat_ids);
	 	$cat_arr = M('categories')->field('cat_name')->where(db_create_in($cat_ids, 'id'))->select();
	 	$tmp = [];
	 	foreach($cat_arr as $v)
	 	{
	 		$tmp[] = $v['cat_name'];
	 	}
	 	$desc = implode(',', $tmp);
	 }
	 elseif($favor['act_range'] == 2)
	 {
	 	$ranges = explode(',', $favor['act_range_ext']);
	 	$brand_arr = M('brands')->field('brand_name')->where(db_create_in($ranges, 'id'))->select();
	 	$tmp = [];
	 	foreach($brand_arr as $v)
	 	{
	 		$tmp[] = $v['brand_name'];
	 	}
	 	$desc = implode(',', $tmp);
	 }
	 elseif($favor['act_range'] == 3)
	 {
	 	$ranges = explode(',', $favor['act_range_ext']);
	 	$goods = M('goods')->field('good_name')->where(db_create_in($ranges, 'id'))->select();
	 	$tmp = [];
	 	foreach($goods as $v)
	 	{
	 		$tmp[] = $v['good_name'];
	 	}
	 	$desc = implode(',', $tmp);	
	 }
	 else
	 {
	 	$desc = '';
	 }
	 return $desc;
}

function compute_discount()
{
	$uid = session('user_auth.uid');
	$now = time();
	$favors = M('favourableActivity')
	  ->where("concat(',', act_recipient, ',') like ',$uid,' AND act_start_time <= $now AND act_end_time >= $now AND act_type IN ('1', '2')");
	$goods = M('carts')
	  ->alias('c')
	  ->field(['SUM(c.good_price * c.good_number) as subtotal', 'c.*', 'g.cat_id', 'g.brand_id'])
	  ->join('goods g on c.good_id=g.id', 'left')
	  ->where(['user_id' => $uid, 'rec_type' => 1, 'is_gift' => 0])
	  ->select();

	$discount = 0;
	$favors = [];
	foreach($favors as $key => $favor)
	{
		$total = 0;
		if($favor['act_range'] == 0)
		{
			foreach($goods as $good)
			{
				$total += $good['subtotal'];
			}
		}
		elseif ($favor['act_range'] == 1)
		{
			$id_list = array();
			$range_ids = explode(',', $favor['act_range_ext']);
			foreach ($range_ids as $key => $value) {
				$id_list = array_merge($id_list, array_keys(getCategories($value)));
			}
			$id_list = array_unique($id_list);
			foreach ($goods as  $good) {
				if(in_array($id_list, $good['cat_id']))
				{
					$total += $good['subtotal'];;
				}
			}
		}
		elseif ($favor['act_range'] == 2)
		{
			$range_ids = explode(',', $favor['act_range_ext']);
			foreach ($goods as  $good) {
				if(in_array($range_ids, $good['brand_id']))
				{
					$total += $good['subtotal'];;
				}
			}
		}
		elseif($favor['act_range'] == 3)
		{
			$range_ids = explode(',', $favor['act_range_ext']);
			foreach ($goods as  $good) {
				if(in_array($range_ids, $good['good_id']))
				{
					$total += $good['subtotal'];;
				}
			}
		}
		else
		{
			continue;
		}
		if($total > 0 && $total >= $favor['act_min_amount'] && ($total <= $favor['act_max_amount'] || $favor['act_max_amount'] ==0 ))
		{
			if($favor['act_type'] == 1)
			{
				$discount += $total - round($total * $favor['act_type_ext'] / 100, 2);
			}
			else
			{
				$discount += round($total - $favor['act_type_ext'], 2);
			}
			$favors[] = $favor['act_name'];
		}
		
	}
	return ['discount' => $discount, 'favor_name' => implode(',', $favors)];
}


