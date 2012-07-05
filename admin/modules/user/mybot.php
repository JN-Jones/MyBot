<?php
if(!defined("IN_MYBB"))
{
	header("HTTP/1.0 404 Not Found");
	exit;
}
$PL or require_once PLUGINLIBRARY;
$lang->load("mybot");

$page->add_breadcrumb_item($lang->mybot, "index.php?module=user-mybot");
$page->output_header($lang->mybot);

if($mybb->input['action']=="add")
	generate_tabs("add");
elseif($mybb->input['action']=="post")
	generate_tabs("post");
elseif($mybb->input['action']=="update")
	generate_tabs("update");
else
	generate_tabs("overview");
echo "Test";

$page->output_footer();

function generate_tabs($selected)
{
	global $lang, $page;

	$sub_tabs = array();
	$sub_tabs['overview'] = array(
		'title' => $lang->mybot,
		'link' => "index.php?module=user-mybot",
		'description' => $lang->mybot_overview
	);
	$sub_tabs['add'] = array(
		'title' => $lang->mybot_addrule,
		'link' => "index.php?module=user-mybot&amp;action=add",
		'description' => $lang->mybot_addrule_desc
	);
	$sub_tabs['post'] = array(
		'title' => $lang->mybot_post,
		'link' => "index.php?module=user-mybot&amp;action=post",
		'description' => $lang->mybot_post_desc
	);
	$sub_tabs['update'] = array(
		'title' => $lang->mybot_update,
		'link' => "index.php?module=user-mybot&amp;action=update",
		'description' => $lang->mybot_update_desc
	);

	$page->output_nav_tabs($sub_tabs, $selected);
}
?>