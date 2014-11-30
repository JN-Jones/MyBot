<?php

class JB_MyBot_Actions_Manager
{
	public static function create($type, $data, $rule)
	{
		$className = "JB_MyBot_Actions_".ucfirst($type);
		if(!class_exists($className))
		    return false;
		return new $className($data, $rule);
	}

	public static function getTypes()
	{
		return array(
			"answer",
			"move",
			"delete",
			"stick",
			"close",
			"report",
			"approve",
			"pm"
		);
	}

	public static function getListArray()
	{
		global $lang;
		$array = array();
		foreach(static::getTypes() as $type)
		{
			$l = "mybot_add_action_{$type}";
			$array[$type] = $lang->$l;
		}
		return $array;
	}

	public static function generateAdditionalFields($data)
	{
		foreach(static::getTypes() as $type)
		{
			$className = "JB_MyBot_Actions_".ucfirst($type);
			if(!class_exists($className))
				continue;
			$className::generateAdditionalFields($data);
		}
	}

	public static function generatePeekers()
	{
		$peekers = "";
		foreach(static::getTypes() as $type)
		{
			$className = "JB_MyBot_Actions_".ucfirst($type);
			if(!class_exists($className))
			    continue;
			$peekers .= $className::generatePeekers()."\n";
		}
		return $peekers;
	}
}