<?php
namespace Home\Controller;

use User\Api\UserApi;
class UserController extends \Think\Controller
{
    public function _initialize()
    {
        $no_login_actions = array('test', 'login', 'register');

        if(empty(session('user.auth')) || empty(session('user.auth_sign'))) {
            if(empty(cookie('user.auth')) || empty(session('cookie.auth_sign'))) {
                if(!in_array(ACTION_NAME, $no_login_actions)) {
                    $this->redirect('login');
                    exit;
                }
            }else {
                if(cookie('user.auth_sign') != data_auth_sign(cookie('user.auth'))) {
                    if(!in_array(ACTION_NAME, $no_login_actions)) {
                        cookie('user', null);
                        $this->redirect('login');
                    }
                }
            }
        }else {
            if(session('user.auth_sign') != data_auth_sign(session('user.auth'))) {
                if(!in_array(ACTION_NAME, $no_login_actions)) {
                  session('user', null);
                  $this->redirect('login');
               }
            }
        }
        
    }
    public function test()
    {
    }

    public function index()
    {
      $this->display();   
    }

    public function login()
    {
      $this->display();
    }


    
    public function register()
    {
        if(IS_POST) {
            $data = I('post.');
            $users = M('myUsers');

            $api = new UserApi();
            $uid = $api->register($data['username'], $data['password'], $data['repassword'], $data['email']);

            if($uid > 0) {
                
                if(!empty($configs['register_points'])) {
                    log_account_change($uid, 0 , 0, $configs['register_points'],$configs['register_points'], '注冊送積分');
                }
            }else {
                $this->error($this->getError($uid));
                exit;
            }

        }else {
            $fields = M('registerFields')
             ->field(['id', 'field_name', 'field_title', 'field_values'])
             ->where(['type'=>1, 'enabled'=>1])
             ->select();

            $this->assign('fields', build_fields_html($fields));
            $this->display();
        }
    }
   

    protected function getError($uid)
    {
        $msg = '';
        switch($uid) {
            case -1:
                $msg = '用户名6到12位';
                break;
            case -2:
                $msg = '用户名不得重复';
                break;
            case -3:
                $msg = '密码5到16位';
                break;
            case -4:
                $msg = '确认密码不一致';
                break;
            case -5:
                $msg = '邮箱格式不正确';
                break;

            default:
                $msg =  $uid . '未知错误';
                break;
        }
        return $msg;
    }
}