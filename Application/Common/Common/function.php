<?php
function P($a, $getVar = false)
{
    echo '<pre>';
    if($getVar)
    {
        $rs = var_export($a, $return);
        echo '</pre>';
        return $rs;
    }
    else
    {
        print_r($a);
        echo '</pre>';
    }
}
function random_number($length = 6)
{
    if($length < 1)
    {
        $length = 6;
    }

    $min = 1;
    for($i=0; $i<$length-1 ; $i++)
    {
        $min *= 10 ;
    }
    $max = $min * 10 - 1;
    return mt_rand($min, $max);
}

function check_email($email)
{
    if(!empty($email) && strpos($email, '@') !== false && strpos($email, '.') !== false)
    {
        $pattern = "/^([a-z0-9+_]|\\.|\\-)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if(preg_match($pattern, $email))
        {
            return true;
        }
    }
    return false;
}

function check_verify($code, $id = '')
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

function build_list_html($arr)
{
    if(empty($arr))
    {
        return '';
    }
    $html = '<ul>';
    foreach($arr as $key => $value)
    {
        $html .= '<li><a href="">'.str_repeat('&nbsp;&nbsp;', $value['level']).$value['cat_name'].'</a></li>';
        if(!empty($value['child']))
        {
            $html .= build_list_html($value['child']);
        }
    }
    $html .= '</ul>';
    return $html;
}

function getStyleName($name, $style)
{
    $styles = array_filter(explode('|', $style));
    if(isset($styles[0]))
    {
        $name = '<font style="color:'.$styles[0].';">' . $name . '</font>';
    }
    if(isset($styles[1]))
    {
        $name = "<$styles[1]>" . $name . "</$styles[1]>";
    }
    return $name;
}
function build_uri($root, $id = 0, $sort_field = '', $sort_order = '', $brand_id = 0, $filter_attr = '', $page = 0)
{
    if(empty($root) || !is_string($root))
    {
        return false;
    }
    $args = [
        'id' => 0,
        'sort_field' => '',
        'sort_order' => '',
        'brand_id' => 0,
        'filter_attr' => ''
    ];

    extract(array_merge($args, compact('id', 'sort_field', 'sort_order', 'brand_id', 'filter_attr')));
    $url = "Home/Category/$root";

    if(!empty($id))
    {
        $url .= "/id/$id";
    }
    if(!empty($sort_field))
    {
        $url .= "/sort_field/$sort_field";
    }
    if(!empty($sort_order))
    {
        $url .= "/sort_order/$sort_order";
    }
    if(!empty($brand_id))
    {
        $url .= "/brand_id/$brand_id";
    }
    if(!empty($filter_attr))
    {
        $url .= "/filter_attr/$filter_attr";
    }
    if(!empty($page))
    {
        $url .= "/p/$page";
    }

    return U($url);
}

function get_cate_goods($cat_id, $sort_field, $sort_order, $bid, $ext_sql = '')
{
    $map = [];
    if($bid > 0)
    {
        $map['brand_id'] = $bid;
    }
    $ext = $ext_sql ? " $ext_sql AND " : '';
    $cats = getChildren($cat_id, 'cat_id');
    $goods_num =  M('goods')
             ->alias('g')
             ->where(array_merge([
                'deleted' => 0,
                'is_on_sale' => 1,
                'is_alone_sale' => 1,
                '_string' => $ext . $cats
             ], $map))->count();

    $page = new \Think\Page($goods_num, C('LIMIT_COUNT'));
    $show = $page->show();

    $goods = M('goods')
             ->alias('g')
             ->field(['id','good_name', 'shop_price'])
             ->where(array_merge([
                'deleted' => 0,
                'is_on_sale' => 1,
                'is_alone_sale' => 1,
                '_string' => $ext . $cats
             ], $map))->order("$sort_field $sort_order")
             ->limit($page->firstRow . ',' . $page->listRows)
             ->select();

    return compact('goods', 'goods_num', 'show');
}

function getChildren($cat_id, $field = '')
{
    return db_create_in(array_unique(array_merge(array($cat_id), array_keys(getCategories($cat_id)))),$field);
}

function get_navs()
{
    $navs = M('navs')
     ->where(['if_show'=>1])
     ->order('view_order desc, id')
     ->select();
     if(!empty($navs))
     {
        foreach($navs as $k =>$nav)
        {
            $navs[$k]['nav_url'] = U('Home/Category/index', array('id'=>$nav['id']));
        }
     }
     return $navs;
}

function is_login()
{
     if(session('user_auth') && session('user_auth_sign') &&  (session('user_auth_sign') == data_auth_sign(session('user_auth'))))
     {
        return true;
     }
     return false;
}

function login($username, $password, $remember)
{
    $uid = (new \User\Api\UserApi())->login($username, $password, $remember);
    if($uid > 0)
    {
        $user = M('myUsers')->field(['username'])->find($uid);
        $sess = ['uid'=>$uid, 'username'=>$user['username']];
        session('user_auth', $sess);
        session('user_auth_sign', data_auth_sign($sess));
        update_user_info();
        return true;
    }
    else
    {
        session('user.login_fail', session('user.login_fail') + 1);
        return false;
    }
}

function update_extended_goods($good_id, $idArray)
{
    $cat_ids = M('goodExtendedCats')->where(['good_id'=>$good_id])->getField('cat_id', true);
    $cat_ids = (array) $cat_ids;
    $deleteArray = array_diff($cat_ids, $idArray);
    if(!empty($deleteArray))
    {
        M('goodExtendedCats')->where(['cat_id'=>['in', $deleteArray]])->delete();
    }
    $addArray = array_diff($idArray, $cat_ids, array(0));
    if(!empty($addArray))
    {
        foreach($addArray as $aid)
        {
            M('goodExtendedCats')->add(['good_id'=>$good_id, 'cat_id'=>$aid]);
        }
    }
}

