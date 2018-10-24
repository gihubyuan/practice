<?php
namespace Home\Widget;

use Think\Controller;

class CateWidget extends Controller
{
	function navs()
	{
		$navs = get_navs();
    $this->assign('navs', $navs);
		$this->display('Cate/navs');
	}

	function sidebar($cat_id)
	{
		$lists = array(
			array('id'=> 1, 'cat_name'=>'商品', 'url'=>'Good/index'), 
			array('id'=> 2, 'cat_name'=>'属性类别', 'url'=>'Good/attr'), 
			array('id'=> 3, 'cat_name'=>'属性列表', 'url'=>'Good/attrList')
		);
		$this->assign('sidebar_lists', $lists);
		$this->assign('current', $cat_id);
		$this->display('Cate/sidebar');
	} 
}