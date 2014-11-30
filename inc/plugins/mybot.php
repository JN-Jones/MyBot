<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Test whether core is installed and if so get it up
defined("JB_CORE_INSTALLED") or require_once MYBB_ROOT."inc/plugins/jones/core/include.php";

$plugins->add_hook("admin_config_plugins_activate_commit", "mybot_installed");

$plugins->add_hook("member_do_register_end", "mybot_register");
$plugins->add_hook("newthread_do_newthread_end", "mybot_thread");
$plugins->add_hook("newreply_do_newreply_end", "mybot_post");
$plugins->add_hook("global_end", "mybot_birthday");

global $mybb;
if($mybb->input['module'] == "config-settings" && $mybb->input['action'] == "change")
	$plugins->add_hook("admin_page_output_footer", array("JB_MyBot_Helpers", "printConfigPeekers"));

if(JB_CORE_INSTALLED === true)
{
	JB_AdminModules::addModule("user", "mybot", "mybot.php");
	JB_AdminModules::addModule("config", "installbot", "installbot.php", false, false, "plugins", false);
}

function mybot_info()
{
	$donate = '<div style="float: right"><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="SQLGRVKSDMZHA">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
</form></div>';

	$info = array(
		"name"			=> "MyBot",
		"description"	=> "Adds a (simple) Bot to your MyBB{$donate}",
		"website"		=> "http://jonesboard.de",
		"author"		=> "Jones",
		"authorsite"	=> "http://jonesboard.de",
		"version"		=> "1.4",
		"codename"		=> "mybot",
		"compatibility"	=> "18*"
	);

	if(JB_CORE_INSTALLED === true)
	    return JB_CORE::i()->getInfo($info);

	return $info;
}

function mybot_install()
{
	jb_install_plugin("mybot");
}

function mybot_installed()
{
	global $install_uninstall, $codename;
	if($codename == "mybot" && $install_uninstall)
		admin_redirect("index.php?module=config-installbot");
}

function mybot_is_installed()
{
	global $db;
	return $db->table_exists("mybot");
}

function mybot_uninstall()
{
	JB_Core::i()->uninstall("mybot");
}

function mybot_register()
{
	global $mybb, $user_info, $db;
	$additional['registered'] = $user_info['username'];
	$additional['regid'] = $user_info['uid'];
	$additional['botname'] = $db->fetch_field($db->simple_select("users", "username", "uid='{$mybb->settings['mybot_user']}'"), "username");
	if($mybb->settings['mybot_react']=="pm") {
		$message = JB_MyBot_Helpers::parse($mybb->settings['mybot_react_pm'], "register", $additional);
		$subject = JB_MyBot_Helpers::parse($mybb->settings['mybot_react_pm_subject'], "register", $additional);
		//Write PM
		require_once MYBB_ROOT."inc/datahandlers/pm.php";
		$pmhandler = new PMDataHandler();

		$pm = array(
			"subject" => $subject,
			"message" => $message,
			"icon" => "",
			"fromid" => $mybb->settings['mybot_user'],
			"do" => "",
			"pmid" => "",
		);
		$pm['toid'][] = $user_info['uid'];
		$pmhandler->set_data($pm);

		// Now let the pm handler do all the hard work.
		if($pmhandler->validate_pm())
		{
			$pminfo = $pmhandler->insert_pm();
		}else {
			$pm_errors = $pmhandler->get_friendly_errors();
			$send_errors = inline_error($pm_errors);
			echo $send_errors;
		}
	} elseif($mybb->settings['mybot_react']=="post") {
		//Write Post
		$message = JB_MyBot_Helpers::parse($mybb->settings['mybot_react_post_text'], "register", $additional);
		$subject = JB_MyBot_Helpers::parse($mybb->settings['mybot_react_post_subject'], "register", $additional);
		require_once  MYBB_ROOT."inc/datahandlers/post.php";
		$posthandler = new PostDataHandler("insert");
		$posthandler->action = "thread";

		// Set the thread data that came from the input to the $thread array.
		$new_thread = array(
			"fid" => $mybb->settings['mybot_react_post_forum'],
			"subject" => $subject,
			"prefix" => "",
			"icon" => "",
			"uid" => $mybb->settings['mybot_user'],
			"username" => $additional['botname'],
			"message" => $message,
			"ipaddress" => get_ip()
		);
		$posthandler->set_data($new_thread);
		$valid_thread = $posthandler->validate_thread();
		if($valid_thread) {
			$posthandler->insert_thread();
		}
	} else
		return;
}

