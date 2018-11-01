<?php

function P($a)
{
    echo '<pre>';
    print_r($a);
    echo '</pre>';
}

function check_verify($code, $id = '')
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}


function build_list_html($arr)
{
    if(empty($arr)) {
        return '';
    }
    $html = '<ul>';
    foreach($arr as $key => $value) {
        $html .= '<li><a href="">'.str_repeat('&nbsp;&nbsp;', $value['level']).$value['cat_name'].'</a></li>';
        if(!empty($value['child'])) {
            $html .= build_list_html($value['child']);
        }
    }
    $html .= '</ul>';
    return $html;
}

function get_cate_goods($cat_id, $sort_field, $sort_order)
{
    $goods_num =  M('goods')
             ->where([
                'deleted' => 0,
                'is_on_sale' => 1,
                'is_alone_sale' => 1,
                '_string' => db_create_in(array_unique(array_merge(array($cat_id), array_keys(getCategories($cat_id)))), 'cat_id')
             ])->count();
    $page = new \Think\Page($goods_num, C('LIMIT_COUNT'));
    $show = $page->show();
    $goods = M('goods')
             ->field(['good_name', 'price'])
             ->where([
                'deleted' => 0,
                'is_on_sale' => 1,
                'is_alone_sale' => 1,
                '_string' => db_create_in(array_unique(array_merge(array($cat_id), array_keys(getCategories($cat_id)))), 'cat_id')
             ])->order("$sort_field $sort_order")
             ->limit($page->firstRow . ',' . $page->listRows)
             ->select();

    
    return compact('goods', 'goods_num', 'show');
}

function get_navs()
{
    $navs = M('navs')
     ->where(['if_show'=>1])
     ->order('view_order desc, id')
     ->select();
     if(!empty($navs)) {
        foreach($navs as $k =>$nav) {
            $navs[$k]['nav_url'] = U('Home/Category/index', array('id'=>$nav['id']));
        }
     }
     return $navs;
}

function is_login()
{
     if(session('user_auth') && session('user_auth_sign') &&  (session('user_auth_sign') == data_auth_sign(session('user_auth')))) {
        return true;
     }
     return false;
}

