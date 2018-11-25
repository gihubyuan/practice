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
		 $_attribute = $attribute;
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
			 $attr_value_list = $spec_list = $id_list = array();
			 foreach($attrs as $attr_id => $attr_item)
			 {
			 		$attr_value_list[$attr_id][] =  $attr_item[$key] . chr(9) . '';
			 	  $spec_list[$attr_id][] = true;
			 	  $id_list[$attr_id] = $attr_id;
 			 }

 			 $goods_attr_id = handle_goods_attr($good_id, $id_list, $spec_list, $attr_value_list);
 			 $good_attr = implode('|', $goods_attr_id);
 			 if(M('products')->where(['good_id'=>$good_id, 'good_attr'=>$good_attr])->find())
 			 {
 			 	  $this->error("重复错误");
 			 }

 			 if(!empty($value))
 			 {

 			 }

 			 
 			 // if(empty($value))
		}
	}
}