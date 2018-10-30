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
		 if(IS_POST) {
		 		$is_insert = I('post.act') == 'act_insert' ? true : false;
				$data = I('post.');
		 		$attr_id_list = !isset($data['attr_id_list']) ? array() : $data['attr_id_list'];
		 		$attr_value_list = !isset($data['attr_value_list']) ? array() : $data['attr_value_list'];
		 		$data['type_id'] = empty($data['type_id']) ? 0 : intval($data['type_id']);
		 		$data['is_best'] = isset($data['is_best']) ? 1 : 0;
		 		$data['is_hot'] = isset($data['is_hot']) ? 1 : 0;
		 		$data['is_new'] = isset($data['is_new']) ? 1 : 0;
		 		$data['promotion_price'] = empty($data['promotion_price']) ? 0 : $data['promotion_price'];
		 		$data['promotion_start'] = empty($data['promotion_start']) ? 0 : $data['promotion_start'];
		 		$data['promotion_end'] = empty($data['promotion_end']) ? 0 : $data['promotion_end'];
		 		$data['number'] = empty($data['number']) ? 0 : $data['number'];
		 		$data['warn_number'] = empty($data['warn_number']) ? 0 : $data['warn_number'];
		 		$data['keywords'] = empty($data['keywords']) ? '' : $data['keywords'];
		 		$data['price'] = empty($data['price']) ? 0 : $data['price'];
		 		$data['weight'] = empty($data['weight']) ? 0 : $data['weight'];
		 		$good_id = empty($data['good_id']) ? 0 : $data['good_id'];
		 		$data['good_name_style'] = $data['name_style_color'] . '|' . $data['name_style_font'];
				unset($data['attr_id_list'],$data['__hash__'], $data['name_style_color'], $data['name_style_font'],$data['attr_value_list'], $data['good_id']);			 		
		 				 			 		
				if(empty($data['good_sn'])) {
					$data['good_sn'] = generate_good_sn();
				}
				if($data['weight_unit'] == 1) {
					$data['weight'] *= 0.001;
				}
				unset($data['weight_unit']);

				if(empty($data['promotion_price'])) {
					$data['promotion_start'] = 0;
					$data['promotion_end'] = 0;
				}

				if($is_insert) {
		 			if(!$good_id = M('goods')->add($data)) {
		 				$this->error("添加失败");
			 	 	  exit;
		 			} 
		 		}else {
		 			if(!M('goods')->where(['id'=>$good_id])->save($data)) {
		 				 $this->error('更新失败');
			 	 	   exit;
		 			}
		 		}

		 		if((isset($data['attr_id_list']) && isset($data['attr_value_list'])) || (empty($data['attr_id_list']) && empty($data['attr_value_list']))) {
			 	 	
				 	 $good_attr_list = array();
				 	 $rs = M('goodAttrs')->where(['good_id'=>$good_id])->select();
				 	 foreach($rs as $v) {
				 	 	 $good_attr_list[$v['attr_id']][$v['attr_value']] = array('good_attr_id'=> $v['id'], 'type'=>'delete');
				 	 }

			 	 		 $attr_list = array();
					 	 $rs = M('attribute')->where(['type_id'=>$data['type_id']])->select();
					 	 foreach($rs as $v) {
					 	 	 $attr_list[$v['id']] = $v['attr_index'];
					 	 }

			 	 		

						if(!empty($attr_value_list)) {
							$keywords = explode('|', trim($data['keywords'],'|'));
							$keywords = array_flip($keywords);
							if(isset($keywords[''])) {
								 unset($keywords['']);
							}
		 		 		  foreach($attr_id_list as $key => $attr_id) {
				 	 				$attr_value = $attr_value_list[$key];
				 	 				if(!empty(trim($attr_value))) {
					 	 				if(isset($good_attr_list[$attr_id][$attr_value])) {
					 	 					$good_attr_list[$attr_id][$attr_value]['type'] = 'update';
					 	 				}else {
					 	 					$good_attr_list[$attr_id][$attr_value]['type'] = 'insert';
					 	 				}
									 	if(!isset($keywords[$attr_value]) && $attr_list[$attr_id] == 1) {
								 	 	 	    $keywords[$attr_value] = "k$attr_id";
								 	  }
					 	 		 }
				 	 		}
				 	 		$keywords = implode('|',array_flip($keywords));
		 	        M('goods')->where(['id'=>$gid])->setField('keywords', $keywords);		
						}
			 	 				 	 		
				 	 foreach($good_attr_list as $attr_id => $attr_value_arr) {
				 	 	 foreach($attr_value_arr as $attr_value => $value) {
				 	 	 		if($value['type'] == 'update') {
				 	 	 			 M('goodAttrs')->save([
				 	 	 			 	 'attr_id' => $attr_id,
				 	 	 			 	 'attr_value' => $attr_value,
				 	 	 			 	 'good_id' => $good_id,
				 	 	 			 	 'id' => $value['good_attr_id']
				 	 	 			 ]);
				 	 	 		}elseif($value['type'] == 'insert') {
				 	 	 			 M('goodAttrs')->add([
				 	 	 			 	 'attr_id' => $attr_id,
				 	 	 			 	 'attr_value' => $attr_value,
				 	 	 			 	 'good_id' => $good_id
				 	 	 			 ]);
				 	 	 		}else {
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
		 	  if(!$is_add) {
		 	  	if(empty($good_id = I('good_id'))) {
			 	  	$this->error('错误');
			 	  	exit;
		 	 	}
		 	  }
		 		if($is_add) {
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
		 			 	 'price' => 0,
		 			 	 'promotion_price' => 0,
		 			 	 'promotion_start' => 0,
		 			 	 'promotion_end' => 0,
		 			 	 'good_id' => 0,
		 			 	 'name_style_color' => '',
		 			 	 'name_style_font' => ''
		 			 );		 
		 			 $this->assign('act', 'act_insert');
		 			 $this->assign('form_header', 'add');
		 		}

		 		if($is_update) {
		 			 $good = M('goods')->find($good_id);
		 			 $style = explode('|', $good['good_name_style']);
		 			 $good['name_style_color'] = $style[0];
		 			 $good['name_style_font'] = $style[1];
		 			 $this->assign('form_header', 'update');
		 			 $this->assign('act', 'act_update');
		 		}
		 	 $this->assign('good', $good);
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

	function show()
	{
		$id = I('request.id');
		$id = $id ? $id : 0; 
		if($id === 0) {
			$this->error('粗无');
			exit;
		}

		/*$good = M('goods')
		  ->alias('g')
			->join('', 'left')*/
		$this->assign('good', $good);
		$this->display();
	}

	function modifyAttr()
	{

		$id = I('request.id');
		$id = $id ? $id : 0; 
		if($id === 0) {
			$this->error('粗无');
			exit;
		}
		if(IS_POST) {
			$model = M('attribute');
			$model->create();
			if($model->save()) {
				$this->success('更新成功');
			}else {
				$this->error('更新失败');
			}
		}else {
			$attr = M('attribute')
			 ->find($id);
			$this->assign('attr', $attr);
			$this->display();
		}
	}
}