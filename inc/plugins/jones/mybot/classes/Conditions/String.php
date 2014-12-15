<?php

class JB_MyBot_Conditions_String extends JB_MyBot_Conditions_Base
{
	protected static $type = "string";

	public function doCheck($thread, $info)
	{
		global $additional;

		$strings = explode("\n", $this->string);
		$found = false;
		$all = true;
		$length = count($strings);
		$reverse = false;
		$reverse = $this->reverse;
		foreach($strings as $key => $string)
		{
			if($key+1 != $length)
				$string = substr($string, 0, -1);
			if($reverse)
			{
				if($string != "" && (strpos(strtolower($info['message']), strtolower($string)) === false && strpos(strtolower($info['subject']), strtolower($string)) === false))
					$all = false;
			}
			else
			{
				if($string != "" && (strpos(strtolower($info['message']), strtolower($string)) !== false || strpos(strtolower($info['subject']), strtolower($string)) !== false))
				{
					$found = true;
					if($additional['foundstring'] == "")
						$additional['foundstring'] = $string;
				}
			}
		}

		if($reverse)
			return !$all;
	
		return $found;

	}

	public function getName()
	{
		global $lang;

		if($this->reverse != false)
			return $lang->mybot_conditions_string_reverse;
		return $lang->mybot_conditions_string;
	}

	public static function generateAdditionalFields($data)
	{
		global $form, $mybb, $form_container, $lang;

		$add_string = $form->generate_text_area("string[string]", $data['string']['string']);
		$form_container->output_row($lang->mybot_add_string, $lang->mybot_add_string_desc, $add_string, '', array(), array('id' => 'string'));

		$add_string_reverse = $form->generate_yes_no_radio("string[reverse]", $data['string']['reverse']);
		$form_container->output_row($lang->mybot_add_string_reverse, $lang->mybot_add_string_reverse_desc, $add_string_reverse, '', array(), array('id' => 'string_reverse'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#conditions"), $("#string"), /string/);
				new Peeker($("#conditions"), $("#string_reverse"), /string/);';
	}
}