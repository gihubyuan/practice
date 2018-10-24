<?php
namespace Home\Controller;


class IndexController extends HomeController
{
    public function index()
    {
        
        /*$lists = D("User")->lists(null);
        $this->assign('lists', $lists);*/
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

    public function test()
    {
        if(IS_POST) {
            if(!M('')->autoCheckToken(I('request.'))) {
                $this->error("重复访问");
                exit;
            }
            dump(I('request.'));
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

    public function delete()
    {
        $ids = I('ids');
        if(!$ids) {
            $this->error("错误");
            exit;
        }
        $map['id'] = array('in', $ids);
        if(M('users')->where($map)->delete()) {
            $this->success("删除陈宫");
        }else {
            $this->error("删除失败");
        }
    }
   
}