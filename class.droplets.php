<?php

/**
 * dbGlossary
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2009 - 2012
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License (GPL)
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

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php');

class dbDroplets extends dbConnectLE {

	const field_id							= 'id';
	const field_name						= 'name';
	const field_code						= 'code';
	const field_description			= 'description';
	const field_modified_when		= 'modified_when';
	const field_modified_by			= 'modified_by';
	const field_active					= 'active';
	const field_comments				= 'comments';

	public function __construct() {
		parent::__construct();
		$this->setTableName('mod_droplets');
		$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
		$this->addFieldDefinition(self::field_name, "VARCHAR(32) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_code, "TEXT NOT NULL DEFAULT ''", false, false, true);
		$this->addFieldDefinition(self::field_description, "TEXT NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_modified_when, "INT(11) NOT NULL DEFAULT '0'");
		$this->addFieldDefinition(self::field_modified_by, "INT(11) NOT NULL DEFAULT '0'");
		$this->addFieldDefinition(self::field_active, "INT(11) NOT NULL DEFAULT '0'");
		$this->addFieldDefinition(self::field_comments, "TEXT NOT NULL DEFAULT ''");
		$this->checkFieldDefinitions();
	} // __construct()

} // class dbDroplets


class checkDroplets {

	var $droplet_path	= '';
	var $error = '';

	public function __construct() {
		$this->droplet_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/droplets/' ;
	} // __construct()

	/**
    * Set $this->error to $error
    *
    * @param STR $error
    */
  public function setError($error) {
    $this->error = $error;
  } // setError()

  /**
    * Get Error from $this->error;
    *
    * @return STR $this->error
    */
  public function getError() {
    return $this->error;
  } // getError()

  /**
    * Check if $this->error is empty
    *
    * @return BOOL
    */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

	public function insertDropletsIntoTable() {
		global $admin;
		// Read droplets from directory
		$folder = opendir($this->droplet_path.'.');
		$names = array();
		while (false !== ($file = readdir($folder))) {
			if (basename(strtolower($file)) != 'index.php') {
				$ext = strtolower(substr($file,-4));
				if ($ext	==	".php") {
					$names[count($names)] = $file;
				}
			}
		}
		closedir($folder);
		// init droplets
		$dbDroplets = new dbDroplets();
		if (!$dbDroplets->sqlTableExists()) {
			// Droplets not installed!
			return false;
		}
		// walk through array
		foreach ($names as $dropfile) {
			//$droplet = addslashes($this->getDropletCodeFromFile($dropfile));
			$droplet = $this->getDropletCodeFromFile($dropfile);
			if ($droplet != "") {
				// get droplet name
				$name = substr($dropfile,0,-4);
				$where = array();
				$where[dbDroplets::field_name] = $name;
				$result = array();
				if (!$dbDroplets->sqlSelectRecord($where, $result)) {
					// error exec query
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbDroplets->getError()));
					return false;
				}
				if (sizeof($result) < 1) {
					// insert this droplet into table
					$description = "Example Droplet";
					$comments = "Example Droplet";
					$cArray = explode("\n",$droplet);
					if (substr($cArray[0],0,3) == "//:") {
						// extract description
						$description = trim(substr($cArray[0],3));
						array_shift($cArray);
					}
					if (substr($cArray[0],0,3) == "//:") {
						// extract comment
						$comments = trim(substr($cArray[0],3));
						array_shift($cArray);
					}
					$data = array();
					$data[dbDroplets::field_name] = $name;
					$code = implode("\r\n", $cArray);
					$data[dbDroplets::field_code] = $code;
					$data[dbDroplets::field_description] = $description;
					$data[dbDroplets::field_comments] = $comments;
					$data[dbDroplets::field_active] = 1;
					$data[dbDroplets::field_modified_by] = $admin->get_user_id();
					$data[dbDroplets::field_modified_when] = time();
					if (!$dbDroplets->sqlInsertRecord($data)) {
						// error exec query
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbDroplets->getError()));
						return false;
					}
				}
			}
		}
		return true;
	} // insertDropletsIntoTable()

	public function getDropletCodeFromFile($dropletfile) {
		$data = "";
		$filename = $this->droplet_path.$dropletfile;
		if (file_exists($filename)) {
			$filehandle = fopen ($filename, "r");
			$data = fread ($filehandle, filesize ($filename));
			fclose($filehandle);
		}
		return $data;
	} // getDropletCodeFromFile()

} // checkDroplets

/**
 * Compatibility function for show_glossary_list()
 * Please use ONLY direct calls to show_glossary_list()
 *
 * @param BOOL $link_intern
 * @param BOOL $link_extern
 * @param ARRAY $groups
 * @return STR
 */

