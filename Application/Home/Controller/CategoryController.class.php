<?php
namespace Home\Controller;


class CategoryController extends \Home\Controller\HomeController
{
    public function index()
    {
        $id = I('id');

        if(empty($id)) {
            $this->error("错误");
            exit;
        }
        
        $this->assign('goods', get_cate_goods($id));
        $this->display();
    }
    

   
}