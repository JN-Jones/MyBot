<?php

class JB_MyBot_Actions_Answer extends JB_MyBot_Actions_Base
{
	protected static $type = "answer";

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