function mybot_birthday()
{
	global $cache, $mybb, $db;

	if(!isset($mybb->settings['mybot_bday']) || $mybb->settings['mybot_bday'] == "none") // We use an old version of MyBot
		return;

	$last_run = $cache->read("mybot_birthday");
	if($last_run !== false) {
		$last['date'] = date("j", $last_run);
		$last['month'] = date("n", $last_run);
		$last['year'] = date("Y", $last_run);

		$now['date'] = date("j");
		$now['month'] = date("n");
		$now['year'] = date("Y");

		//Is it time?
		$diff = array_diff_assoc($last, $now);

		if(count($diff) == 0)
			//Nothing to do
			return;

		$run = true;
		$time = $last_run;
		$todo = array();
		while($run) {
			$time = $time + 24*3600;
			$this['date'] = date("j", $time);
			$this['month'] = date("n", $time);
			$this['year'] = date("Y", $time);

			if(count(array_diff_assoc($this, $now)) == 0)
				//we are ready
				$run = false;

			$todo[] = $this;
		}
	} else {
		//Just run the bdays from today
		$todo[] = array("date" => date("j"), "month" => date("n"), "year" => date("Y"));
	}

	$add = "";
	if($mybb->settings['mybot_bday'] == "post")
		$add = "AND birthdayprivacy = 'all'";

	foreach ($todo as $day) {
		$db_bday = $day['date']."-".$day['month']."-%";
		$query = $db->simple_select("users", "uid, username", "birthday LIKE '{$db_bday}'{$add}");

		while($user = $db->fetch_array($query)) {
			mybot_birthday_write($user['uid'], $user['username']);
		}
	}

	$cache->update("mybot_birthday", time());
}

function mybot_birthday_write($uid, $username)
{
	global $mybb;
	$additional['birthday'] = $username;
	$additional['bid'] = $uid;
	if($mybb->settings['mybot_bday'] == "pm") {
		$message = JB_MyBot_Helpers::parse($mybb->settings['mybot_bday_pm'], "birthday", $additional);
		$subject = JB_MyBot_Helpers::parse($mybb->settings['mybot_bday_pm_subject'], "birthday", $additional);
		//Write PM
		require_once MYBB_ROOT."inc/datahandlers/pm.php";
		$pmhandler = new PMDataHandler();

		$pm = array(
			"subject" => $subject,
			"message" => $message,
			"icon" => "",
			"fromid" => $mybb->settings['mybot_user'],
			"do" => "",
			"pmid" => "",
		);
		$pm['toid'][] = $uid;
		$pmhandler->set_data($pm);

		// Now let the pm handler do all the hard work.
		if($pmhandler->validate_pm())
		{
			$pminfo = $pmhandler->insert_pm();
		}else {
			$pm_errors = $pmhandler->get_friendly_errors();
			$send_errors = inline_error($pm_errors);
			echo $send_errors;
		}
	} elseif($mybb->settings['mybot_bday'] == "post") {
		//Write Post
		$message = JB_MyBot_Helpers::parse($mybb->settings['mybot_bday_post_text'], "birthday", $additional);
		$subject = JB_MyBot_Helpers::parse($mybb->settings['mybot_bday_post_subject'], "birthday", $additional);
		require_once  MYBB_ROOT."inc/datahandlers/post.php";
		$posthandler = new PostDataHandler("insert");
		$posthandler->action = "thread";

		// Set the thread data that came from the input to the $thread array.
		$new_thread = array(
			"fid" => $mybb->settings['mybot_bday_post_forum'],
			"subject" => $subject,
			"prefix" => "",
			"icon" => "",
			"uid" => $mybb->settings['mybot_user'],
			"username" => $additional['botname'],
			"message" => $message,
			"ipaddress" => get_ip()
		);
		$posthandler->set_data($new_thread);
		$valid_thread = $posthandler->validate_thread();
		if($valid_thread) {
			$posthandler->insert_thread();
		}
	}
}

