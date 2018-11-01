<?php
namespace Home\Controller;


class IndexController extends HomeController
{
    public function index()
    {
        $categories = D('categories')->getAllCategories();
        $this->assign('categories', $categories);
        $this->display();    	
    }
    public function login($username = '', $password = '', $remember = '')
    {
        if(IS_POST) {
            $uid = D("User")->login($username, $password, $remember);
            if($uid === false) {
                $this->error(D("User")->getError());
            }

            $this->success("登录成功", U("index"));
        }else {
            $this->display();
        }
    }

    public function logout()
    {
        session('user_auth', null);
        session('user_auth_sign', null);
        $this->redirect('index');
    }

    public function register($username = '', $password = '', $repassword = '')
    {
        if(IS_POST) {
            $uid = D("User")->register($username, $password, $repassword);
            if($uid > 0) {
                $this->success("注册成功", U("index"));
            }else {
                $this->error($this->showErrMsg($uid));
            }
        }else {
           $this->display();
        }
    }

  

    private function showErrMsg($code = 0)
    {
        switch($code)
        {
            case -1:
                $msg = '用户名5到12位';
                break;
            case -2:
                $msg = '用户名已经存在';
                break;
             case -3:
                $msg = '密码5到16位';
                break;
             case -4:
                $msg = '确认密码不一致';
                break;
            default:
                $msg = '未知错误';
                break;
        }
        return $msg;
    }

    public function test()
    {
        dump(M('goods')->getField('good_name', true));
    }

    public function decendant()
    {
        $id = I('get.id');
        if(empty($id)) {
            $this->ajaxReturn(['status'=>0, 'error'=>'错误']);
        }
        $id = base64_decode(substr($id, 4));
        $categories = D('categories')->getAllCategories(false);
        $decendants = build_list_html(D('categories')->findDecendant($categories, $id));
        if(empty($decendants)) {
            $this->ajaxReturn(['status'=>1, 'data'=>array()]);
        }
        $this->ajaxReturn(['status'=>1, 'data'=>$decendants]);
    }


   
}