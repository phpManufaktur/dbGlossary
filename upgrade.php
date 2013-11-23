<?php

/**
 * dbGlossary
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2009 - 2013
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include (WB_PATH . '/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (! file_exists($root . '/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root . '/framework/class.secure.php')) {
        include ($root . '/framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.glossary.php');
require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.droplets.php');

global $admin;
global $database;

if (!defined('DEBUG')) define('DEBUG', true);

if (DEBUG) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}
else {
	ini_set('display_errors', 0);
	error_reporting(E_ERROR);
}

$error = '';

$dbGlossaryLiterature = new dbGlossaryLiterature();
if (!$dbGlossaryLiterature->sqlFieldExists(dbGlossaryLiterature::field_group)) {
	if (!$dbGlossaryLiterature->sqlAlterTableAddField(dbGlossaryLiterature::field_group, "VARCHAR(80) NOT NULL DEFAULT '".dbGlossaryLiterature::group_default."'", dbGlossaryLiterature::field_type)) {
		$error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
	}
}
if ($dbGlossaryLiterature->sqlFieldExists('lit_lastname')) {
	// change fields lit_firstname and lit_lastname to lit_author - first save old fields
	$old_data = array();
	$sql = "SELECT * FROM ".TABLE_PREFIX."mod_glossary_literature";
	$result = $database->query($sql);
	if (!$result) {
		$error .= sprintf('<p>[UPGRADE] %s</p>', $database->get_error());
	}
	else {
		while (false !== ($data = $result->fetchRow())) {
			$old_data[] = $data;
		}
		if (!$dbGlossaryLiterature->sqlAlterTableAddField(dbGlossaryLiterature::field_author, "VARCHAR(255) NOT NULL DEFAULT ''", dbGlossaryLiterature::field_identifer)) {
			$error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
		}
		else {
			// transport old datas to new field lit_author
			foreach ($old_data as $old_field) {
				$where = array();
				$where[dbGlossaryLiterature::field_id] = $old_field[dbGlossaryLiterature::field_id];
				$data = array();
				$data[dbGlossaryLiterature::field_author] = sprintf('%s, %s', $old_field['lit_lastname'], $old_field['lit_firstname']);
				$data[dbGlossaryLiterature::field_update_when] = date('Y-m-d H:i:s');
				$data[dbGlossaryLiterature::field_update_by] = 'SYSTEM';
				if (!$dbGlossaryLiterature->sqlUpdateRecord($data, $where)) {
					$error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
				}
			}
			if (!$dbGlossaryLiterature->sqlAlterTableDropField('lit_firstname') ||
					!$dbGlossaryLiterature->sqlAlterTableDropField('lit_lastname') ||
					!$dbGlossaryLiterature->sqlAlterTableDropField('lit_monographie')) {
				$error .= sprintf('<p>{UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
			}
		}
	}
}

// 0.25 fields ISBN and URL added
if (!$dbGlossaryLiterature->sqlFieldExists(dbGlossaryLiterature::field_isbn)) {
	if (!$dbGlossaryLiterature->sqlAlterTableAddField(dbGlossaryLiterature::field_isbn, "VARCHAR(80) NOT NULL DEFAULT ''", dbGlossaryLiterature::field_published_year)) {
		$error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
	}
}
if (!$dbGlossaryLiterature->sqlFieldExists(dbGlossaryLiterature::field_url)) {
	if (!$dbGlossaryLiterature->sqlAlterTableAddField(dbGlossaryLiterature::field_url, "VARCHAR(255) NOT NULL DEFAULT ''", dbGlossaryLiterature::field_isbn)) {
		$error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
	}
}

// 0.29 added sort field
$dbGlossary = new dbGlossary();
if (!$dbGlossary->sqlFieldExists(dbGlossary::field_sort)) {
	if (!$dbGlossary->sqlAlterTableAddField(dbGlossary::field_sort, "VARCHAR(80) NOT NULL DEFAULT ''", dbGlossary::field_item)) {
		$error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossary->getError());
	}
	else {
		// walk through database and update sort fields
		$SQL = sprintf(	"SELECT * FROM %s",
  									$dbGlossary->getTableName());
		$glossar = array();
		if (!$dbGlossary->sqlExec($SQL, $glossar)) {
			$error .= sprintf('[UPGRADE] %s', $dbGlossary->getError());
		}
		foreach ($glossar as $item) {
			$item[dbGlossary::field_sort] = str_replace($dbGlossary->sort_search, $dbGlossary->sort_replace, $item[dbGlossary::field_item]);
			$where = array(dbGlossary::field_id => $item[dbGlossary::field_id]);
			if (!$dbGlossary->sqlUpdateRecord($item, $where)) {
				$error .= sprintf('[UPGRADE] %s', $dbGlossary->getError());
			}
		}
	}
}

// check configuration
$cfg = new dbGlossaryCfg();
$typeSource = $cfg->getValue(dbGlossaryCfg::cfgTypeSource);
if (strpos($typeSource, '{lastname}') !== false) {
	// need to update configuration
	$cfg->setValueByName('<b>{author}</b>: <i>{title}</i>{subtitle}, {pub_place}, {edition}{pub_year}', dbGlossaryCfg::cfgTypeSource);
}

// 0.31 changed field lengths
if (!$dbGlossaryLiterature->sqlAlterTableChangeField(dbGlossaryLiterature::field_group, dbGlossaryLiterature::field_group, "VARCHAR(255) NOT NULL DEFAULT ''")) {
    $error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
}
if (!$dbGlossaryLiterature->sqlAlterTableChangeField(dbGlossaryLiterature::field_title, dbGlossaryLiterature::field_title, "VARCHAR(255) NOT NULL DEFAULT ''")) {
    $error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
}
if (!$dbGlossaryLiterature->sqlAlterTableChangeField(dbGlossaryLiterature::field_subtitle, dbGlossaryLiterature::field_subtitle, "VARCHAR(255) NOT NULL DEFAULT ''")) {
    $error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
}
if (!$dbGlossaryLiterature->sqlAlterTableChangeField(dbGlossaryLiterature::field_published_place, dbGlossaryLiterature::field_published_place, "VARCHAR(255) NOT NULL DEFAULT ''")) {
    $error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
}
if (!$dbGlossaryLiterature->sqlAlterTableChangeField(dbGlossaryLiterature::field_edition, dbGlossaryLiterature::field_edition, "VARCHAR(255) NOT NULL DEFAULT ''")) {
    $error .= sprintf('<p>[UPGRADE] %s</p>', $dbGlossaryLiterature->getError());
}


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

?>