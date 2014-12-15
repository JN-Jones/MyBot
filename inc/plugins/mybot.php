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
	global $cache;

	JB_Core::i()->uninstall("mybot");

	$cache->delete("mybot", true);
}


function mybot_activate() {}

function mybot_deactivate() {}

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

function mybot_register()
{
	global $mybb, $user_info, $db;
	$additional['registered'] = $user_info['username'];
	$additional['regid'] = $user_info['uid'];
	$additional['botname'] = $db->fetch_field($db->simple_select("users", "username", "uid='{$mybb->settings['mybot_user']}'"), "username");
	if($mybb->settings['mybot_react'] == "pm")
	{
		$message = JB_MyBot_Helpers::parse($mybb->settings['mybot_react_pm'], "register", $additional);
		$subject = JB_MyBot_Helpers::parse($mybb->settings['mybot_react_pm_subject'], "register", $additional);
	}
	else
	{
		$message = JB_MyBot_Helpers::parse($mybb->settings['mybot_react_post_text'], "register", $additional);
		$subject = JB_MyBot_Helpers::parse($mybb->settings['mybot_react_post_subject'], "register", $additional);
	}
	JB_MyBot_Helpers::write($subject, $message, $mybb->settings['mybot_react'], $user_info['uid'], $additional['botname']);
}

function mybot_birthday()
{
	global $cache, $mybb, $db;

	if(!isset($mybb->settings['mybot_bday']) || $mybb->settings['mybot_bday'] == "none") // We use an old version of MyBot
		return;

	$last_run = $cache->read("mybot_birthday");
	if($last_run !== false)
	{
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
		while($run)
		{
			$time = $time + 24*3600;
			$this['date'] = date("j", $time);
			$this['month'] = date("n", $time);
			$this['year'] = date("Y", $time);

			if(count(array_diff_assoc($this, $now)) == 0)
				//we are ready
				$run = false;

			$todo[] = $this;
		}
	} else
	{
		//Just run the bdays from today
		$todo[] = array("date" => date("j"), "month" => date("n"), "year" => date("Y"));
	}

	$add = "";
	if($mybb->settings['mybot_bday'] == "post")
		$add = " AND birthdayprivacy = 'all'";

	foreach ($todo as $day)
	{
		$db_bday = $day['date']."-".$day['month']."-%";
		$query = $db->simple_select("users", "uid, username", "birthday LIKE '{$db_bday}'{$add}");

		while($user = $db->fetch_array($query))
		{
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
	if($mybb->settings['mybot_bday'] == "pm")
	{
		$message = JB_MyBot_Helpers::parse($mybb->settings['mybot_bday_pm'], "birthday", $additional);
		$subject = JB_MyBot_Helpers::parse($mybb->settings['mybot_bday_pm_subject'], "birthday", $additional);
	}
	else
	{
		$message = JB_MyBot_Helpers::parse($mybb->settings['mybot_bday_post_text'], "birthday", $additional);
		$subject = JB_MyBot_Helpers::parse($mybb->settings['mybot_bday_post_subject'], "birthday", $additional);
	}
	JB_MyBot_Helpers::write($subject, $message, $mybb->settings['mybot_bday'], $uid, $additional['botname']);
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

function mybot_work($info, $type)
{
	global $db, $mybb, $groupscache;
	global $additional, $date, $posthandler, $moderation, $pmhandler;

	//We don't want the bot reacting on himself...
	if(!isset($mybb->settings['mybot_selfreact']) || ($mybb->settings['mybot_selfreact'] == "no" && $info['uid'] == $mybb->settings['mybot_user']))
		return;

	//It's difficult to react on a post which isn't visible so we do nothing here
	if(isset($info['visible']) && $info['visible'] != 1)
		return;

	// Set up some handlers we may need
	require_once MYBB_ROOT."inc/datahandlers/post.php";
	$posthandler = new PostDataHandler("insert");
	require_once MYBB_ROOT."inc/class_moderation.php";
	$moderation = new Moderation;
	require_once MYBB_ROOT."inc/datahandlers/pm.php";
	$pmhandler = new PMDataHandler();

	$rules = JB_MyBot_Rule::getFromCache();
	$additional['botname'] = $db->fetch_field($db->simple_select("users", "username", "uid='{$mybb->settings['mybot_user']}'"), "username");

	$thread = get_thread($info['tid']);
	// We have at least one more reply (firstpost)
	++$thread['replies'];
	// And if we're reacting on a post that post is a reply too
	if($type == "post")
		++$thread['replies'];

	$active = array();
	foreach($rules as $rule)
	{
		$add = true;
		foreach($rule->getConditions() as $condition)
		{
			if(!$condition->doCheck($thread, $info))
			{
				$add = false;
				break;
			}
		}

		if($add === true)
			$active[] = $rule;
	}
	$rules = $active;

	// Now grab some data and build our additional array
	$pid = $info['pid'];
	$additional['post'] = $info;
	$additional['pid'] = $pid;
	$additional['post']['timestamp'] = $info['dateline'];

	// Used for all dataline things. We need to add one to avoid posts with the same dataline (would result in the answer post shown before the real post)
	$date = time();
	++$date;

	foreach($rules as $rule)
	{
		foreach($rule->getActions() as $action)
		{
			$action->doAction($pid, $thread, $info, $date);
		}
	}
}