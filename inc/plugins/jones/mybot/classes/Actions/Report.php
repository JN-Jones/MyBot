<?php

class JB_MyBot_Actions_Report extends JB_MyBot_Actions_Base
{
	protected static $type = "report";

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang;

		$add_report = $form->generate_text_box("report", $data['report']);
		$form_container->output_row($lang->mybot_add_report, $lang->mybot_add_report_desc, $add_report, '', array(), array('id' => 'report'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#action"), $("#report"), /report/);';
	}
}