function mybot_cache_update($load = true, $rules = array())
{
	global $cache, $db;

	if($load)
	{
		$rules = JB_MyBot_Rule::getAll();
	}

	foreach($rules as &$rule)
	{
		if($rule instanceof JB_MyBot_Rule)
		    $rule = $rule->toArray();

		if(!is_array($rule['conditions']))
			$rule['conditions'] = @unserialize($rule['conditions']);
		if(!is_array($rule['actions']))
			$rule['actions'] = @unserialize($rule['actions']);
	}
	return $cache->update("mybot_rules", $rules);
}

function mybot_cache_load($id = false)
{
	return JB_MyBot_Rule::getFromCache($id);
}

function mybot_post()
{
	global $post, $postinfo;
	$post['pid'] = $postinfo['pid'];
	$post['visible'] = $postinfo['visible'];
	mybot_work($post, "post");
}

function mybot_thread()
{
	global $new_thread, $thread_info;
	$new_thread['tid'] = $thread_info['tid'];
	$new_thread['pid'] = $thread_info['pid'];
	mybot_work($new_thread, "thread");
}

function mybot_report($post, $botname, $reason)
{
	global $mybb, $db, $lang, $cache;
	require_once MYBB_ROOT."inc/functions_modcp.php";
	$lang->load("report");

	$report_type  = "post";
	$report_string = "report_reason_post";
	$report_title = $lang->$report_string;
	
	if(!is_array($post))
		$post = get_post($post);

	$id = $post['pid'];
	$id2 = $post['tid'];
	$report_type_db = "(type = 'post' OR type = '')";

	$forum = get_forum($post['fid']);

	$id3 = $forum['fid'];
	
	// Check for an existing report
	if(!empty($report_type_db))
	{
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
			'reason' => trim($reason)
		);

		add_report($new_report);
	}
}

function mybot_string_in_message($string, $message, $subject, $reverse)
{
	global $additional;
	$strings = explode("\n", $string);
	$found = false;
	$all = true;
	$length = sizeOf($strings);
	foreach($strings as $key => $string) {
		if($key+1 != $length)
			$string = substr($string, 0, -1);
		if($reverse) {
			if($string != "" && (strpos(strtolower($message), strtolower($string)) === false && strpos(strtolower($subject), strtolower($string)) === false))
				$all = false;
		} else {
			if($string != "" && (strpos(strtolower($message), strtolower($string)) !== false || strpos(strtolower($subject), strtolower($string)) !== false)) {
				$found = true;
				if($additional['foundstring'] == "")
					$additional['foundstring'] = $string;
			}
		}
	}
	if($reverse)
		return !$all;

	return $found;
}

