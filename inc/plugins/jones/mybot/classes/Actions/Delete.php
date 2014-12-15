<?php

class JB_MyBot_Actions_Delete extends JB_MyBot_Actions_Base
{
	protected static $type = "delete";

	public function doAction(&$pid, &$thread, &$info, &$date)
	{
		global $moderation;

		if($this->getData() == "thread" || $thread['firstpost'] == $info['pid'])
			$moderation->delete_thread($info['tid']);
		else
			$moderation->delete_post($info['pid']);
	}

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang;

		$add_delete  = $form->generate_radio_button("delete", "thread", $lang->thread, array("checked"=>true));
		$add_delete .= " ".$form->generate_radio_button("delete", "post", $lang->post);
		$form_container->output_row($lang->mybot_add_delete, $lang->mybot_add_delete_desc, $add_delete, '', array(), array('id' => 'delete'));
	}

	public static function generatePeekers()
	{
		// Make sure the regex doesn't react on "softdelete"
		return 'new Peeker($("#action"), $("#delete"), /^delete/);';
	}
}