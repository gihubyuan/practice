<?php
namespace Admin\Controller;

use Admin\Controller\PublicController;

class GoodController extends PublicController
{
	public function index()
	{
		$goods = M('goods')->order('sort desc, id')->select();
		$this->assign('goods', $goods);
		$this->display();
	}

	public function brandHandle()
	{
		 $act = !empty(I('post.act')) ? I('post.act') : '';
		 if(empty($act) || !in_array($act, array('act_insert', 'act_update'))) {
		 	 $this->error("错误");
		 	 exit;
		 }
		 $is_insert = $act == 'act_insert' ? true: false;
		 $is_update = $act == 'act_update' ? true: false;
		 if($is_update) {
			 	 $id = I('post.id');
			 	 if(!$id) {
			 	 		$this->error("错误");
			 	  	exit;
			 	 }
		}
		 $data = I('post.');
		 unset($data['act']);
		 $data['sort_order'] = intval($data['sort_order']);

		 if($data['old_brand_name'] != $data['brand_name']) {
		 		if(M('brands')->where(['brand_name' => $data['brand_name']])->find()) {
				 	 $this->error("该品牌已存在");
				 	 exit;
		 	  }
		 }
			unset($data['old_brand_name']);
		 if($is_update) {
		 	 M('brands')->save($data);
		 }		

		 if($is_insert) {
		 		unset($data['id']);
		 		M('brands')->add($data);
		 }

		$this->redirect('brands');
		 
	}

	public function catHandle()
	{
		 $act = !empty(I('post.act')) ? I('post.act') : '';
		 if(empty($act) || !in_array($act, array('act_insert', 'act_update')))
		 {
		 	$this->error("错误");
		 	exit;
		 }
		 $is_insert = $act == 'act_insert' ? true: false;
		 $is_update = $act == 'act_update' ? true: false;
		 if($is_update)
		 {
		 	 $id = I('post.id');
		 	 if(!$id)
		 	 {
		 	 	$this->error("错误");
		 	    exit;
		 	 }
		}
		 $data = I('post.');

		 if($data['old_cat_name'] != $data['cat_name'])
		 { 
	 		 if(cat_exists($data['pid'], $data['cat_name'], $data['id']))
	 		 {
	 			 $this->error("该分类名已存在");
			 	 exit;
	 		 }
		 }

		 if(in_array($data['pid'], array_keys(getCategories($data['id'])))) {
 			 $this->error("父级分类选择错误");
		 	 exit;

		 }

 		 $data['view_order'] = !empty(intval($data['view_order'])) ? intval($data['view_order']) : 100;
		 $data['filter_attr'] = implode(',', array_unique(array_diff($data['attr_id'], array(0))));
		 unset($data['old_cat_name'],$data['act'], $data['attr_id']);
		 if($is_update)
		 {
		    M('categories')->save($data);
		 }		

		 if($is_insert)
		 {
	 		unset($data['id']);
	 		M('categories')->add($data);
		 }

		 clear_cache(['cate_relation_sort', 'cat_pid_asc']);
		 $this->redirect('cates');
		 
	}

	public function addBrand()
	{
		 $act = !empty(I('get.act')) ? I('get.act') : '';
		 if(empty($act) || !in_array($act, array('add', 'update'))) {
		 	 $this->error("错误");
		 	 exit;
		 }

		 $is_add = $act == 'add' ? true: false;
		 $is_update = $act == 'update' ? true: false;

		 if($is_update) {
		 	 $id = I('get.id');
		 	 if(!$id) {
		 	 		$this->error("错误");
		 	  	exit;
		 	 }
		 	 $form_header  = '更新';
		 	 $act = 'act_update';
			 $brand = M('brands')->find($id);
			 $brand['old_brand_name'] = $brand['brand_name'];
		 }

		 if(empty($brand)) {
		 	  $brand = [
		 	  	'id' => 0,
		 	  	'brand_name' => '',
		 	  	'brand_desc' => '',
		 	  	'brand_url' => '',
		 	  	'sort_order' => 100,
		 	  	'if_show' => 1,
		 	  	'old_brand_name' => ''
		 	  ];
		 }

		 if($is_add) {
		 	  $act = 'act_insert';
		 	 	$form_header  = '添加';
		 }

		 $this->assign('form_header', $form_header);
		 $this->assign('brand', $brand);
		 $this->assign('act', $act);
		 $this->display();
	}