function show_list($link_intern=true, $link_extern=true, $groups=array()) {
	return show_glossary_list($link_intern, $link_extern, $groups);
} // show_list

/**
 * DROPLET show_glossary_list
 *
 * @param BOOL $link_intern
 * @param BOOL $link_extern
 * @param ARRAY $groups
 * @return STR
 */

function show_glossary_list($link_intern=true, $link_extern=true, $groups=array()) {
	$dbGlossary = new dbGlossary();
	$dbConfig = new dbGlossaryCfg();
	$az_tabs = $dbConfig->getValue(dbGlossaryCfg::cfgAZTabs);
	$az_tabs = explode('|', $az_tabs);
	$az_tabs_use = $dbConfig->getValue(dbGlossaryCfg::cfgAZTabsUse);
	$az_tabs_item = $dbConfig->getValue(dbGlossaryCfg::cfgAZTabsItem);
	$az_tabs_start_empty = $dbConfig->getValue(dbGlossaryCfg::cfgAZTabsStartEmpty);
	if (isset($_REQUEST['tab'])) {
		$active_tab = urldecode($_REQUEST['tab']);
	}
	else {
		($az_tabs_start_empty) ? $active_tab = -1 :$active_tab = $az_tabs[0];
	}
	$tools = new rhTools();
	$page_link = '#';
	$tools->getPageLinkByPageID(PAGE_ID, $page_link);
	$az_tabs_result = '';

	$result = '';
	$and = '';
	if (is_array($groups) && (!empty($groups))) {
		// es sind Gruppen angegeben, die verwendet werden sollen
		$and = ' AND (';
		$start = true;
		foreach ($groups as $group) {
			if (!$start) $and .= ' OR ';
			if ($start) $start = false;
			$and .= sprintf("%s='%s'", dbGlossary::field_group, $group);
		}
		$and .= ')';
	}
	if ($az_tabs_use) {
		// A-Z Ausgabe
		$and .= ' AND (';
		if ($active_tab == '0-9') {
			// Sonderfall, Zeichen 0 bis 9...
			$and .= sprintf('%s LIKE \'0%%\' OR %1$s LIKE \'1%%\' OR %1$s LIKE \'2%%\' OR %1$s LIKE \'3%%\' OR %1$s LIKE \'4%%\' OR %1$s LIKE \'5%%\' OR %1$s LIKE \'6%%\' OR %1$s LIKE \'7%%\' OR %1$s LIKE \'8%%\' OR %1$s LIKE \'9%%\'',
											dbGlossary::field_sort);
		}
		else {
			$start = true;
			$items = explode(',', $active_tab);
			foreach ($items as $item) {
				$item = trim($item);
				if (!$start) $and .= ' OR ';
				if ($start) $start = false;
				$and .= sprintf("%s LIKE '%s%%'", dbGlossary::field_sort, $item);
			}
		}
		$and .= ')';
	}
	$SQL = sprintf(	"SELECT * FROM %s WHERE %s='%s'%s ORDER BY %s ASC",
									$dbGlossary->getTableName(),
									dbGlossary::field_status,
									dbGlossary::status_active,
									$and,
									dbGlossary::field_sort);
	$stichworte = array();
	if (!$dbGlossary->sqlExec($SQL, $stichworte)) {
		$result = sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError());
		return $result;
	}
	// Icons fuer interne und externe Links
	$icon_extern = $dbConfig->getValue(dbGlossaryCfg::cfgIconLinkExtern);
	if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/img/'.$icon_extern)) {
		$img_extern = sprintf('<img src="%s" alt="%s" title="%s" />',
													WB_URL.'/modules/'.basename(dirname(__FILE__)).'/img/'.$icon_extern,
													gl_text_link_extern, gl_text_link_extern);
	}
	else {
		$img_extern = sprintf('<img src="%s" alt="%s" title="%s" />',
													WB_URL.MEDIA_DIRECTORY.'/'.$icon_extern,
													gl_text_link_extern, gl_text_link_extern);
	}
	$icon_intern = $dbConfig->getValue(dbGlossaryCfg::cfgIconLinkIntern);
	if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/img/'.$icon_intern)) {
		$img_intern = sprintf('<img src="%s" alt="%s" title="%s" />',
													WB_URL.'/modules/'.basename(dirname(__FILE__)).'/img/'.$icon_intern,
													gl_text_link_intern, gl_text_link_intern);
	}
	else {
		$img_intern = sprintf('<img src="%s" alt="%s" title="%s" />',
													WB_URL.MEDIA_DIRECTORY.'/'.$icon_intern,
													gl_text_link_intern, gl_text_link_intern);
	}
	if ($az_tabs_use) {
		foreach ($az_tabs as $tab) {
			if ($tab == $active_tab) {
				// aktiver Tab
				$az_tabs_result .= str_replace('{tab}', sprintf('<span class="gl_tab_active">%s</span>', $tab), $az_tabs_item);
			}
			else {
				// inaktiver Tab, Link einfuegen
				$az_tabs_result .= str_replace('{tab}', sprintf('<a href="%s?tab=%s">%s</a>', $page_link, urlencode($tab), $tab), $az_tabs_item);
			}
		}
		$az_tabs_result = sprintf('<div class="gl_tab_bar">%s</div>', $az_tabs_result);
		$result .= $az_tabs_result;
	}
	foreach ($stichworte as $stichwort) {
		$skip = false;
		switch ($stichwort[dbGlossary::field_type]):
		case dbGlossary::type_db_glossary:
			$where = array();
			$where[dbGlossary::field_item] = $stichwort[dbGlossary::field_explain];
			$linked = array();
			if (!$dbGlossary->sqlSelectRecord($where, $linked)) {
				$result = sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError());
				return $result;
			}
			if (sizeof($linked) < 1) {
				$result = sprintf(gl_error_list_link_invalid, $stichwort[dbGlossary::field_explain], $stichwort[dbGlossary::field_item]);
				return $result;
			}
			$linked = $linked[0];
			if ($link_intern) {
				// es handelt sich um einen Querverweis
				if ($linked[dbGlossary::field_type] == dbGlossary::type_link) {
					// der Querverweis ist ein externer Link
					if ($link_extern == false) {
						// keine externen Links anzeigen
						$skip = true;
					}
					else {
						// als externen Link anzeigen
						$explain = sprintf(	'%s&nbsp;<a href="%s" target="%s" title="%s">%s</a>',
																$img_extern,
																$linked[dbGlossary::field_link],
																$dbGlossary->target_array[$linked[dbGlossary::field_target]],
																$linked[dbGlossary::field_explain],
																$linked[dbGlossary::field_item]);
					}
				}
				elseif (!$az_tabs_use) {
					// es werden keine TABs verwendet, Verweis kann direkt angesprungen werden
					$explain = sprintf('%s&nbsp;<a href="#%s">%s</a>', $img_intern, $linked[dbGlossary::field_id], $linked[dbGlossary::field_item]);
				}
				else {
					// Querverweis befindet sich moeglicherweise in einem anderem TAB - Link entsprechend aufbauen...
					$explain = sprintf(	'%s&nbsp;<a href="%s?tab=%s#%s">%s</a>',
															$img_intern,
															$page_link,
															strtoupper($linked[dbGlossary::field_item][0]), // ersten Buchstaben des Verweistext als TAB-Ziel verwenden
															$linked[dbGlossary::field_id],
															$linked[dbGlossary::field_item]);
				}
			}
			else {
				$skip = true;
			}
			break;
		case dbGlossary::type_link:
			if ($link_extern) {
				$explain = sprintf(	'%s&nbsp;<a href="%s" target="%s" title="%s">%s</a>',
														$img_extern,
														$stichwort[dbGlossary::field_link],
														$dbGlossary->target_array[$stichwort[dbGlossary::field_target]],
														$stichwort[dbGlossary::field_explain],
														$stichwort[dbGlossary::field_explain]);
			}
			else {
				$skip = true;
			}
			break;
		default:
			$explain = $stichwort[dbGlossary::field_explain];
			break;
		endswitch;
		if (!$skip) {
			$result .= sprintf(	'<dt><a name="%s"></a>%s</dt>'."\r\n".'<dd>%s</dd>'."\r\n",
													$stichwort[dbGlossary::field_id],
													$stichwort[dbGlossary::field_item],
													$explain);
		}
	}
	if ($az_tabs_use && count($stichworte) < 1) {
		$result .= sprintf(gl_msg_list_tab_empty, $active_tab);
	}
	elseif (count($stichworte) < 1) {
		$result .= gl_msg_list_empty;
	}
	elseif ($az_tabs_use && count($stichworte) > 20) {
		// bei mehr als 20 Eintraegen TAB Leister auch unterhalb einfuegen
		$result .= $az_tabs_result;
	}
	$result = sprintf('<dl class="glossary_list">%s</dl>', $result);
	return $result;
}

