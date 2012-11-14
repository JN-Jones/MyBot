<?php
if(!defined("IN_MYBB"))
{
	header("HTTP/1.0 404 Not Found");
	exit;
}
$PL or require_once PLUGINLIBRARY;
$lang->load("mybot");

$userarray = array(-1=>$lang->mybot_create_user); $grouparray = array(-1=>$lang->mybot_create_group);

$query = $db->simple_select("users", "uid, username");
while($user = $db->fetch_array($query))
    $userarray[$user['uid']] = $user['username'];
uasort($userarray, "sort_user");

foreach($groupscache as $group) {
    if($group['gid']!=1)
	    $grouparray[$group['gid']] = $group['title'];
}
uasort($grouparray, "sort_group");

$page->add_breadcrumb_item($lang->mybot_installing, "index.php?module=config-installbot");
$page->output_header($lang->mybot_installing);

if($mybb->input['action']=="do_add") {
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=config-installbot");
	}

    if(!strlen(trim($mybb->input['user'])))
	{
		flash_message($lang->mybot_user_not, 'error');
		admin_redirect("index.php?module=config-installbot");
	}
	
	if((int)$mybb->input['user']!=-1)
	    $uid = (int)$mybb->input['user'];
	else {
	    if(!strlen(trim($mybb->input['username'])))
		{
			flash_message($lang->mybot_username_not, 'error');
			admin_redirect("index.php?module=config-installbot");
		}
		
	    if(!strlen(trim($mybb->input['pw'])))
		{
			flash_message($lang->mybot_pw_not, 'error');
			admin_redirect("index.php?module=config-installbot");
		}

	    if(!strlen(trim($mybb->input['email'])))
		{
			flash_message($lang->mybot_email_not, 'error');
			admin_redirect("index.php?module=config-installbot");
		}

        if(!strlen(trim($mybb->input['group'])))
		{
			flash_message($lang->mybot_group_not, 'error');
			admin_redirect("index.php?module=config-installbot");
		}

    	if((int)$mybb->input['group']!=-1)
		    $gid = (int)$mybb->input['group'];
		else {
	        if(!strlen(trim($mybb->input['groupname'])))
			{
				flash_message($lang->mybot_groupname_not, 'error');
				admin_redirect("index.php?module=config-installbot");
			}
			//Create Group
			$new_usergroup = array(
				"type" => 2,
				"title" => $db->escape_string($mybb->input['groupname']),
				"description" => "MyBot created Usergroup",
				"namestyle" => "{username}",
				"usertitle" => "MyBot",
				"stars" => 0,
				"starimage" => "images/star.gif",
				"disporder" => 0
			);
			$gid = $db->insert_query("usergroups", $new_usergroup);
			// Update the caches
			$cache->update_usergroups();
			$cache->update_forumpermissions();
			$added_group = true;
		}
		//Create User
		// Set up user handler.
		require_once MYBB_ROOT."inc/datahandlers/user.php";
		$userhandler = new UserDataHandler('insert');

		// Set the data for the new user.
		$new_user = array(
			"username" => $mybb->input['username'],
			"password" => $mybb->input['pw'],
			"password2" => $mybb->input['pw'],
			"email" => $mybb->input['email'],
			"email2" => $mybb->input['email'],
			"usergroup" => $gid,
			"displaygroup" => $gid,
		);

		// Set the data of the user in the datahandler.
		$userhandler->set_data($new_user);
		$errors = '';

		// Validate the user and get any errors that might have occurred.
		if(!$userhandler->validate_user())
		{
			$errors = $userhandler->get_friendly_errors();
		}
		else
		{
			$user_info = $userhandler->insert_user();
			$uid = $user_info['uid'];
			$added_user = true;
		}
	}
	if(!$errors) {
		//Save in Settings
		$update = array("value"=>$uid);
		$db->update_query("settings", $update, "name='mybot_user'");
		if($added_group)
		    $message = $lang->sprintf($lang->mybot_installed_group, $lang->mybot_installed, $mybb->input['username'], $mybb->input['groupname']);
		elseif($added_user)
		    $message = $lang->sprintf($lang->mybot_installed_user, $lang->mybot_installed, $mybb->input['username']);
		else
			$message = $lang->mybot_installed;
		rebuild_settings();
		flash_message($message, 'success');
		admin_redirect("index.php?module=config-plugins");
	}
} else {
	if($errors)
	{
		$page->output_inline_error($errors);
	}
	$form = new Form("index.php?module=config-installbot&amp;action=do_add", "post");
	$form_container = new FormContainer($lang->mybot_installing);

	if(!$uid)
	    $uid = 1;
	$add_user = $form->generate_select_box("user", $userarray, $uid, array("id"=>"user"));
	$form_container->output_row($lang->mybot_user." <em>*</em>", $lang->mybot_user_desc, $add_user);

	$add_username = $form->generate_text_box("username", $mybb->input['username']);
	$form_container->output_row($lang->mybot_username." <em>*</em>", $lang->mybot_username_desc, $add_username, '', array(), array("id"=>"username"));

	$add_pw = $form->generate_password_box("pw");
	$form_container->output_row($lang->mybot_pw." <em>*</em>", $lang->mybot_pw_desc, $add_pw, '', array(), array("id"=>"pw"));

	$add_email = $form->generate_text_box("email", $mybb->input['email']);
	$form_container->output_row($lang->mybot_email." <em>*</em>", $lang->mybot_email_desc, $add_email, '', array(), array("id"=>"email"));

	if(!$gid)
	    $gid = 2;
	$add_group = $form->generate_select_box("group", $grouparray, $gid, array("id"=>"groupselect"));
	$form_container->output_row($lang->mybot_group." <em>*</em>", $lang->mybot_group_desc, $add_group, '', array(), array("id"=>"group"));

	$add_groupname = $form->generate_text_box("groupname", $mybb->input['groupname']);
	$form_container->output_row($lang->mybot_groupname." <em>*</em>", $lang->mybot_groupname_desc, $add_groupname, '', array(), array("id"=>"groupname"));
	$form_container->end();

	$buttons[] = $form->generate_submit_button($lang->mybot_save);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();

	echo '<script type="text/javascript" src="./jscripts/peeker.js"></script>
	<script type="text/javascript">
		Event.observe(window, "load", function() {
			loadPeekers();
		});
		function loadPeekers()
		{
			new Peeker($("user"), $("username"), -1, false);
			new Peeker($("user"), $("pw"), -1, false);
			new Peeker($("user"), $("email"), -1, false);
			new Peeker($("user"), $("group"), -1, false);
			new Peeker($("groupselect"), $("groupname"), -1, false);
		}
	</script>';
}
$page->output_footer();

function sort_user($a, $b)
{
	global $lang;
	if($a == $lang->mybot_create_user)
	    return -1;
	if($b == $lang->mybot_create_user)
	    return 1;

	return strcoll($a, $b);
}
function sort_group($a, $b)
{
	global $lang;
	if($a == $lang->mybot_create_group)
	    return -1;
	if($b == $lang->mybot_create_group)
	    return 1;

	return strcoll($a, $b);
}
?>