	public function brands()
	{
		$brands = M('brands')->order('sort_order desc, id asc')->select();
		$this->assign('brands', $brands);
		$this->display();
	}


	public function addCate()
	{
		 $act = !empty(I('get.act')) ? I('get.act') : '';
		 if(empty($act) || !in_array($act, array('add', 'update'))) {
		 	 $this->error("错误");
		 	 exit;
		 }
		 $attrs = getAllAttrs();
		 $this->assign('attrs', $attrs);

		 $is_add = $act == 'add' ? true: false;
		 $is_update = $act == 'update' ? true: false;


		 if($is_update) {
		 	 $id = I('get.id');
		 	 if(!$id) {
		 	 		$this->error("错误");
		 	  	exit;
		 	 }
		 	 $form_header  = '更新';
		 	 $act = 'act_update';
			 $cat = M('categories')->find($id);
			 $cat['old_cat_name'] = $cat['cat_name'];
		 
			 if($cat['filter_attr']) {
			 	$filter_attr = explode(',', $cat['filter_attr']);
			 	$filter_attr_list = array();
			 	foreach($filter_attr as $k => $attr_id) {
			 		$attr_cat_id = M('attribute')->where(['id'=>$attr_id])->getField('type_id');
			 		$filter_attr_list[$k]['type_list'] = getTypeList($attr_cat_id);
			 		$filter_attr_list[$k]['filter_attr'] = $attr_id;
			 		foreach($attrs[$attr_cat_id] as $val) {
			 			$filter_attr_list[$k]['option'][key($val)] =  current($val);
			 		}
			 	}
			 	$this->assign('filter_attr_list',  $filter_attr_list);

			 }else {
			 	   $attr_cat_id  = 0;
			 }
		 }

		 if(empty($cat)) {
		 	  $cat = [
		 	  	'id' => 0,
		 	  	'cat_name' => '',
		 	  	'if_show' => 1,
		 	  	'view_order' => 100,
		 	  	'old_cat_name' => '',
		 	  	'pid' => 0,
		 	  	'unit' => ''
		 	  ];
		 }

		 if($is_add) {
		 	 $act = 'act_insert';
		 	 $form_header  = '添加';
			 $attr_cat_id  = 0;

		 }

		 $this->assign('attr_cat_id', $attr_cat_id);
		 $this->assign('type_list', getTypeList(0));
		 $this->assign('form_header', $form_header);
		 $this->assign('pcat', getCategories(0, false, $cat['pid']));
		 $this->assign('cat', $cat);
		 $this->assign('act', $act);

		 $this->display();
	}
		
	public function oneAttr()
	{
		$id = I('get.id');
		if(!$id) {
			$this->redirect('Good/attr');
			exit;
		}

		$attr=M('attribute')
			->alias('a')
			->field(['a.*', 'gt.attr_groups'])
			->join('good_attr_types gt on a.type_id=gt.id', 'left')
			->where(['a.id'=>$id])
			->find();
		$this->groups = !empty($attr['attr_groups']) ? explode("\n", str_replace("\r", "", $attr['attr_groups'])) : array();
		$this->assign('attr', $attr);
		$this->display();
	}

	public function attrHandle()
	{
		$data = I('post.');
		$data['status'] = !isset($data['status']) ? 1 : $data['status'];

		if($data['attribute_name'] != $data['old_attr_name']) {
			if(M('attribute')->where(['attribute_name'=>$data['attribute_name'], ])->find()) {
				$this->error("重名!");
				exit;
			}
		}
		unset($data['old_attr_name']);
		M('attribute')->save($data);
		$this->redirect('Good/attr');
	}

	public function oneType()
	{
		$id = I('get.id');
		if(!$id || (!$type=M('goodAttrTypes')->find($id))) {
			$this->redirect('Good/attr');
		}

		$this->assign('type', $type);
		$this->display();
	}

