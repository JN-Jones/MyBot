<?php
if(!defined("IN_MYBB"))
{
	header("HTTP/1.0 404 Not Found");
	exit;
}
$PL or require_once PLUGINLIBRARY;
$lang->load("mybot");

$page->add_breadcrumb_item($lang->mybot, "index.php?module=user-mybot");
$page->output_header($lang->mybot);

if($mybb->input['action']=="add") {
	generate_tabs("add");
	echo "Test";
} elseif($mybb->input['action']=="post") {
	generate_tabs("post");
	if($mybb->request_method == "post") {
		if(!strlen(trim($mybb->input['forum'])))
		{
			flash_message($lang->mybot_post_forum_not, 'error');
			admin_redirect("index.php?module=user-mybot&amp;action=post");
		}
		if(!strlen(trim($mybb->input['subject'])))
		{
			flash_message($lang->mybot_post_subject_not, 'error');
			admin_redirect("index.php?module=user-mybot&amp;action=post");
		}
		if(!strlen(trim($mybb->input['text'])))
		{
			flash_message($lang->mybot_post_text_not, 'error');
			admin_redirect("index.php?module=user-mybot&amp;action=post");
		}
		if(!is_array($forum_cache))
		{
			$forum_cache = cache_forums();
		}
		if($forum_cache[$mybb->input['forum']]['type']=="c") {
			flash_message($lang->mybot_post_category, 'error');
			admin_redirect("index.php?module=user-mybot&amp;action=post");
		}
        $name = $db->fetch_field($db->simple_select("users", "username", "uid='{$mybb->settings['mybot_user']}'"), "username");
		// Set up posthandler.
        require_once  MYBB_ROOT."inc/datahandlers/post.php";
        $posthandler = new PostDataHandler("insert");
        $posthandler->action = "thread";

        // Set the thread data that came from the input to the $thread array.
        $new_thread = array(
        	"fid" => $mybb->input['forum'],
            "subject" => $mybb->input['subject'],
            "prefix" => "",
            "icon" => "",
            "uid" => $mybb->settings['mybot_user'],
            "username" => $name,
            "message" => $mybb->input['text'],
            "ipaddress" => get_ip()
        );
        $posthandler->set_data($new_thread);
        $valid_thread = $posthandler->validate_thread();
		// Fetch friendly error messages if this is an invalid thread
		if(!$valid_thread)
		{
	        $errors = $posthandler->get_friendly_errors();
		} else {
	        $posthandler->insert_thread();
			flash_message($lang->mybot_post_inserted, 'success');
			admin_redirect("index.php?module=user-mybot&amp;action=post");
		}
	}
	if($mybb->request_method != "post" || $errors) {
		if($errors)
		{
			$page->output_inline_error($errors);
		}
		$form = new Form("index.php?module=user-mybot&amp;action=post", "post");
		$form_container = new FormContainer($lang->mybot_post);
	
		$post_forum = $form->generate_forum_select("forum", "");
		$form_container->output_row($lang->mybot_post_forum." <em>*</em>", $lang->mybot_post_forum_desc, $post_forum);
	
		$post_subject = $form->generate_text_box("subject");
		$form_container->output_row($lang->mybot_post_subject." <em>*</em>", $lang->mybot_post_subject_desc, $post_subject);

		$post_text = $form->generate_text_area("text");
		$form_container->output_row($lang->mybot_post_text." <em>*</em>", $lang->mybot_post_text_desc, $post_text);
	
		$form_container->end();
	
		$buttons[] = $form->generate_submit_button($lang->mybot_post_submit);
		$buttons[] = $form->generate_reset_button($lang->reset);
		$form->output_submit_wrapper($buttons);
		$form->end();
	}
} elseif($mybb->input['action']=="documentation") {
	generate_tabs("documentation");
	$table = new Table;
	$table->construct_header($lang->mybot_variable, array("width"=>"10%"));
	$table->construct_header($lang->mybot_description);

	$table->construct_cell("{boardname}");
	$table->construct_cell($lang->mybot_boardname);
	$table->construct_row();

	$table->construct_cell("{botname}");
	$table->construct_cell($lang->mybot_botname);
	$table->construct_row();
	
	$table->output($lang->mybot_global);


	$table = new Table;
	$table->construct_header($lang->mybot_variable, array("width"=>"10%"));
	$table->construct_header($lang->mybot_description);

	$table->construct_cell("{registered}");
	$table->construct_cell($lang->mybot_registered);
	$table->construct_row();
	
	$table->output($lang->mybot_register);
} else {
	generate_tabs("overview");
	echo "Test";
}

$page->output_footer();

function generate_tabs($selected)
{
	global $lang, $page;

	$sub_tabs = array();
	$sub_tabs['overview'] = array(
		'title' => $lang->mybot,
		'link' => "index.php?module=user-mybot",
		'description' => $lang->mybot_overview
	);
	$sub_tabs['add'] = array(
		'title' => $lang->mybot_addrule,
		'link' => "index.php?module=user-mybot&amp;action=add",
		'description' => $lang->mybot_addrule_desc
	);
	$sub_tabs['post'] = array(
		'title' => $lang->mybot_post,
		'link' => "index.php?module=user-mybot&amp;action=post",
		'description' => $lang->mybot_post_desc
	);
	$sub_tabs['documentation'] = array(
		'title' => $lang->mybot_documentation,
		'link' => "index.php?module=user-mybot&amp;action=documentation",
		'description' => $lang->mybot_documentation_desc
	);

	$page->output_nav_tabs($sub_tabs, $selected);
}
?>