<?php

/**
 * dbGlossary
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2009 - 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: install.php 16 2011-07-19 16:04:28Z phpmanufaktur $
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
	$message = 'Sorry, but dbGlossary needs Website Baker 2.8, the installation was NOT successfull! Please UNINSTALL dbGlossary to prevent problems!';
	echo '<script language="javascript">alert ("'.$message.'");</script>';
}
else {

	require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.glossary.php');
	require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.droplets.php');
	
	global $admin;
  
	$error = '';
	// Install dbGlossary
	$dbGlossary = new dbGlossary(true);
	if ($dbGlossary->isError()) {
		$error .= sprintf('<p>[Installation] %s</p>', $dbGlossary->getError());
	}
	// Install dbGlossaryLiterature
	$dbGlossaryLiterature = new dbGlossaryLiterature(true);
	if ($dbGlossaryLiterature->isError()) {
		$error .= sprintf('<p>[Installation] %s</p>', $dbGlossaryLiterature->getError());
	}
	// Install dbGlossaryFootnotes
	$dbGlossaryFootnotes = new dbGlossaryFootnotes(true);
	if ($dbGlossaryFootnotes->isError()) {
		$error .= sprintf('<p>[Installation] %s</p>', $dbGlossaryFootnotes->getError());
	}
	// Install dbGlossaryCfg
	$dbGlossaryCfg = new dbGlossaryCfg(true);
	if ($dbGlossaryCfg->isError()) {
		$error .= sprintf('<p>[Installation] %s</p>', $dbGlossaryCfg->getError());
	}
	
	if (defined('LEPTON_VERSION')) {
		// register imageTweak at LEPTON outputInterface
		if (!file_exists(WB_PATH .'/modules/output_interface/output_interface.php')) {
			$error .= '<p>Missing LEPTON outputInterface, can\'t register dbGlossary - installation is not complete!</p>';
		}
		else {
			if (!function_exists('register_output_filter')) include_once(WB_PATH .'/modules/output_interface/output_interface.php');
			register_output_filter('dbglossary', 'dbGlossary');
		}
	} // LEPTON
	else {
		// Patch WebsiteBaker Output Filter
		$message = "";
		if (file_exists(WB_PATH .'/modules/output_filter/filter-routines.php')) {
		  if (!isPatched(WB_PATH .'/modules/output_filter/filter-routines.php')) {
				if (doPatch(WB_PATH .'/modules/output_filter/filter-routines.php')) {
						$message = "Frontend output filter succesfully patched for dbGlossary... dbGlossary are now ready to use.";
				} 
				else {
					$message = "Patching frontend output filter failed... Please click the HELP button in the dbGlossary admintool for instructions how to manual patch the output filter.";
				}
			} 
			else {
				$message = "The frontend output filter was already patched.";
			}
		} 
		else {
			$message = "Installation not completed. The frontend output filter does not exist (Wrong WB version?). dbGlossary cannot be used.";
		}
		if ($message != "") {
			echo '<script language="javascript">alert ("'.$message.'");</script>';
		}
	} // output_filter 
	
	// Install Droplets
	$droplets = new checkDroplets();
  if ($droplets->insertDropletsIntoTable()) {
  	$message = 'The Droplets for dbGlossary where successfully installed! Please look at the Help for further informations.';
  }
  else {
  	$message = 'The installation of the Droplets for dbGlossary failed. Error: '. $droplets->getError();
  }
  if ($message != "") {
		echo '<script language="javascript">alert ("'.$message.'");</script>';
	}
		
	if (!empty($error)) {
		$admin->print_error($error);
	}
	
} // WB 2.8

?>