function show_literature_list($groups=array()) {
	$dbLiterature = new dbGlossaryLiterature();
	$dbConfig = new dbGlossaryCfg();
	$az_tabs = $dbConfig->getValue(dbGlossaryCfg::cfgAZTabs);
	$az_tabs = explode('|', $az_tabs);
	$az_tabs_use = $dbConfig->getValue(dbGlossaryCfg::cfgAZTabsUse);
	$az_tabs_item = $dbConfig->getValue(dbGlossaryCfg::cfgAZTabsItem);
	$az_tabs_start_empty = $dbConfig->getValue(dbGlossaryCfg::cfgAZTabsStartEmpty);
	if (isset($_REQUEST['tab'])) {
		$active_tab = urldecode($_REQUEST['tab']);
	}
	else {
		($az_tabs_start_empty) ? $active_tab = -1 :$active_tab = $az_tabs[0];
	}
	$tools = new rhTools();
	$page_link = '#';
	$tools->getPageLinkByPageID(PAGE_ID, $page_link);
	$az_tabs_result = '';

	$result = '';
	$and = '';
	if (is_array($groups) && (!empty($groups))) {
		// es sind Gruppen angegeben, die verwendet werden sollen
		$and = ' AND (';
		$start = true;
		foreach ($groups as $group) {
			if (!$start) $and .= ' OR ';
			if ($start) $start = false;
			$and .= sprintf("%s='%s'", dbGlossaryLiterature::field_group, $group);
		}
		$and .= ')';
	}
	if ($az_tabs_use) {
		// A-Z Ausgabe
		$and .= ' AND (';
		$start = true;
		$items = explode(',', $active_tab);
		foreach ($items as $item) {
			$item = trim($item);
			if (!$start) $and .= ' OR ';
			if ($start) $start = false;
			$and .= sprintf("%s LIKE '%s%%'", dbGlossaryLiterature::field_author, $item);
		}
		$and .= ')';
	}
	$SQL = sprintf(	"SELECT * FROM %s WHERE %s='%s'%s ORDER BY %s ASC",
									$dbLiterature->getTableName(),
									dbGlossaryLiterature::field_status,
									dbGlossaryLiterature::status_active,
									$and,
									dbGlossaryLiterature::field_author);
	$literature = array();
	if (!$dbLiterature->sqlExec($SQL, $literature)) {
		$result = sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbLiterature->getError());
		return $result;
	}
	if ($az_tabs_use) {
		foreach ($az_tabs as $tab) {
			if ($tab == $active_tab) {
				// aktiver Tab
				$az_tabs_result .= str_replace('{tab}', $tab, $az_tabs_item);
			}
			else {
				// inaktiver Tab, Link einfuegen
				$az_tabs_result .= str_replace('{tab}', sprintf('<a href="%s?tab=%s">%s</a>', $page_link, urlencode($tab), $tab), $az_tabs_item);
			}
		}
		$az_tabs_result = sprintf('<div class="gl_tab_bar">%s</div>', $az_tabs_result);
		//$result .= $az_tabs_result;
	}
	// Ausgabe der Liste nach Autorenname
	foreach ($literature as $item) {
		// Autorenname
		$author = $item[dbGlossaryLiterature::field_author];
		$publication = sprintf('<span class="literature_title">%s</span>', $item[dbGlossaryLiterature::field_title]);
		if (!empty($item[dbGlossaryLiterature::field_subtitle]))
			$publication .= sprintf('<span class="literature_subtitle">%s</span>', $item[dbGlossaryLiterature::field_subtitle]);
		$desc = '';
		if (!empty($item[dbGlossaryLiterature::field_published_place]))
			$desc = $item[dbGlossaryLiterature::field_published_place];
		if (!empty($item[dbGlossaryLiterature::field_edition]))
			$desc .= ', '.$item[dbGlossaryLiterature::field_edition];
		if (!empty($item[dbGlossaryLiterature::field_published_year]))
			$desc .= ', '.$item[dbGlossaryLiterature::field_published_year];
	  if (!empty($item[dbGlossaryLiterature::field_isbn]))
	    $desc .= '<br />'.$item[dbGlossaryLiterature::field_isbn];
		$publication .= sprintf('<span class="literature_description">%s</span>', $desc);
		$result .= sprintf(	'<dt>%s</dt>'."\r\n".'<dd>%s</dd>'."\r\n", $author, $publication);
	}

	$result = sprintf('%s'."\r\n".'<dl class="literature_list">%s</dl>', $az_tabs_result, $result);


	if ($az_tabs_use && count($literature) > 20) {
		// bei mehr als 20 Eintraegen TAB Leiste auch unterhalb einfuegen
		$result .= "\r\n".$az_tabs_result;
	}

	return $result;
} // show_literature_list()

?>