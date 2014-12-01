<?php

class JB_MyBot_Actions_Stick extends JB_MyBot_Actions_Base
{
	protected static $type = "stick";

	public function doAction(&$pid, &$thread, &$info, &$date)
	{
		global $moderation;

		if($thread['sticky'] == 1)
			$moderation->unstick_threads($info['tid']);
		else
			$moderation->stick_threads($info['tid']);
	}
}