function login($username, $password, $remember)
{
    $uid = (new \User\Api\UserApi())->login($username, $password, $remember);
    if($uid > 0) {
        $user = M('myUsers')->field(['username'])->find($uid);
        $sess = ['uid'=>$uid, 'username'=>$user['username']];
        session('user_auth', $sess);
        session('user_auth_sign', data_auth_sign($sess));
        update_user_info();
        return true;
    }else {
        session('user.login_fail', session('user.login_fail') + 1);
        return false;
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

            if($uid > 0) {
                if(!empty($configs['register_points'])) {
                    log_account_change($uid, 0 , 0, $configs['register_points'],$configs['register_points'], '注冊送積分');
                }

                if(C('AFFILIATE_ENABLED') == 1) {
                    $user = get_affiliate();
                    if($user['uid'] >0) {
                        $invitation_points = C('INVITATION_POINTS');
                        $invitation_points_up = C('INVITATION_POINTS_UP');
                        if(!empty($invitation_points)) {
                            if(!empty($invitation_points_up)) {
                                if($invitation_points + $user['rank_points'] <= $invitation_points_up) {
                                log_account_change($user['uid'], 0 , 0, $invitation_points,0 , '邀请得积分');
                                }
                            }else {
                                log_account_change($user['uid'], 0 , 0, $invitation_points, 0 , '邀请得积分');
                            }
                            M('myUsers')->where(['id'=>$uid])->setField(['affiliate_id'=>$user['uid']]);
                        }
                    }
                }

                
                
                $other_keys = ['msn', 'qq', 'home_phone', 'office_phone', 'pwd_question', 'pwd_question_answer'];
                $temp = array();
                foreach($data as $key => $data_item) {
                    if(in_array($key, $other_keys)) {
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
    if($uid <=0 || !$uid) {
        return false;
    }
    $user = M('myUsers')->field(true)->find($uid);
    if($user) {
        if($user['rank_id'] > 0) {
          $rank =  M('userRank')->field(['is_special'])->find($user['rank_id']);
           if($rank['is_special'] == 0 || is_null($rank['is_special'])) {
             M('myUsers')->save(['id'=>$uid, 'rank_id'=>0]);
             $user['rank_id'] = 0;
           }
        }

        if($user['rank_id'] == 0) {
            $rank = M('userRank')
                ->field(['discount', 'id'])
                ->where(['min_points'=>['ELT', $user['rank_points']], 'max_points' => ['GT', $user['rank_points']]])
                ->find();
            if($rank) {
                session('user.discount', $rank['discount'] / 100.00);
                session('user.rank_id', $rank['id']);
            }else {
                session('user.discount', 1);
                session('user.rank_id', 0);
            }
               
        }else {
             $rank = M('userRank')->field(['discount', 'id'])->find($user['rank_id']);
             if($rank) {
                session('user.discount', $rank['discount'] / 100.00);
                session('user.rank_id', $rank['id']);
            }else {
                session('user.discount', 1);
                session('user.rank_id', 0);
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

function check_email($email)
{
    if(!preg_match('/[a-zA-Z][a-zA-Z0-9_]+@[a-zA-Z_0-9]+(\.com|\.cn|\.edu)+/', $email)) {
        return false;
    }
    return true;
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
    if(!M('accountLog')->add($data)) {
        return false;
    }
    unset($data);

    $sql = "UPDATE my_users 
       SET user_money = user_money + $user_money,
           frozen_money = frozen_money + $frozen_money,
           rank_points = rank_points + $rank_points,
           pay_points = pay_points + $pay_points
        WHERE  id = $uid LIMIT 1";
   if(!(new \Think\Model())->execute($sql)) {
     return false;
   }
   return true;
}


function build_fields_html($id = 0)
{
    $html = '';
     if($id != 0) {
        $fields = M('registerFields')
             ->field(['id', 'field_name', 'field_title', 'field_values'])
             ->where(['type'=>1, 'enabled'=>1])
             ->find($id);
        if($fields) {
            $fields = array($fields);
        }
     }else {
        $fields = M('registerFields')
             ->field(['id', 'field_name', 'field_title', 'field_values'])
             ->where(['type'=>1, 'enabled'=>1])
             ->select();
     }
     
    foreach($fields as $field) {
        $field_values = $field['field_values'];
        if(!empty($field_values)) {
            $field_values = preg_replace('/\r/', '', $field_values);
            $options = preg_split('/\n/', $field_values);
            $html .= '<strong>'.$field['field_title'].'</strong><select name="extend_field'.$field['id'].'"  class="form-control">
        <option value="">--请选择问题--</option>';

            foreach($options as $option) {
                $html .= "<option value=\"$option\" ". ($option == $pwd_index ? 'selected' : '').">$option</option>";
            }
         $html .= '</select><div class="form-group">
        <label for="">密码回答问题</label>
        <input type="text" name="pwd_question_answer" class="form-control">
    </div>';
        }else {
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
    if(empty($name)) {
        return false;
    }
    $arr = explode('/', $name);
    $func = array_pop($arr);
    $className = array_pop($arr);
    $module = empty($arr) ? 'Common' : array_pop($arr);
    $callback = $module . '\Api\\' . $className . 'Api::' . $func;
    if(is_string($param)) {
        parse_str($param, $param);
    }
    return  call_user_func_array($callback, $param);
}

function generate_good_sn()
{
    $model = M('goods');
    $fields = $model->field(array('MAX(id)'=>'max_id'))->find();
    $sn = 'gn' . date('Ymd') . mt_rand(10000, 99999) . ($fields['max_id'] + 1);
    while($data = $model->where(['good_sn'=>$sn])->find()) {
        $sn = 'gn' . date('Ymd') . mt_rand(10000, 99999) . $fields['max_id'];
    }
    return $sn;
}

function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
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


function auto_login()
{

}

function data_auth_sign($data)
{
	$data = array_filter($data);
	ksort($data);
	return sha1(http_build_query($data));
}


function get_insert_type_name($type_id)
{
    if($type_id < 1) {
        return false;
    }
    $name = '';
    switch($type_id) {
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
    if($type ==0) {
        return '';
    }
    
   $attrs = M('attribute')
        ->alias('a')
        ->join('good_attrs gt on a.id=gt.attr_id and gt.good_id='.$good_id, 'left')
        ->field(['input_type_values', 'input_type_id','attribute_name','a.id'=>'aid', 'attr_value'])
        ->where(['a.type_id'=>$type])
        ->select();

    $html = '';
    foreach($attrs as $v) {
        switch($v['input_type_id']) {
            case 1: 
                    $html .= '<div class="form-group">
                    <label for="">'.$v['attribute_name'].': </label>
                    <input type="hidden" name="attr_id_list[]" value="'.$v['aid'].'">
                    <input type="text" name="attr_value_list[]" value="'.$v['attr_value'].'" class="form-control">
                    </div>';
                break;
            case 2:
                $t = '';
                foreach(explode(',', trim($v['input_type_values'], ',')) as $vv) {
                    $t .= "<option value='$vv' ". ($vv == $v['attr_value'] ? 'selected' : '') .">$vv</option>";
                }
                $html .= '<div class="form-group">
                          <label for="">'.$v['attribute_name'].': </label>
                            <input type="hidden" name="attr_id_list[]" value="'.$v['aid'].'">
                            <select name="attr_value_list[]" class="form-control">
                            <option value=""></option>
                                '.$t.'
                           </select>
                        </div>';
                break;
        }
    }

    return $html;
}


function getCategories($cid, $type = true, $selected = 0)
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

    $cateSorts = categories_sort($cid, $arr2);
    
    if($type) {
        foreach($cateSorts as $key => $value) {
            $cateSorts[$key]['url'] = U('Home/Category/index', array('id'=>$key));
        }
        return $cateSorts;
    }else {
        $html = '<div class="form-group"><label for="">商品分类</label><select name="cat_id" class="form-control"><option value="0">--请选择--</option>';
        foreach($cateSorts as $key => $value) {
            $html .= '<option value="'.$value['id'].'" '.($value['id'] == $selected ? 'selected' : '').'>'.str_repeat('&nbsp;', intval($value['level']) * 4).$value['name']. '</option>';
        }
        $html .='</select></div>';
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
        }else {
            if(empty($tree[$index_id])) {
                return array();
            }

            foreach($tree as $key => $value) {
                if($key != $index_id) {
                    unset($tree[$key]);
                }else {
                    break;
                }
            }
            
            $spec_id_level = $tree[$index_id]['level'];
            $spec_id_array = array();
            foreach($tree as $key => $value) {
                if(($spec_id_level == $value['level'] && $index_id != $value['id'] ) || ($value['level'] < $spec_id_level )) {
                    break;
                }else {
                    $spec_id_array[$key] = $value;
                }
            }

            $cates[$index_id] = $spec_id_array;
            return $spec_id_array;
        }
}


function db_create_in($value_list, $fields = '') 
{
    if(empty($value_list)) {
        return "IN ('')";
    }else {
        if(!is_array($value_list)) {
           $value_list =  explode(',', $value_list);
        }
       $value_list =  array_unique($value_list);
    
       $list_item = '';
       foreach($value_list as $value) {
          if(!empty($value)) {
             $list_item .= $list_item ? ",'$value'" : "'$value'";
          }
       }

       if(empty($list_item)) {
         return "$fields IN ('')";
       }else {
         return "$fields IN ($list_item)";
       }
    }
    
}