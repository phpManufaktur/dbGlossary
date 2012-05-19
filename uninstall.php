<?php

/**
 * dbGlossary
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2009 - 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: uninstall.php 16 2011-07-19 16:04:28Z phpmanufaktur $
 * 
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */

// try to include LEPTON class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php'); 
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) { 
			include($dir.'/framework/class.secure.php'); $inc = true;	break; 
		} 
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

if (WB_VERSION < 2.8) {
	$message = 'Please install dbGlossary after upgrade to Website Baker 2.8 again.';
	echo '<script language="javascript">alert ("'.$message.'");</script>';
}
else {
	// only uninstall at WB 2.8...
	require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.glossary.php');
	
	global $admin;
	
	$error = '';
	
	$dbGlossary = new dbGlossary();
	if ($dbGlossary->sqlTableExists()) {
		if (!$dbGlossary->sqlDeleteTable()) {
			$error .= sprintf('<p>[Delete Table] %s</p>', $dbGlossary->getError());
		}
	}
	
	$dbGlossaryCfg = new dbGlossaryCfg();
	if ($dbGlossaryCfg->sqlTableExists()) {
		if (!$dbGlossaryCfg->sqlDeleteTable()) {
			$error .= sprintf('<p>[Delete Table] %s</p>', $dbGlossaryCfg->getError());
		}
	}
	
	$dbGlossaryLiterature = new dbGlossaryLiterature();
	if ($dbGlossaryLiterature->sqlTableExists()) {
		if (!$dbGlossaryLiterature->sqlDeleteTable()) {
			$error .= sprintf('<p>[Delete Table] %s</p>', $dbGlossaryLiterature->getError());
		}
	}
	
	$dbGlossaryFootnotes = new dbGlossaryFootnotes();
	if ($dbGlossaryFootnotes->sqlTableExists()) {
		if (!$dbGlossaryFootnotes->sqlDeleteTable()) {
			$error .= sprintf('<p>[Delete Table] %s</p>', $dbGlossaryFootnotes->getError());
		}
	}

	if (defined('LEPTON_VERSION')) {
		// unregister dbGlossary from LEPTON outputInterface
		if (!file_exists(WB_PATH .'/modules/output_interface/output_interface.php')) {
			$error .= '<p>Missing LEPTON outputInterface, can\'t unregister dbGlossary!</p>';
		}
		else {
			if (!function_exists('register_output_filter')) include_once(WB_PATH .'/modules/output_interface/output_interface.php');
			unregister_output_filter('dbglossary');
		}
	} // LEPTON
	else {
		// Try to unpatch output_filter of WebsiteBaker
		if (file_exists(WB_PATH .'/modules/output_filter/filter-routines.php')) {
			if (isPatched(WB_PATH .'/modules/output_filter/filter-routines.php')) {
				if (!unPatch()) {
					$message = "Uninstalling dbGlossary did not remove the changes made in the Output Filter module. Please reinstall the Ouput Filter module.";
				} 
				else {
					$message = "Uninstalling dbGlossary from the Output Filter module was succesfull.";
				}
				echo '<script language="javascript">alert ("'.$message.'");</script>';
			}
		}
	} // output_filter
	
	if (!empty($error)) {
		$admin->print_error($error);
	}
	
} // WB 2.8

?>