<?php

class JB_MyBot_Actions_Delete extends JB_MyBot_Actions_Base
{
	protected static $type = "delete";

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang;

		$add_delete  = $form->generate_radio_button("delete", "thread", $lang->thread, array("checked"=>true));
		$add_delete .= " ".$form->generate_radio_button("delete", "post", $lang->post);
		$form_container->output_row($lang->mybot_add_delete, $lang->mybot_add_delete_desc, $add_delete, '', array(), array('id' => 'delete'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#action"), $("#delete"), /delete/);';
	}
}