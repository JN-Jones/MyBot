<?php

class JB_MyBot_Conditions_Manager
{
	public static function create($type, $data, $rule)
	{
		$className = "JB_MyBot_Conditions_".ucfirst($type);
		if(!class_exists($className))
		    return false;
		return new $className($data, $rule);
	}

	public static function getTypes()
	{
		return array(
			"user",
			"group",
			"forum",
			"string",
			"postlimit",
			"prefix"
		);
	}

	public static function getListArray()
	{
		global $lang;
		$array = array();
		foreach(static::getTypes() as $type)
		{
			$l = "mybot_add_conditions_{$type}";
			$array[$type] = $lang->$l;
		}
		return $array;
	}

	public static function generateAdditionalFields($data)
	{
		foreach(static::getTypes() as $type)
		{
			$className = "JB_MyBot_Conditions_".ucfirst($type);
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
			$className = "JB_MyBot_Conditions_".ucfirst($type);
			if(!class_exists($className))
			    continue;
			$peekers .= $className::generatePeekers()."\n";
		}
		return $peekers;
	}
}