<?php

namespace User\Api;

define('UCENTER_ROOT', dirname(dirname(__FILE__)));

require_once UCENTER_ROOT . '/Common/function.php';
require_once UCENTER_ROOT . '/Conf/config.php';

abstract class Api 
{
	public function __construct() {
		$this->_init();
	}

	abstract protected function _init();

}