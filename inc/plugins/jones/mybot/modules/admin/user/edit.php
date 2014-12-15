<?php

class Module_Edit extends JB_Module_Base
{
	private $rule;

	public function start()
	{
		global $mybb, $lang;
		$id = (int)$mybb->input['id'];
		if(!$id)
		{
			flash_message($lang->mybot_no_id, 'error');
			admin_redirect("index.php?module=".MODULE);
		}
		$this->rule = JB_MyBot_Rule::getFromCache($id);
		if($this->rule === false)
		{
			flash_message($lang->mybot_no_id, 'error');
			admin_redirect("index.php?module=".MODULE);
		}
	}

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
			$conditions['string']['string'] = $mybb->input['string']['string'];
			$conditions['string']['reverse'] = $mybb->input['string']['reverse'];
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

		if(in_array("softdelete", $mybb->input['actions']))
			$actions['softdelete'] = $mybb->input['softdelete'];

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

		$this->rule->title = $mybb->get_input('title');
		$this->rule->setConditions($conditions, true);
		$this->rule->setActions($actions, true);

		if($this->rule->validate())
		{
			$this->rule->save();

			mybot_cache_update();

			flash_message($lang->mybot_add_edited, 'success');
			admin_redirect("index.php?module=".MODULE);
		}

		$errors = $this->rule->getErrors();
		$this->get();
	}

	public function get()
	{
		global $mybb, $lang, $page, $errors, $db, $form, $form_container, $userarray;
		generate_tabs("overview");

		$rule = $this->rule;
		foreach(JB_MyBot_Conditions_Manager::getTypes() as $type)
		{
			if($rule->hasCondition($type))
				$conditions[] = $type;
		}

		foreach(JB_MyBot_Actions_Manager::getTypes() as $type)
		{
			if($rule->hasAction($type))
				$actions[] = $type;
		}


		$thread_checked = true;
		$post_checked = false;
		if($rule->hasAction("delete") && $rule->getAction("delete")->getData() == "post")
		{
			$thread_checked = false;
			$post_checked = true;
		}

		$athread_checked = true;
		$apost_checked = false;
		if($rule->hasAction("approve") && $rule->getAction("approve")->getData() == "post")
		{
			$athread_checked = false;
			$apost_checked = true;
		}

		if($rule->hasAction("pm"))
		{
			$pm = $rule->getAction("pm")->user;
			if($pm != "last" && $pm != "start")
				$pm = "other";
		}

		if($errors)
		{
			$page->output_inline_error($errors);
		}

		$query = $db->simple_select("users", "uid, username");
		$userarray[-1] = $lang->thread_creator;
		while($user = $db->fetch_array($query))
			$userarray[$user['uid']] = $user['username'];
		uasort($userarray, "sort_user");

		$form = new Form("index.php?module=".MODULE."&amp;action=edit", "post");
		$form_container = new FormContainer($lang->mybot_addrule);

		$add_title = $form->generate_text_box("title", $rule->title);
		$form_container->output_row($lang->mybot_add_title." <em>*</em>", $lang->mybot_add_title_desc, $add_title);

		$conditions_list = JB_MyBot_Conditions_Manager::getListArray();
		$add_conditions = $form->generate_select_box("conditions[]", $conditions_list, $conditions, array("multiple"=>true, "id"=>"conditions"));
		$form_container->output_row($lang->mybot_add_conditions." <em>*</em>", $lang->mybot_add_conditions_desc, $add_conditions);

		JB_MyBot_Conditions_Manager::generateAdditionalFields($rule->conditions);

		$action_list = JB_MyBot_Actions_Manager::getListArray();
		$add_actions = $form->generate_select_box("actions[]", $action_list, $actions, array("multiple"=>true, "id"=>"action"));
		$form_container->output_row($lang->mybot_add_action." <em>*</em>", $lang->mybot_add_action_desc, $add_actions);

		JB_MyBot_Actions_Manager::generateAdditionalFields($rule->actions);

		echo $form->generate_hidden_field("id", $rule->id);
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