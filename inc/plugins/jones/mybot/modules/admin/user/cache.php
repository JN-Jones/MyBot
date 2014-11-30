<?php

class Module_Cache extends JB_Module_Base
{
	public function get()
	{
		global $lang;
		mybot_cache_update();
	
		flash_message($lang->mybot_cache_reloaded, 'success');
		admin_redirect("index.php?module=".MODULE);
	}
}