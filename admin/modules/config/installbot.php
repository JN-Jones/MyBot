<?php
if(!defined("IN_MYBB"))
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

$lang->load("mybot");

JB_AdminModules::loadModule("mybot", false, "install");