	public function typeHandle()
	{
		$data = I('post.');
		$data['sort'] = !empty($data['sort']) ? $data['sort'] : 50;
		if($data['type_name'] != $data['old_type_name']) {
			if(M('goodAttrTypes')->where(['type_name'=>$data['type_name']])->find()) {
				$this->error("重名!");
				exit;
			}
		}

		$attr_groups = $data['attr_groups'];
		unset($data['old_type_name'], $data['attr_groups']);
		if(M('goodAttrTypes')->save($data))
		{
			$new_groups = explode("\n", str_replace("\r", '', $attr_groups));
			$old_groups = M('goodAttrTypes')->where(['id'=>$data['id']])->getField('attr_groups');
			$old_groups = explode("\n", str_replace("\r", '', $old_groups));
			foreach($old_groups as $grp)
			{
				$found = array_search($grp, $new_groups);

				if($found === false || $found === null)
				{
					M('attribute')->where(['attr_group'=>$grp, 'type_id'=>$data['id']])->setField(['attr_group'=>'']);
				}
				
			}
		}
		else
		{
			$this->error("更新失败");
		}
		
		$this->redirect('Good/attr');
	}

	public function attrsOfType()
	{
		$id = I('id');
		if(!$id || (!$type=M('goodAttrTypes')->find($id))) {
			$this->redirect('Good/attr');
		}
		$this->attrs = M('attribute')
		    ->alias('a')
		    ->field(['a.id', 'a.attribute_name', 'gt.type_name'])
		    ->join('good_attr_types gt on a.type_id=gt.id', 'left')
			->where(['a.type_id'=>$id])
			->order('a.type_id,a.id')
			->select();
		$this->display();
	}

	public function attr()
	{
	   $types =  M('goodAttrTypes')
	     ->alias('gt')
	     ->join('attribute a on gt.id=a.type_id', 'left')
		   ->field(array('type_name', 'gt.id', 'count(gt.id)'=>'num'))
		   ->where(['gt.status'=>1, 'a.status'=>1])
		   ->group('gt.id')
		   ->select();
		 $this->assign('types', $types);
		 $this->display();
	}

	public function cates()
	{		
		$this->assign('cates', getCategories());
		$this->display();
	}


