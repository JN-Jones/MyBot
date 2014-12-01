<?php

class JB_MyBot_Conditions_String_reverse extends JB_MyBot_Conditions_Base
{
	protected static $type = "string_reverse";

	public function doCheck($thread, $info)
	{
		return true;
	}

	public function getName()
	{
		return "";
	}

	public function validate()
	{
		return true;
	}
}