<?php

class mongobase_btc extends mongobase_mb
{
    
    function __construct($options = array(), $key = 'btc')
	{
		
	}

	public function query($options = array())
	{
		$ini = $this->ini();
		$results = false;
		$defaults = array(
			'method'	=> 'query',
			'function'	=> 'getbalance',
			'options'	=> null
		);
		$settings = array_merge($defaults, $options);
		$jsonrpc = new mongobase_jsonrpc(array('url'=>'http://'.$ini['btc']['username'].':'.$ini['btc']['password'].'@'.$ini['btc']['host'].':'.$ini['btc']['port'].'/'));
		if($settings['options'])
		{
			if(is_array($settings['options']) && count($settings['options']) == 1)
			{
				$results = $jsonrpc->$settings['function']($settings['options'][0]);
			}
			elseif(is_string($settings['options']))
			{
				if($settings['function'] == 'getbalance') $results = $jsonrpc->$settings['function']($settings['options'], 0);
				else $results = $jsonrpc->$settings['function']($settings['options']);
			}
		}
		else $results = $jsonrpc->$settings['function']();
		return $results;
	}
    
}