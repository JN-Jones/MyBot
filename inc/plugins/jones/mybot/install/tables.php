<?php

$tables[] = "CREATE TABLE mybb_mybot (
	id int(11) NOT NULL AUTO_INCREMENT,
	title varchar(50) DEFAULT NULL,
	conditions text NOT NULL,
	actions text NOT NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM;";