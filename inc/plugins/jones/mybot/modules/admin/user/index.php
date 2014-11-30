<?php

class Module_Index extends JB_Module_Base
{
	public function get()
	{
		global $lang;

		generate_tabs("overview");
		$rules = mybot_cache_load();
	
		$table = new Table;
		$table->construct_header($lang->mybot_title, array("width" => "10%"));
		$table->construct_header($lang->mybot_conditions, array("width" => "35%"));
		$table->construct_header($lang->mybot_actions, array("width" => "35%"));
		$table->construct_header($lang->controls, array("colspan" => 2, "width" => "20%"));
	
		if(is_array($rules) && count($rules) > 0)
		{
			foreach($rules as $rule)
			{
				$conditions = $actions = array();

				foreach($rule->getConditions() as $condition)
				    $conditions[] = $condition->getName();
				$conditions = array_filter($conditions);

    			foreach($rule->getActions() as $action)
				    $actions[] = $action->getName();
				$actions = array_filter($actions);

				$table->construct_cell($rule->title);
				$table->construct_cell(implode(", ", $conditions));
				$table->construct_cell(implode(", ", $actions));
				$table->construct_cell("<a href=\"index.php?module=".MODULE."&amp;action=edit&amp;id={$rule->id}\">{$lang->edit}</a>");
				$table->construct_cell("<a href=\"index.php?module=".MODULE."&amp;action=delete&amp;id={$rule->id}\">{$lang->delete}</a>");
				$table->construct_row();
			}
		}
		else
		{
			$table->construct_cell($lang->mybot_no_rules, array("colspan" => 5, "style" => "text-align: center"));
			$table->construct_row();
		}
		$table->output($lang->mybot_overview);
	}
}