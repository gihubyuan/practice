<?php

namespace Common\Api;

class ConfigApi
{
	public static function lists($param = '')
	{
		$configs = M('systemConfig')
			->field(['config_name', 'config_value'])
			->where(['enabled'=>1])
			->order('sort desc')
			->select();

		if(!empty($configs)) {
			return self::parseLists($configs);
		}
	}

	private static function parseLists($configs)
	{
		 $arr = array();
		 foreach($configs as $key => $config) {
		 	 $arr[$config['config_name']] = $config['config_value'];
		 }
		 return $arr;
	} 
}