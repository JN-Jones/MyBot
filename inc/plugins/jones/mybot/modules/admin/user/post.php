<?php

class Module_Post extends JB_Module_Base
{
	public function post()
	{
		global $mybb, $lang, $forum_cache, $db, $errors;

		if(!strlen(trim($mybb->input['forum'])))
		{
			flash_message($lang->mybot_post_forum_not, 'error');
			admin_redirect("index.php?module=".MODULE."&amp;action=post");
		}
		if(!strlen(trim($mybb->input['subject'])))
		{
			flash_message($lang->mybot_post_subject_not, 'error');
			admin_redirect("index.php?module=".MODULE."&amp;action=post");
		}
		if(!strlen(trim($mybb->input['text'])))
		{
			flash_message($lang->mybot_post_text_not, 'error');
			admin_redirect("index.php?module=".MODULE."&amp;action=post");
		}
		if(!is_array($forum_cache))
		{
			$forum_cache = cache_forums();
		}
		if($forum_cache[$mybb->input['forum']]['type']=="c")
		{
			flash_message($lang->mybot_post_category, 'error');
			admin_redirect("index.php?module=".MODULE."&amp;action=post");
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
			"ipaddress" => get_ip() // TODO: doesn't work in 1.8!
		);
		$posthandler->set_data($new_thread);
		$valid_thread = $posthandler->validate_thread();
		// Fetch friendly error messages if this is an invalid thread
		if(!$valid_thread)
		{
			$errors = $posthandler->get_friendly_errors();
			$this->get();
		}
		else
		{
	 		$posthandler->insert_thread();
			flash_message($lang->mybot_post_inserted, 'success');
			admin_redirect("index.php?module=".MODULE."&amp;action=post");
		}
	}

	public function get()
	{
		global $errors, $lang, $page;

		generate_tabs("post");
		if($errors)
		{
			$page->output_inline_error($errors);
		}
		$form = new Form("index.php?module=".MODULE."&amp;action=post", "post");
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
}