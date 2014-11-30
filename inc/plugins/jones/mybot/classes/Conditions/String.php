<?php

class JB_MyBot_Conditions_String extends JB_MyBot_Conditions_Base
{
	protected static $type = "string";

	public function getName()
	{
		global $lang;

		if($this->getRule()->getCondition("string_reverse") !== false && $this->getRule()->getCondition("string_reverse")->getData())
			return $lang->mybot_conditions_string_reverse;
		return $lang->mybot_conditions_string;
	}

	public static function generateAdditionalFields($data)
	{
		global $form, $mybb, $form_container, $lang;

		$add_string = $form->generate_text_area("string", $data['string']);
		$form_container->output_row($lang->mybot_add_string, $lang->mybot_add_string_desc, $add_string, '', array(), array('id' => 'string'));

		$add_string_reverse = $form->generate_yes_no_radio("string_reverse", $data['string_reverse']);
		$form_container->output_row($lang->mybot_add_string_reverse, $lang->mybot_add_string_reverse_desc, $add_string_reverse, '', array(), array('id' => 'string_reverse'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#conditions"), $("#string"), /string/);
				new Peeker($("#conditions"), $("#string_reverse"), /string/);';
	}
}