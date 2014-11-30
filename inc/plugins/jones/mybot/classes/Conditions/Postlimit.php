<?php

class JB_MyBot_Conditions_Postlimit extends JB_MyBot_Conditions_Base
{
	protected static $type = "postlimit";

	public function getName()
	{
		global $lang;
		return $lang->sprintf($lang->mybot_conditions_postlimit, $this->getData());
	}

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang;

		$add_postlimit = $form->generate_text_box("postlimit", $data['postlimit']);
		$form_container->output_row($lang->mybot_add_postlimit, $lang->mybot_add_postlimit_desc, $add_postlimit, '', array(), array('id' => 'postlimit'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#conditions"), $("#postlimit"), /postlimit/);';
	}
}