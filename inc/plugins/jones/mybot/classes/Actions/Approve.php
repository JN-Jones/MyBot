<?php

class JB_MyBot_Actions_Approve extends JB_MyBot_Actions_Base
{
	protected static $type = "approve";

	public function doAction(&$pid, &$thread, &$info, &$date)
	{
		global $moderation;

		if($this->getData() == "thread" || $thread['firstpost'] == $info['pid'])
		{
			if($thread['visible'] != 1)
				$moderation->approve_threads($info['tid']);
			else
				$moderation->unapprove_threads($info['tid']);
		}
		else
		{
			if($info['visible'] != 1)
				$moderation->approve_posts(array($pid));
			else
				$moderation->unapprove_posts(array($pid));
		}

	}

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