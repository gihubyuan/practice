<?php

function put_affiliate($data)
{
	unset($data['__hash__']);
	return M('systemConfig')->where(['config_name'=>'affiliate'])->save(['config_value'=>serialize($data)]);
}

function getCategories($type = true)
{
	static $arr2 = null;
	
	if($arr2 === null) {
		$arr2 = S('cat_pid_asc');
			if($arr2 == null) {
					$arr = M('goods')
						->alias('g')
						->field(['cat_id', 'count(cat_id)' => 'goods_num'])
						->where(['g.is_on_sale'=>1, 'g.deleted'=>0])
						->group('g.cat_id')
						->select();

					$arr2 = M('categories')
						  ->alias('c')
						  ->field(['c.id','c.cat_name','c.pid','c.if_show','c.view_order','count(cc.id)' => 'children'])
						  ->join('categories cc on c.id = cc.pid', 'left')
						  ->order('pid asc', 'c.id asc')
						  ->group('c.id')
						  ->select();

				  $temp = array();
					foreach($arr as $k => $value) {
						$temp[$value['cat_id']] = $value['goods_num'];
					}

					foreach($arr2 as $k => $value) {
						 $arr2[$k]['good_num'] = isset($temp[$value['id']]) ? $temp[$value['id']] : 0;
					}

					if(count($arr2) < 1000) {
						S('cat_pid_asc', $arr2);
					}
			}
	}

	if(empty($arr2)) {
		 return $type ? array() : '';
	}

	$cateSorts = categories_sort(0, $arr2);
	
	if($type) {
		return $cateSorts;
	}else {
		$html = '<select name="cat_id" class="form-control">';
		foreach($cateSorts as $key => $value) {
			$html .= '<option value="'.$value['id'].'">'.str_repeat('&nbsp;&nbsp;--', $value['level']).$value['name']. '</option>';
		}
		$html .='</select>';
		return $html;
	}	

}

function categories_sort($index_id, $list)
{
	static $cates;

	if(isset($cates[$index_id])) {
		return $cates[$index_id];
	}

	if(!isset($cates[0])) {
		 $data = S('cate_relation_sort');
		 if($data == null) {
			 	$level = $pid = 0;
				$level_arr =  $tree = $cat_id_arr = array();
				while(!empty($list)) {
					foreach($list as $key => $value) {
						$cat_id = $value['id'];
						if($level == 0 && $pid == 0 ) {
							 $tree[$cat_id] = $value;
							 $tree[$cat_id]['level'] = $level;
						   $tree[$cat_id]['name'] = $value['cat_name'];
						   unset($list[$key]);
							if($value['children'] == 0) {
								 continue;
							}

							$pid = $cat_id;
							$cat_id_arr[] = $cat_id;
							$level_arr[$cat_id] = ++$level;
							continue;
						}

						if($value['pid'] == $pid) {
							 $tree[$cat_id] = $value;
							 $tree[$cat_id]['level'] = $level;
						   $tree[$cat_id]['name'] = $value['cat_name'];
						   unset($list[$key]);

						   if($value['children'] > 0) {
						   	  $pid = $cat_id;
									$cat_id_arr[] = $cat_id;
							    $level_arr[$cat_id] = ++$level;
						   }
						}else if($value['pid'] > $pid) {
							break;
						}
					}

					if(!empty($cat_id_arr)) 
					{
						$pid = array_pop($cat_id_arr);
					}
					else 
					{
						  $level_arr = [];
						 	$pid = 0;
						 	$level = 0;
						 	$cat_id_arr = [];
						 	continue;
					}
						
					if($pid && isset($level_arr[$pid])) {
						 $level = $level_arr[$pid];
					}else {
						 $level = 0;
					}
				}
				 if(count($tree) <= 2000)
				 {
						S('cate_relation_sort', $tree);
				 }
			 }else {
			 	  $tree = $data;
			 }
		 	$cates[0] = $tree;
		}else {
			$tree = $cates[0];
		}
		if(!$index_id) {
			return $tree;
		}
}