<?php

class JB_MyBot_Helpers
{
	public static function printConfigPeekers()
	{
		echo '<script type="text/javascript">
			$(document).ready(function() {
				new Peeker($("#setting_mybot_react"), $("#row_setting_mybot_react_pm_subject"), /pm/, false);
				new Peeker($("#setting_mybot_react"), $("#row_setting_mybot_react_pm"), /pm/, false);
				new Peeker($("#setting_mybot_react"), $("#row_setting_mybot_react_post_forum"), /post/, false);
				new Peeker($("#setting_mybot_react"), $("#row_setting_mybot_react_post_subject"), /post/, false);
				new Peeker($("#setting_mybot_react"), $("#row_setting_mybot_react_post_text"), /post/, false);
	
				new Peeker($("#setting_mybot_bday"), $("#row_setting_mybot_bday_pm_subject"), /pm/, false);
				new Peeker($("#setting_mybot_bday"), $("#row_setting_mybot_bday_pm"), /pm/, false);
				new Peeker($("#setting_mybot_bday"), $("#row_setting_mybot_bday_post_forum"), /post/, false);
				new Peeker($("#setting_mybot_bday"), $("#row_setting_mybot_bday_post_subject"), /post/, false);
				new Peeker($("#setting_mybot_bday"), $("#row_setting_mybot_bday_post_text"), /post/, false);

				new Peeker($(".setting_mybot_remember"), $("#row_setting_mybot_remember_time"), 1, true);
				new Peeker($(".setting_mybot_remember"), $("#row_setting_mybot_remember_subject"), 1, true);
				new Peeker($(".setting_mybot_remember"), $("#row_setting_mybot_remember_message"), 1, true);
			});
		</script>';
	}

	public static function parse($text, $type="", $additional=array())
	{
		global $mybb, $db;
	
		if(!isset($additional['botname']))
			$additional['botname'] = $db->fetch_field($db->simple_select("users", "username", "uid='{$mybb->settings['mybot_user']}'"), "username");

		$text = str_replace('{boardname}', $mybb->settings['bbname'], $text);
		$text = str_replace('{botname}', $additional['botname'], $text);
		if($type == "register")
		{
			if(isset($additional['registered']))
				$text = str_replace('{registered}', $additional['registered'], $text);
			if(isset($additional['regid']))
				$text = str_replace('{regid}', $additional['regid'], $text);
		}
		if($type == "birthday")
		{
			if(isset($additional['birthday']))
				$text = str_replace('{birthday}', $additional['birthday'], $text);
			if(isset($additional['bid']))
				$text = str_replace('{bid}', $additional['bid'], $text);
		}
		if($type == "remember")
		{
			if(isset($additional['remember']))
				$text = str_replace('{remember}', $additional['remember'], $text);
			if(isset($additional['rid']))
				$text = str_replace('{rid}', $additional['rid'], $text);
		}

		if($type == "thread")
		{
			//We can only replace something if we had a pid
			if(isset($additional['pid']))
			{
				//If no tid is set get it
				if(!isset($additional['tid']))
				{
					if(isset($style) && $style['pid'] == $additional['pid'] && $style['tid'])
					{
						$additional['tid'] = $style['tid'];
						unset($style['tid']);
					}
					else
					{
						$options = array(
							"limit" => 1
						);
						$query = $db->simple_select("posts", "tid", "pid=".$additional['pid'], $options);
						$post = $db->fetch_array($query);
						$additional['tid'] = $post['tid'];
					}
				}

				$thread = get_thread($additional['tid']);
				$post = get_post($additional['pid']);

				//Is the first post the same as the last?
				if(!isset($additional['type']))
				{
					if($thread['firstpost'] == $additional['pid'])
					 	$additional['type'] = "thread";
					else
						$additional['type'] = "post";
				}

				//Check all informations and add the missing ones
				if(!isset($additional['post']['subject']))
					$additional['post']['subject'] = $post['subject'];

				if(!isset($additional['post']['link']))
				{
					$link = $mybb->settings['bburl']."/".get_post_link($additional['pid'], $additional['tid'])."#pid{$additional['pid']}";
					$additional['post']['link'] = "[url={$link}]{$additional['post']['subject']}[/url]";
				}

				if(!isset($additional['post']['message']))
					$additional['post']['message'] = $post['message'];

				if(!isset($additional['post']['timestamp']))
					$additional['post']['timestamp'] = $post['dateline'];

				if(!isset($additional['post']['date']))
					$additional['post']['date'] = date($mybb->settings['dateformat'], $additional['post']['timestamp']);

				if(!isset($additional['post']['time']))
					$additional['post']['time'] = date($mybb->settings['timeformat'], $additional['post']['timestamp']);

				if(!isset($additional['post']['uid']))
					$additional['post']['uid'] = $post['uid'];

				if(!isset($additional['post']['user']))
					$additional['post']['user'] = $post['username'];

				if(!isset($additional['post']['userlink']))
				{
					$link = $mybb->settings['bburl']."/".get_profile_link($additional['post']['uid']);
					$additional['post']['userlink'] = "[url={$link}]{$additional['post']['user']}[/url]";
				}

				//Do the same for the firstpost
				if($additional['type'] == $thread)
					$additional['thread'] = $additional['post'];
				else
				{
					$post = get_post($thread['firstpost']);

					$additional['thread']['subject'] = $post['subject'];

					$link = $mybb->settings['bburl']."/".get_post_link($post['pid'], $additional['tid'])."#pid{$post['pid']}";
					$additional['thread']['link'] = "[url={$link}]{$additional['thread']['subject']}[/url]";

					$additional['thread']['message'] = $post['message'];

					$additional['thread']['timestamp'] = $post['dateline'];

					$additional['thread']['date'] = date($mybb->settings['dateformat'], $additional['thread']['timestamp']);

					$additional['thread']['time'] = date($mybb->settings['timeformat'], $additional['thread']['timestamp']);

					$additional['thread']['uid'] = $post['uid'];

					$additional['thread']['user'] = $post['username'];

					$link = $mybb->settings['bburl']."/".get_profile_link($additional['thread']['uid']);
					$additional['thread']['userlink'] = "[url={$link}]{$additional['thread']['user']}[/url]";
				}

				//Get the forum
				$forum = get_forum($thread['fid']);
				$additional['thread']['forum'] = $forum['name'];

				$additional['thread']['answers'] = my_number_format($thread['replies']) +1;
				$additional['thread']['views'] = my_number_format($thread['views']) +1;

				//Now we can replace everything ;)
				$text = str_replace('{lastpost->user}', $additional['post']['user'], $text);
				$text = str_replace('{lastpost->userlink}', $additional['post']['userlink'], $text);
				$text = str_replace('{lastpost->subject}', $additional['post']['subject'], $text);
				$text = str_replace('{lastpost->id}', $additional['pid'], $text);
				$text = str_replace('{lastpost->link}', $additional['post']['link'], $text);
				$text = str_replace('{lastpost->date}', $additional['post']['date'], $text);
				$text = str_replace('{lastpost->time}', $additional['post']['time'], $text);
				$text = str_replace('{lastpost->message}', $additional['post']['message'], $text);
				$text = str_replace('{lastpost->uid}', $additional['post']['uid'], $text);
				$text = str_replace('{lastpost->timestamp}', $additional['post']['timestamp'], $text);

				$text = str_replace('{thread->user}', $additional['thread']['user'], $text);
				$text = str_replace('{thread->userlink}', $additional['thread']['userlink'], $text);
				$text = str_replace('{thread->subject}', $additional['thread']['subject'], $text);
				$text = str_replace('{thread->id}', $additional['tid'], $text);
				$text = str_replace('{thread->link}', $additional['thread']['link'], $text);
				$text = str_replace('{thread->date}', $additional['thread']['date'], $text);
				$text = str_replace('{thread->time}', $additional['thread']['time'], $text);
				$text = str_replace('{thread->message}', $additional['thread']['message'], $text);
				$text = str_replace('{thread->uid}', $additional['thread']['uid'], $text);
				$text = str_replace('{thread->timestamp}', $additional['thread']['timestamp'], $text);
				$text = str_replace('{thread->forum}', $additional['thread']['forum'], $text);
				$text = str_replace('{thread->answers}', $additional['thread']['answers'], $text);
				$text = str_replace('{thread->views}', $additional['thread']['views'], $text);
	
				$text = str_replace('{foundstring}', $additional['foundstring'], $text);
			}
		}
		return $text;
	}

