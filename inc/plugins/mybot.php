<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
if(!defined("PLUGINLIBRARY"))
{
    define("PLUGINLIBRARY", MYBB_ROOT."inc/plugins/pluginlibrary.php");
}

$plugins->add_hook("admin_config_action_handler", "mybot_admin_config_action_handler");
$plugins->add_hook("admin_config_plugins_activate_commit", "mybot_installed");
$plugins->add_hook("admin_user_menu", "mybot_admin_user_menu");
$plugins->add_hook("admin_user_action_handler", "mybot_admin_user_action_handler");
$plugins->add_hook("admin_user_permissions", "mybot_admin_user_permissions");

function mybot_info()
{
	return array(
		"name"			=> "MyBot",
		"description"	=> "Adds a simple Bot to your MyBB",
		"website"		=> "",
		"author"		=> "Jones",
		"authorsite"	=> "http://mybbdemo.tk",
		"version"		=> "0.1",
		"guid" 			=> "",
		"compatibility" => "16*"
	);
}

function mybot_install()
{
	global $lang, $PL;
	mybot_uninstall();
	$plugininfo = mybot_info();
	$lang->load("mybot");
    if(!file_exists(PLUGINLIBRARY))
    {
        flash_message($lang->mybot_pl_missing, "error");
        admin_redirect("index.php?module=config-plugins");
    }
    $PL or require_once PLUGINLIBRARY;

    if($PL->version < 8)
    {
//        flash_message($lang->mybot_pl_old, "error");
        flash_message($PL->version, "error");
        admin_redirect("index.php?module=config-plugins");
    }
    global $PL;
	$PL->settings("mybot",
	  	"MyBot",
	  	"Settings for the \"MyBot\" Plugin",
	  	array(
	      	"user" => array(
	          	"title" => "Bot",
	          	"description" => "The User which acts as the bot. Please insert the User ID of this bot",
		        "optionscode" => "text",
		        "value" => "0",
	          ),
	      	"react" => array(
	          	"title" => "Reaction on a new User?",
		        "optionscode" => "select
none=Nothing
pm=Write a PM
post=Write a Post",
		        "value" => "none",
	          ),
	      	"react_pm" => array(
	          	"title" => "What stands in the PM?",
	          	"description" => "Just needed when the bot sends a PM to a new User",
		        "optionscode" => "textarea",
		        "value" => "Hi {registered},

welcome here on {boardname}. We hope you have fun here.

Best regards,
{boardname} Team",
	          ),
	      	"react_post_forum" => array(
	          	"title" => "Forum to post in",
	          	"description" => "In which forum should the bot post?",
		        "optionscode" => "text",
		        "value" => "0",
	          ),
	      	"react_post_text" => array(
	          	"title" => "What stands in the post?",
	          	"description" => "Just needed when the bot posts in a forum when a new User registers",
		        "optionscode" => "textarea",
		        "value" => "Hi {registered},

welcome here on {boardname}. We hope you have fun here. If you have questions you can ask them here.

Best regards,
{boardname} Team",
	          ),
		)
    );

	$PL->cache_update("mybot_version", $plugininfo['version']);
}

function mybot_installed()
{
	global $install_uninstall, $codename;
	if($codename=="mybot" && $install_uninstall)
	    admin_redirect("index.php?module=config-installbot");
}

function mybot_is_installed()
{
	global $PL;
	$PL or require_once PLUGINLIBRARY;
	if($PL->cache_read("mybot_version")!="")
	    return true;
	return false;
}

function mybot_uninstall()
{
	global $PL;
	$PL->cache_delete("mybot_version");
}

function mybot_admin_config_action_handler($actions)
{
	global $action;
	echo $action;
	$actions['installbot'] = array(
		"active" => "plugins",
		"file" => "installbot.php"
	);

	return $actions;
}

function mybot_admin_user_menu($sub_menu)
{
	global $lang;

	$lang->load("mybot");

	$sub_menu[] = array("id" => "mybot", "title" => $lang->mybot, "link" => "index.php?module=user-mybot");

	return $sub_menu;
}

function mybot_admin_user_action_handler($actions)
{
	$actions['mybot'] = array(
		"active" => "mybot",
		"file" => "mybot.php"
	);

	return $actions;
}

function mybot_admin_user_permissions($admin_permissions)
{
	global $lang;

	$lang->load("mybot");

	$admin_permissions['mybot'] = $lang->mybot_permission;

	return $admin_permissions;
}

function mybot_activate()
{}

function mybot_deactivate()
{}
?>