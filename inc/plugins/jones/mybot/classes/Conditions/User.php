<?php

class JB_MyBot_Conditions_User extends JB_MyBot_Conditions_Base
{
	protected static $type = "user";

	public static function generateAdditionalFields($data)
	{
		global $userarray, $form, $form_container, $lang;

		$add_user = $form->generate_select_box("user[]", $userarray, $data['user'], array("multiple"=>true));
		$form_container->output_row($lang->mybot_add_user, $lang->mybot_add_user_desc, $add_user, '', array(), array('id' => 'user'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#conditions"), $("#user"), /user/);';
	}
}