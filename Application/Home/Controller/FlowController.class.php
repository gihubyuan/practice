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


