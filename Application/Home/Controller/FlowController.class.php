<?php
namespace Home\Controller;

use Think\Controller;

class FlowController extends \Home\Controller\HomeController
{
	public function addToCart()
	{
			$specs = I('post.specs');
			$id = I('post.id');
			$num = I('post.num');
			$num = intval($num) < 1 ? 1 : intval($num);


			// $result = array('error'=>'', 'content'=>'');

			if(empty($id))
			{
				$this->ajaxReturn(['error'=>'错误']);
			}

			$result = add_to_cart($id, $num, $specs);
			$this->ajaxReturn($result);
	}

}


