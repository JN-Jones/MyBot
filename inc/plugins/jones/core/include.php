<?php

// Whether or not to use the development version
define("USE_DEVELOPMENT", false);

// This file is only supposed to do some general checks (eg Core installed)
if(!file_exists(MYBB_ROOT."inc/plugins/jones/core/Core.php"))
	define("JB_CORE_INSTALLED", false);
else
{
	define("JB_CORE_INSTALLED", true);
	// Require the core and get the instance once - mainly to setup the auto loader in that class
	require_once MYBB_ROOT."inc/plugins/jones/core/Core.php";
	JB_Core::i();
}

if(JB_CORE_INSTALLED === false && $plugins != null)
	$plugins->add_hook("admin_config_plugins_plugin_list", "jonescore_notice");

function jb_install_plugin($codename, $register = array(), $core_minimum = false, $mybb_minimum = false, $php_minimum = "5.3")
{
	if(JB_CORE_INSTALLED === false)
		$installed = jb_install_core();

	// In case we installed the core and this plugin needs to register itself we need to do this here. Otherwise the installer can't find its files
	if($installed === true && isset($register['vendor']) && isset($register['prefix']))
	{
		JB_Core::i(); // Needed to register the autoloader
		JB_Packages::i()->register($register['prefix'], $register['vendor'], $codename);
	}

	// Don't use an else as the function above might change the value
	if(JB_CORE_INSTALLED === true || $installed === true)
		JB_Core::i()->install($codename, $core_minimum, $mybb_minimum, $php_minimum);
	else
	{
		// This message should normally never appear as "jb_install_core" should already throw an error
		flash_message("Couldn't install", 'error');
		admin_redirect('index.php?module=config-plugins');
	}
}

// Called on installation when the core isn't set up
function jb_install_core()
{
	// We don't want to have any problems guys
	if(JB_CORE_INSTALLED === true)
		return true;

	$auto = jb_download_core();

	// Still nothing here? Poke the user!
	if($auto === false)
	{
		global $page;

		// Languages not available, using english which (hopefully) everybody understands

		$page->output_header("Jones Core not installed");

		$table = new Table;
		$table->construct_header("Attention");
		$table->construct_cell("Jones Core classes are missing. Please load them from <a href=\"https://github.com/JN-Jones/JonesCore\">GitHub</a> and follow the instructions in the ReadMe. Afterwards you can reload this page.");
		$table->construct_row();
		$table->output("Jones Core not installed");

		$page->output_footer();
		exit;	
	}

	require_once MYBB_ROOT."inc/plugins/jones/core/Core.php";
	return true;
}

function jb_update_core()
{
	$auto = jb_download_core();

	if($auto === false)
	{
		global $page;

		$page->output_header(JB_Lang::get("update_failed"));

		$table = new Table;
		$table->construct_header(JB_Lang::get("attention"));
		$table->construct_cell(JB_Lang::get("update_get"));
		$table->construct_row();
		$table->output(JB_Lang::get("update_failed"));

		$page->output_footer();
		exit;
	}
}

function jb_download_core()
{
	// No need to try anything if we can't unzip the file at the end
	if(!class_exists("ZipArchive"))
		return false;

	$branch = "master";
	if(defined("USE_DEVELOPMENT") && USE_DEVELOPMENT === true)
		$branch = "development";

	$content = fetch_remote_file("https://codeload.github.com/JN-Jones/JonesCore/zip/{$branch}");

	// Wasn't able to get the zip from github
	if($content === false || empty($content))
		return false;

	// Now save the zip!
	$file = @fopen(MYBB_ROOT."inc/plugins/jones/core/temp.zip", "w");

	// Wasn't able to create the file
	if($file === false)
		return false;

	@fwrite($file, $content);
	@fclose($file);

	// We got the file - now unzip it
	$zip = new ZipArchive();
	$zip->open(MYBB_ROOT."inc/plugins/jones/core/temp.zip");
	$success = $zip->extractTo(MYBB_ROOT."inc/plugins/jones/core/temp/");
	$zip->close();

	// Something went wrong
	if($success === false)
	    return false;

	// Now move the core recursive and then delete everything
	jb_move_recursive(MYBB_ROOT."inc/plugins/jones/core/temp/JonesCore-{$branch}/");
	jb_remove_recursive(MYBB_ROOT."inc/plugins/jones/core/temp/");
	@unlink(MYBB_ROOT."inc/plugins/jones/core/temp.zip");

	return true;
}

function jb_move_recursive($direction)
{
	global $mybb;
	if(substr($direction, -1, 1) != "/")
		$direction .= "/";
	if(!is_dir($direction))
		die("Something went wrong!");
	$dir = opendir($direction);
	while(($new = readdir($dir)) !== false) {
		if($new == "." || $new == "..")
			continue;

		if(is_file($direction.$new)) {
			if(substr($new, 0, 4) == ".git" || strtolower(substr($new, 0, 6)) == "readme") {
				unlink($direction.$new);
				continue;
			}
			$old_dir = $direction.$new;
			$t = str_replace(MYBB_ROOT, "", $old_dir);
			$t2 = strpos($t, "/");
			$t2 = strpos($t, "/", $t2+1);
			$t2 = strpos($t, "/", $t2+1);
			$t2 = strpos($t, "/", $t2+1);
			$t2 = strpos($t, "/", $t2+1);
			$t2 = strpos($t, "/", $t2+1);
			$start = strlen(MYBB_ROOT)+$t2;
			$relative = substr($old_dir, $start+1);
			if(substr($relative, 0, 6) == "admin/")
				$relative = $mybb->config['admin_dir']."/".substr($relative, 6);

			$new_dir = MYBB_ROOT.$relative;
			$cdir = substr($new_dir, 0, strrpos($new_dir, "/"));

			if(!is_dir($cdir))
				mkdir($cdir, 0777, true);

			rename($old_dir, $new_dir);
		} elseif(is_dir($direction.$new)) {
			jb_move_recursive($direction.$new);
		}
	}
	closedir($dir);
}

function jb_remove_recursive($direction)
{
	if(substr($direction, -1, 1) != "/")
		$direction .= "/";
	if(!is_dir($direction))
		die("Something went wrong");
	$dir = opendir($direction);
	while(($new = readdir($dir)) !== false) {
		if($new == "." || $new == "..")
			continue;

		if(is_file($direction.$new)) {
			unlink($direction.$new);
		} elseif(is_dir($direction.$new)) {
			jb_remove_recursive($direction.$new);
		}
	}
	closedir($dir);
	rmdir($direction);
}

function jonescore_notice()
{
	// No need to check for activated plugins
	// If a plugin is activated this file is included before the hook is called and so this function
	// If no plugin is activated this file is included after the hook is called and so this function won't be called

	// First: try to install it on the fly
	$auto = jb_download_core();

	// No languages available - english!
	if($auto === false)
		echo "<div class=\"alert\">Jones Core needs to be installed! Please load them from <a href=\"https://github.com/JN-Jones/JonesCore\">GitHub</a> and follow the instractions in the ReadMe. Afterwards you can reload this page.</div>";
}