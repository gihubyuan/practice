<?php
namespace Home\Controller;


class CategoryController extends \Home\Controller\HomeController
{
    public function index()
    {
        $id = I('id');

        if(empty($id)) {
            $this->error("错误");
            exit;
        }

        $default_sort_order = C('DEFAULT_SORT_ORDER') == 0 ? 'asc' :  (C('DEFAULT_SORT_ORDER') == 1 ? 'desc' : 
            'asc');
        $default_sort_field = C('DEFAULT_SORT_FIELD') == 0 ? 'id' :  (C('DEFAULT_SORT_FIELD') == 1 ? 'price' : 
            'update_time');
        $sort_field = I('get.sort') && in_array(strtolower(I('get.sort')), array('id', 'price', 'update_time')) ? trim(I('get.sort')) : $default_sort_field;
        $sort_order = I('get.order') && in_array(strtolower(I('get.order')), array('asc', 'desc')) ? I('get.order') : $default_sort_order;
       
        $data = get_cate_goods($id, $sort_field, $sort_order);
        $this->assign('goods', $data['goods']);
        $this->assign('goods_num', $data['goods_num']);
        $this->assign('show', $data['show']);
        $this->assign('norder', $sort_order == 'asc' ? 'desc' : 'asc');
        $this->assign('id', $id);
        $this->display();
    }
    

   
}