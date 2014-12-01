<?php

class JB_MyBot_Conditions_User extends JB_MyBot_Conditions_Base
{
	protected static $type = "user";

	public function doCheck($thread, $info)
	{
		// -1 is thread opener
		if(@in_array(-1, $this->getData()) && $thread['uid'] == $info['uid'])
			return true;

		// else we need to check the real uid
		if(@in_array($info['uid'], $this->getData()))
			return true;

		return false;
	}

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