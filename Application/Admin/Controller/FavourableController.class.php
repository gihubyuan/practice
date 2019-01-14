<?php 
namespace Admin\Controller;

use Admin\Controller\PublicController;

class FavourableController extends PublicController
{

	public function index()
	{
		$this->favors = M('favourableActivity')->select();
		$this->display();
	}

	public function add()
	{
		if(IS_POST)
		{
			 $act_name = !empty(I('post.act_name')) ? I('post.act_name') : '';
			 $act_recipient = !empty(I('post.act_recipient')) ? implode(',', I('post.act_recipient')) : array();
			 $act_start_time = !empty(I('post.act_start_time')) ? (I('post.act_start_time') + time()) : 0;
			 $act_end_time = !empty(I('post.act_end_time')) ? (I('post.act_end_time') + time()): 0;
			 $act_range = !empty(I('post.act_range')) ? I('post.act_range') : 0;
			 $act_range_ext = !empty(I('post.act_range_ext')) ? implode(',', I('post.act_range_ext')) : '';
			 $act_min_amount = !empty(I('post.act_min_amount')) ? I('post.act_min_amount') : 0;
			 $act_max_amount = !empty(I('post.act_max_amount')) ? I('post.act_max_amount') : 0;
			 $act_type = !empty(I('post.act_type')) ? I('post.act_type') : 0;
			 $act_type_ext = !empty(I('post.act_type_ext')) ? I('post.act_type_ext') : 0;

			 if(empty($act_name) || empty($act_recipient))
			 {
			 	 $this->error('参数错误');
			 }

			 if($act_type == TYPE_GIFT)
			 {
			 	  $gift_id = I('post.gift_id');
			 	  $gift_price = I('post.gift_price');
			 	  $ext = serialize(array('gift_id'=>$gift_id, 'gift_price' => $gift_price));
			 }
			 else
			 {
			 	  $ext = serialize(array());
			 }

			$insert_id = M('favourableActivity')
			  ->add(array(
			  	'act_name' => $act_name,
			  	'act_recipient' => $act_recipient,
			  	'act_start_time' => $act_start_time,
			  	'act_end_time' => $act_end_time,
			  	'act_range' => $act_range,
			  	'act_range_ext' => $act_range_ext,
			  	'act_min_amount' => $act_min_amount,
			  	'act_max_amount' => $act_max_amount,
			  	'act_type' => $act_type, 
			  	'act_type_ext' => $act_type_ext, 
			  	'ext' => $ext, 
			  ));
			  if($insert_id)
			  {
			  	 $this->redirect('Favourable/index');
			  	 exit;
			  }
			  $this->error('添加失败');
		}
		else
		{
			$users = M('userRank')->field(['id', 'rank_name'])->select();
			$ranks = array();
			foreach ($users as $key => $value) {
				$ranks[$value['id']] = $value['rank_name'];
			}
			$this->assign('ranks', $ranks);

			$good_list = M('goods')->where(['is_on_sale' => 1])->getField('good_name,id');
			$this->assign('good_list', $good_list);
			$this->display();
		}
		
	}
}