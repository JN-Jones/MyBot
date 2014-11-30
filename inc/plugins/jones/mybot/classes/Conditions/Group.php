<?php

class JB_MyBot_Conditions_Group extends JB_MyBot_Conditions_Base
{
	protected static $type = "group";

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang;

		$add_group = $form->generate_group_select("group[]", $data['group'], array("multiple"=>true));
		$form_container->output_row($lang->mybot_add_group, $lang->mybot_add_group_desc, $add_group, '', array(), array('id' => 'group'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#conditions"), $("#group"), /group/);';
	}
}