	public static function write($subject, $message, $type, $uid=0, $botname="", $fid=0)
	{
		if($type == "pm")
			static::writePM($subject, $message, $uid);
		else if($type == "post")
			static::writeThread($subject, $message, $botname, $fid);
	}

	public static function writePM($subject, $message, $uid)
	{
		global $mybb;
		// Write PM
		require_once MYBB_ROOT."inc/datahandlers/pm.php";
		$pmhandler = new PMDataHandler();

		$pm = array(
			"subject"	=> $subject,
			"message"	=> $message,
			"icon"		=> "",
			"fromid"	=> $mybb->settings['mybot_user'],
			"do"		=> "",
			"pmid"		=> "",
		);
		$pm['toid'][] = $uid;
		$pmhandler->set_data($pm);

		// Now let the pm handler do all the hard work.
		if($pmhandler->validate_pm())
		{
			$pminfo = $pmhandler->insert_pm();
		}
		else
		{
			$pm_errors = $pmhandler->get_friendly_errors();
//			$send_errors = inline_error($pm_errors);
//			echo $send_errors;
		}
	}

	public static function writeThread($subject, $message, $botname, $fid=0)
	{
		global $mybb;

		if($fid === 0)
		{
			$fid = $mybb->settings['mybot_react_post_forum'];
		}

		// Write Post
		require_once  MYBB_ROOT."inc/datahandlers/post.php";
		$posthandler = new PostDataHandler("insert");
		$posthandler->action = "thread";

		// Set the thread data that came from the input to the $thread array.
		$new_thread = array(
			"fid"		=> $fid,
			"subject"	=> $subject,
			"prefix"	=> "",
			"icon"		=> "",
			"uid"		=> $mybb->settings['mybot_user'],
			"username"	=> $botname,
			"message"	=> $message
		);
		$posthandler->set_data($new_thread);
		$valid_thread = $posthandler->validate_thread();
		if($valid_thread)
			$posthandler->insert_thread();
	}
}