	function goodHandle()
	{
		 if(IS_POST) 
		 {
	 		if(!empty($_FILES['good_img']['name']))
	 		{
		 		if($_FILES['good_img']['error'] > 0)
		 		{
		 			 if($_FILES['good_img']['error'] == 1)
		 			 {
		 			 	  $upload_max_filesize = ini_get('upload_max_filesize');
		 			 		$this->error(sprintf('超过%s规定大小', $upload_max_filesize));
		 			 }

		 			 if($_FILES['good_img']['error'] == 2)
		 			 {
		 			 	  $upload_max_filesize = I('post.MAX_FILE_SIZE');
		 			 		$this->error(sprintf('超过%s规定大小', $upload_max_filesize));
		 			 }
		 		}
		 		$stat = upload_image($_FILES['good_img'],'aa.jpg',  $_FILES['good_img']);
		 		if($stat['has_error'])
		 		{
		 			$this->error($stat['error']);
		 		}
	 		}
	 		unset($_POST['MAX_FILE_SIZE']);
	 		$is_insert = I('post.act') == 'act_insert' ? true : false;
			$data = I('post.');
	 		$attr_id_list = !isset($data['attr_id_list']) ? array() : $data['attr_id_list'];
	 		$attr_value_list = !isset($data['attr_value_list']) ? array() : $data['attr_value_list'];
	 		$attr_price_list = !isset($data['attr_price_list']) ? array() : $data['attr_price_list'];
	 		$cat_extended_id = !empty($data['cat_extended_id']) ? array_filter(array_unique($data['cat_extended_id'])) : array();
	 		$data['type_id'] = empty($data['type_id']) ? 0 : intval($data['type_id']);
	 		$data['is_best'] = isset($data['is_best']) ? 1 : 0;
	 		$data['is_hot'] = isset($data['is_hot']) ? 1 : 0;
	 		$data['is_new'] = isset($data['is_new']) ? 1 : 0;
	 		$data['is_new'] = isset($data['is_shipping']) ? 1 : 0;
	 		$data['integral'] = empty($data['integral']) ? 0 : intval($data['integral']);
	 		$data['give_integral'] = empty($data['give_integral']) ? 0 : intval($data['give_integral']);
	 		$data['rank_integral'] = empty($data['rank_integral']) ? 0 : intval($data['rank_integral']);
	 		$data['promotion_price'] = empty($data['promotion_price']) ? 0 : $data['promotion_price'];
	 		$data['promotion_start'] = empty($data['promotion_start']) ? 0 : $data['promotion_start'];
	 		$data['promotion_end'] = empty($data['promotion_end']) ? 0 : $data['promotion_end'];
	 		$data['number'] = empty($data['number']) ? 0 : $data['number'];
	 		$data['warn_number'] = empty($data['warn_number']) ? 0 : $data['warn_number'];
	 		$data['keywords'] = empty($data['keywords']) ? '' : $data['keywords'];
	 		$data['shop_price'] = empty($data['shop_price']) ? 0 : intval($data['shop_price']);
	 		$data['market_price'] = empty($data['market_price']) ? 0 : intval($data['market_price']);
	 		$data['weight'] = empty($data['weight']) ? 0 : $data['weight'];
	 		$data['last_update'] = time();
	 		$good_id = empty($data['good_id']) ? 0 : $data['good_id'];
	 		$user_price = isset($data['user_price']) ? $data['user_price'] : array();
	 		$rank_ids = isset($data['rank_id']) ?  $data['rank_id'] : array();
	 		$volume_number = isset($data['volume_number']) ?  $data['volume_number'] : array();
	 		$volume_price = isset($data['volume_price']) ?  $data['volume_price'] : array();
	 		$data['good_img'] = !isset($stat['path']) ? '': $stat['path'];
	 		$data['good_name_style'] = $data['name_style_color'] . '|' . $data['name_style_font'];
			unset($data['attr_id_list'],$data['__hash__'], $data['name_style_color'], $data['name_style_font'],$data['attr_value_list'], $data['good_id'], $data['cat_extended_id'],$data['user_price'], $data['rank_id'], $data['volume_number'], $data['volume_price']);			 		
		 		
	 		if(empty($data['brand_id'])) 
	 		{
	 			 unset($data['brand_id']);
	 		}		 			 		
			if(empty($data['good_sn'])) 
			{
				$data['good_sn'] = generate_good_sn();
			}
			if($data['weight_unit'] == 1) 
			{
				$data['weight'] *= 0.001;
			}
			unset($data['weight_unit']);

			if(empty($data['promotion_price'])) 
			{
				$data['promotion_start'] = 0;
				$data['promotion_end'] = 0;
			}

			if(!empty($volume_number) && !empty($volume_price))
	 		{
	 			  $counts = array_count_values($volume_number);
	 			  foreach($counts as $count)
	 			  {
	 			  	 if($count > 1)
	 			  	 {
	 			  		$this->error("重复了volume_number");
	 			  		exit;
	 			  	 }
	 			  }
	 		}

			if($is_insert) 
			{
				 $data['add_time'] = time();
	 			 if(!$good_id = M('goods')->add($data)) 
	 			 {
	 				 $this->error("添加失败");
		 	 	     exit;
	 			 }
	 			 foreach($cat_extended_id as $extend_id) 
	 			 {
	 				 M('goodExtendedCats')-> add(['good_id'=>$good_id, 'cat_id'=>$extend_id]);
	 			 }
	 		}
	 		else 
	 		{
	 			if(!M('goods')->where(['id'=>$good_id])->save($data)) 
	 			{
	 			  $this->error('更新失败');
		 	 	   exit;
	 			}
	 			update_extended_goods($good_id, $cat_extended_id);
	 		}

	 		if(!empty($user_price) && !empty($rank_ids))
	 		{
	 			 handle_member_price($good_id, $user_price, $rank_ids);
	 		}

			if(!empty($volume_number) && !empty($volume_price))
		 	{
		 		 handle_volume_price($good_id, $volume_number, $volume_price);
		 	}

	 		if((isset($data['attr_id_list']) && isset($data['attr_value_list'])) || (empty($data['attr_id_list']) && empty($data['attr_value_list']))) 
	 		{
		 	 	
			 	 $good_attr_list = array();
			 	 $rs = M('goodAttrs')->where(['good_id'=>$good_id])->select();
			 	 foreach($rs as $v) 
			 	 {
			 	 	 $good_attr_list[$v['attr_id']][$v['attr_value']] = array('good_attr_id'=> $v['id'], 'type'=>'delete');
			 	 }

	 	 		 $attr_list = array();
			 	 $rs = M('attribute')->where(['type_id'=>$data['type_id']])->select();
			 	 foreach($rs as $v) 
			 	 {
			 	 	 $attr_list[$v['id']] = $v['attr_index'];
			 	 }

				if(!empty($attr_value_list)) 
				{
						$keywords = explode('|', trim($data['keywords'],'|'));
						$keywords = array_flip($keywords);

						if(isset($keywords[''])) 
						{
							 unset($keywords['']);
						}
		 		 		foreach($attr_id_list as $key => $attr_id) 
		 		 		{
			 	 				$attr_value = $attr_value_list[$key];
			 	 				$attr_price = $attr_price_list[$key];
			 	 				if(!empty($attr_value))
			 	 				{
				 	 					if(isset($good_attr_list[$attr_id][$attr_value]))
					 	 				{
					 	 					$good_attr_list[$attr_id][$attr_value]['type'] = 'update';
					 	 				}
					 	 				else 
					 	 				{
					 	 					$good_attr_list[$attr_id][$attr_value]['type'] = 'insert';
					 	 				}
					 	 				$good_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
			 	 				}
		 	 				
							 	if(!isset($keywords[$attr_value]) && $attr_list[$attr_id] == 1)
							 	{
						 	 	 	 $keywords[$attr_value] = "k$attr_id";
						 	  }
				 	 	 }

			 	 		     $keywords = implode('|',array_flip($keywords));
	 	       			 M('goods')->where(['id'=>$gid])->setField('keywords', $keywords);		
				}
		 	 				 	 		
			 	 foreach($good_attr_list as $attr_id => $attr_value_arr) 
			 	 {
				 	 	 foreach($attr_value_arr as $attr_value => $value) 
				 	 	 {
					 	 	 		if($value['type'] == 'update') 
					 	 	 		{
					 	 	 			 M('goodAttrs')->save([
					 	 	 			 	 'attr_id' => $attr_id,
					 	 	 			 	 'attr_value' => $attr_value,
					 	 	 			 	 'good_id' => $good_id,
					 	 	 			 	 'id' => $value['good_attr_id'],
					 	 	 			 	 'attr_price' => $value['attr_price']
					 	 	 			 ]);
					 	 	 		}
					 	 	 		elseif($value['type'] == 'insert') 
					 	 	 		{
					 	 	 			 M('goodAttrs')->add([
					 	 	 			 	 'attr_id' => $attr_id,
					 	 	 			 	 'attr_value' => $attr_value,
					 	 	 			 	 'good_id' => $good_id,
					 	 	 			 	 'attr_price' => $value['attr_price']
					 	 	 			 ]);
					 	 	 		}
					 	 	 		else 
					 	 	 		{
					 	 	 			  M('goodAttrs')->delete($value['good_attr_id']);
					 	 	 		}
				 	 	 }
			 	 }
		 	}
	 	  $this->redirect('index');
		 }
	}


