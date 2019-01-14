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
	  $cart_items = M('carts')->where(['user_id'=>$user_id])->select();
	  foreach ($cart_items as $key => $value)
	  {
	  	if($value['parent_id'] > 0)
	  	{
	  		$cart_items[$key]['good_name'] .= ' [配件]';
	  	}
	  	$cart_items[$key]['total'] = sprintf('￥%d元', round(floatval($value['good_number'] * $value['good_price']),2));
	  }
	  $good_ids = M('carts')->field(['good_id'])->where(['user_id'=>$user_id])->select();

	  foreach($good_ids as $k => $good_id)
	  {
	  	 $good_ids[$k] = $good_id['good_id'];
	  }
	  $group_goods =  get_fittings($good_ids);
	  $this->assign('group_goods', $group_goods);

	  $good_price_total = M('carts')->getField('SUM(good_price * good_number)');
	  $market_price_total = M('carts')->getField('SUM(market_price * good_number)');
	  $saving = $market_price_total - $good_price_total;
	  $saving_ratio = round($saving / $market_price_total * 100, 2) ;
	  $this->assign('saving_mark', sprintf('您的购买价格比市场价节省%d元, 优惠率%f%%', $saving, $saving_ratio));
	  $this->assign('cart_items', $cart_items);
	  $this->display();
	}

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



