<?php

namespace Home\Model;

use Think\Model;

class CategoriesModel extends Model
{
	 protected $_validate = array(
	 	
	 );

	 protected $_auto = array(
	 );

	 function getAllCategories($nesting = true)
	 {
	 	  $cats = $this->where(['if_show'=>1])->order('view_order desc', 'id asc')->select();
	 	  if($nesting == true) {
	 	  	$cats = $this->unlimitedLevel($cats);
	 	  }
	 	  return $cats;
	 }

	 protected function unlimitedLevel($arr, $pid = 0)
	 {	
	 	  if(empty($arr)) {
	 	  	 return array();
	 	  }
	 	 	$tree = [];
	 	 	foreach($arr as $key => $value) {
	 	 		$cat_id = $value['id'];
	 	 		if($value['pid'] == $pid) {
	 	 			$tree[$cat_id] = $value;
	 	 			$tree[$cat_id]['crypt_id'] = mt_rand(1000, 9999) . base64_encode($cat_id);
	 	 			$tree[$cat_id]['child'] = $this->unlimitedLevel($arr, $cat_id);
	 	 		}
	 	 	}
	 	 	return $tree;
	 }

	 
	 public function findDecendant($arr, $id, $level = 0)
	 {
	 	  $cat = $this->where(['if_show'=>1])->find($id);
	 	 	if(empty($cat) || empty($arr)) return array();
	 	 	$tree = [];
	 	 	foreach($arr as $key => $value) {
	 	 		$cat_id = $value['id'];
	 	 		if($value['pid'] == $cat['id']) {
	 	 			$tree[$cat_id] = $value;
	 	 			$tree[$cat_id]['level'] = $level;
	 	 			$tree[$cat_id]['child'] = $this->findDecendant($arr, $value['id'], $level+1);
	 	 		}
	 	 	}
	 	 	return $tree;
	 }

}