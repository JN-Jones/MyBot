<?php

class JB_MyBot_Actions_Approve extends JB_MyBot_Actions_Base
{
	protected static $type = "approve";

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang;

		$add_approve  = $form->generate_radio_button("approve", "thread", $lang->thread, array("checked"=>true));
		$add_approve .= " ".$form->generate_radio_button("approve", "post", $lang->post);
		$form_container->output_row($lang->mybot_add_approve, $lang->mybot_add_approve_desc, $add_approve, '', array(), array('id' => 'approve'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#action"), $("#approve"), /approve/);';
	}
}