<?php

class JB_MyBot_Conditions_Forum extends JB_MyBot_Conditions_Base
{
	protected static $type = "forum";

	public function doCheck($thread, $info)
	{
		return @in_array($info['fid'], $this->getData());
	}

	public static function generateAdditionalFields($data)
	{
		global $form, $mybb, $form_container, $lang;

		$add_forum = $form->generate_forum_select("forum[]", $data['forum'], array("multiple"=>true));
		$form_container->output_row($lang->mybot_add_forum, $lang->mybot_add_forum_desc, $add_forum, '', array(), array('id' => 'forum'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#conditions"), $("#forum"), /forum/);';
	}
}