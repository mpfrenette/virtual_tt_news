<?php

########################################################################
# Extension Manager/Repository config file for ext "cablan_virtual_tt_news".
#
# Auto generated 07-05-2010 13:37
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'tt_news virtual tree system',
	'description' => 'This extension allows to build a virtual news category tree',
	'category' => 'plugin',
	'author' => 'Martin-Pierre Frenette',
	'author_email' => 'typo3@cablan.net',
	'shy' => '',
	'dependencies' => 'tt_news',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'tt_news' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:10:{s:9:"ChangeLog";s:4:"5c68";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"d90d";s:14:"ext_tables.php";s:4:"ca17";s:14:"ext_tables.sql";s:4:"5308";s:16:"locallang_db.xml";s:4:"b5f1";s:19:"doc/wizard_form.dat";s:4:"9b5d";s:20:"doc/wizard_form.html";s:4:"128e";s:40:"pi1/class.tx_cablanvirtualttnews_pi1.php";s:4:"931a";}',
);

?>