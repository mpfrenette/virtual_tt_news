<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Martin-Pierre Frenette <typo3@cablan.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   78: class tx_cablanvirtualttnews_pi1 extends tslib_pibase
 *  105:     function getRecord( $table, $uid ,$enableFields = 1 )
 *  134:     function initialize( $conf)
 *  179:     function main($content, $conf)
 *  215:     function GetActualParentCategory( $parentCategory, $entryLevel)
 *  265:     function BuildRootLineMenu($parentCategory, $page)
 *  328:     function BuildMenu($uid, $page, $level = 1)
 *  437:     function ProcessMenuItem( $cat_row, $itemTS, $page, $subItemsContent = '', $level = 0)
 *  494:     function GetCatLink( $cat_row, $page, $title = NULL)
 *  536:     function AddATagParam( $menuItem, $ATagParams)
 *  549:     function IsActiveCategory($uid)
 *  585:     function GetSubCategoriesRecursively($parentuid, $level = 1)
 *  610:     function GetBreadCrumbs($currentUid, $topUid)
 *  666:     function AddSubCategoriesToCurrentPage($I,$conf)
 *  699:     function GetCatTitle($cat_row)
 *  735:     static function pregCallBack($data)
 *  796:     function extraItemMarkerProcessor($markerArray, $row, $lConf, $ttnewsObj)
 *  839:     function SetPageTitleRegister()
 *
 * TOTAL FUNCTIONS: 17
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/*
 * Description: This is the main file of the virtual_tt_news class.
 * What this allows, is to build a menu of tt_news categories, so that
 * a TYPO3 site can be built using tt_news articles as if they were content
 * elements, and the tt_news categories become the pages.
 *
 * The advantage is to allow to use TYPO3 categories as if this was a Wordpress
 * site, but with the power, speed and flexibility of TYPO3 instead of being
 * in Wordpress.
 *
 * This class was the focus of a presentation at the TYPO3 Conference in Québec
 * City by Martin-Pierre Frenette.
 *
 * This extension is meant for TYPO3 4.5.
 */
require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'virtual tt_news subpages' for the 'cablan_virtual_tt_news' extension.
 *
 * @author	Martin-Pierre Frenette <typo3@cablan.net>
 * @package	TYPO3
 * @subpackage	tx_cablanvirtualttnews
 */
