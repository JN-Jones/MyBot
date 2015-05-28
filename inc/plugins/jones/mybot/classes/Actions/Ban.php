<?php

class JB_MyBot_Actions_Ban extends JB_MyBot_Actions_Base
{
	protected static $type = "ban";

	public function doAction(&$pid, &$thread, &$info, &$date)
	{
		global $mybb, $db, $lang, $cache;

		$user = $this->getData();

		if($user == "last")
		{
			$user = $info['uid'];
		}
		else if($user == "start")
		{
			$post = get_post($thread['firstpost']);
			$user = $post['uid'];
		}

		// We don't want to ban super admins
		if(is_super_admin($user))
			return;

		$user = dbe($user);

		// Check whether user is already banned
		$tQuery = $db->simple_select('banned', 'uid', "uid={$user}");
		if($db->num_rows($tQuery) > 0)
			return;

		// This is a new ban!
		$user = get_user($user);
		$insert_array = array(
			'uid' => $user['uid'],
			'gid' => 0,
			'oldgroup' => $user['usergroup'],
			'oldadditionalgroups' => $user['additionalgroups'],
			'olddisplaygroup' => $user['displaygroup'],
			'admin' => (int)$mybb->settings['mybot_user'],
			'dateline' => TIME_NOW,
			'bantime' => '---',
			'lifted' => 0,
			'reason' => ''
		);
		$db->insert_query('banned', $insert_array);

		// Moved the user to the 'Banned' Group
		$update_array = array(
			'usergroup' => 7,
			'displaygroup' => 0,
			'additionalgroups' => '',
		);
		$db->update_query('users', $update_array, "uid = '{$user['uid']}'");
		$db->delete_query("forumsubscriptions", "uid = '{$user['uid']}'");
		$db->delete_query("threadsubscriptions", "uid = '{$user['uid']}'");
		$cache->update_banned();
	}

	public static function generateAdditionalFields($data)
	{
		global $form, $form_container, $lang;

		$list = array(
			"last" => $lang->mybot_add_pm_last,
			"start" => $lang->mybot_add_pm_start
		);
		$add_ban = $form->generate_select_box("ban", $list, $data['ban'], array("id"=>"bam_select"));
		$form_container->output_row($lang->mybot_add_ban, $lang->mybot_add_ban_desc, $add_ban, '', array(), array('id' => 'ban'));
	}

	public static function generatePeekers()
	{
		return 'new Peeker($("#action"), $("#ban"), /ban/);';
	}
}
