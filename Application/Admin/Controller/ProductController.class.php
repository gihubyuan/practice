<?php
namespace Admin\Controller;

use Admin\Controller\PublicController;

class ProductController extends PublicController
{
	public function index()
	{
		$id = I('id');
		if(!$id || (!$good = M('goods')->find($id)))
		{
			$this->redirect('Index/index');
		}

		$good_attrs = M('goodAttrs')
		 ->alias('ga')
		 ->field(['attribute_name', 'ga.attr_id', 'ga.attr_value'])
		 ->join('attribute a on ga.attr_id=a.id', 'left')
		 ->where(['a.input_value_type'=>2, 'ga.good_id'=>$id])
		 ->order('ga.attr_id')
		 ->select();

		 $attribute = array();
		 foreach($good_attrs as $attr)
		 {
		 	 $attribute[$attr['attr_id']]['name'] = $attr['attribute_name'];
		 	 $attribute[$attr['attr_id']]['attr_values'][] = $attr['attr_value'];
		 }
		$this->assign('colspans', count($attribute) + 2);
		 
		$products = product_list($id);
		$_attribute = $attr_list = array();
		foreach($products as $product)
		{
			foreach($product['attrs'] as $k =>$val)
			{
				$attr_list[$k][] = $val;
			}
		}

		foreach($attr_list as $attr_item)
		{
			foreach($attribute as $attr_id => $attr)
			{
				$diff = array_diff($attr_item, $attr['attr_values']);
				if(empty($diff))
				{
					$_attribute[$attr_id] = $attr;
				}
			}
		}
		if(empty($_attribute))$_attribute=$attribute;
		$this->assign('products', $products);
		$this->assign('attribute', $_attribute);
		$this->assign('good_id', $id);
		$this->assign('good_name', sprintf('商品名称: %s', $good['good_name']));
		$this->assign('sn', sprintf('序列号: %s', $good['good_sn']));
		$this->display();
	}

	function productAdd()
	{
		$good_id = I('post.good_id');
		$product_sn = I('post.product_sn');
		$product_number = I('post.product_number');
		$attrs = I('post.attr');
		foreach($product_sn as $key => $value)
		{
			 $product_number[$key] = !empty($product_number[$key]) ? intval($product_number[$key]) : (C('USE_STORAGE') > 0 ? C('DEFAULT_STORAGE') : 0);
			 $attr_value_list = $spec_list = $id_list = array();
			 foreach($attrs as $attr_id => $attr_item)
			 {
			 	  if(!$attr_item[$key])
			 	  {
			 	  	continue 2;
			 	  }
			 	  $attr_value_list[$attr_id] =  $attr_item[$key] . chr(9) . '';
			 	  $spec_list[$attr_id] = true;
			 	  $id_list[$attr_id] = $attr_id;
 			 }

 			 $goods_attr_id = handle_goods_attr($good_id, $id_list, $spec_list, $attr_value_list);
 			 // $goods_attr_id = sortGoodAttrId($goods_attr_id);
 			 $good_attr = implode('|', $goods_attr_id);
 			 if(M('products')->where(['good_id'=>$good_id, 'good_attr'=>$good_attr])->count())
 			 {
 			 	  $this->error("重复错误");
 			 }

 			 if(!empty($value))
 			 {
 			 	 if(M('goods')->where(['good_id'=>$good_id, 'good_sn'=>$value])->count())
 			 	 {
 			 	 	$this->error("good_sn重复");
 			 	 }
 			 	 if(M('products')->where(['good_id'=>$good_id, 'product_sn'=>$value])->count())
 			 	 {
 			 	 	$this->error("product_sn重复");
 			 	 }
 			 }

 			$product_id = M('products')->add([
 			 	'product_sn' => $value,
 			 	'good_id' => $good_id,
 			 	'product_number' => $product_number[$key],
 			 	'good_attr' => $good_attr
 			 ]);

 			if(!$product_id)
 			{
 				continue;
 			}

 			if(empty($value))
 			{
 				$good_sn = M('goods')->where(['id'=>$good_id])->getField('good_sn');
 				M('products')->save([
 					'product_id' => $product_id,
 					'product_sn' => $good_sn . 'g_p' . $product_id
 				]);
 			}

 			$storage = M('products')->where(['good_id'=>$good_id])->getField('SUM(product_number)');
 			M('goods')->save([
 				'id' => $good_id,
 				'number' => $storage
 			]);
		}
	}
}