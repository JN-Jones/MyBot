<?php

class JB_MyBot_Actions_Pm extends JB_MyBot_Actions_Base
{
	protected static $type = "pm";

	public function validate()
	{
		global $lang;
		if(count($this->getData()) == 0)
			$this->setError($lang->mybot_add_pm_not);
		if(empty($this->user))
			$this->setError($lang->mybot_add_pm_user_not);
   		if(empty($this->subject))
			$this->setError($lang->mybot_add_subject_not);
		if(empty($this->message))
			$this->setError($lang->mybot_add_message_not);

		if(count($this->getErrors()) > 0)
		    return false;
		return true;
	}

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang, $userarray;

		if(isset($data['pm']['user']))
		    $data['pm_user'] = $data['pm']['user'];
		if(isset($data['pm']['subject']))
		    $data['subject'] = $data['pm']['subject'];
		if(isset($data['pm']['message']))
		    $data['message'] = $data['pm']['message'];

		$pm_list = array(
				"last" => $lang->mybot_add_pm_last,
				"start" => $lang->mybot_add_pm_start,
				"other" => $lang->mybot_add_pm_other);
		$add_pm = $form->generate_select_box("pm", $pm_list, $data['pm'], array("id"=>"pm_select"));
		$form_container->output_row($lang->mybot_add_pm, $lang->mybot_add_pm_desc, $add_pm, '', array(), array('id' => 'pm'));

		$add_pm_user = $form->generate_select_box("pm_user", $userarray, $data['pm_user']);
		$form_container->output_row($lang->mybot_add_pm_user." <em>*</em>", $lang->mybot_add_pm_user_desc, $add_pm_user, '', array(), array('id' => 'pm_user'));

		$add_subject = $form->generate_text_box("subject", $data['subject']);
		$form_container->output_row($lang->mybot_add_subject, $lang->mybot_add_subject_desc, $add_subject, '', array(), array('id' => 'subject'));

		$add_message = $form->generate_text_area("message", $data['message']);
		$form_container->output_row($lang->mybot_add_message, $lang->mybot_add_message_desc, $add_message, '', array(), array('id' => 'message'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#action"), $("#pm"), /pm/);
				new Peeker($("#action"), $("#subject"), /pm/);
				new Peeker($("#action"), $("#message"), /pm/);

				new Peeker($("#pm_select"), $("#pm_user"), /other/, false);';
	}
}