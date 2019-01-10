<?php 
namespace Admin\Controller;

class LoginController extends \Think\Controller
{

	function login()
	{
		$this->display();
	}	

	function loginHandle()
	{
		session('admin_id', 1);
		$this->redirect('Index/index');		
	}

}