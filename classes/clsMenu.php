<?php

/*
 * I created this file to make it easier to change the menu options
 * rather than having to have them static on each page.
 * $arrHomeMenu is used by all public pages,
 * $arrAdminMenu is used by all Admin pages.
*/

function BuildMenu($_WhichMenu="Home",$_ListView='public',$_SubMenu=FALSE) {

	//Home Menu Array
	$arrHomeMenu['Home'] = array('href' => '/index.php', 'title' => 'Go to Home Page', 'altHref' => '', 'view' => 'public');
//	$arrHomeMenu['Blog'] = array('href' => '/blog/index.php', 'title' => 'Read the latest on the Neilsen\'s Blog', 'altHref' => '', 'view' => 'public');
//	$arrHomeMenu['Photos &amp; Videos'] = array('href' => '/gallery.php', 'title' => 'Our Photo &amp; Video Gallery', 'altHref' => '', 'view' => 'public');
//	$arrHomeMenu['Design Portfolio'] = array('href' => '/portfolio.php', 'title' => 'My Graphic Design Portfolio', 'altHref' => '', 'view' => 'public');
	$arrHomeMenu['Talk To Us'] = array('href' => '/contact.php', 'title' => 'Contact us if you dare...', 'altHref' => '', 'view' => 'public');
	
	//UVSC Home Menu Array
	$arrHomeMenu['Blog'] = array('href' => '/blog/index.php', 'title' => 'Read the latest on the Neilsen\'s Blog', 'altHref' => '', 'view' => 'uvsc');
	$arrHomeMenu['Talk To Me'] = array('href' => '/contact.php?t=engl', 'title' => 'Contact me, if you dare...', 'altHref' => '/contact.php', 'view' => 'uvsc');
	
	//Admin Menu Array
	$arrAdminMenu['Home'] = array('href' => '/admin/index.php', 'title' => 'View a list of all Administration Sections', 'altHref' => '/admin/', 'view' => 'public');
	$arrAdminMenu['Blog'] = array('href' => '/admin/blog.php', 'title' => 'Add, Edit, or Delete Blog Entries', 'altHref' => '/admin/', 'view' => 'public');
	$arrAdminMenu['Users'] = array('href' => '/admin/users.php', 'title' => 'Add, Edit, or Delete Users', 'altHref' => '/admin/', 'view' => 'public');
	$arrAdminMenu['Images'] = array('href' => '/admin/images.php', 'title' => 'Add, Rename, or Delete Images', 'altHref' => '/admin/', 'view' => 'public');
	$arrAdminMenu['Logout'] = array('href' => '/admin/logout.php', 'title' => 'Logout of Admininistration', 'altHref' => '', 'view' => 'public');

	/*** SUBMENU Indexes are found by using ucfirst() on the $_MenuName, so only capitalize the first letter ***/
	$arrAdminMenu['Blog']['SubMenu']['Add New Entry'] = array('href' => '/admin/blog.php?action=AddEntry', 'title' => 'Add a new Blog Entry');
	$arrAdminMenu['Blog']['SubMenu']['Blog Admin Home'] = array('href' => '/admin/blog.php', 'title' => 'Return to the Blog Admin Home');
	
	$arrAdminMenu['Images']['SubMenu']['Upload New Image'] = array('href' => '/admin/images.php?action=UploadImage', 'title' => 'Add a new Blog Entry');
	$arrAdminMenu['Images']['SubMenu']['Image Admin Home'] = array('href' => '/admin/images.php', 'title' => 'Return to the Blog Admin Home');

	$arrAdminMenu['Users']['SubMenu']['Add New User'] = array('href' => 'users.php?action=AddUser', 'title' => 'Add a new User');
	$arrAdminMenu['Users']['SubMenu']['User Admin Home'] = array('href' => 'users.php', 'title' => 'Return to the User Admin Home');
	
	//Momentum Menu Array
	$arrMomentumMenu['Routes'] = array('href' => '/momentum/', 'title' => 'View a list of all Gym Routes', 'altHref' => '/momentum/index.php', 'view' => 'momentum');
	$arrMomentumMenu['Setters'] = array('href' => '/momentum/setters/', 'title' => 'Add, Edit, or Delete Setters', 'altHref' => '/momentum/setters/index.php', 'view' => 'momentum');
	$arrMomentumMenu['Sections'] = array('href' => '/momentum/sections/', 'title' => 'Add, Edit, or Delete Gym Sections', 'altHref' => '/momentum/sections/index.php', 'view' => 'momentum');
	$arrMomentumMenu['Logout'] = array('href' => '/momentum/logout.php', 'title' => 'Logout of Momentum Routes Admininistration', 'altHref' => '', 'view' => 'momentum');

	/*** SUBMENU Indexes are found by using ucfirst() on the $_MenuName, so only capitalize the first letter ***/
	$arrMomentumMenu['Routes']['SubMenu']['Add New Route'] = array('href' => '/momentum/add.php', 'title' => 'Add a new gym route');
	$arrMomentumMenu['Setters']['SubMenu']['Add New Setter'] = array('href' => '/momentum/setters/add.php', 'title' => 'Add new setter');
	$arrMomentumMenu['Sections']['SubMenu']['Add New Section'] = array('href' => '/momentum/sections/add.php', 'title' => 'Add new gym section');
	
	$listHTML = "";
	//Concat the Menu name as a variable, and then use a Variable Variable ($$) in the foreach
	// to avoid having duplicated code with different array names
	$DisplayMenu = "arr" . $_WhichMenu . "Menu";
	$arrCurMenu = $$DisplayMenu;

	$i = 1;
	if ($_SubMenu == true && !strstr($_SERVER['SCRIPT_NAME'],'momentum')) {
/* 		$_MenuName = preg_replace(array('/\/(admin|momentum)\//','/\.php/'),array('',''),$_SERVER['SCRIPT_NAME']); */
		$_MenuName = basename($_SERVER['SCRIPT_NAME'],'.php');

		if ($_MenuName == 'index') {
			foreach ($arrCurMenu as $ButtonName => $LinkAttr) {
				if ($ButtonName != 'Home') $listHTML .= "\t\t\t<li><a href=\"" . $LinkAttr['href'] . "\" title=\"" . $LinkAttr['title'] ."\">" . $ButtonName . "</a></li>\n";
			}
		} elseif (isset($arrCurMenu[ucfirst($_MenuName)]['SubMenu'])) {
			foreach ($arrCurMenu[ucfirst($_MenuName)]['SubMenu'] as $ButtonName => $LinkAttr) {
				$listHTML .= "\t\t\t<li><a href=\"" . $LinkAttr['href'] . "\" title=\"" . $LinkAttr['title'] ."\">" . $ButtonName . "</a></li>\n";
			}
		}
	} else {
		$float = $_ListView != 'all' ? ' class="left"' : '';
		$curMenuOpt = '';
		foreach ($arrCurMenu as $ButtonName => $LinkAttr) {
			// Only return <li>'s of the Links since who knows the way I want them displayed.
			// the <ul class="..."> must be printed before this menu is called.
			if ($LinkAttr['view'] == $_ListView) {
				if ($_SERVER['SCRIPT_NAME'] == $LinkAttr['href'] || $_SERVER['SCRIPT_NAME'] == $LinkAttr['altHref'] || (strstr($_SERVER['SCRIPT_NAME'], 'momentum') && strstr($_SERVER['SCRIPT_NAME'], strtolower($ButtonName)))) {
					$listHTML .= "<li" . $float . "><a href=\"" . $LinkAttr['href'] . "\" title=\"" . $LinkAttr['title'] ."\" class=\"current\">" . $ButtonName . "</a></li>";
					$curMenuOpt = $ButtonName;
				} else {
					$listHTML .= "<li" . $float . "><a href=\"" . $LinkAttr['href'] . "\" title=\"" . $LinkAttr['title'] ."\">" . $ButtonName . "</a></li>";
				}
			}
			$i++;
		}

		if ($_SubMenu == true && $curMenuOpt != '' && strstr($_SERVER['SCRIPT_NAME'],'momentum')) {
			$listHTML .= '</ul>'."\n";
			$listHTML .= '<ul class="submenu clearfix">'."\n";
			foreach ($arrCurMenu[$curMenuOpt]['SubMenu'] as $ButtonName => $LinkAttr) {
				$listHTML .= "\t\t\t<li><a href=\"" . $LinkAttr['href'] . "\" title=\"" . $LinkAttr['title'] ."\">" . $ButtonName . "</a></li>\n";
			}
		}
		
	}

	return $listHTML;

}//end BuildMenu()

?>