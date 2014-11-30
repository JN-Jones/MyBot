<?php

class Module_Delete extends JB_Module_Base
{
	private $id;

	public function start()
	{
		global $lang, $mybb;

		$this->id = (int)$mybb->input['id'];
		if(!$this->id)
		{
			flash_message($lang->mybot_no_id, 'error');
			admin_redirect("index.php?module=".MODULE);
		}
	
		if($mybb->input['no'])
		{
			admin_redirect("index.php?module=".MODULE);
		}
	}
	
	public function post()
	{
		global $lang;
		// Don't trust the cache :P
		JB_MyBot_Rule::getById($this->id)->delete();
		mybot_cache_update();
		flash_message($lang->mybot_delete_success, 'success');
		admin_redirect("index.php?module=".MODULE);
	}

	public function get()
	{
		global $page, $lang;
		$page->output_confirm_action("index.php?module=".MODULE."&action=delete&id={$this->id}", $lang->mybot_delete_confirm);
	}
}