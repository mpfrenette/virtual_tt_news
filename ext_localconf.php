<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

// this adds virtual tt_news as one of the list_types. Added later for easier management.
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_cablanvirtualttnews_pi1.php', '_pi1', 'list_type', 1);
?>