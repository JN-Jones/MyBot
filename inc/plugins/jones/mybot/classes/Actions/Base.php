<?php

abstract class JB_MyBot_Actions_Base
{
	private $data;
	private $rule;
	private $errors = array();
	protected static $type;

	public function __construct($data, $rule)
	{
		$this->data = $data;
		$this->rule = $rule;
	}

	public function getData($serialize=false)
	{
		if($serialize === true)
			return @serialize($this->data);
		return $this->data;
	}

	public function getType()
	{
		return static::$type;
	}

	public function getRule()
	{
		return $this->rule;
	}

	public function getName()
	{
		global $lang;
		$l = "mybot_actions_".static::$type;
		return $lang->$l;
	}

	public function validate()
	{
		global $lang;
		if(empty($this->data))
		{
			$l = "mybot_add_".static::$type."_not";
			$this->errors[] = $lang->$l;
			return false;
		}
		return true;
	}

	public function setError($message)
	{
		$this->errors[] = $message;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public abstract function doAction(&$pid, &$thread, &$info, &$date);

	public static function generateAdditionalFields($data) {}
	public static function generatePeekers() {}

	public function __get($key)
	{
		return $this->data[$key];
	}

	public function __set($key, $value)
	{
		$this->data[$key] = $value;
	}

	public function __isset($key)
	{
		return isset($this->data[$key]);
	}

	public function __unset($key)
	{
		unset($this->data[$key]);
	}
}