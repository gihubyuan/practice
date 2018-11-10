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
            'last_update');
        $sort_field = I('get.sort_field') && in_array(strtolower(I('get.sort_field')), array('id', 'price', 'last_update')) ? trim(I('get.sort_field')) : $default_sort_field;
        $sort_order = I('get.sort_order') && in_array(strtolower(I('get.sort_order')), array('asc', 'desc')) ? I('get.sort_order') : $default_sort_order;

        // $brand_id = !empty(I('get.brand_id')) ? I('get.brand_id') : 0;
       






        $data = get_cate_goods($id, $sort_field, $sort_order);
        $this->assign('goods', $data['goods']);
        $this->assign('goods_num', $data['goods_num']);
        $this->assign('show', $data['show']);
        $this->assign('sort_field', $sort_field);
        $this->assign('sort_order', $sort_order);
        $this->assign('default', $default_sort_order);
        $this->assign('id', $id);
        $this->display();


    }
   

   
}