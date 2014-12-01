<?php

class JB_MyBot_Actions_Report extends JB_MyBot_Actions_Base
{
	protected static $type = "report";

	public function doAction(&$pid, &$thread, &$info, &$date)
	{
		global $mybb, $db, $lang, $cache;

		require_once MYBB_ROOT."inc/functions_modcp.php";
		$lang->load("report");
	
		$report_type  = "post";
		$report_string = "report_reason_post";
		$report_title = $lang->$report_string;
	
		$post = get_post($pid);
	
		$id = $post['pid'];
		$id2 = $post['tid'];
		$report_type_db = "(type = 'post' OR type = '')";
	
		$forum = get_forum($post['fid']);
	
		$id3 = $forum['fid'];
	
		// Ignore duplicated reports by our bot
		$query = $db->simple_select("reportedcontent", "*", "reportstatus != '1' AND id = '{$id}' AND {$report_type_db}");

		if($db->num_rows($query))
		{
			// Existing report
			$report = $db->fetch_array($query);
			$report['reporters'] = my_unserialize($report['reporters']);

			if($mybb->user['uid'] == $report['uid'] || is_array($report['reporters']) && in_array($mybb->user['uid'], $report['reporters']))
			{
				// Already reported
				return;
			}
		}
	
		// Is this an existing report or a new offender?
		if(!empty($report))
		{
			// Existing report, add vote
			$report['reporters'][] = $mybb->user['uid'];
			update_report($report);
		}
		else
		{
			// Bad user!
			$new_report = array(
				'id' => $id,
				'id2' => $id2,
				'id3' => $id3,
				'uid' => $mybb->settings['mybot_user'],
				'reason' => trim($this->getData())
			);
	
			add_report($new_report);
		}
	}

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