class tx_cablanvirtualttnews_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_cablanvirtualttnews_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_cablanvirtualttnews_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'cablan_virtual_tt_news';	// The extension key.
	var $pi_checkCHash = true;
	var $enableCatFields = null;
	var $catlistWhere = '';
	var $config = array();

	var $menuPid;

	var $mode = 'HMENU';
	var $parentCategory = 0;
	var $entryLevel = 1;
	var $menuTS = array();



	/**
	 * This simple function, missing from the TYPO3 API, allows to load a
	 * single record from the database, using the enableFields or not.
	 *
	 * @param	string		$table: the table name
	 * @param	int		$uid: the primary key (uid) of the record
	 * @param	boolean		$enableFields: whether to add the enableFields api call to the where. Default: true.
	 * @return	the		requested row, or null
	 */
	function getRecord( $table, $uid ,$enableFields = 1 ){
        $where = ' uid=' . intval($uid );

        if ( $enableFields ) {
            if ( $this->cObj != NULL ||  $this->cObj = t3lib_div::makeInstance('tslib_cObj') ){
                $where .=  $this->cObj->enableFields($table);
            }
        }
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery( "*", $table, $where);
        return @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
    }


	/**
	 * This function initializes the configuration. It is common to all TYPO3
	 * plugins.
	 *
	 * In this case, it reads the configuration from the plugin (stored in
	 * typoscript and passed to conf) and allows to overwrite the menu
	 * configuration.
	 *
	 * It also reads the virtual_page which is TYPO3 page where the
	 * virtual_tt_news configuration is stored.
	 *
	 * All of the links in the menu will point to that page.
	 *
	 * @param	array		$conf: The PlugIn configuration
	 * @return	[type]		...
	 */
	function initialize( $conf){

		// Get the PID from which to make the menu.
		// If a page is set as in the Typoscript, use that
		// Otherwise use the page's id-number from TSFE
		$this->menuPid = intval($conf['virtual_page']?$conf['virtual_page']:$GLOBALS['TSFE']->id);

		$this->menuTS['1.'] = $conf['1.'];
		// The first level menu is the only one which doesn't have to have
		// to be explicitely enabled, but our code needs it, so we force it.
		$this->menuTS['1.']["enable"] = "1";

		$this->menuTS['2.'] = $conf['2.'];
		$this->menuTS['3.'] = $conf['3.'];
		$this->menuTS['4.'] = $conf['4.'];
		$this->menuTS['5.'] = $conf['5.'];
		$this->menuTS['6.'] = $conf['6.'];
		$this->menuTS['7.'] = $conf['7.'];
		$this->menuTS['8.'] = $conf['8.'];
		$this->menuTS['9.'] = $conf['9.'];
		$this->menuTS['10.'] = $conf['10.'];


		// we also allow to specify custom Category list where conditions.
		$this->catlistWhere = $conf['catListWhere'];

		// instead of called the enableFields on every call to tt_news_cat,
		// save them as they are global.
		if ( !isset($this->enableCatFields) ){
			$this->enableCatFields = $this->cObj->enableFields('tt_news_cat');
		}

	}

	/**
	 * The main method of the PlugIn. This is the function which is called
	 * by the plugin itself.
	 *
	 * This function basically builds the virtual menu, and sets the current
	 * page title for the browser bar to the current virtual page.
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The		content that is displayed on the website (Menu)
	 */
	function main($content, $conf)	{

		$this->initialize($conf);
		$this->config = $conf;

		// Now, get an array with all the subpages to this pid:
		// (Function getMenu() is founc  in class.t3lib_page.php)
		$menuItems_level1 = $GLOBALS['TSFE']->sys_page->getMenu($this->menuPid);

		//Entry level 0 and 1 are the same, neg values ignored for now
		$this->entryLevel = max(1,intval( $conf['entryLevel']));

		// The parent category is the root category of news articles
		// for the virtual_tt_news system.
		$this->parentCategory = $this->GetActualParentCategory($conf['parentCategory'], $this->entryLevel);

		// Prepare the menu
		$cat_content = $this->BuildMenu($this->parentCategory,$this->menuPid);

		// change the page title to the current virtual page
		$this->SetPageTitleRegister();

		return $cat_content;
	}

	/**
	 * This function returns the proper top category, taking into
	 * account the entryLevel.
	 *
	 * In TYPO3, the entryLevel allows to create a sub-menu or even
	 * a sub-sub-menu which only lists items 2 or 3 levels deep!
	 *
	 * @param	int		$parentCategory: The parent category
	 * @param	int		$entryLevel: the entry level
	 * @return	the		uid of the parent category
	 */
	function GetActualParentCategory( $parentCategory, $entryLevel){

		// an entry Level of 0 or 1 means we don't skip any levels.
		if ( $entryLevel > 1){

			// the current category id is read from the tx_ttnews parameter,
			// which usually filled by realUrl, so that the category
			// name is translated by realUrl into the category ID
			// automatically.
			$tx_ttnews =t3lib_div::_GET('tx_ttnews');
			$cat = intval( $tx_ttnews['cat']);

			// We simulate the deeper menu by reusing the GetBreadCrumbs
			// function used to make a BreadCrumbs menu, because this is
			// the easiest way to dig down the current tree of categories
			$breadCrumbs = $this->GetBreadCrumbs($cat, $parentCategory);

			// we skip one since the top category is NEVER listed in
			// the breadcrumbs!!!
			$entryLevel -= 1;

			// If we have less breadcrumbs than the entryLevel, then we
			// stick to the current parentCategory, since we are not yet
			// deep enough in the structure.
			if (count($breadCrumbs) > $entryLevel   ){

				// We fetch the proper breadCrumbs. We use array_shift
				// to avoid relying on the index.
				while ( $entryLevel > 0){
					array_shift($breadCrumbs);
					$entryLevel--;

				}
				$parentCategory = array_shift($breadCrumbs);
			}
		}
		return $parentCategory;
	}

	/**
	 * This function creates a rootline menu, whihc is what people
	 * now usually call a breadcrumb menu.
	 *
	 * GetBreadcrumbs returns the page ids, while this function
	 * builds the actual HTML for the breadcrumbs menu.
	 *
	 * @param	int		$parentCategory: The parent category
	 * @param	int		$page: the virtual_tt_news page
	 * @return	the		built menu
	 */
	function BuildRootLineMenu($parentCategory, $page){

			$this->mode = 'ROOTLINE';

			// the current category id is read from the tx_ttnews parameter,
			// which usually filled by realUrl, so that the category
			// name is translated by realUrl into the category ID
			// automatically.
			$tx_ttnews =t3lib_div::_GET('tx_ttnews');
			$cat = intval( $tx_ttnews['cat']);

			// if we don't have a current category, we are on the home page
			if ( $cat == 0){
				$cat = $parentCategory;
			}

			// For this menu time, the source of data is the breadcrumbs!
			$breadCrumbs = $this->GetBreadCrumbs($cat, $parentCategory);

			// These are the default split from TYPO3, which generates
			// what is prior to the links and after the links.
			$NOsplit =$GLOBALS['TSFE']->tmpl->splitConfArray( $this->menuTS['1.']['NO.'],count($breadCrumbs) );
			$ACTsplit =$GLOBALS['TSFE']->tmpl->splitConfArray( $this->menuTS['1.']['ACT.'],count($breadCrumbs) );

			// For each item in the breadcrumps, we scan to see if it's the
			// current item or not. It will determine if it's ACTive or NOrmal.
			foreach( $breadCrumbs as $cat_uid){
				$current = count($cats);
				$cat_row = $this->getRecord('tt_news_cat', $cat_uid);

				if ($cat_uid == $cat){
						$itemTS = $ACTsplit[$current];
					}
					else{
						$itemTS = $NOsplit[$current];
					}

				$cat_content = '';
				// ProcessMenutitem is the function which makes the actual
				// link and the content of the link.
				$cat_content = $this->ProcessMenuItem($cat_row, $itemTS, $page);
				$cats[] = $cat_content;

			}

			if ( count($cats) > 0){
				// the wrap array is what is put before and after the menu
				// such as <ul>|</ul>
				$wrapArray = explode('|',$this->menuTS['1.']['wrap']);
				$content = $wrapArray[0]. implode('', $cats). $wrapArray[1];
			}

			return $content;
	}

	/**
	 * This recursive function builds the menu.
	 *
	 * @param	int		$uid: The parent category number
	 * @param	int		$page: the page number to point to
	 * @param	int		$level: the recursive depth
	 * @return	The		content that is displayed on the website (Menu)
	 */
	function BuildMenu($uid, $page, $level = 1){

	$this->mode = 'HMENU';

		// a rootline menu is a breadcrumb. We need a special function to
		// generate them.
		if ( $this->config['special'] == 'rootline'){
			return $this->BuildRootLineMenu($uid, $page);
		}

		// If the current level isn't enabled, we ignore it.
		if ( intval($this->menuTS[$level.'.']['enable']) != 0){



			// If there is no orderBy specified in Typoscript, the default
			// is to use the sorting value built in TYPO3 and then, the uid.
			if ( $this->config['catOrderBy'] == ''){
				$this->config['catOrderBy']  = 'sorting,uid';
			}

			// Just like with normal HMENU, it is possible to exclude categories
			// using a comma separated value list.
			if ( $this->config['excludeUidList']){

				$excludeArray = t3lib_div::intExplode(',',$this->config['excludeUidList']);
				if ( is_array($excludeArray)){
					$where .= ' AND uid NOT IN ('. implode(',', $excludeArray).') ';
				}

			}

			// We fetch the sub-pages of the current level
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_news_cat', 'tt_news_cat.parent_category='.$uid .' ' . $this->enableCatFields . $where. $this->catlistWhere, '', 'tt_news_cat.' . $this->config['catOrderBy']);
			if ( $res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0){
				$cats = array();

				// These are the default split from TYPO3, which generates
				// what is prior to the links and after the links.
				$NOsplit =$GLOBALS['TSFE']->tmpl->splitConfArray( $this->menuTS[$level. '.']['NO.'],$GLOBALS['TYPO3_DB']->sql_num_rows($res) );
				$ACTsplit =$GLOBALS['TSFE']->tmpl->splitConfArray( $this->menuTS[$level. '.']['ACT.'],$GLOBALS['TYPO3_DB']->sql_num_rows($res) );

				while($cat_row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) 	{


					$current = count($cats);

					// If the category is active (in the current tree)
					// we use the ACTsplit andforce the process the sub-menus
					if ($this->IsActiveCategory($cat_row['uid'])){
						$itemTS = $ACTsplit[$current];
						$processSub = true;
					}
					// otherwise, we are a NOrmal split, and only process
					// the sub-menus if expAll of the level is not 0.
					else{
						$itemTS = $NOsplit[$current];
						$processSub = intval($this->menuTS[$level.'.']['expAll']) != 0;

					}
					$sub_content = null;
					if ( $processSub){
						// this is the recursive part! We build the menu if we
						// process submenus.
						$sub_content =	 $this->BuildMenu($cat_row['uid'],$page, $level + 1);
					}


					// ProcessMenutitem is the function which makes the actual
					// link and the content of the link.
					$cat_content = $this->ProcessMenuItem($cat_row, $itemTS, $page, $sub_content, $level);

					$cats[] = $cat_content;
				}

			}
		}


		// This is the custrom hook called after the menu was generated as
		// a whole. It allows to manipulate the individual elements manually.
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['virtual_tt_news']['postCatContentHook'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['virtual_tt_news']['postCatContentHook'] as $_classRef) {
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$cats = $_procObj->postCatContentProcessor($cats, $this);
				}
			}

		if ( count($cats) > 0){
			// the wrap array is what is put before and after the menu
			// such as <ul>|</ul>
			$wrapArray = explode('|',$this->menuTS[$level.'.']['wrap']);
			$content = $wrapArray[0]. implode('', $cats). $wrapArray[1];
		}

		return $content;
	}


	/**
	 * This function builds a single menu item
	 *
	 * @param	array		$cat_row: category row
	 * @param	array		$itemTS: the typoscript of the item
	 * @param	int		$page: the virtual tt_news page
	 * @param	string		$subItemsContent: the submenu
	 * @param	int		$level: the menu level we are at
	 * @return	The		content for that menu item
	 */
	function ProcessMenuItem( $cat_row, $itemTS, $page, $subItemsContent = '', $level = 0){


		// This is the custrom hook called to manipulate the individual
		// typoscript of items, before the menuitem is procsses.
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['virtual_tt_news']['itemTSHook'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['virtual_tt_news']['itemTSHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$itemTS = $_procObj->itemTSProcessor($cat_row, $itemTS, $this,$level);
			}
		}

		$cat_content = '';
		// Since our menu is a menu of tt_news categories, instead of page
		// titles, we use category titles!
		$catTitle = $this->GetCatTitle($cat_row);

		$values = array();

		// We load the various typoscript wraps for the menuitem
		$wrapitemAndSubArray = explode('|',$itemTS['wrapItemAndSub']);
		$allWrap = explode( '|', $itemTS['allWrap']);
		$linkWrap = explode('|', $itemTS['linkWrap']);

		// As per TYPO3 specs, the linkWrap is actually the link of the title.
		// It doesn't actually wrap the link anymore, but the property
		// wasn't renamed when its behavior was changed.
		$title = $linkWrap[0]. $catTitle . $linkWrap[1];

		// We need our own link creation function, since we link to a single
		// page and use parameters to select the category.
		$menuitem = $this->GetCatLink($cat_row, $page, $title);

		// This allows to add classes or other elements to the A tag.
		// If you need a custom A tag element, you can use the itemTSHook
		// hook above, allowing to generate a custom ATagParams per element.
		$menuitem = $this->AddATagParam($menuitem, $itemTS['ATagParams']);

		// wrapitemandSub wraps the whole menu item AND its sub-items.
		$cat_content =$wrapitemAndSubArray[0];
		// Allwrap wraps the actual menu item itself, but not the sub-items.
		$cat_content .= $allWrap[0]. $menuitem . $allWrap[1];
		$cat_content .= $subItemsContent;
		$cat_content .=	$wrapitemAndSubArray[1];

		return $cat_content;
	}

	/**
	 * This function builds a category link, using the standard tt_news
	 * param tx_ttnews[cat], unless of course, the catogory is a shortcut.
	 *
	 * @param	array		$cat_row: category row
	 * @param	int		$page: the virtual tt_news page
	 * @param	string		$title: title in the link
	 * @return	The		full <a href="LINK">Title</a> html content
	 */
	function GetCatLink( $cat_row, $page, $title = NULL){

		// The title parameter is optional. In reality, we usually specify
		// it in order to apply a linkWrap, but it can be omitted.
		if ( $title == NULL){
			$title = $this->GetCatTitle($cat_row);
		}

		// When a category row is a shortcut, the shortcut will have the
		// primary key (uid) of the page it is a shortcut to stored as an
		// integer. To link external, you can simply use a page of type
		// "External URL" and redirect the category to that page.
		if ( $cat_row['shortcut'] > 0 ){
			$content = $this->pi_linkTP(
				$title,
				array(), //NOW we don't pass the paramter for shortcuts.
				1,
				$cat_row['shortcut']
				);

		}else{
		// but when it's not a shortcut, we link to the virtual tt_news page,
		// passing the tx_tt_news[cat] parameter.
			$content = $this->pi_linkTP(
				$title,
				array( 'tx_ttnews[cat]' => $cat_row['uid']),
				1,
				$page
				);
		}

		return $content;
	}
	/**
	 * This function adds the A Tag param to the existing link HTML.
	 * In this care, we do the fast substr_replace to inject the ATagParem
	 * just before the initial >
	 *
	 * @param	string		$menuItem: the HTML of the menuItem
	 * @param	string		$ATagParams: the params to add
	 * @return	The		full <a href="LINK">Title</a> html content
	 */
	function AddATagParam( $menuItem, $ATagParams){

		$menuItem = substr_replace($menuItem, ' '. $ATagParams . ' >', strpos( $menuItem, '>'),1);
		return $menuItem;
	}

	/**
	 * this function checks if the passed category is the current
	 * category or one of its sub-categories set in the tx_ttnews[cat] parameter.
	 *
	 * @param	int		$uid: The category number to check
	 * @return	boolean		true if the category (or one of its sub) is the current
	 */
	function IsActiveCategory($uid){

		// the current category id is read from the tx_ttnews parameter,
		// which usually filled by realUrl, so that the category
		// name is translated by realUrl into the category ID
		// automatically.
		$tx_ttnews =t3lib_div::_GET('tx_ttnews');
		$cat = intval( $tx_ttnews['cat']);

		// if the current category is the same one we are asking, we are active!
		if ( $cat == $uid){
			return true;
		}

		// otherwise, we need to check all of the sub-categories
		$cats = $this->GetSubCategoriesRecursively($uid);

		if ( in_array($cat, $cats)){
			return true;
		}

		return false;
	}

	/**
	 * this function recursively returns the subcategories of the passed parameter.
	 * the parent cat will NOT be in the array.
	 *
	 * Oddly enough, there are no existing TYPO3 or tt_news functions that
	 * performs this for tt_news categories, even there are for pages and other
	 * tables.
	 *
	 * @param	int		$parentuid: The parent category number
	 * @param	int		$level: the recursion depth, for debugging.
	 * @return	array		a single dimension array of all uids of sub categories
	 */
	function GetSubCategoriesRecursively($parentuid, $level = 1){
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_news_cat', 'parent_category='.$parentuid .' ' . $this->enableCatFields, '', 'parent_category, uid');

		$uids = array();
		while ( $res &&
			$GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0 &&
			(list($uid) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res) ) &&
			$uid > 0 ){
				$uids[] = $uid;
				$uids = array_merge($uids, $this->GetSubCategoriesRecursively($uid, $level+1));
		}
		return $uids;
	}


	/**
	 * this function takes the current category, and builds up an array
	 * of all parent categories until the top category is found.
	 *
	 *                  from the top category down to the current category.
	 *
	 * @param	int		$currentuid: The bottom category to start with
	 * @param	int		$topuid: the recursion depth, for debugging.
	 * @return	array		a single dimension array of all uids of categories,
	 */
	function GetBreadCrumbs($currentUid, $topUid){

		// If we are already the top category, no need to search the database!
		if ( $currentUid == $topUid){
			return array($currentUid);
		}

		$breadCrumbs = array();

		// The first element to add to the array is the current category.
		array_push($breadCrumbs, $currentUid);

		// If the current category is not the top category, it means that
		// we have at least one parent. It is a big assumptions, but at worst,
		// if by mistake we have a top level category, we will simply not
		// find the current category and break.
		do{

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('parent_category', 'tt_news_cat', 'uid='.$currentUid .' ' . $this->enableCatFields);

			// Yes, we simulate a sort of recursion by loading the parent
			// category id directly in currentUid. It may feel odd to overwrite
			// the function parameter, but it's like using recursion but without
			// the waste of multiple function calls.
			if ( $res && (list($currentUid) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res) ) ){
				// we unshift to place the parent ahead of the last entry.
				array_unshift($breadCrumbs, $currentUid);
			}
			else{
				break;
			}
		}while ( $currentUid != 0 && $currentUid != $topUid );

		return $breadCrumbs;
	}

	/**
	 * this function is a itemProcArray processor function which adds categories to
	 * to the current menu item using the $I['parts']['after'] option.
	 *
	 * What this allows, is another way to loas the virtual tt_news menu,
	 * by allowing to add virtual pages from categories to a classic non-virtual
	 * tt_news TYPO3 menu.
	 *
	 * This allows to mix classic menus with virtual tt_news!
	 *
	 * To do the opposite (add pages under a virtual tt_news menu), you simply
	 * need to create categories which are shortcuts.
	 *
	 * This function isn't meant to be called directly, but rather, to be
	 * called by TYPO3 using the itemProcArray typoscript directive.
	 *
	 * @param	array		$I: the original $I array from itemProcArray
	 * @param	array		$conf: the $conf array from itemProcArray
	 * @return	array		$I: the modified $I array
	 */
	function AddSubCategoriesToCurrentPage($I,$conf){

		$this->initialize($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_cablanvirtualttnews_pi1.']);

		// The field "virtualnewsmountpoint" for a category is the field
		// used to determine where the virtual menu will be inserted.
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_news_cat', 'tx_cablanvirtualttnews_virtualnewsmountpoint='.$I['uid'] .' ' . $this->enableCatFields . $this->catlistWhere, '', 'parent_category, uid');
		if ( $res &&
			$GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0 &&
			(list($uid) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res) ) &&
			$uid > 0 ){

			// We still call BuildMenu...
			$cat_content = $this->BuildMenu($uid,$this->menuPid );

			// but we insert the generated menu into the $I['parts']['arfter']
			// which it typically reserved by TYPO3 for the subpages section
			// of a menu.
			$I["parts"]["after"] = $cat_content;
		}
		return $I;
	}

	/**
	 * this function returns the title of the category.
	 *
	 * What's complicated, is that the category title of tt_news_cat records
	 * is stored in an language overlay field, so if the current language isn't
	 * the default language(0), we need to check the overlay!
	 *
	 * @param	array		$data: the match
	 * @return	string		the replacement
	 */
	function GetCatTitle($cat_row){
		$title = $cat_row['title'];

		if ($GLOBALS['TSFE']->sys_language_content > 0) {
			// find translations of category titles, which are in a |
			// seperated list.
			$catTitleArr = t3lib_div::trimExplode('|', $cat_row['title_lang_ol']);

			// Often, a language overlay is not specified by using || or | |
			// to kip it. As such, we need to check if the actual length of
			// of the overlay is longer than 1. Also, the first overlay (0),
			// is actually for language #1, because language #0, is not in
			// the overlay! Hence the sys_language_content -1.
			if ( isset($catTitleArr[($GLOBALS['TSFE']->sys_language_content - 1)]) &&
				strlen($catTitleArr[($GLOBALS['TSFE']->sys_language_content - 1)])> 1){
				$title = $catTitleArr[($GLOBALS['TSFE']->sys_language_content - 1)];
			}
		}
		return $title;

	}

	/**
	 * this function is a static callback used to replace user typed
	 * category links. It is a preg_replace_callback
	 * The named regex field "text" will be the text of the link, and
	 * the regex field "cat" is the number of the category.
	 *
	 * In short, this allows to replace category links in news articles,
	 * with actual links to categories!
	 *
	 * See the extraItemMarkerProcessor function for more information.
	 *
	 * @param	array		$data: the match
	 * @return	string		the replacement
	 */
	static function pregCallBack($data){

		// Since this function is called statically, we have saved our
		// $this pointer...
		$savedthis = $GLOBALS['tx_cablanvirtualttnews_pi1']['saved']['this'];

		$cat_row = $savedthis->getRecord('tt_news_cat', intval($data['cat']));

		// This hook allows to use custom Regex strings to process the
		// category name. The $data in this cased is usally passed by
		// reference.
		//
		// Of course, regex is not required...
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['virtual_tt_news']['linkRegexHook'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['virtual_tt_news']['linkRegexHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->LinkRegexProcessor($data, $cat_row, $savedthis);
			}
		}

		// If we did find the category, link it and either use the text
		// (if found), or the category title (if not specified)
		if ($cat_row['uid']  > 0 ){

			if ( strlen($data['text']) > 0 ){
				$text = $data['text'];
			}
			else{
				$text = $this->GetCatTitle($cat_row);
			}

			$page = $savedthis->menuPid;
			$content = $savedthis->GetCatLink($cat_row, $page, $text);
		}
		else{
			// if the cat is not found, we just return the text. This will
			// ignore the link request.
			$content = $data['text'];
		}

		return $content;
	}


	/**
	 * this function is a standard tt_news extraItemMarkerProcessor hook
	 * callback, which allows to modify the markerArray used to fill in
	 * the tt_news template.
	 *
	 * In our case, we use regex to search for [[[CAT-##]]] tags in the content
	 * or subheader of news articles.
	 *
	 * We also allow to process other tags via the linkRegExp Typoscript
	 * configuration field.
	 *
	 * @param	array		$data: the match
	 * @param	[type]		$row: ...
	 * @param	[type]		$lConf: ...
	 * @param	[type]		$ttnewsObj: ...
	 * @return	string		the replacement
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, $ttnewsObj)    {

	 	// The default RegExp will turn this:
	 	//    this is before the link [[[CAT-12|This is the text]]],this is after the text
	 	// into:
	 	//    this is before the link <a href="index.php?id=PAGE&tx_news[cat]=12">This is the text</a>, this is after the text
	 	// with PAGE being the virtual tt_news page.

	 	$preg = '/'; // start slash
	 	$preg .= '\[\[\['; // find the [[[ literal
	 	$preg .= 'CAT\-'; // find the CAT-
	 	$preg .= '(?P<cat>[0-9]+)'; // find the cat number
	 	$preg .= '\|'; // find the |
	 	$preg .= '(?P<text>.*?)'; // the text
	 	$preg .= '\]\]\]'; // find the ]]] literal
	 	$preg .= '/'; // end slash


	 	// preg_replace_callback functions are called statically, so we need
	 	// to save our this pointer.
		$GLOBALS['tx_cablanvirtualttnews_pi1']['saved'] = array(
			'this' => $this,
			);

		$markerArray['###NEWS_CONTENT###'] = preg_replace_callback( $preg, 'tx_cablanvirtualttnews_pi1::pregCallBack', $markerArray['###NEWS_CONTENT###']);
		$markerArray['###NEWS_SUBHEADER###'] = preg_replace_callback( $preg, 'tx_cablanvirtualttnews_pi1::pregCallBack', $markerArray['###NEWS_SUBHEADER###']);

		// If we have a custom regular expression, we call it second.
		if ( strlen($this->config['linkRegExp']) > 0 ){
			$markerArray['###NEWS_CONTENT###'] = preg_replace_callback( $this->config['linkRegExp'], 'tx_cablanvirtualttnews_pi1::pregCallBack', $markerArray['###NEWS_CONTENT###']);
			$markerArray['###NEWS_SUBHEADER###'] = preg_replace_callback( $this->config['linkRegExp'], 'tx_cablanvirtualttnews_pi1::pregCallBack', $markerArray['###NEWS_SUBHEADER###']);
		}
	 	return $markerArray;
	 }


	/**
	 * this function allows to set register "virtual_tt_news_title" to the
	 * current page, thus allowing to use typoscript to set the <title>
	 * tag in the header to the current category.
	 *
	 * @return	[type]		...
	 */
 	function SetPageTitleRegister(){
		// the current category id is read from the tx_ttnews parameter,
		// which usually filled by realUrl, so that the category
		// name is translated by realUrl into the category ID
		// automatically.
		$tx_ttnews =t3lib_div::_GET('tx_ttnews');
		$cat = intval( $tx_ttnews['cat']);

		$cat_row = $this->getRecord('tt_news_cat', intval($cat));

		$title = $this->GetCatTitle($cat_row);

		$this->cObj->LOAD_REGISTER(array('virtual_tt_news_title' =>  $title ));

	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cablan_virtual_tt_news/pi1/class.tx_cablanvirtualttnews_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cablan_virtual_tt_news/pi1/class.tx_cablanvirtualttnews_pi1.php']);
}

?>