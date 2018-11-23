<?php
namespace Home\Controller;

use User\Api\UserApi;

class UserController extends \Think\Controller
{
    public function _initialize()
    {   
        define('CAPTCHA_REGISTER', 64);
        define('CAPTCHA_LOGIN', 128);
        define('CAPTCHA_COMMENT', 256);
        define('CAPTCHA_LOGIN_FAIL', 512); 
        $configs = api('Config/lists');
        C($configs);
         if(C('SITE_CLOSED') == 1) {
            exit("网站已关闭,请稍后在访问~");
        }
        
        if(is_login()) {
            $this->redirect('Index/index');
            exit;
        }
        $this->assign('navs', get_navs());
        
    }
   
    function getByQuestionFirst()
    {
        $this->assign('act', ACTION_NAME);
        $this->display();
    }
    function getByQuestionSecond()
    {
        if(IS_POST) {
            $username = I('post.username'); 
            !$username && $this->error('错误');
            $user = M('myUsers')->field(['id','username', 'pwd_question'])->where(['username'=>$username])->find();
            empty($user) && $this->error("错误");
            empty($user['pwd_question']) && $this->error("没有匹配到密码问题");
            session('temp_username', $user['username']); 
            session('temp_userid', $user['id']); 
            $this->assign('questions', build_fields_html(4));
            $this->assign('act', ACTION_NAME);
            $this->assign('uid', $user['id']);
            $this->display('getByQuestionFirst');
        }
    }

    function getByQuestionThird()
    {
        if(IS_POST) {
            $question = I('post.extend_field4'); 
            $answer = I('post.pwd_question_answer'); 
            $uid = I('post.uid'); 
            (empty($question) || empty($answer || empty($uid))) && $this->error('错误');
            if(is_null(session('temp_username')) || is_null(session('temp_userid')) || ($uid != session('temp_userid'))) {
                $this->error('错误');
            }
            
            $user = M('myUsers')->field(['id','pwd_question', 'pwd_question_answer'])->where(['id'=>session('temp_userid')])->find();
            empty($user) && $this->error("用户不存在");
            session('temp_username', null); 
            session('temp_userid', null); 
            if($user['pwd_question'] != $question) {
                $this->error("您的问题选择错误");
            }else {
                if($user['pwd_question_answer'] == $answer) {
                    session('verify_userid', $user['id']);
                    $this->assign('questions', build_fields_html(4));
                    $this->assign('act', ACTION_NAME);
                    $this->display('getByQuestionFirst');
                }else {
                    $this->error("答案错误");
                }
            }

        }
    }

    function getByQuestionFourth()
    {
        if(IS_POST) {
            $password = I('post.password'); 
            empty($password) && $this->error("密码不得唯恐");
            $uid = session('verify_userid');
            session('verify_userid', null);
            is_null( $uid) &&  $this->error("ID错误");
            $userApi = new UserApi();
            if($userApi->setProfileById($uid, $password)) {
                $userApi->logout();
            }else {
                $this->error("更新失败");
            }
        }else {
            $this->error("错误");
        }
    }

    public function login()
    {
          if(is_null(session('user.login_fail')))
          {
              session('user.login_fail', 0);
          }
          if(IS_POST)
          {
                $data = I('post.');
                if((C('CAPTCHA') & CAPTCHA_LOGIN) && (!(C('CAPTCHA') & CAPTCHA_LOGIN_FAIL) || ((C('CAPTCHA') & CAPTCHA_LOGIN_FAIL) && session('user.login_fail') > 2))) {
                    if(empty($data['vcode']))
                    {
                        $this->error("验证码不得唯恐");
                    }
                    if(!check_verify($data['vcode'], 2))
                    {
                        $this->error("验证码错误");
                    }
                }

                if(login($data['username'], $data['password'], $data['remember']))
                {
                    $this->success('登陆成功', U('Index/index'));
                }
                else
                {
                    $this->error('登陆失败');
                }
          }
          else
          {
                if((C('CAPTCHA') & CAPTCHA_LOGIN) && (!(C('CAPTCHA') & CAPTCHA_LOGIN_FAIL) || ((C('CAPTCHA') & CAPTCHA_LOGIN_FAIL) && session('user.login_fail') > 2)))
                {
                    $this->assign('captcha_enabled', 1);
                }
                else
                {
                    $this->assign('captcha_enabled', 0);
                }
                $this->display();
          }
    }
       
    public function register()
    {
        if(IS_POST) {
            if(C('REGISTER_CLOSED') == 1)  {
                $this->error('注册关闭');
                exit;
            }
            $data = I('post.');

            if((CAPTCHA_REGISTER & C('CAPTCHA')) > 0) {
                if(empty($data['vcode'])) {
                    $this->error("验证码唯恐");
                }
                if(!check_verify($data['vcode'], 1)) {
                    $this->error("验证码错误");
                }
            }
           $uid  = register($data);
           if($uid >0) {
                dump(session());
                echo '注册成功';
            }else {
                $this->error($this->getError($uid));
                exit;
            }

        }else {
             if(C('REGISTER_CLOSED') == 1)  {
                 $this->assign('register_on', 0);
             }else {
                 $this->assign('register_on', 1);
             }

            if((CAPTCHA_REGISTER & C('CAPTCHA')) > 0) {
                $this->assign('captcha_on', 1);
            }
            $this->assign('fields', build_fields_html());
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