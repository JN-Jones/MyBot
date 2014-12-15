<?php

class JB_MyBot_Actions_Softdelete extends JB_MyBot_Actions_Base
{
	protected static $type = "softdelete";

	public function doAction(&$pid, &$thread, &$info, &$date)
	{
		global $moderation;

		if($this->getData() == "thread" || $thread['firstpost'] == $info['pid'])
		{
			if($info['visible'] != -1)
				$moderation->soft_delete_threads($info['tid']);
			else
				$moderation->restore_threads($info['tid']);
		}
		else
		{
			if($info['visible'] != -1)
				$moderation->soft_delete_posts(array($info['pid']));
			else
				$moderation->restore_posts(array($info['pid']));
		}
	}

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang;

		$add_softdelete  = $form->generate_radio_button("softdelete", "thread", $lang->thread, array("checked"=>true));
		$add_softdelete .= " ".$form->generate_radio_button("softdelete", "post", $lang->post);
		$form_container->output_row($lang->mybot_add_softdelete, $lang->mybot_add_softdelete_desc, $add_softdelete, '', array(), array('id' => 'softdelete'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#action"), $("#softdelete"), /softdelete/);';
	}
}