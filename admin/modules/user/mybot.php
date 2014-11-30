<?php
if(!defined("IN_MYBB"))
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

if(function_exists("myplugins_info"))
	define(MODULE, "myplugins-mybot");
else
	define(MODULE, "user-mybot");

$lang->load("mybot");

$page->add_breadcrumb_item($lang->mybot, "index.php?module=".MODULE);
if($mybb->input['action']!="delete")
{
	$page->output_header($lang->mybot);
}

JB_AdminModules::loadModule();

$page->output_footer();

function generate_tabs($selected)
{
	global $lang, $page;

	$sub_tabs = array();
	$sub_tabs['overview'] = array(
		'title' => $lang->mybot,
		'link' => "index.php?module=".MODULE,
		'description' => $lang->mybot_overview
	);
	$sub_tabs['add'] = array(
		'title' => $lang->mybot_addrule,
		'link' => "index.php?module=".MODULE."&amp;action=add",
		'description' => $lang->mybot_addrule_desc
	);
	$sub_tabs['post'] = array(
		'title' => $lang->mybot_post,
		'link' => "index.php?module=".MODULE."&amp;action=post",
		'description' => $lang->mybot_post_desc
	);
	$sub_tabs['documentation'] = array(
		'title' => $lang->mybot_documentation,
		'link' => "index.php?module=".MODULE."&amp;action=documentation",
		'description' => $lang->mybot_documentation_desc
	);
	$sub_tabs['cache'] = array(
		'title' => $lang->mybot_cache_reload,
		'link' => "index.php?module=".MODULE."&amp;action=cache",
		'description' => ""
	);

	$page->output_nav_tabs($sub_tabs, $selected);
}

function sort_user($a, $b)
{
	global $lang;
	if($a == $lang->thread_creator)
		return -1;
	if($b == $lang->thread_creator)
		return 1;

	return strcoll($a, $b);
}
?>