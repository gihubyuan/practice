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
        $filter_str = empty(I('get.filter_attr')) ? '' : I('get.filter_attr');
        $filter_str = preg_match('/^[\.\d]+$/', $filter_str) ? $filter_str : '';
        $filter_attrs = empty($filter_str) ?  '' : explode('.', $filter_str); 
        $brand_id = !empty(I('get.brand_id')) ? I('get.brand_id') : 0;
       
        $children = get_children($id);

        $brands = M('brands')
             ->alias('b')
             ->field(['b.id', 'b.brand_name', 'count(*)'=>'num'])
             ->join('goods g on b.id=g.brand_id', 'inner')
             ->join('good_extended_cats gc on g.id=gc.good_id', 'left')
             ->where(['b.if_show'=>1,'g.is_alone_sale'=>1, 'g.is_on_sale'=>1,'g.deleted'=>0, '_string'=> 'g.cat_id ' . $children . ' or gc.cat_id ' .  $children])
             ->group('b.id')
             ->having('num > 0')
             ->order('b.sort_order desc, b.id')
             ->select();
             
        if(!empty($brands)) {
            foreach($brands as $key => $brand) {
                $temp_key = $key + 1;
                $brands[$temp_key]['brand_name'] = $brand['brand_name'];
                $brands[$temp_key]['selected'] = $brand['id'] == $brand_id ? 1 : 0;
                $brands[$temp_key]['url'] = build_uri('index', $id, $sort_field, $sort_order, $brand['id'], $filter_str);
            }
            $brands[0]['brand_name'] = '全部';
            $brands[0]['selected'] = $brand_id == 0 ? 1 : 0;
            $brands[0]['url'] = build_uri('index', $id, $sort_field, $sort_order, 0, $filter_str);
            $this->assign('brand_arr', $brands);
        }
        
       
        $cat_info = M('categories')->find($id);        
   
        if($cat_info['filter_attr'] > 0)
        {
            $filter_ids = explode(',', $cat_info['filter_attr']);
             $filter_attr_list = [];
            foreach($filter_ids as $key => $filter_id)
            {
                $attr_name = M('attribute')
                  ->alias('a')
                  ->field(['attribute_name'])
                  ->join('good_attrs ga on a.id=ga.attr_id', 'inner')
                  ->join('goods g on ga.good_id=g.id', 'inner')
                  ->where(['g.is_alone_sale'=>1, 'g.is_on_sale'=>1,'g.deleted'=>0, 
                    '_string'=> 'g.cat_id ' . $children . ' or g.id ' . get_extended_goods($children),
                    'a.id' => $filter_id])
                  ->find();

                if($attr_name)
                {
                    $attr_name = $attr_name['attribute_name'];
                    $attr_list = M('goodAttrs')
                     ->alias('ga')
                     ->field(['attr_value', 'MIN(ga.id)'=>'good_attr_id'])
                     ->join('goods g on ga.good_id=g.id', 'inner')
                     ->where(['ga.attr_id'=>$filter_id, 'g.is_alone_sale'=>1, 'g.is_on_sale'=>1,'g.deleted'=>0, 
                    '_string'=> 'g.cat_id ' . $children . ' or g.id ' . get_extended_goods($children)])
                     ->group('ga.attr_value')
                     ->select();

                     $temp_url = [];
                     for($i=0, $cnt=count($filter_ids); $i<$cnt; $i++)
                     {
                        $temp_url[$i] = empty($filter_attrs[$i]) ? '0' : $filter_attrs[$i];
                     }
                     $temp_url[$key] = '0';
                     $filter_attr_list[$key]['filter_attr_name'] = $attr_name;
                     $filter_attr_list[$key]['attr_list'][0]['attr_value'] = '全部';
                     $filter_attr_list[$key]['attr_list'][0]['selected'] = empty($filter_attrs[$key]) ? 1 : 0;
                     $filter_attr_list[$key]['attr_list'][0]['url'] = build_uri('index', $id, $sort_field, $sort_order, $brand_id, implode('.', $temp_url));
                     if($attr_list)
                     {
                        foreach($attr_list as $k => $attr_item)
                        {
                            $temp_key = $k + 1;
                            $temp_url[$key] = $attr_item['good_attr_id'];
                            $filter_attr_list[$key]['attr_list'][$temp_key]['attr_value'] = $attr_item['attr_value'];
                            $filter_attr_list[$key]['attr_list'][$temp_key]['url'] = build_uri('index', $id, $sort_field, $sort_order, $brand_id, implode('.', $temp_url));
                            $filter_attr_list[$key]['attr_list'][$temp_key]['selected'] = ($filter_attrs[$key] == $attr_item['good_attr_id']) ? 1 : 0;
                        }
                     }
                }
            }
            $this->assign('filter_attr_list', $filter_attr_list);
        }

        $ext_sql = '';
        if($filter_attrs)
        {
            $ids = [];
            foreach($filter_attrs as $k => $v)
            {
                if($v && is_numeric($v)  && isset($filter_ids[$k]))
                {
                   $ids[] =  $v;
                }
            }

            $ids = M('good_attrs')
             ->alias('g')
             ->field(['DISTINCT(a.good_id)'])
             ->join('good_attrs a on g.attr_value=a.attr_value', 'inner')
             ->where(['_string' => "g.id " . db_create_in($ids)])
             ->select();
             foreach($ids as $k => $id_item)
             {
                $ids[$k] = $id_item['good_id'];
             }
             $ext_sql = db_create_in($ids, 'g.id');
        }

        $data = get_cate_goods($id, $sort_field, $sort_order, $brand_id, $ext_sql);
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

function get_extended_goods($cat_ids)
{
    $list = M('goodExtendedCats')
     ->alias('g')
     ->field(['good_id'])
     ->where('g.cat_id ' . $cat_ids)
     ->select();

     $ids = [];
     foreach($list as $li)
     {
        $ids[] = $li['good_id'];
     }
     return db_create_in($ids);
}