	function addGood()
	{	
		 	  $is_add = I('get.act') == 'add' ? true : false;
		 	  $is_update = I('get.act') == 'update' ? true : false;
		 	  if(!$is_add)
		 	  {
		 	  	if(empty($good_id = I('good_id')))
		 	  	{
			 	  	$this->error('错误');
			 	  	exit;
		 	 		}
		 	  }
		 		if($is_add)
		 		{
		 			 $good = array(
		 			 	 'good_name' => '',
		 			 	 'good_sn' => '',
		 			 	 'type_id' => 0,
		 			 	 'keywords' => '',
		 			 	 'is_hot' => 0,
		 			 	 'is_new' => 0,
		 			 	 'is_best' => 0,
		 			 	 'number' => 0,
		 			 	 'warn_number' => 0,
		 			 	 'weight' => 0,
		 			 	 'shop_price' => 0,
		 			 	 'market_price' => 0,
		 			 	 'promotion_price' => 0,
		 			 	 'promotion_start' => 0,
		 			 	 'promotion_end' => 0,
		 			 	 'id' => 0,
		 			 	 'name_style_color' => '',
		 			 	 'name_style_font' => '',
		 			 	 'brand_id' => 0,
		 			 	 'give_integral' => 0,
		 			 	 'rank_integral' => 0,
		 			 	 'integral' => 0,
		 			 	 'good_desc' => '',
		 			 );		 
		 			 $this->assign('act', 'act_insert');
		 			 $this->assign('extend_cats', [getCategories(0, false)]);
		 			 $this->assign('cates', getCategories(0, false));
		 			 $this->assign('form_header', 'add');
		 		}

		 		if($is_update)
		 		{
			 			 $good = M('goods')->find($good_id);
			 			 $style = explode('|', $good['good_name_style']);
			 			 $good['name_style_color'] = $style[0];
			 			 $good['name_style_font'] = $style[1];
			 			 $extend_cat_ids = M('goodExtendedCats')->where(['good_id'=>$good_id])->getField('cat_id', true);
			 			 $extend_cats = [];
			 			 foreach($extend_cat_ids as $cat_id)
			 			 {
			 			 	 $extend_cats[] = getCategories(0, false, $cat_id);
			 			 }

			 			 $this->assign('extend_cats', $extend_cats);
			 			 $this->assign('form_header', 'update');
			 			 $this->assign('cates', getCategories(0, false, $good['cat_id']));
			 			 $this->assign('act', 'act_update');
		 		}

		 	 $this->rank_list = M('userRank')->field(['id', 'rank_name'])->select();
		 	 if($is_update)
		 	 {
			 		 $memPrices = M('memberPrice')->field(['user_rank', 'member_price'])->where(['good_id'=>$good_id])->select();
			 		 $mPrices = array();
			 		 foreach($memPrices as $price)
			 		 {
			 		 	 $mPrices[$price['user_rank']] = $price['member_price'];
			 		 }
			 		 $this->assign('mPrices', $mPrices);
		 	 
			 		 $this->volumes = M('volumePrice')->field(['volume_number', 'volume_price'])->where(['price_type'=>1, 'good_id'=>$good_id])->select();
		 	 }

		 	 $this->assign('good', $good);
		 	 $brands = M('brands')->field(['id', 'brand_name'])->where(['if_show'=>1])->order('sort_order desc, id')->select();
		 	 $this->assign('brands', $brands);
			 $options = M('goodAttrTypes')->where(['status'=>1])->select();
			 $this->assign('options', $options);

			 $this->display();
	}

