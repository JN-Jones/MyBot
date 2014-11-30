<?php

$settingsgroup = array(
	"title" 		=> "MyBot Settings",
	"description"	=> 'Settings for the "MyBot" Plugin',
);

$settings[] = array(
	"name"			=> "mybot_user",
	"title"			=> "Bot",
	"description"	=> "Please insert the UID of the user who should be the bot",
	"optionscode"	=> "numeric",
	"value"			=> "0"
);

$settings[] = array(
	"name"			=> "mybot_selfreact",
	"title"			=> "React on himself?",
	"description"	=> "Should the bot react on his posts when someone is logged in with this user?<br />This doesn't end in a loop!",
	"optionscode"	=> "yesno",
	"value"			=> "no"
);

$settings[] = array(
	"name"			=> "mybot_react",
	"title"			=> "What should the bot do when a new user registers?",
	"description"	=> "",
	"optionscode"	=> "select
none=Nothing
pm=Send a PM
post=Create a thread",
	"value"			=> "none"
);

$settings[] = array(
	"name"			=> "mybot_react_pm_subject",
	"title"			=> "Subject (PM)",
	"description"	=> "Just needed when the bot sends a PM to a new User<br />See the <a href=\"index.php?module=user-mybot&amp;action=documentation\">documentation</a> for more information",
	"optionscode"	=> "text",
	"value"			=> "Welcome {registered}"
);

$settings[] = array(
	"name"			=> "mybot_react_pm",
	"title"			=> "Message (PM)",
	"description"	=> "Just needed when the bot sends a PM to a new User<br />See the <a href=\"index.php?module=user-mybot&amp;action=documentation\">documentation</a> for more information",
	"optionscode"	=> "textarea",
	"value"			=> "Hi {registered},

welcome on {boardname}

Best regards,
{botname}"
);

$settings[] = array(
	"name"			=> "mybot_react_post_forum",
	"title"			=> "Welcome forum",
	"description"	=> "Which forum should be used by the bot to post in?",
	"optionscode"	=> "numeric",
	"value"			=> "0"
);

$settings[] = array(
	"name"			=> "mybot_react_post_subject",
	"title"			=> "Subject (Thread)",
	"description"	=> "Just needed when the bot posts in a forum when a new User registers<br />See the <a href=\"index.php?module=user-mybot&amp;action=documentation\">documentation</a> for more information",
	"optionscode"	=> "text",
	"value"			=> "Welcome {registered}"
);

$settings[] = array(
	"name"			=> "mybot_react_post_text",
	"title"			=> "Message (Thread)",
	"description"	=> "Just needed when the bot posts in a forum when a new User registers<br />See the <a href=\"index.php?module=user-mybot&amp;action=documentation\">documentation</a> for more information",
	"optionscode"	=> "textarea",
	"value"			=> "Hi {registered},

welcome on {boardname}

Best regards,
{botname}"
);

$settings[] = array(
	"name"			=> "mybot_bday",
	"title"			=> "What should the bot do when a user has birthday?",
	"description"	=> "",
	"optionscode"	=> "select
none=Nothing
pm=Send a PM
post=Create a thread",
	"value"			=> "none"
);

$settings[] = array(
	"name"			=> "mybot_bday_pm_subject",
	"title"			=> "Subject (PM)",
	"description"	=> "See the <a href=\"index.php?module=user-mybot&amp;action=documentation\">documentation</a> for more information",
	"optionscode"	=> "text",
	"value"			=> "Happy Birthday {birthday}"
);

$settings[] = array(
	"name"			=> "mybot_bday_pm",
	"title"			=> "Message (PM)",
	"description"	=> "See the <a href=\"index.php?module=user-mybot&amp;action=documentation\">documentation</a> for more information",
	"optionscode"	=> "textarea",
	"value"			=> "Hi {birthday},

we wish you a Happy Birthday!

Best regards,
{botname}"
);

$settings[] = array(
	"name"			=> "mybot_bday_post_forum",
	"title"			=> "Congratulation forum",
	"description"	=> "Which forum should be used by the bot to post in?",
	"optionscode"	=> "numeric",
	"value"			=> "0"
);

$settings[] = array(
	"name"			=> "mybot_bday_post_subject",
	"title"			=> "Subject (Thread)",
	"description"	=> "See the <a href=\"index.php?module=user-mybot&amp;action=documentation\">documentation</a> for more information",
	"optionscode"	=> "text",
	"value"			=> "Happy Birthday {birthday}"
);

$settings[] = array(
	"name"			=> "mybot_bday_post_text",
	"title"			=> "Message (Thread)",
	"description"	=> "See the <a href=\"index.php?module=user-mybot&amp;action=documentation\">documentation</a> for more information",
	"optionscode"	=> "textarea",
	"value"			=> "Hi {birthday},

we wish you a Happy Birthday!

Best regards,
{botname}"
);