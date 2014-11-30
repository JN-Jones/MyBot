<?php

class JB_MyBot_Conditions_Prefix extends JB_MyBot_Conditions_Base
{
	protected static $type = "prefix";

	public static function generateAdditionalFields($data)
	{
		global $form, $mybb, $form_container, $lang;

		$prefixes = build_prefixes();
		if(!$prefixes)
			$prefixes = array();
		$pr = array();
		foreach($prefixes as $prefix)
			$pr[$prefix['pid']] = $prefix['prefix'];
		$add_prefixes = $form->generate_select_box("prefix[]", $pr, $data['prefix'], array("multiple"=>true));
		$form_container->output_row($lang->mybot_add_prefix, $lang->mybot_add_prefix_desc, $add_prefixes, '', array(), array('id' => 'prefix'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#conditions"), $("#prefix"), /prefix/);';
	}
}