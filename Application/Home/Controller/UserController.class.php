<?php
namespace Home\Controller;

use User\Api\UserApi;
class UserController extends \Think\Controller
{
    public function _initialize()
    {
        $no_login_actions = array('login', 'register');

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

        }else {
            $fields = M('registerFields')
             ->field(['id', 'field_name', 'field_title', 'field_values'])
             ->where(['type'=>1, 'enabled'=>1])
             ->select();

            $this->assign('fields', build_fields_html($fields));
            $this->display();
        }
    }
   
}