function register($data)
{
    $data['qq'] = !empty($data['extend_field1']) ? $data['extend_field1'] :'';
    $data['home_phone'] = !empty($data['extend_field2']) ? $data['extend_field2'] :'';
    $data['office_phone'] = !empty($data['extend_field3']) ? $data['extend_field3'] :'';
    $data['pwd_question'] = !empty($data['extend_field4']) ? compile_str($data['extend_field4']) :'';
    $data['pwd_question_answer'] = !empty($data['pwd_question_answer']) ? compile_str($data['pwd_question_answer']) : '';
    $api = new \User\Api\UserApi();
    $uid = $api->register($data['username'], $data['password'], $data['repassword'], $data['email']);

    if($uid > 0) 
    {
        if(!empty($configs['register_points']))
        {
            log_account_change($uid, 0 , 0, $configs['register_points'],$configs['register_points'], '注冊送積分');
        }

        if(C('AFFILIATE_ENABLED') == 1)
        {
            $user = get_affiliate();
            if($user['uid'] >0)
            {
                $invitation_points = C('INVITATION_POINTS');
                $invitation_points_up = C('INVITATION_POINTS_UP');
                if(!empty($invitation_points))
                {
                    if(!empty($invitation_points_up))
                    {
                        if($invitation_points + $user['rank_points'] <= $invitation_points_up)
                        {
                            log_account_change($user['uid'], 0 , 0, $invitation_points,0 , '邀请得积分');
                        }
                    }
                    else 
                    {
                        log_account_change($user['uid'], 0 , 0, $invitation_points, 0 , '邀请得积分');
                    }
                    M('myUsers')->where(['id'=>$uid])->setField(['affiliate_id'=>$user['uid']]);
                }
            }
        }

        $other_keys = ['msn', 'qq', 'home_phone', 'office_phone', 'pwd_question', 'pwd_question_answer'];
        $temp = array();
        foreach($data as $key => $data_item)
        {
            if(in_array($key, $other_keys))
            {
                $temp[$key] = $data_item;
            }
        }
        $temp['reg_time'] = time();
        M('myUsers')->where(['id'=>$uid])->save($temp);
        update_user_info();
        return true;
    }          
    return $uid;
}

function compile_str($str)
{
    return strtr($str, array('<' => '《', '>' => '》', '"' => '“', "'" => '”'));
}

function update_user_info()
{
    $uid = session('user_auth.uid');
    if($uid <=0 || !$uid)
    {
        return false;
    }
    $user = M('myUsers')->field(true)->find($uid);
    if($user)
    {
        if($user['rank_id'] > 0)
        {
           $rank =  M('userRank')->field(['is_special'])->find($user['rank_id']);
           if($rank['is_special'] == 0 || is_null($rank['is_special']))
           {
             M('myUsers')->save(['id'=>$uid, 'rank_id'=>0]);
             $user['rank_id'] = 0;
           }
        }

        if($user['rank_id'] == 0)
        {
            $rank = M('userRank')
                ->field(['discount', 'id'])
                ->where(['min_points'=>['ELT', $user['rank_points']], 'max_points' => ['GT', $user['rank_points']]])
                ->find();
            if($rank)
            {
                session('discount', $rank['discount'] / 100.00);
                session('rank_id', $rank['id']);
            }
            else 
            {
                session('discount', 1);
                session('rank_id', 0);
            }
               
        }
        else
        {
             $rank = M('userRank')->field(['discount', 'id'])->find($user['rank_id']);
             if($rank)
             {
                session('discount', $rank['discount'] / 100.00);
                session('rank_id', $rank['id']);
             }
             else
             {
                session('discount', 1);
                session('rank_id', 0);
             }
        }

        M('myUsers')->save([
            'id'=> $uid,
            'last_login_time' => time(),
            'last_login_ip' => get_client_ip(),
            'visit_counts' => ['exp','visit_counts +1']
        ]); 
        return true;
    } 
    return false;
}

function get_affiliate()
{
    $uid = cookie('affiliate_uid');
    if(!empty($uid)) {
        $user = M('myUsers')->find($uid);
        if($user) {
            return ['uid'=>$user['id'], 'rank_points'=>$user['rank_points']];
        }else {
          cookie('affiliate_uid', null);
        }
    }
    return 0;
}

function log_account_change($uid, $user_money, $frozen_money, $rank_points, $pay_points, $change_desc = '', $change_type = 1)
{
    $data = [
        'uid' => $uid,
        'user_money' => $user_money,
        'frozen_money' => $frozen_money,
        'rank_points' => $rank_points,
        'pay_points' => $pay_points,
        'change_desc' => $change_desc,
        'change_type' => $change_type
    ];
    if(!M('accountLog')->add($data))
    {
        return false;
    }
    unset($data);

    $sql = "UPDATE my_users 
       SET user_money = user_money + $user_money,
           frozen_money = frozen_money + $frozen_money,
           rank_points = rank_points + $rank_points,
           pay_points = pay_points + $pay_points
        WHERE  id = $uid LIMIT 1";
   if(!(new \Think\Model())->execute($sql))
   {
     return false;
   }
   return true;
}

