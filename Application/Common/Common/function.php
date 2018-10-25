<?php
function check_email($email)
{
    if(!preg_match('/[a-zA-Z][a-zA-Z0-9_]+@[a-zA-Z_]+(\.com|\.cn|\.edu)+/', $email)) {
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
        WHERE  id = $uid LIMIT 1"；
   if(!(new \Think\Model())->execute($sql)) {
     return false;
   }
   return true;
}

function build_fields_html($fields)
{
    $html = '';
    foreach($fields as $field) {
        $field_values = $field['field_values'];
        if(!empty($field_values)) {
            $field_values = preg_replace('/\r/', '', $field_values);
            $options = preg_split('/\n/', $field_values);
            $html .= '<select name="'.$field['field_name'].'" id="input" class="form-control">
        <option value="">--请选择问题--</option>';

            foreach($options as $option) {
                $html .= "<option value=\"$option\">$option</option>";
            }
         $html .= '</select>';
        }else {
            $html .= '<div class="form-group">
        <label for="">'.$field['field_title'].'</label>
        <input type="password" class="form-control" name="'.$field['field_name'].'">
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

function get_navs($id = null, $nav_name = null)
{
	static $navs;
    
    if(!$navs) {
    	$navs = S('s_static_navs');
    }

    if(empty($navs)) {
    	$lists = M('navs')->where(['status'=>1])->order('pid', 'sort desc')->select();
    	$lists = list_to_tree($lists);
    	$navs = $lists;
    	if(count($lists) < 1000) {
	    	S('s_static_navs', $lists);
    	}
    }
    return $navs;
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

function is_login()
{
	if(!cookie('uid')) {
		if(($user = session('user_auth')) && ($sign = session('user_auth_sign'))) {
			if(data_auth_sign($user) == $sign) {
				return true;
			}else {
				return false;
			}
		}else {
			return false;
		}
	}else {
		return true;
	}
}

function data_auth_sign($data)
{
	$data = array_filter($data);
	ksort($data);
	return sha1(http_build_query($data));
}

function encrypt_password($password, $key = 'jdjKDd+(jk,l{sdf#')
{
	return empty($password) ? '' : md5(sha1($password). $key);
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