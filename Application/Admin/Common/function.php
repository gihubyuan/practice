<?php

function put_affiliate($data)
{
	unset($data['__hash__']);
	return M('systemConfig')->where(['config_name'=>'affiliate'])->save(['config_value'=>serialize($data)]);
}