function build_fields_html($id = 0)
{
     $html = '';
     if($id != 0)
     {
        $fields = M('registerFields')
             ->field(['id', 'field_name', 'field_title', 'field_values'])
             ->where(['type'=>1, 'enabled'=>1])
             ->find($id);
        if($fields)
        {
            $fields = array($fields);
        }
     }
     else 
     {
        $fields = M('registerFields')
         ->field(['id', 'field_name', 'field_title', 'field_values'])
         ->where(['type'=>1, 'enabled'=>1])
         ->select();
     }
     
    foreach($fields as $field)
    {
        $field_values = $field['field_values'];
        if(!empty($field_values))
        {
            $field_values = preg_replace('/\r/', '', $field_values);
            $options = preg_split('/\n/', $field_values);
            $html .= '<strong>'.$field['field_title'].'</strong><select name="extend_field'.$field['id'].'"  class="form-control">
        <option value="">--请选择问题--</option>';

            foreach($options as $option)
            {
                $html .= "<option value=\"$option\" ". ($option == $pwd_index ? 'selected' : '').">$option</option>";
            }
         $html .= '</select><div class="form-group">
        <label for="">密码回答问题</label>
        <input type="text" name="pwd_question_answer" class="form-control">
    </div>';
        }
        else
        {
            $html .= '<div class="form-group">
        <label for="">'.$field['field_title'].'</label>
        <input type="password" class="form-control" name="extend_field'.$field['id'].'">
    </div>';
        }
    }
    return $html;
}

function api($name = '', $param = array())
{
    if(empty($name))
    {
        return false;
    }
    $arr = explode('/', $name);
    $func = array_pop($arr);
    $className = array_pop($arr);
    $module = empty($arr) ? 'Common' : array_pop($arr);
    $callback = $module . '\Api\\' . $className . 'Api::' . $func;
    if(is_string($param))
    {
        parse_str($param, $param);
    }
    return  call_user_func_array($callback, $param);
}

function generate_good_sn()
{
    $model = M('goods');
    $fields = $model->field(array('MAX(id)'=>'max_id'))->find();
    $sn = 'gn' . date('Ymd') . mt_rand(10000, 99999) . ($fields['max_id'] + 1);
    while($data = $model->where(['good_sn'=>$sn])->find())
    {
        $sn = 'gn' . date('Ymd') . mt_rand(10000, 99999) . $fields['max_id'];
    }
    return $sn;
}

