<?php

class JB_MyBot_Rule extends JB_Classes_StorableObject
{
	static protected $table = "mybot";
	static protected $cache = array();
	static protected $timestamps = false;
	static protected $user = false;

	private $actions = array();
	private $conditions = array();

	public function __construct($data)
	{
		if(empty($data['id']))
			$data['id'] = -1;

		$this->data = $data;

		$this->setActions($data['actions']);
		$this->setConditions($data['conditions']);
	}

	public function validate($hard = true)
	{
		global $lang;

		if(empty($this->data['title']))
			$this->errors[] = $lang->mybot_add_title_not;

		if(empty($this->data['conditions']))
			$this->errors[] = $lang->mybot_add_conditions_not;

		if(empty($this->data['actions']))
			$this->errors[] = $lang->mybot_add_action_not;

		foreach($this->getConditions() as $condition)
		{
			if(!$condition->validate())
				$this->errors = array_merge($this->errors, $condition->getErrors());
		}

		foreach($this->getActions() as $action)
		{
			if(!$action->validate())
				$this->errors = array_merge($this->errors, $action->getErrors());
		}

		if(!empty($this->errors))
			return false;

		return true;
	}

	// Get all objects
	public static function getFromCache($id=false)
	{
		global $cache;
	
		$content = $cache->read("mybot_rules");
		if(!is_array($content))
		{
			mybot_cache_update();
			$content = $cache->read("mybot_rules");
		}

		if(is_array($content))
		{
			foreach($content as &$rule)
			{
				$rule = static::create($rule);
			}
		}

		if($id === false)
			return $content;

		if(!isset($content[$id]))
			return false;

		return $content[$id];
	}

	// Saves the current object
	public function save()
	{
		global $db, $mybb;

		// First: Validate
		if(!$this->validate(true))
			return false;

		$this->data['actions'] = $this->getActions(true);
		$this->data['conditions'] = $this->getConditions(true);

		// Escape everything
		$data = dbe($this->data);

		// Not existant -> insert
		if($this->data['id'] == -1)
		{
			unset($data['id']);
			static::runHook("save", $data);

			if(static::$timestamps)
				$this->data['dateline'] = $data['dateline'] = TIME_NOW;
			if(static::$user && empty($this->data['uid']))
				$this->data['uid'] = $data['uid'] = $mybb->user['uid'];
			$this->data['id'] = $db->insert_query(static::$table, $data);
		}
		// exists -> update
		else
			$db->update_query(static::$table, $data, "id='{$this->data['id']}'");

		return true;
	}

	public function setActions($actions, $clear=false)
	{
		if(is_string($actions))
			$actions = @unserialize($actions);

		if(is_array($actions))
		{
			if($clear === true)
				$this->actions = array();

			foreach($actions as $type => $data)
			{
				$action = JB_MyBot_Actions_Manager::create($type, $data, $rule);
				if($action !== false)
					$this->actions[] = $action;
			}
		}
	}

	public function getActions($serialize = false)
	{
		if($serialize === false)
			return $this->actions;

		$actions = array();
		foreach($this->actions as $action)
		{
			$actions[$action->getType()] = $action->getData();
		}
		return @serialize($actions);
	}

	public function getAction($type)
	{
		foreach($this->actions as $action)
		{
			if($action->getType() == $type)
				return $action;
		}
		return false;
	}

	public function hasAction($type)
	{
		if($this->getAction($type) === false)
			return false;
		return true;
	}

	public function setConditions($conditions, $clear=false)
	{
		if(is_string($conditions))
			$conditions = @unserialize($conditions);

		if(is_array($conditions))
		{
			if($clear === true)
				$this->conditions = array();

			foreach($conditions as $type => $data)
			{
				$condition = JB_MyBot_Conditions_Manager::create($type, $data, $this);
				if($condition !== false)
					$this->conditions[] = $condition;
			}
		}
	}

	public function getConditions($serialize = false)
	{
		if($serialize === false)
			return $this->conditions;

		$conditions = array();
		foreach($this->conditions as $condition)
		{
			$conditions[$condition->getType()] = $condition->getData();
		}
		return @serialize($conditions);
	}

	public function getCondition($type)
	{
		foreach($this->conditions as $condition)
		{
			if($condition->getType() == $type)
				return $condition;
		}
		return false;
	}

	public function hasCondition($type)
	{
		if($this->getCondition($type) === false)
			return false;
		return true;
	}

	public function toArray()
	{
		return array(
			"id"			=> $this->id,
			"title"			=> $this->title,
			"conditions"	=> $this->getConditions(true),
			"actions"		=> $this->getActions(true)
		);
	}
}