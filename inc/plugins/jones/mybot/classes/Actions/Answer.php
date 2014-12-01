<?php

class JB_MyBot_Actions_Answer extends JB_MyBot_Actions_Base
{
	protected static $type = "answer";

	public function doAction(&$pid, &$thread, &$info, &$date)
	{
		global $additional, $posthandler, $mybb;

		$subject = preg_replace('#RE:\s?#i', '', $info['subject']);
		$subject = "RE: ".$subject;

		// Set the post data that came from the input to the $post array.
		$post = array(
			"tid"		=> $info['tid'],
			"replyto"	=> $pid,
			"fid"		=> $info['fid'],
			"subject"	=> $subject,
			"icon"		=> $info['icon'],
			"uid"		=> $mybb->settings['mybot_user'],
			"username"	=> $additional['botname'],
			"message"	=> JB_MyBot_Helpers::parse($this->getData(), "thread", $additional),
			"dateline"	=> $date
		);
		$posthandler->set_data($post);
		$valid_thread = $posthandler->validate_post();
		if(!$valid_thread)
		{
//			die(inline_error($posthandler->get_friendly_errors()));
		}
		else
		{
			$ninfo = $posthandler->insert_post();
			$pid = $ninfo['pid'];
			++$date;
		}
	}

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang;

		$add_answer = $form->generate_text_area("answer", $data['answer']);
		$form_container->output_row($lang->mybot_add_answer, $lang->mybot_add_answer_desc, $add_answer, '', array(), array('id' => 'answer'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#action"), $("#answer"), /answer/);';
	}
}