	public function getAttrHtml()
	{
		$type_id = I('get.type_id');
		$good_id = I('get.good_id');
		$type_id = $type_id ? $type_id : 0;	
		$good_id = $good_id ? $good_id : 0;	
		$this->ajaxReturn(['status'=>1, 'data' => build_attr_html($type_id, $good_id)]);
	}

	public function attrList()
	{
	   $attrlist =  M('goodAttrTypes')
	   			->alias('gt')
	   			->field(array('gt.id'=>'gtid', 'a.id' => 'gaid','gt.type_name', 'a.attribute_name', 'insert_type_id','insert_type_values'))
					->join('attribute a on gt.id =a.type_id', 'left')		   
					->where(['gt.status'=>1])->select();
		 $this->assign('attrlist', $attrlist);
		 $this->display();
	}

	function modifyAttr()
	{

			$id = I('request.id');
			$id = $id ? $id : 0; 
			if($id === 0)
			{
				$this->error('粗无');
				exit;
			}
			if(IS_POST)
			{
					$model = M('attribute');
					$model->create();
					if($model->save())
					{
							$this->success('更新成功');
					}
					else
					{
							$this->error('更新失败');
					}
			}
			else
			{
				$attr = M('attribute')->find($id);
				$this->assign('attr', $attr);
				$this->display();
			}
	}
}



function getAllAttrs()
{
	$attrs = M('attribute')
	->alias('a')
	->field(['a.id', 'a.type_id', 'a.attribute_name'])
	->join('good_attr_types at on a.type_id=at.id','inner')
	->where(['a.status'=>1, 'at.status'=>1])
	->select();
	$list = array();

	foreach($attrs as $k => $val)
	{
		$list[$val['type_id']][] = [$val['id']=>$val['attribute_name']];
	}
	return $list;
}

function getTypeList($selected)
{
	$types = M('goodAttrTypes')->field(['type_name', 'id'])->where(['status'=>1])->select();
	$options = '';
	foreach($types as $type)
	{
		$options .= '<option value="'.$type['id'].'" '.($selected == $type['id'] ? 'selected' : '').'>'.$type['type_name'].'</option>';
	}
	return $options;
}

function cat_exists($pid, $name, $cat_id)
{
	return !empty(M('categories')->where(['pid'=>$pid, 'cat_name'=>$name, 'id'=>['neq'=>$cat_id]])->find()) ? true : false;
}