function mybot_work($info, $type)
{
	global $db, $mybb, $groupscache, $additional;

	//We don't want the bot reacting on himself...
	if(!isset($mybb->settings['mybot_selfreact']) || ($mybb->settings['mybot_selfreact'] == "no" && $info['uid'] == $mybb->settings['mybot_user']))
		return;

	//It's difficult to react on a post which isn't visible so we do nothing here
	if(isset($info['visible']) && $info['visible'] != 1)
		return;

	require_once MYBB_ROOT."inc/datahandlers/post.php";
	$posthandler = new PostDataHandler("insert");
	require_once MYBB_ROOT."inc/class_moderation.php";
	$moderation = new Moderation;
	require_once MYBB_ROOT."inc/datahandlers/pm.php";
	$pmhandler = new PMDataHandler();

	$rules = mybot_cache_load();
	$additional['botname'] = $db->fetch_field($db->simple_select("users", "username", "uid='{$mybb->settings['mybot_user']}'"), "username");
	$thread = get_thread($info['tid']);
	++$thread['replies'];
	if($type == "post")
		++$thread['replies'];
	$active = array();
	foreach($rules as $rule) {
		if(array_key_exists("user", $rule['conditions'])) {
			$continue = true;
			if(@in_array(-1, $rule['conditions']['user']) && $thread['uid'] == $info['uid'])
				$continue = false;
			if(@in_array($info['uid'], $rule['conditions']['user']))
				$continue = false;

			if($continue)
				continue;
		}
		if(array_key_exists("group", $rule['conditions']) && !is_member($rule['conditions']['group'], $info['uid'])) {
			continue;
		}
		if(array_key_exists("forum", $rule['conditions']) && !@in_array($info['fid'], $rule['conditions']['forum'])) {
			continue;
		}
		if(array_key_exists("string", $rule['conditions']) && !mybot_string_in_message($rule['conditions']['string'], $info['message'], $info['subject'], $rule['conditions']['string_reverse'])) {
			continue;
		}
		if(array_key_exists("postlimit", $rule['conditions']) && $thread['replies'] > $rule['conditions']['postlimit']) {
			continue;
		}
		if(array_key_exists("prefix", $rule['conditions']) && !@in_array($thread['prefix'], $rule['conditions']['prefix'])) {
			continue;
		}
		$active[] = $rule;
	}
	$rules = $active;


	$pid = $info['pid'];
	$additional['post'] = $info;
	$additional['pid'] = $pid;
	$additional['post']['timestamp'] = $info['dateline'];
	$date = time();
	++$date;
	foreach($rules as $rule) {
		if(array_key_exists("answer", $rule['actions'])) {
			$subject = preg_replace('#RE:\s?#i', '', $info['subject']);
			$subject = "RE: ".$subject;

			// Set the post data that came from the input to the $post array.
			$post = array(
				"tid" => $info['tid'],
				"replyto" => $pid,
				"fid" => $info['fid'],
				"subject" => $subject,
				"icon" => $info['icon'],
				"uid" => $mybb->settings['mybot_user'],
				"username" => $additional['botname'],
				"message" => JB_MyBot_Helpers::parse($rule['actions']['answer'], "thread", $additional),
				"ipaddress" => get_ip(),
				"dateline" => $date
			);
			$posthandler->set_data($post);
			$valid_thread = $posthandler->validate_post();
			if(!$valid_thread)
			{
				echo inline_error($posthandler->get_friendly_errors());
			}
			$ninfo = $posthandler->insert_post();
			$pid = $ninfo['pid'];
			++$date;
		}

		if(array_key_exists("move", $rule['actions'])) {
			$info['tid'] = $moderation->move_thread($info['tid'], $rule['actions']['move']);
			$thread = get_thread($info['tid']);
		}

		if(array_key_exists("delete", $rule['actions'])) {
			if($rule['actions']['delete'] == "thread" || $thread['firstpost'] == $info['pid'])
				$moderation->delete_thread($info['tid']);
			else
				$moderation->delete_post($info['pid']);
		}

		if(array_key_exists("stick", $rule['actions'])) {
			if($thread['sticky'] == 1)
				$moderation->unstick_threads($info['tid']);
			else
				$moderation->stick_threads($info['tid']);
		}
		
		if(array_key_exists("close", $rule['actions'])) {
			if($thread['closed'] == 1)
				$moderation->open_threads($info['tid']);
			else
				$moderation->close_threads($info['tid']);
		}

		if(array_key_exists("report", $rule['actions'])) {
			mybot_report($pid, $additional['botname'], $rule['actions']['report']);
		}

		if(array_key_exists("approve", $rule['actions'])) {
			if($rule['actions']['approve'] == "thread" || $thread['firstpost'] == $info['pid']) {
				if($thread['visible'] != 1)
					$moderation->approve_threads($info['tid']);
				else
					$moderation->unapprove_threads($info['tid']);
			} else {
				if($info['visible'] != 1)
					$moderation->approve_posts(array($pid));
				else
					$moderation->unapprove_posts(array($pid));
			}
		}

		if(array_key_exists("pm", $rule['actions'])) {
			if($rule['actions']['pm']['user'] == "last")
				$rule['actions']['pm']['user'] = $info['uid'];
			elseif($rule['actions']['pm']['user'] == "start") {
				$post = get_post($thread['firstpost']);
				$rule['actions']['pm']['user'] = $post['uid'];
			}

			$pm = array(
				"subject" => JB_MyBot_Helpers::parse($rule['actions']['pm']['subject'], "thread", $additional),
				"message" => JB_MyBot_Helpers::parse($rule['actions']['pm']['message'], "thread", $additional),
				"icon" => "",
				"fromid" => $mybb->settings['mybot_user'],
				"do" => "",
				"pmid" => "",
			);
			$pm['toid'][] = $rule['actions']['pm']['user'];
			$pmhandler->set_data($pm);

			// Now let the pm handler do all the hard work.
			if($pmhandler->validate_pm())
			{
				$pminfo = $pmhandler->insert_pm();
			}else {
				$pm_errors = $pmhandler->get_friendly_errors();
				$send_errors = inline_error($pm_errors);
				echo $send_errors;
			}
		}
	}
}

function mybot_activate() {}

function mybot_deactivate() {}
?>