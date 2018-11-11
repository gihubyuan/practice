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

        $brand_id = !empty(I('get.brand_id')) ? I('get.brand_id') : 0;
       

        $brands = M('brands')
             ->alias('b')
             ->field(['b.id', 'b.brand_name', 'count(*)'=>'num'])
             ->join('goods g on b.id=g.brand_id', 'inner')
             ->join('good_extended_cats gc on g.id=gc.good_id', 'left')
             ->where(['b.if_show'=>1,'g.is_alone_sale'=>1, 'g.is_on_sale'=>1,'g.deleted'=>0,'_string'=>( getChildren($id, 'g.cat_id') . ' or ' .  getChildren($id, 'gc.cat_id'))])
             ->group('b.id')
             ->having('num > 0')
             ->order('b.sort_order desc, b.id')
             ->select();
        if(!empty($brands)) {
            $brand_arr = [];
            foreach($brands as $key => $brand) {
                $temp_key = $key + 1;
                $brand_arr[$temp_key]['brand_name'] = $brand['brand_name'];
                $brand_arr[$temp_key]['selected'] = $brand['id'] == $brand_id ? 1 : 0;
                $brand_arr[$temp_key]['url'] = build_uri('index', $id, $sort_field, $sort_order, $brand['id']);
            }
            $brand_arr[0]['brand_name'] = '全部';
            $brand_arr[0]['selected'] = $brand_id == 0 ? 1 : 0;
            $brand_arr[0]['url'] = build_uri('index', $id, $sort_field, $sort_order, 0);
            $this->assign('brand_arr', $brand_arr);
        }
        
       
        
             
      /*  if($brand_id > 0) {
        
        }*/





        // getChildren($id);
        $data = get_cate_goods($id, $sort_field, $sort_order, $brand_id);
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