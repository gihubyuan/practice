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

    public function logout()
    {
        session('user_auth', null);
        session('user_auth_sign', null);
        $this->redirect("Index/index");
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