<?php

class JB_MyBot_Actions_Close extends JB_MyBot_Actions_Base
{
	protected static $type = "close";

	public function doAction(&$pid, &$thread, &$info, &$date)
	{
		global $moderation;

		if($thread['closed'] == 1)
			$moderation->open_threads($info['tid']);
		else
			$moderation->close_threads($info['tid']);
	}
}