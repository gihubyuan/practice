<?php
namespace Home\Controller;

use User\Api\UserApi;
class UserController extends \Think\Controller
{
    public function _initialize()
    {
        if(is_login()) {
            $this->redirect('Index/index');
            exit;
        }
        
        $configs = api('Config/lists');
          C($configs);
         if(C('SITE_CLOSED') == 1) {
            exit("网站已关闭,请稍后在访问~");
        }
    }
    public function test()
    {
       echo md5('yuanwei888');
    }   

    public function index()
    {
      $this->display();   
    }

    public function getByQuestionFirst()
    {
       $this->assign('act', ACTION_NAME);
       $this->display();
    }

    public function getByQuestionSecond()
    {
        if(IS_POST) {
            $data = I('post.');
            $user = M('myUsers')->where(['username'=>$data['username']])->find();
            session('temp_user_id', $user['id']);
            session('temp_username', $user['username']);
            session('temp_pwd_question', $user['pwd_question']);
        }else {
            $this->assign('field', build_question_html(4));
            $this->assign('act', ACTION_NAME);
            $this->display('getByQuestionFirst');
        }
        
    }



    public function login()
    {
      define('CAPTCHA_LOGINFAIL', 1);
      if(is_null(session('user.login_fail'))) {
        session('user.login_fail', 0);
      }
      if(IS_POST) {
        $data = I('post.');

        if(C('REGISTER_CAPTCHA') > 0 && ( !(C('REGISTER_CAPTCHA') & CAPTCHA_LOGINFAIL) || ((C('REGISTER_CAPTCHA') & CAPTCHA_LOGINFAIL) && session('user.login_fail') > 2))) {
            if(empty($data['vcode'])) {
                    $this->error("验证码不得唯恐");
                }
            if(!check_verify($data['vcode'], 2)) {
                    $this->error("验证码错误");
             }
        }

        if(login($data['username'], $data['password'], $data['remember'])) {
            $this->success('登陆成功', U('Index/index'));
        }else {
            $this->error('登陆失败');
        }

      }else {
        if(C('REGISTER_CAPTCHA') > 0 && ( !(C('REGISTER_CAPTCHA') & CAPTCHA_LOGINFAIL) || ((C('REGISTER_CAPTCHA') & CAPTCHA_LOGINFAIL) && session('user.login_fail') > 2))) {
            $this->assign('captcha_enabled', 1);
            $this->assign('rand', mt_rand());
        }else {
            $this->assign('captcha_enabled', 0);
        }
        $this->display();
      }
      
    }
    

    public function register()
    {
        define('CAPTCHA_KO', 0);
        
        if(IS_POST) {
            if(C('REGISTER_CLOSED') == 1)  {
                $this->error('注册关闭');
                exit;
            }
            $data = I('post.');

            if(CAPTCHA_KO & C('REGISTER_CAPTCHA') > 0) {
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

            if(CAPTCHA_KO & C('REGISTER_CAPTCHA') > 0) {
                $this->assign('captcha_on', 1);
                $this->assign('RAND', mt_rand());
            }
            $this->assign('fields', build_fields_html());
            $this->display();
        }
    }

    function getVerifyCode($id)
    {
        $config = array(
            'fontSize' => 30, // 验证码字体大小
            'length' => 4, // 验证码位数
       );
        $Verify = new \Think\Verify($config);
        $Verify->entry($id);
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