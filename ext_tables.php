<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


//This adds a new menu type, which allows to list tt_new categories as if they were pages.
t3lib_extMgm::addPlugin(array(
	'LLL:EXT:cablan_virtual_tt_news/locallang_db.xml:tt_content.menu_type_pi1',
	$_EXTKEY . '_pi1'
) ); 



// this adds the mount point and the sorting to news categories, thus allowing categories to 
// have a different virtual page! This is very useful if a category needs a different template,
// such as for a multi-site setup.
// 
// It also allows to change the sorting so that the categories are sorted like pages.
// Please note that sorting is already a field in tt_news_cat, but it's not manually
// editable, so I simply make it editable for easier management.
$tempColumns = array (
	'tx_cablanvirtualttnews_virtualnewsmountpoint' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:cablan_virtual_tt_news/locallang_db.xml:tt_news_cat.tx_cablanvirtualttnews_virtualnewsmountpoint',		
		'config' => array (
			'type' => 'group',	
			'internal_type' => 'db',	
			'allowed' => 'pages',	
			'size' => 1,	
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
	'sorting' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:cablan_virtual_tt_news/locallang_db.xml:tt_news_cat.sorting',		
		'config' => array (
			'type' => 'input',	
			
		)
	),
);


// this section simply connects everything properly: it sets the tempColumns, and adds the fields to the edition.
t3lib_div::loadTCA('tt_news_cat');
t3lib_extMgm::addTCAcolumns('tt_news_cat',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tt_news_cat','sorting,tx_cablanvirtualttnews_virtualnewsmountpoint;;;;1-1-1');
?>