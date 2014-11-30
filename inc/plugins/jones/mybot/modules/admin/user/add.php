<?php

class Module_Add extends JB_Module_Base
{
	public function post()
	{
		global $mybb, $errors, $lang, $db;

		// Build the conditions array
		if(!isset($mybb->input['conditions']))
		    $mybb->input['conditions'] = array();
		if(in_array("user", $mybb->input['conditions']))
			$conditions['user'] = $mybb->input['user'];

		if(in_array("group", $mybb->input['conditions']))
			$conditions['group'] = $mybb->input['group'];

		if(in_array("forum", $mybb->input['conditions']))
			$conditions['forum'] = $mybb->input['forum'];

		if(in_array("string", $mybb->input['conditions']))
		{
			$conditions['string'] = $mybb->input['string'];
			$conditions['string_reverse'] = $mybb->input['string_reverse'];
		}

		if(in_array("postlimit", $mybb->input['conditions']))
			$conditions['postlimit'] = $mybb->input['postlimit'];

		if(in_array("prefix", $mybb->input['conditions']))
			$conditions['prefix'] = $mybb->input['prefix'];

		// And the actions array
		if(!isset($mybb->input['actions']))
		    $mybb->input['actions'] = array();
		if(in_array("answer", $mybb->input['actions']))
			$actions['answer'] = $mybb->input['answer'];

		if(in_array("move", $mybb->input['actions']))
			$actions['move'] = $mybb->input['move'];

		if(in_array("delete", $mybb->input['actions']))
			$actions['delete'] = $mybb->input['delete'];

		if(in_array("stick", $mybb->input['actions']))
			$actions['stick'] = true;

		if(in_array("close", $mybb->input['actions']))
			$actions['close'] = true;

		if(in_array("report", $mybb->input['actions']))
			$actions['report'] = $mybb->input['report'];

		if(in_array("approve", $mybb->input['actions']))
			$actions['approve'] = $mybb->input['approve'];

		if(in_array("pm", $mybb->input['actions']))
		{
			$actions['pm']['user'] = $mybb->input['pm'];
			if($mybb->input['pm'] == "other")
				$actions['pm']['user'] = $mybb->input['pm_user'];
			$actions['pm']['subject'] = $mybb->input['subject'];
			$actions['pm']['message'] = $mybb->input['message'];
		}

		$array = array(
			'title'			=> $mybb->get_input('title'),
			'conditions'	=> $conditions,
			'actions'		=> $actions
		);

		$rule = JB_MyBot_Rule::create($array);

    	if($rule->validate())
		{
			$rules = mybot_cache_load();

			$rule->save();

			$rules[] = $rule;
			mybot_cache_update(false, $rules);

			flash_message($lang->mybot_add_added, 'success');
			admin_redirect("index.php?module=".MODULE);
		}

		$errors = $rule->getErrors();
		$this->get();
	}

	public function get()
	{
		global $mybb, $lang, $page, $errors, $db, $form, $form_container, $userarray;
		generate_tabs("add");

		if($errors)
		{
			$page->output_inline_error($errors);
		}

		$query = $db->simple_select("users", "uid, username");
		$userarray[-1] = $lang->thread_creator;
		while($user = $db->fetch_array($query))
			$userarray[$user['uid']] = $user['username'];
		uasort($userarray, "sort_user");

		$form = new Form("index.php?module=".MODULE."&amp;action=add", "post");
		$form_container = new FormContainer($lang->mybot_addrule);

		$add_title = $form->generate_text_box("title", $mybb->input['title']);
		$form_container->output_row($lang->mybot_add_title." <em>*</em>", $lang->mybot_add_title_desc, $add_title);

		$conditions_list = JB_MyBot_Conditions_Manager::getListArray();
		$add_conditions = $form->generate_select_box("conditions[]", $conditions_list, $mybb->input['conditions'], array("multiple"=>true, "id"=>"conditions"));
		$form_container->output_row($lang->mybot_add_conditions." <em>*</em>", $lang->mybot_add_conditions_desc, $add_conditions);

		JB_MyBot_Conditions_Manager::generateAdditionalFields($mybb->input);

		$action_list = JB_MyBot_Actions_Manager::getListArray();
		$add_actions = $form->generate_select_box("actions[]", $action_list, $mybb->input['actions'], array("multiple"=>true, "id"=>"action"));
		$form_container->output_row($lang->mybot_add_action." <em>*</em>", $lang->mybot_add_action_desc, $add_actions);

		JB_MyBot_Actions_Manager::generateAdditionalFields($mybb->input);

		$form_container->end();

		$buttons[] = $form->generate_submit_button($lang->mybot_addrule);
		$buttons[] = $form->generate_reset_button($lang->reset);
		$form->output_submit_wrapper($buttons);
		$form->end();

		echo '<script type="text/javascript" src="./jscripts/mybot_peeker.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				'.JB_MyBot_Conditions_Manager::generatePeekers().
				JB_MyBot_Actions_Manager::generatePeekers()
			.'});
		</script>';
	}
}