<?php

class Module_Documentation extends JB_Module_Base
{
	public function get()
	{
		global $lang;

		generate_tabs("documentation");
		$table = new Table;
		$table->construct_header($lang->mybot_variable, array("width"=>"15%"));
		$table->construct_header($lang->mybot_description);
	
		$table->construct_cell("{boardname}");
		$table->construct_cell($lang->mybot_doc_boardname);
		$table->construct_row();
	
		$table->construct_cell("{botname}");
		$table->construct_cell($lang->mybot_doc_botname);
		$table->construct_row();
	
		$table->output($lang->mybot_global);
	
	
		$table = new Table;
		$table->construct_header($lang->mybot_variable, array("width"=>"15%"));
		$table->construct_header($lang->mybot_description);
	
		$table->construct_cell("{registered}");
		$table->construct_cell($lang->mybot_doc_registered);
		$table->construct_row();
		$table->construct_cell("{regid}");
		$table->construct_cell($lang->mybot_doc_regid);
		$table->construct_row();
	
		$table->output($lang->mybot_register);
	
	
		$table = new Table;
		$table->construct_header($lang->mybot_variable, array("width"=>"15%"));
		$table->construct_header($lang->mybot_description);
	
		$table->construct_cell("{birthday}");
		$table->construct_cell($lang->mybot_doc_birthday);
		$table->construct_row();
		$table->construct_cell("{bid}");
		$table->construct_cell($lang->mybot_doc_bid);
		$table->construct_row();
	
		$table->output($lang->mybot_birthday);


		$table = new Table;
		$table->construct_header($lang->mybot_variable, array("width"=>"15%"));
		$table->construct_header($lang->mybot_description);

		$table->construct_cell("{remember}");
		$table->construct_cell($lang->mybot_doc_remember);
		$table->construct_row();
		$table->construct_cell("{rid}");
		$table->construct_cell($lang->mybot_doc_rid);
		$table->construct_row();

		$table->output($lang->mybot_remember);


		$table = new Table;
		$table->construct_header($lang->mybot_variable, array("width"=>"15%"));
		$table->construct_header($lang->mybot_description);
	
		$table->construct_cell("{lastpost->user}");
		$table->construct_cell($lang->mybot_doc_user);
		$table->construct_row();
	
		$table->construct_cell("{lastpost->userlink}");
		$table->construct_cell($lang->mybot_doc_userlink);
		$table->construct_row();
	
		$table->construct_cell("{lastpost->subject}");
		$table->construct_cell($lang->mybot_doc_subject);
		$table->construct_row();
	
		$table->construct_cell("{lastpost->id}");
		$table->construct_cell($lang->mybot_doc_id);
		$table->construct_row();
	
		$table->construct_cell("{lastpost->link}");
		$table->construct_cell($lang->mybot_doc_link);
		$table->construct_row();
	
		$table->construct_cell("{lastpost->date}");
		$table->construct_cell($lang->mybot_doc_date);
		$table->construct_row();
	
		$table->construct_cell("{lastpost->time}");
		$table->construct_cell($lang->mybot_doc_time);
		$table->construct_row();
	
		$table->construct_cell("{lastpost->message}");
		$table->construct_cell($lang->mybot_doc_message);
		$table->construct_row();
	
		$table->construct_cell("{lastpost->uid}");
		$table->construct_cell($lang->mybot_doc_uid);
		$table->construct_row();
	
		$table->construct_cell("{lastpost->timestamp}");
		$table->construct_cell($lang->mybot_doc_timestamp);
		$table->construct_row();
	
		$table->construct_cell($lang->mybot_doc_thread, array("colspan"=>2));
		$table->construct_row();
	
		$table->construct_cell("{thread->forum}");
		$table->construct_cell($lang->mybot_doc_forum);
		$table->construct_row();
	
		$table->construct_cell("{thread->answers}");
		$table->construct_cell($lang->mybot_doc_answers);
		$table->construct_row();
	
		$table->construct_cell("{thread->views}");
		$table->construct_cell($lang->mybot_doc_views);
		$table->construct_row();
	
		$table->construct_cell("{foundstring}");
		$table->construct_cell($lang->mybot_doc_foundstring);
		$table->construct_row();
	
		$table->output($lang->mybot_thread);
	}
}
