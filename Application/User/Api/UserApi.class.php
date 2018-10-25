<?php
namespace User\Api;

class UserApi extends Api 
{

	protected function _init()
	{
		$this->model = new \User\Model\UcenterMemberModel();
	}

	public function login($username, $password)
	{
		return $this->model->login($username, $password);
	}
	public function register($username, $password, $repassword, $email)
	{
		return $this->model->register($username, $password, $repassword, $email);
	}
}