function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list))
    {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data)
        {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data)
        {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId)
            {
                $tree[] =& $list[$key];
            }
            else
            {
                if (isset($refer[$parentId]))
                {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

function status_to_desc($status)
{
	return $status == 0 ? '禁用': '启用';
}

function data_auth_sign($data)
{
	$data = array_filter($data);
	ksort($data);
	return sha1(http_build_query($data));
}

function get_insert_type_name($type_id)
{
    if($type_id < 1)
    {
        return false;
    }
    $name = '';
    switch($type_id)
    {
        case 1:
            $name = '手工录入';
            break;
        case 2:
            $name = '固定选择';
            break;
        default:
            $name = 'unknown';
            break;
    }
    return $name;
}

function build_attr_html($type = 0, $good_id = 0)
{   
    if($type ==0)
    {
        return '';
    }
    
   $attrs = M('attribute')
        ->alias('a')
        ->join('good_attrs gt on a.id=gt.attr_id and gt.good_id='.$good_id, 'left')
        ->field(['input_value_type', 'input_type_values', 'input_type_id','attribute_name','gt.attr_price','a.id'=>'aid', 'attr_value', 'attr_price'])
        ->where(['a.type_id'=>$type])
        ->order('a.type_id,a.id')
        ->select();

    $html = '';
    $index = 0;
    
    foreach($attrs as $attr)
    {
        $html .= '<div class="form-group">';
        $html .= "<label>{$attr['attribute_name']}</label>";
        if($attr['input_value_type'] == 2 || $attr['input_value_type'] == 3)
        {
            $html .= $attr['aid'] != $index ? ' <a href="javascript:;" onclick="addAttr(this)">[ + ]</a>'
            : ' <a href="javascript:;" onclick="removeAttr(this)">[ - ]</a>';
            $index = $attr['aid'];
        }

        $html .= '<input type="hidden" name="attr_id_list[]" value="'.$attr['aid'].'">';
        if($attr['input_type_id'] == 1)
        {
                
            $html .= '<input type="text" class="form-control" name="attr_value_list[]" value="'.$attr['attr_value'].'">';
            
        }
        elseif ($attr['input_type_id'] == 2)
        {
            $html .= '<select class="form-control" name="attr_value_list[]">';
            $html .= '<option value="">--Please select--</value>';
            $values = explode("\n", strtr($attr['input_type_values'], "\r", ''));
            foreach($values as $value) {
                $value = trim($value);
                $html .= '<option value="'.$value.'" '.($value == $attr['attr_value'] ? 'selected' : '').'>'.$value.'</option>';
            }
            $html .= '</select>';
        }
        else
        {
             $html .= '<textarea type="text" class="form-control" name="attr_value_list[]">'.$attr['attr_value'].'</textarea>';
        }

        if($attr['input_value_type'] == 2 || $attr['input_value_type'] == 3)
        {
           $html .= '<input type="text" name="attr_price_list[]" value="'.$attr['attr_price'].'" class="form-control" placeholder="请输入价格">';
        }
        else
        {
           $html .= '<input type="hidden" name="attr_price_list[]" value="">';
        }

        $html .= '</div>';
    }

    return $html;
}

function getCategories($cid, $type = true, $selected = 0)
{
    static $arr2 = null;
    
    if($arr2 === null)
    {
        $arr2 = S('cat_pid_asc');
            if($arr2 == null)
            {
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
                    foreach($arr as $k => $value)
                    {
                        $temp[$value['cat_id']] = $value['goods_num'];
                    }

                    foreach($arr2 as $k => $value)
                    {
                         $arr2[$k]['good_num'] = isset($temp[$value['id']]) ? $temp[$value['id']] : 0;
                    }

                    if(count($arr2) < 1000)
                    {
                        S('cat_pid_asc', $arr2);
                    }
            }
    }

    if(empty($arr2)) {
         return $type ? array() : '';
    }

    $cateSorts = categories_sort($cid, $arr2);
    
    if($type)
    {
        foreach($cateSorts as $key => $value)
        {
            $cateSorts[$key]['url'] = U('Home/Category/index', array('id'=>$key));
        }
        return $cateSorts;
    }
    else
    {
        $html = '';
        foreach($cateSorts as $key => $value)
        {
            $html .= "<option value=\"{$value['id']}\" ";
            $html .= $value['id'] == $selected ? 'selected' : '';
            $html .= '>';
            $html .=  str_repeat('&nbsp;', intval($value['level']) * 4) . $value['name'];
            $html .= '</option>';
        }
        return $html;
    }   
}

function clear_cache($name)
{
    if(empty($name))
    {
        return '';
    }
    return is_array($name) ? array_map('clear_cache', $name) : S($name, null);
}

function categories_sort($index_id, $list)
{
    static $cates;

    if(isset($cates[$index_id]))
    {
        return $cates[$index_id];
    }

    if(!isset($cates[0]))
    {
         $data = S('cate_relation_sort');
         if($data == null)
         {
                $level = $pid = 0;
                $level_arr =  $tree = $cat_id_arr = array();
                while(!empty($list))
                {
                    foreach($list as $key => $value)
                    {
                        $cat_id = $value['id'];
                        if($level == 0 && $pid == 0 )
                        {
                            $tree[$cat_id] = $value;
                            $tree[$cat_id]['level'] = $level;
                            $tree[$cat_id]['name'] = $value['cat_name'];
                            unset($list[$key]);
                            if($value['children'] == 0)
                            {
                                 continue;
                            }

                            $pid = $cat_id;
                            $cat_id_arr[] = $cat_id;
                            $level_arr[$cat_id] = ++$level;
                            continue;
                        }

                        if($value['pid'] == $pid)
                        {
                           $tree[$cat_id] = $value;
                           $tree[$cat_id]['level'] = $level;
                           $tree[$cat_id]['name'] = $value['cat_name'];
                           unset($list[$key]);

                           if($value['children'] > 0)
                           {
                              $pid = $cat_id;
                              $cat_id_arr[] = $cat_id;
                              $level_arr[$cat_id] = ++$level;
                           }
                        }
                        else if($value['pid'] > $pid) 
                        {
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
                        
                    if($pid && isset($level_arr[$pid]))
                    {
                         $level = $level_arr[$pid];
                    }
                    else
                    {
                         $level = 0;
                    }
                }
                 if(count($tree) <= 2000)
                 {
                     S('cate_relation_sort', $tree);
                 }
             }
             else
             {
                  $tree = $data;
             }
            $cates[0] = $tree;
        }
        else
        {
            $tree = $cates[0];
        }

        if(!$index_id)
        {
            return $tree;
        }
        else
        {
            if(empty($tree[$index_id]))
            {
                return array();
            }

            foreach($tree as $key => $value)
            {
                if($key != $index_id)
                {
                    unset($tree[$key]);
                }
                else
                {
                    break;
                }
            }
            
            $spec_id_level = $tree[$index_id]['level'];
            $spec_id_array = array();
            foreach($tree as $key => $value)
            {
                if(($spec_id_level == $value['level'] && $index_id != $value['id'] ) || ($value['level'] < $spec_id_level ))
                {
                    break;
                }
                else
                {
                    $spec_id_array[$key] = $value;
                }
            }

            $cates[$index_id] = $spec_id_array;
            return $spec_id_array;
        }
}


function db_create_in($value_list, $fields = '') 
{
    if(empty($value_list))
    {
        return "";
    }
    else
    {
        if(!is_array($value_list))
        {
           $value_list =  explode(',', $value_list);
        }
       $value_list =  array_unique($value_list);
    
       $list_item = '';
       foreach($value_list as $value)
       {
          if(!empty($value))
          {
             $list_item .= $list_item ? ",'$value'" : "'$value'";
          }
       }

       if(empty($list_item))
       {
         return "$fields IN ('')";
       }
       else
       {
         return "$fields IN ($list_item)";
       }
    }
    
}

function assign_comments($tpl, $id)
{
    $count = M('comments')
     ->where([
      'comment_type'=>0,
      'pid'=>0,
      'reply_id'=>$id,
      'status'=>1])
     ->count();

     $num = !empty(C('COMMENT_NUMBER')) ? intval(C('COMMENT_NUMBER')) : 2;
     $Page = new \Think\Page($count, $num);

     $list = M('comments')
        ->field(['username', 'add_time', 'content', 'comment_rank'])
        ->where([
          'comment_type'=>0,
          'pid'=>0,
          'reply_id'=>$id,
          'status'=>1])
        ->order('id desc,add_time desc')
        ->limit($Page->firstRow.','.$Page->listRows)
        ->select();

     $tpl->assign('list',$list);
     $tpl->assign('page',$Page->show());
     if(is_login()) 
     {
        $tpl->assign('email', M('myUsers')->where(['id'=>session('user_auth.uid')])->getField('email'));
     }
}

function get_good_properties($good_id)
{
    if(!$good_id)
    {
        return false;
    }

    $attr_group = M('goods')
     ->alias('g')
     ->field(['attr_groups'])
     ->join('good_attr_types gt on g.type_id=gt.id', 'inner')
     ->where(['g.id'=>$good_id])
     ->find();

     $attr_group = $attr_group['attr_group'];
     $groups = array();
     if(!empty($attr_group))
     {
        $attr_group = explode("\n", strtr($attr_group, "\r", ''));
        foreach($attr_group as $grp)
        {
            $grp = trim($grp);
            $groups[$grp] = $grp;
        }
     }

     $attrs = M('goodAttrs')
      ->alias('ga')
      ->field(['input_value_type', 'ga.id'=>'g_a_id', 'a.attribute_name', 'attr_price', 'ga.attr_value', 'attr_group', 'a.id', 'attr_id'])
      ->join('attribute a on ga.attr_id=a.id', 'left')
      ->where(['ga.good_id'=>$good_id])
      ->select();

      $properties = array();
      $list = array();
      foreach($attrs as $attr)
      {
         if($attr['input_value_type'] == 1)
         {
            $group = isset($groups[$attr['attr_group']]) ? $groups[$attr['attr_group']] : '属性列表';
            $list['prop'][$group][$attr['attribute_name']] = $attr['attr_value'];
         }
         else
         {
            $list['spec'][$attr['attr_id']]['name'] = $attr['attribute_name'];
            $list['spec'][$attr['attr_id']]['type'] = $attr['input_value_type'];
            $list['spec'][$attr['attr_id']]['values'][] = array(
                'label' => $attr['attr_value'],
                'price' => $attr['attr_price'],
                'gid' => $attr['g_a_id'],
            );
         }
      }
      return $list;
}

function handle_member_price($good_id, $user_price, $rank_ids)
{
    foreach($rank_ids as $key => $r_id)
    {
        $u_price = $user_price[$key];

        $cnt = M('memberPrice')
         ->field('id')
         ->where(['good_id'=>$good_id, 'user_rank'=>$r_id])
         ->find();

        if($cnt)
        {
            if($u_price == -1)
            {
                M('memberPrice')->delete($cnt['id']);
            }
            else
            {
                M('memberPrice')->save(['id'=> $cnt['id'], 'good_id'=>$good_id, 'user_rank'=>$r_id, 'member_price'=>$u_price]);
            }
        }
        else
        {
            if($u_price >= 0)
            {
                M('memberPrice')->add(['good_id'=>$good_id, 'user_rank'=>$r_id, 'member_price'=> $u_price]);
            }
        }
    }
}

function handle_volume_price($good_id, $volume_number, $volume_price)
{
    M('volumePrice')->where(['price_type'=>1, 'good_id'=>$good_id])->delete();

    foreach($volume_number as $k => $v_number)
    {
        $v_price = $volume_price[$k];
        if($v_price)
        {
             M('volumePrice')->add(['price_type'=>1, 'good_id'=>$good_id, 'volume_number'=>$v_number, 'volume_price'=> $v_price]);
        }
    }
}

function check_file_type($file, $real_file, $allow_types)
{
    if($real_file)
    {
        $ext = substr($real_file, strrpos($real_file, '.') + 1);
    }
    else
    {
        $ext = substr($file, strrpos($file, '.') + 1);
    }

    if($ext && stripos($allow_types, '|' . $ext . '|') === false)
    {
        return false;
    }

    $fhr = fopen($file, 'rb');

    if(!$fhr)
    {
        return false;
    }

    $str = fread($fhr, 0x400);
    @fclose($fhr);

    $type = '';
    if(isset($str{2}))
    {
        if (substr($str, 0, 4) == 'MThd' && $ext != 'txt')
            {
                $type = 'mid';
            }
            elseif (substr($str, 0, 4) == 'RIFF' && $ext == 'wav')
            {
                $type = 'wav';
            }
            elseif (substr($str ,0, 3) == "\xFF\xD8\xFF")
            {
                $type = 'jpg';
            }
            elseif (substr($str ,0, 4) == 'GIF8' && $ext != 'txt')
            {
                $type = 'gif';
            }
            elseif (substr($str ,0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A")
            {
                $type = 'png';
            }
            elseif (substr($str ,0, 2) == 'BM' && $ext != 'txt')
            {
                $type = 'bmp';
            }
            elseif ((substr($str ,0, 3) == 'CWS' || substr($str ,0, 3) == 'FWS') && $ext != 'txt')
            {
                $type = 'swf';
            }
            elseif (substr($str ,0, 4) == "\xD0\xCF\x11\xE0")
            {   // D0CF11E == DOCFILE == Microsoft Office Document
                if (substr($str,0x200,4) == "\xEC\xA5\xC1\x00" || $ext == 'doc')
                {
                    $type = 'doc';
                }
                elseif (substr($str,0x200,2) == "\x09\x08" || $ext == 'xls')
                {
                    $type = 'xls';
                } elseif (substr($str,0x200,4) == "\xFD\xFF\xFF\xFF" || $ext == 'ppt')
                {
                    $type = 'ppt';
                }
            } 
            elseif (substr($str ,0, 4) == "PK\x03\x04")
            {
                if (substr($str,0x200,4) == "\xEC\xA5\xC1\x00" || $ext == 'docx')
                {
                    $type = 'docx';
                }
                elseif (substr($str,0x200,2) == "\x09\x08" || $ext == 'xlsx')
                {
                    $type = 'xlsx';
                } elseif (substr($str,0x200,4) == "\xFD\xFF\xFF\xFF" || $ext == 'pptx')
                {
                    $type = 'pptx';
                }else
                {
                    $type = 'zip';
                }
            } 
            elseif (substr($str ,0, 4) == 'Rar!' && $ext != 'txt')
            {
                $type = 'rar';
            } 
            elseif (substr($str ,0, 4) == "\x25PDF")
            {
                $type = 'pdf';
            } 
            elseif (substr($str ,0, 3) == "\x30\x82\x0A")
            {
                $type = 'cert';
            } 
            elseif (substr($str ,0, 4) == 'ITSF' && $ext != 'txt')
            {
                $type = 'chm';
            } 
            elseif (substr($str ,0, 4) == "\x2ERMF")
            {
                $type = 'rm';
            }
        }

        if($type && stripos($allow_types, '|'. $type . '|') !== false)
        {
            return true;
        }
        return false;
}

function upload_image($img, $dest_img, $type)
{
    $stat = ['has_error'=> false, 'error'=>''];
    if(!check_file_type($img['tmp_name'], $dest_img, '|jpg|jpeg|png|gif|'))
    {
        $stat['has_error'] = true;
        $stat['error'] = '不允许的类型';
        return $stat;
    }

    $dir = './uploads/' . date('Ym') . '/';
    if(!file_exists($dir))
    {
        mkdir($dir, 0777, true);
    }
    $newName = time();
    for($i = 0; $i< 10; $i++)
    {
        $newName .= mt_rand(100, 999);
    }
    $newName .= '.jpg';

    if(!move_uploaded_file($img['tmp_name'], $dir . $newName))
    {
        $stat['has_error'] = true;
        $stat['error'] = '上传失败,请重新尝试';
        return $stat;
    }
    $stat['path'] = trim($dir . $newName, './');
    return $stat;
}

function get_good_info($id)
{
    $good = M('goods')
     ->alias('g')
     ->field(['g.*', 'b.brand_name', 'c.unit', 'ifnull(avg(ct.comment_rank), 0)'=>'comment_rank'])
     ->join('categories c on g.cat_id=c.id', 'left')
     ->join('brands b on g.brand_id=b.id', 'left')
     ->join('comments ct on ct.reply_id=g.id and ct.status=1', 'left')
     ->where(['g.deleted'=>0, 'g.is_on_sale'=>1, 'g.id'=>$id])
     ->group('g.id')
     ->select();

     $good = $good[0];
     $good['comment_rank'] = ceil($good['comment_rank']) == 0 ? 5 : ceil($good['comment_rank']);
     $good['market_price'] = sprintf('￥%d元', round($good['market_price']));

     return $good;
}

function handle_goods_attr($good_id, $id_list, $spec_list, $attr_value_list)
{
    $result_arr = array();
    foreach($id_list as $k => $id)
    {
        $value = $price = '';
        if($spec_list[$k] === false)
        {
            $value = $attr_value_list[$k];
            $price = '';
        }
        else
        {
            $value_list = $price_list = array();
            if(!empty($attr_value_list[$k]))
            {
                $attr_arr = explode(chr(13), $attr_value_list[$k]);
                foreach($attr_arr as $val)
                {
                    $arr = explode(chr(9), $val);
                    $value_list[] = $arr[0];
                    $price_list[] = $arr[1];
                }
            }
            
            $value = implode(chr(13), $value_list);
            $price = implode(chr(13), $price_list);

            $goodAttrId = M('goodAttrs')
             ->where([
                'good_id' => $good_id,
                'attr_id' => $k,
                'attr_value' => $value])
             ->getField('id');
             if($goodAttrId)
             {
                M('goodAttrs')
                 ->save([
                    'attr_value' => $value,
                    'id' => $goodAttrId
                 ]);
                 $result_arr[$k] = $goodAttrId;
             }
             else
             {
                $r_id = M('goodAttrs')
                 ->add([
                    'good_id' => $good_id,
                    'attr_id' => $k,
                    'attr_value' => $value,
                    'attr_price' => $price
                 ]);
                 $result_arr[$k] = $r_id;
             }
        }
    }
    return $result_arr;
}

function product_list($id)
{
    $products = M('products')->where(['good_id'=>$id])->select();
    $attrs = M('goodAttrs')->field(['id', 'attr_value'])->where(['good_id'=>$id])->select();
    $list = array();
    foreach($attrs as $v)
    {
        $list[$v['id']] = $v['attr_value'];
    }
    foreach($products as $key => $val)
    {
        $good_atrs = explode('|', $val['good_attr']);
        $temp = [];
        foreach($good_atrs as $atr_id)
        {
            $temp[] = $list[$atr_id];
        }
        $products[$key]['attrs'] = $temp;
    }
    return $products;
}

function get_volume_prices($id, $price_type = 1)
{
    $prices = M('volumePrice')
        ->where(['good_id'=>$id, 'price_type'=>$price_type])
        ->order('volume_number desc')
        ->select();
     $list = array();
     foreach($prices as $k => $p)
     {
        $list[$k] = array();
        $list[$k]['volume_number'] = $p['volume_number']; 
        $list[$k]['volume_price'] = sprintf('￥%d元', round($p['volume_price'])); 
        $list[$k]['volume_price_orgin'] = $p['volume_price']; 
     }
     return $list;
}

function getFinalPrice($good_id, $number, $specs, $calcSpec = false)
{
     $volume_price = $promotion_price = $final_price =0;
     $number = $number > 0 ? $number : 1;
     $arr_prices = get_volume_prices($good_id);
     foreach($arr_prices as $val)
     {
         if($number >= $val['volume_number'])
         {
             $volume_price = $val['volume_price_orgin'];
             break;
         }
     }
     $good_info = M('goods')
        ->alias('g')
        ->join('member_price mp on g.id=mp.good_id and mp.user_rank = ' . session('rank_id') , 'left')
        ->field(['IFNULL(mp.member_price, shop_price * '  .session('discount') . ')' => 'user_price', 'promotion_price', 'promotion_start', 'promotion_end'])->where(['g.id'=>$good_id, 'deleted'=>0])->find();

     $user_price = $good_info['user_price'];
     if($good_info['promotion_price'] > 0)
     {
          $gtime = time();
          if($gtime >= $good_info['promotion_start'] && $gtime <= $good_info['promotion_end'])
          {
            $promotion_price = $good_info['promotion_price'];
          }
          else
          {
            $promotion_price = 0;
          }
     }

     $compare_arr = [$volume_price, $promotion_price, $user_price];
     $compare_arr = empty(array_filter($compare_arr)) ? [0] : array_filter($compare_arr);
     $final_price = min($compare_arr);

     if($calcSpec)
     {
         $ids = explode(',', $specs);
         $attr_prices = M('goodAttrs')->field(['attr_price'])->where(db_create_in($ids, 'id'))->select();
         foreach($attr_prices as $val)
         {
             if(!empty($val['attr_price']))
             {
                 $final_price += $val['attr_price'];
             }
         }
     }
     return $final_price;
}

function add_to_cart($parent_id = 0, $id, $num, $specs)
{
    $user_id = session('user_auth.uid');
    if(empty($user_id))
    {
      $this->error('请先登录');
      exit;
    }

    $result = array('error'=>'', 'content' => '');

    if($parent_id > 0)
    {
        $parent_cnt = M('carts')->where(['user_id'=> $user_id, 'good_id'=>$parent_id])->count();
        if(!$parent_cnt)
        {
            $this->error("没有基本件");
            exit;
        }
    }

    $good = M('goods')->where(['deleted'=>0])->find($id);
    if(!$good)
    {
        $result['error'] = '商品不存在';
        return $result;
    }
    if($good['is_on_sale'] ==0 )
    {
        $result['error'] = '商品已下架';
        return $result;
    }
    if($parent_id == 0 && $good['is_alone_sale'] = 0)
    {
        $result['error'] = '该商品不单独销售';
        return $result;
    }

    $prod = M('products')->where(['good_id'=>$id])->count();

     if(is_spec($specs) && $prod > 0)
    {
        $product_info = get_product_info($id, $specs);
    }
    
    if(empty($product_info))
    {
        $product_info = array('product_id'=> 0, 'product_number' => 0);
    }

    if(C('USE_STORAGE') > 0)
    {
        if($num > $good['number'])
        {
            $result['error'] = '超过库存最大量';
            return $result;
        }

        if(is_spec($specs) && $prod > 0)
        {
            if($num > $product_info['product_number'])
            {
                $result['error'] = '超过库存最大量';
                return $result;
            }
        }
    }

    $data['good_attr_id'] = implode(',', $specs);
    $data['good_id'] = $id;
    $data['good_sn'] = $good['good_sn'];
    $data['is_real'] = 1;
    $data['good_name'] = $good['good_name'];
    $data['rec_type'] = 1;
    $data['is_shipping'] = 1;
    $data['is_gift'] = 0;
    $data['extension_code'] = '';
    $data['user_id'] = $user_id;
    $data['market_price'] = $good['market_price'] + spec_price($specs);
    $data['product_id'] = $product_info['product_id'];
    $data['good_attr'] = get_good_attr_info($specs);
    $good_price = getFinalPrice($id, $num, $specs, $true);

    if($parent_id > 0)
    {
        $basic_list = M('groupGoods')
         ->field(['parent_id', 'good_price'])
         ->where(['good_id'=>$id, 'parent_id'=> $parent_id, '_string' => 'good_price < '. $good_price])
         ->select();
         $basic_e = array();
         foreach ($basic_list as $key => $value) {
            $basic_e[$value['parent_id']] = $value['good_price'];
         }

         $basic_count_list = array();
         if($basic_e)
         {
           $basic_count_list = M('carts')
             ->field(['SUM(good_number)' => 'number', 'good_id'])
             ->where(['user_id'=>$user_id, 'parent_id'=>0, 'good_id'=>$parent_id])
             ->group('good_id')
             ->select();
             $basic_count_list_e = array();
            foreach($basic_count_list as $value)
            {
                $basic_count_list_e[$value['good_id']] = $value['number'];
            }
         }

         if($basic_count_list_e)
         {
            $fitting_list = M('carts')
             ->field(['SUM(good_number)' => 'number', 'parent_id'])
             ->where(['user_id'=>$user_id, 'parent_id'=>$parent_id, 'good_id'=>$id])
             ->group('parent_id')
             ->select();

            $fitting_list_e = array();
            foreach ($fitting_list as $key => $value) {
                $basic_count_list_e[$value['parent_id']] -= $value['number'];
            }
         }

         foreach($basic_e as $pid => $basic_price)
         {
            if($num <= 0)
            {
                continue;
            }
            if(!isset($basic_count_list_e[$pid]))
            {
                continue;
            }

            if($basic_count_list_e[$pid] <= 0)
            {
                continue;
            }
            $data['good_number'] =  min($num, $basic_count_list_e[$pid]);
            $data['good_price'] = max($basic_price, 0);
            $data['parent_id'] = $parent_id;
            M('carts')->add($data);
            $num -= $basic_count_list_e[$pid];
         }
    }

    if($num > 0)
    {
       $cart = M('carts')
        ->where([
            'good_id' => $id,
            'user_id' => $user_id,
            'parent_id' => 0,
            'good_attr_id' => implode(',', $specs)
        ])->find();

        if($cart)
        {
           $num += $cart['good_number'];
           if(is_spec($specs) && !empty($prod))
           {
              $default_storage = $product_info['product_number'];
           }
           else
           {
              $default_storage = $good['number']; 
           }

           if(C('USE_STORAGE') ==0 || $num <= $default_storage)
           {
              $data['good_number'] = $num;
              $data['good_price'] = $good_price;
              $data['parent_id'] = 0;
              M('carts')
               ->where(['good_id'=>$id, 'user_id' => $user_id, 'good_attr_id' => implode(',', $specs)])
               ->save($data);
           }
        }
        else 
        {
             $data['good_number'] = $num;
             $data['good_price'] = $good_price;
             $data['parent_id'] = 0;
             M('carts')->add($data);
        }
    }

    return $result;
}

function get_good_attr_info($specs)
{
  if(empty($specs))
  {
    return '';
  }
  $arr = M('goodAttrs')
   ->alias('ga')
   ->field(['a.attribute_name', 'ga.attr_value', 'ga.attr_price'])
   ->join('attribute a on ga.attr_id=a.id', 'inner')
   ->where(db_create_in($specs, 'ga.id'))
   ->select();

  $attr = '';
  $format = "%s:%s[%s] \n";
  if(!empty($arr))
  {
    foreach ($arr as $key => $value) {
       $attr_price = round(floatval($value['attr_price']), 2);
       $attr .= sprintf($format, $value['attribute_name'], $value['attr_value'],  $attr_price);
    }
    $attr = str_replace('[0]', '', $attr);
  }
  return $attr;
}

function is_spec($good_attr_ids)
{
    if(!$good_attr_ids || !is_array($good_attr_ids))
    {
        return false;
    }

    $result = M('goodAttrs')->where('id ' . db_create_in($good_attr_ids))->select();
    if(!empty($result))
    {
        return true;
    }
    return false;
}

function get_product_info($good_id, $specs)
{
    $attr_ids = M('attribute')
     ->alias('a')
     ->field(['ga.id'])
     ->join('good_attrs ga on a.id=ga.attr_id', 'left')
     ->where(['_string'=> db_create_in($specs, 'ga.id'), 'a.input_value_type'=>2])
     ->order('a.id asc')
     ->select();
     $attr_ids = array_column($attr_ids, 'id');
     if(!empty($attr_ids))
     {
        $good_attr = implode('|', $attr_ids);
        return  M('products')->where(['good_id'=>$good_id, 'good_attr'=> $good_attr])->find();
     }
     return false;
}

function spec_price($specs)
{
    if(empty($specs)) {
        return 0;
    }
   return  M('goodAttrs')->where(db_create_in($specs, 'id'))->getField('SUM(attr_price)');
}

function get_specification_list($good_id)
{
    $cnt = M('attribute')
     ->alias('a')
     ->join('good_attrs ga on a.id=ga.attr_id', 'left')
     ->where(['ga.good_id'=>$good_id,'a.input_value_type'=>2])
     ->count();
     return $cnt > 0 ? true : false;
}

function send_mail()
{
    $name = '2546857860@qq.com';
    $email = '2546857860@qq.com';
    $subject = '邮箱验证申请';
    $type = 1;
    $notification = false;
    $shop_name = '测试组';
    $from = 'yuan3666073@163.com';
$content =<<<'cont'
    <div id="qm_con_body">
    <div class="qmbox qm_con_body_content" id="mailContentContainer" style="">
    <p style="margin:0px;padding:0px;"><strong style="font-size:14px;line-height:24px;color:#333333;font-family:arial,sans-serif;">亲爱的用户：<></p>
    <p style="margin:0px;padding:0px;line-height:24px;font-size:12px;color:#333333;font-family:'宋体',arial,sans-serif;"><span style="font-size: larger;">您好！</span><span style="font-size: larger;"><br />
    </span></p>
    <p style="margin:0px;padding:0px;line-height:24px;font-size:12px;color:#333333;font-family:'宋体',arial,sans-serif;"><span style="font-size: larger;">您当前正在进行邮箱身份验证，请在页面的邮箱验证码输入框中输入此次验证码：</span></p>
    <p style="margin:0px;padding:0px;line-height:24px;font-size:12px;color:#333333;font-family:'宋体',arial,sans-serif;"><span style="color: rgb(51, 102, 255);"><span style="font-size: larger;"><span style="border-bottom: 1px dashed rgb(204, 204, 204); z-index: 1;">3306</span></span></span><span style="font-size: larger;"> </span><span style="font-size: larger;">验证码的有效时间为30分钟，请在有效时间内完成验证。</span></p>
    <p style="margin:0px;padding:0px;line-height:24px;font-size:12px;color:#333333;font-family:'宋体',arial,sans-serif;"><span style="font-size: larger;">如非本人操作，请忽略此邮件，由此给您带来的不便请谅解！</span><span style="font-size: medium;"> </span></p>
    <p>测试组<br />
    <span style="border-bottom:1px dashed #ccc;">2019/01/09 10:48</span></p>
    </div>
    </div>
cont;
    $charset = "UTF8";
    $content_type = ($type == 0) ?
                'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
    $content   =  base64_encode($content);

    $headers = array();
    $headers[] = 'Date: ' . gmdate('D, j M Y H:i:s') . ' +0000';
    $headers[] = 'To: "' . '=?' . $charset . '?B?' . base64_encode($name) . '?=' . '" <' . $email. '>';
    $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?='.'" <' . $from . '>';
    $headers[] = 'Subject: ' . '=?' . $charset . '?B?' . base64_encode($subject) . '?=';
    $headers[] = $content_type . '; format=flowed';
    $headers[] = 'Content-Transfer-Encoding: base64';
    $headers[] = 'Content-Disposition: inline';
    if ($notification)
    {
        $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?='.'" <' . $GLOBALS['_CFG']['smtp_mail'] . '>';
    }

    /* 获得邮件服务器的参数设置 */
    $params['host'] = 'smtp.163.com';
    $params['port'] = 25;
    $params['user'] = 'yuan3666073@163.com';
    $params['pass'] = 'tuanpi_3666073';

    $send_params['recipients'] = $email;
    $send_params['body'] = $content;
    $send_params['headers'] = $headers;
    $send_params['from'] = $from;

    define('SMTP_STATUS_NOT_CONNECTED', 1, true);
    define('SMTP_STATUS_CONNECTED',     2, true);

    $smt = new smtp($params);
    if($smt->connect() && $smt->send($send_params))
    {
        echo '发送成功';
        exit;
    }
}

/*function sortGoodAttrId($idArr)
{
    M('goodAttrs')
     ->where('id ' . db_create_in($idArr))
     ->order('attr_id asc')
     ->select();
}*/