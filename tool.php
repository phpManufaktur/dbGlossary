<?php

/**
 * dbGlossary
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2009 - 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: tool.php 17 2011-07-20 04:43:30Z phpmanufaktur $
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

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php');
require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.editor.php');

if (DEBUG) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}
else {
	ini_set('display_errors', 0);
	error_reporting(E_ERROR);
}

global $tools;
global $parser;

if (!is_object($tools)) $tools = new rhTools();
if (!is_object($parser)) $parser = new Dwoo(); 

$toolGloss = new toolGlossary();
$toolGloss->action();


class toolGlossary {
	
	const request_action 						= 'act';
	const request_csv_export				= 'csvex';
	const request_csv_import				= 'csvim';
	const request_items							= 'its';
	const request_abc								= 'abc';
	
	const action_default						= 'def';
	const action_catchword					= 'cat';
	const action_catchword_check		= 'catc';
	const action_help								= 'hlp';
	const action_glossary						= 'glos';
	const action_config							= 'cfg';
	const action_config_check				= 'cc';
	const action_csv_ex_glossary		= 'csvexgl';
	const action_csv_im_glossary		= 'csvimgl';
	const action_csv_ex_literature	= 'csvexli';
	const action_csv_im_literature	= 'csvimli';
	const action_literature					= 'lit';
	const action_source							= 'src';
	const action_source_check				= 'srcc';
	
	private $tab_navigation_array = array(
		self::action_glossary						=> gl_tab_glossary,
		self::action_catchword					=> gl_tab_catchword,
		self::action_literature					=> gl_tab_literature,
		self::action_source							=> gl_tab_source,
		self::action_config							=> gl_tab_config,
		self::action_help								=> gl_tab_help
	);
	
	private $tab_abc_array = array(
		'a'		=> 'A',
		'b'		=> 'B',
		'c'		=> 'C',
		'd'		=> 'D',
		'e'		=> 'E',
		'f'		=> 'F',
		'g'		=> 'G',
		'h'		=> 'H',
		'i'		=> 'I,J',
		'k'		=> 'K',
		'l'		=> 'L',
		'm'		=> 'M',
		'n'		=> 'N',
		'o'		=> 'O',
		'p'		=> 'P',
		'q'		=> 'Q',
		'r'		=> 'R',
		's'		=> 'S',
		't'		=> 'T',
		'u'		=> 'U',
		'v'		=> 'V',
		'w'		=> 'W',
		'x'		=> 'X,Y,Z',
		'0-9'	=> '0-9'
	);
	
	private $page_link 					= '';
	private $img_url						= '';
	private $template_path			= '';
	private $error							= '';
	private $message						= '';
	
	private $swNavHide					= array();
	
	public function __construct() {
		$this->page_link = ADMIN_URL.'/admintools/tool.php?tool=dbglossary';
		$this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/' ;
		$this->img_url = WB_URL.'/modules/'.basename(dirname(__FILE__)).'/img/';
		if (!defined('LEPTON_VERSION'))	$this->checkOutputFilter();
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

  /**
   * Reset Error to empty String
   */
  public function clearError() {
  	$this->error = '';
  }

  /** Set $this->message to $message
    * 
    * @param STR $message
    */
  public function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
    * Get Message from $this->message;
    * 
    * @return STR $this->message
    */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
    * Check if $this->message is empty
    * 
    * @return BOOL
    */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage
  
  /**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
    if ($info_text == false) {
      return -1; 
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      } 
    }
    return -1;
  } // getVersion()
  
  /**
   * Check the output_filter and patch it if possible
   * 
   * @return BOOL
   */
  private function checkOutputFilter() {
  	if (file_exists(WB_PATH .'/modules/output_filter/filter-routines.php')) {
  		if (!isPatched(WB_PATH .'/modules/output_filter/filter-routines.php')) {
  			// output_filter is not patched
  			if (doPatch(WB_PATH .'/modules/output_filter/filter-routines.php')) {
  				// successfully patched
  				$this->setMessage(gl_msg_output_filter_patched);
  				return true;
  			}
  			else {
  				// must be patched manually
  				$this->setMessage(gl_msg_output_filter_not_patched);
  				return false;
  			}
  		}
  		else {
  			// already patched
  			return true;
  		}
  	}
  	else {
  		// missing output_filter
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, gl_error_missing_output_filter));
  		return false;
  	}
  }
  
  /**
   * Verhindert XSS Cross Site Scripting
   * 
   * @param REFERENCE $_REQUEST Array
   * @return $request
   */
	public function xssPrevent(&$request) {
  	if (is_string($request)) {
	    $request = html_entity_decode($request);
	    $request = strip_tags($request);
	    $request = trim($request);
	    $request = stripslashes($request);
  	}
	  return $request;
  } // xssPrevent()
	
  public function action() {
  	$html_allowed = array(dbGlossary::field_explain);
  	foreach ($_REQUEST as $key => $value) {
  		if (!in_array($key, $html_allowed)) {
  			// Sonderfall: Value Felder der Konfiguration werden durchnummeriert und duerfen HTML enthalten...
  			if (strpos($key, dbGlossaryCfg::field_value) != 0) {
    			$_REQUEST[$key] = $this->xssPrevent($value);
  			}
  		} 
  	}
    isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_default;
  	switch ($action):
  	case self::action_source:
  		$this->show(self::action_source, $this->dlgAddSource());
  		break;
  	case self::action_source_check:
  		if (!$this->sourceCheck()) {
  			if ($this->isError()) {
  				$this->show(self::action_source, $this->getError());
  			}
  			else {
  				$this->show(self::action_source, $this->dlgAddSource());
  			}
  		}
  		else {
  			$this->show(self::action_literature, $this->dlgLiterature());
  		}
  		break;
  	case self::action_literature:
  		$this->show(self::action_literature, $this->dlgLiterature());
  		break;
  	case self::action_csv_ex_glossary:
  		$this->show(self::action_glossary, $this->csvExportGlossary());
  		break;
  	case self::action_csv_im_glossary:
  		$this->show(self::action_glossary, $this->csvImportGlossary());
  		break;
  	case self::action_csv_ex_literature:
  		$this->show(self::action_literature, $this->csvExportLiterature());
  		break;
  	case self::action_csv_im_literature:
  		$this->show(self::action_literature, $this->csvImportLiterature());
  		break;
  	case self::action_config:
  		$this->show(self::action_config, $this->dlgConfig());
  		break;
  	case self::action_config_check:
  		$this->show(self::action_config, $this->configCheck());
  		break;
  	case self::action_glossary:
  		$this->show(self::action_glossary, $this->dlgGlossary());
  		break;
  	case self::action_catchword:
  		$this->show(self::action_catchword, $this->dlgCatchword());
  		break;
  	case self::action_catchword_check:
  		if (!$this->catchwordCheck()) {
  			if ($this->isError()) {
  				$this->show(self::action_catchword, $this->getError());
  			}
  			else {
  				$this->show(self::action_catchword, $this->dlgCatchword());
  			}
  		}
  		else {
  			$this->show(self::action_glossary, $this->dlgGlossary());
  		}
  		break;
  	case self::action_help:
  		$this->show(self::action_help, $this->dlgHelp());
  		break;
  	case self::action_default:
  	default:
  		$this->show(self::action_glossary, $this->dlgGlossary());
  		break;
  	endswitch;
  } // action
	
  	
  /**
   * Erstellt eine Navigationsleiste
   * 
   * @param $action - aktives Navigationselement
   * @return STR Navigationsleiste
   */
  public function getNavigation($action) {
  	$result = '';
  	foreach ($this->tab_navigation_array as $key => $value) {
  		if (!in_array($key, $this->swNavHide)) {
	  		($key == $action) ? $selected = ' class="selected"' : $selected = ''; 
	  		$result .= sprintf(	'<li%s><a href="%s">%s</a></li>', 
	  												$selected,
	  												sprintf('%s&%s=%s', $this->page_link, self::request_action, $key),
	  												$value
	  												);
  		}
  	}
  	$result = sprintf('<ul class="nav_tab">%s</ul>', $result);
  	return $result;
  } // getNavigation()
  
  /**
   * Prueft HTTP Links
   * 
   * @param $url - zu pruefende URL
   * @param $code_only = false - nur den Status Code zurueckgeben
   * 
   * @author Johannes Froemter <j-f@gmx.net>
   * @author Ralf Hertsch <hertsch@berlin.de>
   */
  private function linkCheck($url, $code_only = false) {
	  $url = trim($url);
	  if (!preg_match("=://=", $url)) $url = "http://$url";
	  $url = parse_url($url);
	  if (strtolower($url["scheme"]) != "http") return FALSE;
	
	  if (!isset($url["port"])) $url["port"] = 80;
	  if (!isset($url["path"])) $url["path"] = "/";
	
	  //$fp = fsockopen($url["host"], $url["port"], &$errno, &$errstr, 30);
		$fp = @fsockopen($url["host"], $url["port"]);
		@stream_set_timeout($fp, 15); 
	  
	  if (!$fp) return FALSE;
	  else
	  {
	    $head = "";
	    $httpRequest = "HEAD ". $url["path"] ." HTTP/1.1\r\n"
	                  ."Host: ". $url["host"] ."\r\n"
	                  ."Connection: close\r\n\r\n";
	    fputs($fp, $httpRequest);
	    while(!feof($fp)) $head .= fgets($fp, 1024);
	    fclose($fp);
			$matches = array();
	    preg_match("=^(HTTP/\d+\.\d+) (\d{3}) ([^\r\n]*)=", $head, $matches);
	    $http["Status-Line"] = $matches[0];
	    $http["HTTP-Version"] = $matches[1];
	    $http["Status-Code"] = $matches[2];
	    $http["Reason-Phrase"] = $matches[3];
	
	    // Nur den HTTP Status Code zurueckgeben
	    if ($code_only) return $http["Status-Code"];
	
	    $rclass = array("Informational", "Success",
	                    "Redirection", "Client Error",
	                    "Server Error");
	    $http["Response-Class"] = $rclass[$http["Status-Code"][0] - 1];
	
	    preg_match_all("=^(.+): ([^\r\n]*)=m", $head, $matches, PREG_SET_ORDER);
	    foreach($matches as $line) $http[$line[1]] = $line[2];
	
	    // Bei Umleitungen den Status Code der umgeleiteten Adresse ermitteln
	    if ($http["Status-Code"][0] == 3)
	      $http["Location-Status-Code"] = $this->linkCheck($http["Location"], true);
	
	    return $http;
	  }
  } // linkCheck()
  
  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   * 
   * @param $action - aktives Navigationselement
   * @param $content - Inhalt
   * 
   * @return ECHO RESULT
   */
  public function show($action, $content) {
  	global $parser;
  	if ($this->isError()) {
  		$content = $this->getError();
  		$class = ' class="error"';
  	}
  	else {
  		$class = '';
  	}
  	$data = array(
  		'navigation'			=> $this->getNavigation($action),
  		'class'						=> $class,
  		'content'					=> $content
  	);
  	$parser->output($this->template_path.'backend.body.htt', $data);
  } // show()

  public function dlgHelp() {
    global $parser;
    
  	if (file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'_help.htt')) {
  		$help_file = WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'_help.htt';
  	}
  	else {
  		$help_file = WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE_help.htt';
  	}
  	$data = array(
  		'img_src'			=> WB_URL.'/modules/'.basename(dirname(__FILE__)).'/img/dbglossary_200.jpg',
  		'version'			=> $this->getVersion(),
  		'img_rh'			=> WB_URL.'/modules/'.basename(dirname(__FILE__)).'/img/rh_schriftzug_small.png',
  		'actual_year'	=> date('Y')
  	);
  	return $parser->get($help_file, $data);
  } // dlgHelp()
  
  public function dlgCatchword() {
  	global $parser;
  	$form_name = 'form_edit';
  	((isset($_REQUEST[dbGlossary::field_id])) && (!empty($_REQUEST[dbGlossary::field_id]))) ? $id = $_REQUEST[dbGlossary::field_id] : $id = -1;
  	$items = '';
  	$dbGlossary = new dbGlossary();
  	if ($id != -1) {
  		// Existierendes Stichwort auslesen
  		$item = array();
  		$where = array();
  		$where[dbGlossary::field_id] = $id;
  		if (!$dbGlossary->sqlSelectRecord($where, $item)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
  			return false;
  		}
  		if (sizeof($item) < 1) {
  			// Fehler: gesuchte Frage existiert nicht
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(gl_error_item_id, $id)));
  		}
  		$item = $item[0];
  		foreach ($item as $key => $value) {
  			if (isset($_REQUEST[$key])) {
  				$item[$key] = $_REQUEST[$key];
  			}
  		}
  		
  	}
  	else {
  		// neues Stichwort
  		$item = $dbGlossary->getFields();
  		$item[dbGlossary::field_id] = $id;
  		$item[dbGlossary::field_status] = dbGlossary::status_active;
  		foreach ($item as $key => $value) {
  			if (isset($_REQUEST[$key])) {
  				//(is_string($_REQUEST[$key])) ? $item[$key] = utf8_decode($_REQUEST[$key]) : $item[$key] = $_REQUEST[$key];
  				$item[$key] = $_REQUEST[$key]; 
  			}
  		}
  	}
  	$items = '';
  	$row = '<tr><td class="label">%s</td><td>%s</td></tr>';
  	// ID
  	if ($id == -1) {
  		$items .= sprintf($row, '', gl_text_no_id);
  	}
  	else {
  		$items .= sprintf($row, '', sprintf('ID %05d', $id));
  	}
  	// Typ
  	$select = '';
  	foreach ($dbGlossary->type_array as $value => $name) {
  		($value == $item[dbGlossary::field_type]) ? $selected = ' selected="selected"' : $selected = '';
			$select .= sprintf('<option value="%s"%s>%s</option>', $value, $selected, $name);
  	}
  	$items .= sprintf($row, gl_label_type, sprintf(	'<select id="%s" name="%s" onchange="document.body.style.cursor=\'wait\';glossaryChangeType(\'%s\',\'%s\',\'%s\',\'%s\',\'%s\',\'%s\',\'%s\',\'%s\'); return false;">%s</select>', 
  																									dbGlossary::field_type,
  																									dbGlossary::field_type,
  																									sprintf('%s&%s=%s&%s=%s',
  																													$this->page_link,
  																													self::request_action,
  																													self::action_catchword,
  																													dbGlossary::field_id,
  																													$item[dbGlossary::field_id]), 
  																									dbGlossary::field_type,
  																									dbGlossary::field_item,
  																									dbGlossary::field_explain,
  																									dbGlossary::field_link,
  																									dbGlossary::field_target,
  																									dbGlossary::field_group,
  																									dbGlossary::field_status,
  																									$select));
  	/*
  	$items .= sprintf($row, gl_label_type, sprintf(	'<select name="%s" onchange="document.body.style.cursor=\'wait\';window.location=\'%s\'+this.value; return false;">%s</select>', 
  																									dbGlossary::field_type,
  																									sprintf('%s&%s=%s&%s=%s&%s=',
  																													$this->page_link,
  																													self::request_action,
  																													self::action_catchword,
  																													dbGlossary::field_id,
  																													$item[dbGlossary::field_id],
  																													dbGlossary::field_type), 
  																									$select));
  	*/
  	// Stichwort
  	$items .= sprintf($row, gl_label_stichwort, sprintf('<input type="text" id="%s" name="%s" value="%s" />', dbGlossary::field_item, dbGlossary::field_item, $item[dbGlossary::field_item]));
  	if ($item[dbGlossary::field_type] == dbGlossary::type_html) {
  		// Erlaeuterung darf HTML Code enthalten
  		isset($item[dbGlossary::field_explain]) ? $content=$item[dbGlossary::field_explain] : $content = '';
  		ob_start();
				show_wysiwyg_editor(dbGlossary::field_explain, dbGlossary::field_explain, $content, '99%', '200px');
				$editor = ob_get_contents();
			ob_end_clean();
			$items .= sprintf($row, gl_label_explain, $editor);
  	}
  	else {
  		// Erklaerung darf KEINEN HTML Code enthalten...
  		$value = trim(strip_tags($item[dbGlossary::field_explain]));
  		$items .= sprintf($row, gl_label_explain, sprintf('<textarea name="%s">%s</textarea>', dbGlossary::field_explain, $value));
  	}
		// Link
		$items .= sprintf($row, gl_label_link, sprintf('<input type="text" id="%s" name="%s" value="%s" />', dbGlossary::field_link, dbGlossary::field_link, $item[dbGlossary::field_link]));
		// Target
		$select = '';
  	foreach ($dbGlossary->target_array as $value => $name) {
  		($value == $item[dbGlossary::field_target]) ? $selected = ' selected="selected"' : $selected = '';
			$select .= sprintf('<option value="%s"%s>%s</option>', $value, $selected, $name);
  	}
  	$items .= sprintf($row, gl_label_target, sprintf('<select id="%s" name="%s">%s</select>', dbGlossary::field_target, dbGlossary::field_target, $select));
  	// Gruppen
  	$config = new dbGlossaryCfg();
  	$groups = $config->getValue(dbGlossaryCfg::cfgGroupArray);
  	if (!in_array($item[dbGlossary::field_group], $groups)) {
  		$item[dbGlossary::field_group] = $groups[0];
  	}
  	$select = '';
  	foreach ($groups as $group) {
  		($group == $item[dbGlossary::field_group]) ? $selected = ' selected="selected"' : $selected = '';
  		$select .= sprintf('<option value="%s"%s>%s</option>', $group, $selected, $group);
  	}
  	$items .= sprintf($row, gl_label_group, sprintf('<select id="%s" name="%s">%s</select>', dbGlossary::field_group, dbGlossary::field_group, $select));
		
		// Status
		$select = '';
  	foreach ($dbGlossary->status_array as $value => $name) {
  		($value == $item[dbGlossary::field_status]) ? $selected = ' selected="selected"' : $selected = '';
			$select .= sprintf('<option value="%s"%s>%s</option>', $value, $selected, $name);
  	}
  	$items .= sprintf($row, gl_label_status, sprintf('<select id="%s" name="%s">%s</select>', dbGlossary::field_status, dbGlossary::field_status, $select));
  	
  	// Mitteilungen anzeigen
		if ($this->isMessage()) {
			$intro = sprintf('<div class="message">%s</div>', $this->getMessage());
		}
		else {
			($id != -1) ? $intro = gl_intro_catchword_edit : $intro = gl_intro_catchword_new; 
			$intro = sprintf('<div class="intro">%s</div>', $intro);
		}
		// Ueberschrift
		($id != -1) ? $header = gl_header_catchword_edit : $header = gl_header_catchword_new;
		$data = array(
			'form_name'					=> $form_name,
  		'form_action'				=> $this->page_link,
  		'action_name'				=> self::request_action,
  		'action_value'			=> self::action_catchword_check,
  		'id_name'						=> dbGlossary::field_id,
  		'id_value'					=> $id,
  		'header'						=> $header,
  		'intro'							=> $intro,
  		'items'							=> $items,
  		'btn_ok'						=> gl_btn_ok,
  		'btn_abort'					=> gl_btn_abort,
  		'abort_location'		=> $this->page_link,
  		'add_buttons'				=> ''
		);
		return $parser->get($this->template_path.'backend.catchword.htt', $data);  	
  } // dlgCatchword()
  
  /**
   * Prueft neuen oder geaenderten dbGlossary Eintrag
   * @todo Maskierung von Hochkommata pruefen
   * @return BOOL
   */
  public function catchwordCheck() {
  	global $tools;
  	$result = ''; 
  	$dbGlossary = new dbGlossary();
  
  	$fields = $dbGlossary->getFields();
  	foreach ($fields as $key => $value) {
  		switch ($key):
  		case dbGlossary::field_id:
  			$fields[dbGlossary::field_id] = $_REQUEST[dbGlossary::field_id];
  			break;
  		case dbGlossary::field_item:
  			(isset($_REQUEST[dbGlossary::field_item])) ? $value = trim($_REQUEST[dbGlossary::field_item]) : $value = '';
  			if (strlen($value) < 2) {
  				// Stichwort muss mindestens 2 Zeichen lang sein
  				$result .= gl_error_item_length;
  			}
  			elseif ($_REQUEST[dbGlossary::field_id] == -1) {
  				// neuer Eintrag, Duplikate verhindern
  				$SQL = sprintf(	"SELECT * FROM %s WHERE %s='%s' AND %s!='%s'", 
  												$dbGlossary->getTableName(),
  												dbGlossary::field_item,
  												$value,
  												dbGlossary::field_status,
  												dbGlossary::status_deleted );
  				$check = array();
  				if (!$dbGlossary->sqlExec($SQL, $check)) {
  					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
  					return false;
  				}
  				if (sizeof($check) > 0) {
  					// Treffer, das Stichwort existiert bereits
  					$result .= sprintf(gl_error_item_exists, $value, $check[0][dbGlossary::field_id]);
  				}
  			}
  			$fields[dbGlossary::field_item] = $value;
  			$fields[dbGlossary::field_sort] = str_replace($dbGlossary->sort_search, $dbGlossary->sort_replace, $value); 
  			break;
  		case dbGlossary::field_explain:
  			(isset($_REQUEST[dbGlossary::field_explain])) ? $value = trim($_REQUEST[dbGlossary::field_explain]) : $value = '';
  			if ((empty($value)) && (empty($_REQUEST[dbGlossary::field_link]))) {
  				$result .= gl_error_explain_empty;
  			}
  			// mask Quotes
  			if ($_REQUEST[dbGlossary::field_type] != dbGlossary::type_html) {
  				// HTML nur bei HTML Stichwort erlauben
  				$value = strip_tags($value);
  			}
  			$fields[dbGlossary::field_explain] = $value;
  			break;
  		case dbGlossary::field_type:
  			(isset($_REQUEST[dbGlossary::field_type])) ? $value = $_REQUEST[dbGlossary::field_type] : $value = dbGlossary::type_undefined;
  			if ($value == dbGlossary::type_undefined) {
  				$result .= gl_error_type_undefined;
  			}
  			elseif ($value == dbGlossary::type_db_glossary) {
  				// dbGlossary: Verweis pruefen
  				(isset($_REQUEST[dbGlossary::field_explain])) ? $item = trim($_REQUEST[dbGlossary::field_explain]) : $item = '';
  				$SQL = sprintf(	"SELECT * FROM %s WHERE %s='%s' AND %s!='%s'", 
  												$dbGlossary->getTableName(),
  												dbGlossary::field_item,
  												$item,
  												dbGlossary::field_status,
  												dbGlossary::status_deleted );
  				$check = array();
  				if (!$dbGlossary->sqlExec($SQL, $check)) {
  					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
  					return false;
  				}
  				if (sizeof($check) > 0) {
  					// ok - es existiert ein passendes Stichwort
  					if ($check[0][dbGlossary::field_type] == dbGlossary::type_db_glossary) {
  						// Fehler: Verweis auf Verweis...
  						(isset($_REQUEST[dbGlossary::field_item])) ? $quer = trim($_REQUEST[dbGlossary::field_item]) : $quer = '';
  						$result .= sprintf(gl_error_type_target_invalid, $quer, $item);
  					}
  				}
  				else {
  					// das angegebene Stichwort wurde nicht gefunden
  					(isset($_REQUEST[dbGlossary::field_item])) ? $quer = trim($_REQUEST[dbGlossary::field_item]) : $quer = '';
  					$result .= sprintf(gl_error_type_target_missing, $quer, $item);
  				}
  			}
  			$fields[dbGlossary::field_type] = $value;
  			break;
  		case dbGlossary::field_link:
  			(isset($_REQUEST[dbGlossary::field_link])) ? $value = $_REQUEST[dbGlossary::field_link] : $value = '';
  			if (!empty($value)) {
  				// Link pruefen
  				  	// check config 
  				$config = new dbGlossaryCfg();
  				$doCheck = $config->getValue(dbGlossaryCfg::cfgLinkCheck);
  				// linkcheck is enabled
  				if ($doCheck) {
  					$link_check = $this->linkCheck($value);
  					if ($link_check['Status-Code'] != 200) {
  						$result .= sprintf(gl_error_link_status, $value, $link_check['Status-Line']);
  					}
  				}
  			}
 				$fields[dbGlossary::field_link] = $value;
  			break;
  		case dbGlossary::field_target:
  			(isset($_REQUEST[dbGlossary::field_target])) ? $value = $_REQUEST[dbGlossary::field_target] : $value = dbGlossary::target_self;
  			$fields[dbGlossary::field_target] = $value;
  			break;
  		case dbGlossary::field_group:
  			$config = new dbGlossaryCfg();
  			$groups = $config->getValue(dbGlossaryCfg::cfgGroupArray);
  			(isset($_REQUEST[dbGlossary::field_group])) ? $value = $_REQUEST[dbGlossary::field_group] : $value = $groups[0];
  			$fields[dbGlossary::field_group] = $value;
  			break;
  		case dbGlossary::field_status:
  			(isset($_REQUEST[dbGlossary::field_status])) ? $value = $_REQUEST[dbGlossary::field_status] : $value = dbGlossary::status_active;
  			$fields[dbGlossary::field_status] = $value;
  			break;
  		endswitch;
  	}
  	if (empty($result)) {
  		// keine Fehler, Datensatz uebernehmen
  		$fields[dbGlossary::field_update_by] = $tools->getDisplayName();
  		$fields[dbGlossary::field_update_when] = date('Y-m-d H:i:s');
  		if ($fields[dbGlossary::field_id] == -1) {
  			// neuer Datensatz
  			unset($fields[dbGlossary::field_id]);
  			$id = -1;
  			if (!$dbGlossary->sqlInsertRecord($fields, $id)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
  				return false; 
  			}
  			$_REQUEST[dbGlossary::field_id] = $id;
  			$_REQUEST[self::request_abc] = strtolower($fields[dbGlossary::field_item][0]);
  			$result = sprintf(gl_msg_item_add, $id);
  		}
  		else {
  			// Datensatz aktualisieren
  			$where = array();
  			$where[dbGlossary::field_id] = $fields[dbGlossary::field_id];
  			unset($fields[dbGlossary::field_id]);
  			if (!$dbGlossary->sqlUpdateRecord($fields, $where)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
  				return false;
  			}
  			$_REQUEST[self::request_abc] = strtolower($fields[dbGlossary::field_item][0]);
  			$result = sprintf(gl_msg_item_update, $where[dbGlossary::field_id]);
  		}
  		$this->setMessage($result);
  		return true;
  	}
  	else {
  		$this->setMessage($result);
  		return false;
  	}
  } // catchwordCheck()
  
  public function dlgGlossary() {
  	global $parser;
  	$dbGlossary = new dbGlossary();
  	
  	// A-Z TAB's...
  	$abc_tab = '';
  	(isset($_REQUEST[self::request_abc])) ? $abc = $_REQUEST[self::request_abc] : $abc = 'a';
  	foreach ($this->tab_abc_array as $key => $value) {
  		($key== $abc) ? $selected = ' class="selected"' : $selected = ''; 
  		$abc_tab .= sprintf(	'<li%s><a href="%s">%s</a></li>', 
	  												$selected,
	  												sprintf('%s&%s=%s&%s=%s', $this->page_link, self::request_action, self::action_glossary, self::request_abc, $key),
	  												$value
	  												);
  	}
  	$abc_tab = sprintf('<ul class="nav_tab">%s</ul>', $abc_tab);
  	switch ($abc):
  	case 'i':
  	case 'j':
  		// SELECT I,J
  		$search = sprintf("(%s LIKE 'i%%' OR %s LIKE 'j%%')",
  											dbGlossary::field_sort,
  											dbGlossary::field_sort);
  		break;
  	case 'x':
  	case 'y':	
  	case 'z':	
  		// SELECT X,Y,Z
  		$search = sprintf("(%s LIKE 'x%%' OR %s LIKE 'y%%' OR %s LIKE 'z%%')",
  											dbGlossary::field_sort,
  											dbGlossary::field_sort,
  											dbGlossary::field_sort);
  		break;
  	case '0-9':
  		// SELECT 0-9
  		$search = sprintf('(%s LIKE \'0%%\' OR %1$s LIKE \'1%%\'  OR %1$s LIKE \'2%%\'  OR %1$s LIKE \'3%%\'  OR %1$s LIKE \'4%%\'  OR %1$s LIKE \'5%%\'  OR %1$s LIKE \'6%%\'  OR %1$s LIKE \'7%%\'  OR %1$s LIKE \'8%%\'  OR %1$s LIKE \'9%%\' )',
  											dbGlossary::field_sort);
  		break;
  	default:
  		// alle anderen Buchstaben
  		$search = sprintf("%s LIKE '%s%%'", dbGlossary::field_sort, $abc);
  		break;
  	endswitch;
  	// Anzahl der Datensaetze ermitteln
  	$count = array();
  	$SQL = sprintf(	"SELECT COUNT(%s) AS 'COUNT' FROM %s WHERE %s!='%s'",
  									dbGlossary::field_id,
  									$dbGlossary->getTableName(),
  									dbGlossary::field_status,
  									dbGlossary::status_deleted);
  	if (!$dbGlossary->sqlExec($SQL, $count)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
  		return false;
  	}
  	$count = $count[0]['COUNT'];
  	if ($count < 1) {
  		// es gibt noch keine Stichworte, Hilfeseite aufrufen
  		return $this->dlgHelp();
  	}
  									
  	$SQL = sprintf(	"SELECT * FROM %s WHERE %s AND %s!='%s' ORDER BY %s ASC",
  									$dbGlossary->getTableName(),
  									$search,
  									dbGlossary::field_status,
  									dbGlossary::status_deleted,
  									dbGlossary::field_sort);
  	$stichworte = array();
  	if (!$dbGlossary->sqlExec($SQL, $stichworte)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
  		return false;
  	}
  	// Gruppen auslesen
  	$config = new dbGlossaryCfg();
  	$groups = $config->getValue(dbGlossaryCfg::cfgGroupArray);
  	
  	$items = '';
  	if (sizeof($stichworte) < 1) {
  		$items = sprintf(gl_error_empty_abc_tab, $this->tab_abc_array[$abc]);
  	}
  	else {
	  	$header 	= '<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>';
	  	$row 			= '<tr class="%s"><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
	  	// Kopfzeile schreiben
	  	$items .= sprintf($header,
	  										gl_header_glossary_id,
	  										gl_header_glossary_stichwort,
	  										gl_header_glossary_edit,
	  										gl_header_glossary_typ,
	  										gl_header_glossary_target,
	  										gl_header_glossary_group,
	  										gl_header_glossary_status,
	  										gl_header_glossary_update_when);
  	}
		$config = new dbGlossaryCfg();
		$template_abbr 		= $config->getValue(dbGlossaryCfg::cfgTypeAbbr);
		$template_acronym = $config->getValue(dbGlossaryCfg::cfgTypeAcronym);
		$template_text 		= $config->getValue(dbGlossaryCfg::cfgTypeText);
		$template_link		= $config->getValue(dbGlossaryCfg::cfgTypeLink); 
		$template_html		= $config->getValue(dbGlossaryCfg::cfgTypeHTML); 										
  	// Einzelne Zeilen schreiben
  	$flipFlop = true;
  	foreach ($stichworte as $stichwort) {
  		if ($flipFlop) {
  		  $flipFlop = false; $flip = 'flip';
  		}
  		else {
  		  $flipFlop = true; $flip = 'flop';
  		}
  		$sw 		= $stichwort[dbGlossary::field_item];
  		$type 	= $stichwort[dbGlossary::field_type];
  		$gl_id 	= $stichwort[dbGlossary::field_id];
  		if ($stichwort[dbGlossary::field_type] == dbGlossary::type_db_glossary) {
  			// dbGlossary: Verweis - anderen Datensatz verwenden
  			$SQL = sprintf(	"SELECT * FROM %s WHERE %s='%s' AND %s!='%s'",
  											$dbGlossary->getTableName(),
  											dbGlossary::field_item,
  											$stichwort[dbGlossary::field_explain],
  											dbGlossary::field_status,
  											dbGlossary::status_deleted);
  			$verweis = array();
  			if (!$dbGlossary->sqlExec($SQL, $verweis)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
  				return false;
  			}
  			if (sizeof($verweis) > 0) {
  				// Verweis gefunden
  				//$old_stichwort = $stichwort;
  				$stichwort = $verweis[0];
  				$type = dbGlossary::type_db_glossary;
  			}
  		}
			$search = array('{catchword}', '{explain}', '{link}', '{target}');
			$replace = array(	$sw, 
												$stichwort[dbGlossary::field_explain], 
												$stichwort[dbGlossary::field_link], 
												$dbGlossary->target_array[$stichwort[dbGlossary::field_target]]);
			switch ($stichwort[dbGlossary::field_type]):
  		case dbGlossary::type_abbreviation:
				$sw = str_ireplace($search, $replace, $template_abbr);
				break;
			case dbGlossary::type_acronym:
				$sw = str_ireplace($search, $replace, $template_acronym);
				break;
			case dbGlossary::type_text:
				$sw = str_ireplace($search, $replace, $template_text);
				break;
			case dbGlossary::type_link:
				$sw = str_ireplace($search, $replace, $template_link);
				break;
			case dbGlossary::type_html:
				//$replace[1] = htmlspecialchars($replace[1]);
				$replace[1] = strip_tags($replace[1]);
				$sw = str_ireplace($search, $replace, $template_html);
				break;
			default:
				// Fehler, Typ ist nicht bekannt!
				$sw = str_ireplace($search, $replace, sprintf(gl_error_type_unknown, $stichwort[dbGlossary::field_type]));
				break;
			endswitch;
			
			
  		$id = sprintf('<a href="%s" title="%s">%05d</a>', 
  									sprintf('%s&%s=%s&%s=%s', $this->page_link, self::request_action, self::action_catchword, dbGlossary::field_id, $gl_id),
  									gl_header_glossary_edit,
  									$gl_id);
  		$edit = sprintf('<a href="%s" title="%s"><img src="%s" /></a>',
  										sprintf('%s&%s=%s&%s=%s', $this->page_link, self::request_action, self::action_catchword, dbGlossary::field_id, $gl_id),
  										gl_header_glossary_edit,
  										$this->img_url.'edit.gif');
  		if (!in_array($stichwort[dbGlossary::field_group], $groups)) {
  			$stichwort[dbGlossary::field_group] = $groups[0];	
  		}
  		$items .= sprintf($row,
  											$flip,
  											$id,
  											$sw,
  											$edit,
  											$dbGlossary->type_array[$type],
  											$dbGlossary->target_array[$stichwort[dbGlossary::field_target]],
  											$stichwort[dbGlossary::field_group],
  											$dbGlossary->status_array[$stichwort[dbGlossary::field_status]],
  											$dbGlossary->mySQLdate2datum($stichwort[dbGlossary::field_update_when])
  											);
  	} // foreach 
  	
  	// Import / Export
  	$groups = $config->getValue(dbGlossaryCfg::cfgGroupArray);
  	$export_group = sprintf('<option value="-1" selected="selected">%s</option>', gl_text_all_groups);
  	foreach ($groups as $group) {
  		$export_group .= sprintf('<option value="%s">%s</option>', $group, $group);
  	}
  	$export_group = sprintf('<select name="%s" size="1">%s</select>', self::request_csv_export, $export_group);
  	
  	$import_file = sprintf('<input name="%s" type="file">', self::request_csv_import);
  	$data = array(
  		'form_export_name'		=> 'csv_export',
  		'form_action'					=> $this->page_link,
  		'action_name'					=> self::request_action,
  		'action_export_value'	=> self::action_csv_ex_glossary,
  		'export_label'				=> gl_label_csv_export,
  		'export_group'				=> $export_group,
  		'btn_export'					=> gl_btn_export,
  		// Import
  		'form_import_name'		=> 'csv_import',
  		'action_import_value'	=> self::action_csv_im_glossary,
  		'import_label'				=> gl_label_csv_import,
  		'import_file'					=> $import_file,
  		'btn_import'					=> gl_btn_import
  	);
  	$csv = $parser->get($this->template_path.'backend.csv.htt', $data);
  	
  	// Mitteilungen anzeigen
		if ($this->isMessage()) {
			$intro = sprintf('<div class="message">%s</div>', $this->getMessage());
		}
		else {
			$intro = sprintf('<div class="intro">%s</div>', sprintf(gl_intro_glossary, $count));
		}		
		$data = array(
			//'header'				=> gl_header_glossary,
  		'abc'						=> $abc_tab,
  		'intro'					=> $intro,
  		'items'					=> $items,
  		'csv'						=> $csv
		);
		return $parser->get($this->template_path.'backend.glossary.htt', $data);
  } // dlgCatchword()
  
  public function dlgConfig() {
  	global $parser;
		$dbGlossaryCfg = new dbGlossaryCfg();
		$SQL = sprintf(	"SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s",
										$dbGlossaryCfg->getTableName(),
										dbGlossaryCfg::field_status,
										dbGlossaryCfg::status_deleted,
										dbGlossaryCfg::field_name);
		$config = array();
		if (!$dbGlossaryCfg->sqlExec($SQL, $config)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryCfg->getError()));
			return false;
		}
		$count = array();
		$items = sprintf(	'<tr><th>%s</th><th>%s</th><th>%s</th></tr>',
											gl_header_cfg_identifier,
											gl_header_cfg_value,
											gl_header_cfg_description );
		$row = '<tr><td>%s</td><td>%s</td><td>%s</td></tr>';
		// bestehende Eintraege auflisten
		foreach ($config as $entry) {
			$id = $entry[dbGlossaryCfg::field_id];
			$count[] = $id;
			$label = constant($entry[dbGlossaryCfg::field_label]);
			(isset($_REQUEST[dbGlossaryCfg::field_value.'_'.$id])) ? 
				$val = $_REQUEST[dbGlossaryCfg::field_value.'_'.$id] : 
				$val = $entry[dbGlossaryCfg::field_value];
				// Hochkommas maskieren 
				$val = str_replace('"', '&quot;', stripslashes($val));
			$value = sprintf(	'<input type="text" name="%s_%s" value="%s" />', dbGlossaryCfg::field_value, $id,	$val);
			$desc = constant($entry[dbGlossaryCfg::field_description]);
			$items .= sprintf($row, $label, $value, $desc);
		}
		$items_value = implode(",", $count);
		// Konfiguration auslesen
		$developerMode = $dbGlossaryCfg->getValue(dbGlossaryCfg::cfgDeveloperMode);
		// Mitteilungen anzeigen
		if ($this->isMessage()) {
			$intro = sprintf('<div class="message">%s</div>', $this->getMessage());
		}
		else {
			$intro = sprintf('<div class="intro">%s</div>', gl_intro_cfg);
		}		
		$data = array(
			'form_name'						=> 'konfiguration',
			'form_action'					=> $this->page_link,
			'action_name'					=> self::request_action,
			'action_value'				=> self::action_config_check,
			'items_name'					=> self::request_items,
			'items_value'					=> $items_value,
			'header'							=> gl_header_cfg,
			'intro'								=> $intro,
			'items'								=> $items,
			'btn_ok'							=> gl_btn_ok,
			'btn_abort'						=> gl_btn_abort,
			'abort_location'			=> $this->page_link
		);
		return $parser->get($this->template_path.'backend.cfg.htt', $data);
	} // dlgConfig()
	
	/**
	 * Ueberprueft Aenderungen die im Dialog dlgConfig() vorgenommen wurden
	 * und aktualisiert die entsprechenden Datensaetze.
	 * Fuegt neue Datensaetze ein.
	 * 
	 * @return STR DIALOG dlgConfig()
	 */
	public function configCheck() {
		global $tools;
		$message = '';
		$dbGlossaryCfg = new dbGlossaryCfg();
		// ueberpruefen, ob ein Eintrag geaendert wurde
		if ((isset($_REQUEST[self::request_items])) && (!empty($_REQUEST[self::request_items]))) {
			$ids = explode(",", $_REQUEST[self::request_items]);
			foreach ($ids as $id) {
				if (isset($_REQUEST[dbGlossaryCfg::field_value.'_'.$id])) {
					$value = $_REQUEST[dbGlossaryCfg::field_value.'_'.$id];
					$where = array();
					$where[dbGlossaryCfg::field_id] = $id; 
					$config = array();
					if (!$dbGlossaryCfg->sqlSelectRecord($where, $config)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryCfg->getError()));
						return false;
					}
					if (sizeof($config) < 1) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(gl_error_cfg_id, $id)));
						return false;
					}
					$config = $config[0];
					if ($config[dbGlossaryCfg::field_value] != $value) {
						// Wert wurde geaendert
						if (($config[dbGlossaryCfg::field_name] == dbGlossaryCfg::cfgGroupArray) &&	(empty($value))) {
							// Sonderfall: Gruppe darf nicht leer sein!
							$message .= gl_error_cfg_group_empty;		
						}
						else {
							if (!$dbGlossaryCfg->setValue($value, $id) && $dbGlossaryCfg->isError()) {
								$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryCfg->getError()));
								return false;
							}
							elseif ($dbGlossaryCfg->isMessage()) {
								$message .= $dbGlossaryCfg->getMessage();
							}
							else {
								// Datensatz wurde aktualisiert
								$message .= sprintf(gl_msg_cfg_id_updated, $id, $config[dbGlossaryCfg::field_name]);
							}
						}
					}
				}
			}		
		}		
		// ueberpruefen, ob ein neuer Eintrag hinzugefuegt wurde
		if ((isset($_REQUEST[dbGlossaryCfg::field_name])) && (!empty($_REQUEST[dbGlossaryCfg::field_name]))) {
			// pruefen ob dieser Konfigurationseintrag bereits existiert
			$where = array();
			$where[dbGlossaryCfg::field_name] = $_REQUEST[dbGlossaryCfg::field_name];
			$where[dbGlossaryCfg::field_status] = dbGlossaryCfg::status_active;
			$result = array();
			if (!$dbGlossaryCfg->sqlSelectRecord($where, $result)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryCfg->getError()));
				return false;
			}
			if (sizeof($result) > 0) {
				// Eintrag existiert bereits
				$message .= sprintf(gl_msg_cfg_add_exists, $where[dbGlossaryCfg::field_name]);
			}
			else {
				// Eintrag kann hinzugefuegt werden
				$data = array();
				$data[dbGlossaryCfg::field_name] = $_REQUEST[dbGlossaryCfg::field_name];
				if (((isset($_REQUEST[dbGlossaryCfg::field_type])) && ($_REQUEST[dbGlossaryCfg::field_type] != dbGlossaryCfg::type_undefined)) &&
						((isset($_REQUEST[dbGlossaryCfg::field_value])) && (!empty($_REQUEST[dbGlossaryCfg::field_value]))) &&
						((isset($_REQUEST[dbGlossaryCfg::field_label])) && (!empty($_REQUEST[dbGlossaryCfg::field_label]))) &&
						((isset($_REQUEST[dbGlossaryCfg::field_description])) && (!empty($_REQUEST[dbGlossaryCfg::field_description])))) {
					// Alle Daten vorhanden
					unset($_REQUEST[dbGlossaryCfg::field_name]);
					$data[dbGlossaryCfg::field_type] = $_REQUEST[dbGlossaryCfg::field_type];
					unset($_REQUEST[dbGlossaryCfg::field_type]);
					$data[dbGlossaryCfg::field_value] = stripslashes(str_replace('&quot;', '"', $_REQUEST[dbGlossaryCfg::field_value]));
					unset($_REQUEST[dbGlossaryCfg::field_value]);
					$data[dbGlossaryCfg::field_label] = $_REQUEST[dbGlossaryCfg::field_label];
					unset($_REQUEST[dbGlossaryCfg::field_label]);
					$data[dbGlossaryCfg::field_description] = $_REQUEST[dbGlossaryCfg::field_description];
					unset($_REQUEST[dbGlossaryCfg::field_description]);
					$data[dbGlossaryCfg::field_status] = dbGlossaryCfg::status_active;
					$data[dbGlossaryCfg::field_update_by] = $tools->getDisplayName();
					$data[dbGlossaryCfg::field_update_when] = date('Y-m-d H:i:s');
					$id = -1;
					if (!$dbGlossaryCfg->sqlInsertRecord($data, $id)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryCfg->getError()));
						return false; 
					}
					$message .= sprintf(gl_msg_cfg_add_success, $id, $data[dbGlossaryCfg::field_name]);		
				}
				else {
					// Daten unvollstaendig
					$message .= gl_msg_cfg_add_incomplete;
				}
			}
		}
		// Sollen Daten als CSV gesichert werden?
		if ((isset($_REQUEST[self::request_csv_export])) && ($_REQUEST[self::request_csv_export] == 1)) {
			// Daten sichern
			$where = array();
			$where[dbGlossaryCfg::field_status] = dbGlossaryCfg::status_active;
			$csv = array();
			$csvFile = WB_PATH.MEDIA_DIRECTORY.'/'.date('ymd-His').'-glossary-cfg.csv';
			if (!$dbGlossaryCfg->csvExport($where, $csv, $csvFile)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryCfg->getError()));
				return false; 
			}
			$message .= sprintf(gl_msg_cfg_csv_export, basename($csvFile));
		}
		
		if (!empty($message)) $this->setMessage($message);
		return $this->dlgConfig();
	} // checkConfig()
  
	public function csvExportGlossary() {
		// Liste als CSV exportieren
		isset($_REQUEST[self::request_csv_export]) ? $grp = $_REQUEST[self::request_csv_export] : $grp = -1;
		$dbGlossary = new dbGlossary();
		$where = array();
		$where[dbGlossary::field_status] = dbGlossary::status_active;
		if ($grp != -1) {
			$where[dbGlossary::field_group] = $grp;
		}
		$csv = array();
		$csvFile = WB_PATH.MEDIA_DIRECTORY.'/'.date('ymd-His').'-glossary-export.csv';
		if (!$dbGlossary->csvExport($where, $csv, $csvFile)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
			return false; 
		}
		if ($grp == -1) {
			$this->setMessage(sprintf(gl_msg_csv_export_liste_all, basename($csvFile)));
		}
		else {
			$this->setMessage(sprintf(gl_msg_csv_export_liste_grp, $grp, basename($csvFile)));
		}
		return $this->dlgGlossary();
	} // csvExportGlossary()
	
	public function csvExportLiterature() {
		// Liste als CSV exportieren
		$dbGlossaryLiterature = new dbGlossaryLiterature();
		$where = array();
		$where[dbGlossaryLiterature::field_status] = dbGlossaryLiterature::status_active;
		$csv = array();
		$csvFile = WB_PATH.MEDIA_DIRECTORY.'/'.date('ymd-His').'-literature-export.csv';
		if (!$dbGlossaryLiterature->csvExport($where, $csv, $csvFile)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryLiterature->getError()));
			return false; 
		}
		$this->setMessage(sprintf(gl_msg_csv_export_literature, basename($csvFile)));
		return $this->dlgLiterature();
	} // csvExportLiterature()
	
	
	public function csvImportGlossary() {
		if (is_uploaded_file($_FILES[self::request_csv_import]['tmp_name'])) {
			// Dateiupload bearbeiten
			$csvFile = WB_PATH.MEDIA_DIRECTORY.'/'.date('ymd-His').'-glossary-import.csv';
			if (move_uploaded_file($_FILES[self::request_csv_import]['tmp_name'], $csvFile)) {
				// Datei uebertragen
				$message = sprintf(gl_msg_csv_file_moved, $_FILES[self::request_csv_import]['name'], basename($csvFile));
				// SPECIAL: GPSP
				if (file_exists(WB_PATH.'/modules/dbgpsptool/include.dbglossary.php')) {
					// Spezielle Importroutine fuer GPSP
					include(WB_PATH.'/modules/dbgpsptool/include.dbglossary.php');					
				}
				else {
					// Standard Import
					$dbGlossary = new dbGlossary();
					$csvArray = array();
					if (!$dbGlossary->csvImport($csvArray, $csvFile)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossary->getError()));
						return false;
					}
					if (sizeof($csvArray) > 0) {
						$items = '';
						$start = true;
						foreach ($csvArray as $item) {
							$start ? $add = '' : $add = ', ';
							if ($start) $start = false;
							$items .= $add.$item[dbGlossary::field_item];
						}
						$message .= sprintf(gl_msg_csv_imp_glossary, $items);
					}
					else {
						$message .= gl_msg_csv_imp_no_glossary;
					}
				}
				$this->setMessage($message);
			}
			else {
				// Fehler bei der Uebertragung
				$this->setMessage(sprintf(gl_error_csv_move_error, $_FILES[self::request_csv_import]['name']));
			}
		}
		else {
			$this->setMessage(gl_error_csv_no_file);
		}
		return $this->dlgGlossary();
	} // csvImportGlossary()
	
	public function csvImportLiterature() {
		if (is_uploaded_file($_FILES[self::request_csv_import]['tmp_name'])) {
			// Dateiupload bearbeiten
			$csvFile = WB_PATH.MEDIA_DIRECTORY.'/'.date('ymd-His').'-literature-import.csv';
			if (move_uploaded_file($_FILES[self::request_csv_import]['tmp_name'], $csvFile)) {
				// Datei uebertragen
				$message = sprintf(gl_msg_csv_file_moved, $_FILES[self::request_csv_import]['name'], basename($csvFile));
				$dbGlossaryLiterature = new dbGlossaryLiterature();
				$csvArray = array();
				if (!$dbGlossaryLiterature->csvImport($csvArray, $csvFile)) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryLiterature->getError()));
					return false;
				}
				if (sizeof($csvArray) > 0) {
					$items = '';
					$start = true;
					foreach ($csvArray as $item) {
						$start ? $add = '' : $add = ', ';
						if ($start) $start = false;
						$items .= $add.$item[dbGlossaryLiterature::field_identifer];
					}
					$message .= sprintf(gl_msg_csv_imp_literature, $items);
				}
				else {
					$message .= gl_msg_csv_imp_no_literature;
				}
				$this->setMessage($message);
			}
			else {
				// Fehler bei der Uebertragung
				$this->setMessage(sprintf(gl_error_csv_move_error, $_FILES[self::request_csv_import]['name']));
			}
		}
		else {
			$this->setMessage(gl_error_csv_no_file);
		}
		return $this->dlgLiterature();
	} // csvImportLiterature()
	
	public function dlgAddSource() {
		global $parser;
		$form_name = 'form_edit';
		((isset($_REQUEST[dbGlossaryLiterature::field_id])) && (!empty($_REQUEST[dbGlossaryLiterature::field_id]))) ? $id = $_REQUEST[dbGlossaryLiterature::field_id] : $id = -1;
  	$dbGlossaryLiterature = new dbGlossaryLiterature();
  	if ($id != -1) {
  		// Existierende Literaturquelle auslesen
  		$item = array();
  		$where = array();
  		$where[dbGlossaryLiterature::field_id] = $id;
  		if (!$dbGlossaryLiterature->sqlSelectRecord($where, $item)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryLiterature->getError()));
  			return false;
  		}
  		if (sizeof($item) < 1) {
  			// Fehler: gesuchte Quelle existiert nicht
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(gl_error_item_id, $id)));
  		}
  		$item = $item[0];
  		foreach ($item as $key => $value) {
  			if (isset($_REQUEST[$key])) {
  				$item[$key] = $_REQUEST[$key]; 
  			}
  		}
  	}
  	else {
  		// neues Stichwort
  		$item = $dbGlossaryLiterature->getFields();
  		$item[dbGlossaryLiterature::field_id] = $id;
  		$item[dbGlossaryLiterature::field_status] = dbGlossaryLiterature::status_active;
  		foreach ($item as $key => $value) {
  			if (isset($_REQUEST[$key])) {
  				$item[$key] = $_REQUEST[$key]; 
  			}
  		}
  	}
  	
  	$items = '';
  	$row = '<tr><td class="label">%s</td><td>%s</td></tr>';
  	// ID
  	if ($id == -1) {
  		$items .= sprintf($row, '', gl_text_no_id);
  	}
  	else {
  		$items .= sprintf($row, '', sprintf('ID %05d', $id));
  	}
  	// Identifier
  	$items .= sprintf($row, gl_label_identifier, sprintf('<input type="text" name="%s" value="%s" />', dbGlossaryLiterature::field_identifer, $item[dbGlossaryLiterature::field_identifer]));
  	// Group
  	$dbConfig = new dbGlossaryCfg();
  	$groups = $dbConfig->getValue(dbGlossaryCfg::cfgLitGroupArray);
  	$select = '';
  	foreach ($groups as $group) {
  		($group == $item[dbGlossaryLiterature::field_group]) ? $selected = ' selected="selected"' : $selected = '';
  		$select .= sprintf(	'<option value="%s"%s>%s</option>',	$group,	$selected, $group);
  	}
  	$items .= sprintf($row, gl_label_group, sprintf('<select name="%s">%s</select>', dbGlossaryLiterature::field_group, $select));
  	// authors
  	$items .= sprintf($row, gl_label_authors, sprintf('<input type="text" name="%s" value="%s" />', dbGlossaryLiterature::field_author, $item[dbGlossaryLiterature::field_author]));
  	// Title
  	$items .= sprintf($row, gl_label_source_title, sprintf('<input type="text" name="%s" value="%s" />', dbGlossaryLiterature::field_title, $item[dbGlossaryLiterature::field_title]));
  	// Subtitle
  	$items .= sprintf($row, gl_label_source_subtitle, sprintf('<input type="text" name="%s" value="%s" />', dbGlossaryLiterature::field_subtitle, $item[dbGlossaryLiterature::field_subtitle]));
  	// published place
  	$items .= sprintf($row, gl_label_source_published_place, sprintf('<input type="text" name="%s" value="%s" />', dbGlossaryLiterature::field_published_place, $item[dbGlossaryLiterature::field_published_place]));
  	// edition
  	$items .= sprintf($row, gl_label_source_edition, sprintf('<input type="text" name="%s" value="%s" />', dbGlossaryLiterature::field_edition, $item[dbGlossaryLiterature::field_edition]));
  	// published year
  	$items .= sprintf($row, gl_label_source_published_year, sprintf('<input type="text" name="%s" value="%s" />', dbGlossaryLiterature::field_published_year, $item[dbGlossaryLiterature::field_published_year]));
  	// ISBN
  	$items .= sprintf($row, gl_label_source_isbn, sprintf('<input type="text" name="%s" value="%s" />', dbGlossaryLiterature::field_isbn, $item[dbGlossaryLiterature::field_isbn]));
  	// URL
  	$items .= sprintf($row, gl_label_source_url, sprintf('<input type="text" name="%s" value="%s" />', dbGlossaryLiterature::field_url, $item[dbGlossaryLiterature::field_url]));
  	
  	// Status
		$select = '';
  	foreach ($dbGlossaryLiterature->status_array as $value => $name) {
  		($value == $item[dbGlossaryLiterature::field_status]) ? $selected = ' selected="selected"' : $selected = '';
			$select .= sprintf('<option value="%s"%s>%s</option>', $value, $selected, $name);
  	}
  	$items .= sprintf($row, gl_label_status, sprintf('<select name="%s">%s</select>', dbGlossaryLiterature::field_status, $select));
  	
  	// Mitteilungen anzeigen
		if ($this->isMessage()) {
			$intro = sprintf('<div class="message">%s</div>', $this->getMessage());
		}
		else {
			($id != -1) ? $intro = gl_intro_source_edit : $intro = gl_intro_source_new; 
			$intro = sprintf('<div class="intro">%s</div>', $intro);
		}
		// Ueberschrift
		($id != -1) ? $header = gl_header_source_edit : $header = gl_header_source_new;
  	
		$data = array(
		'form_name'				=> $form_name,
			'form_action'			=> $this->page_link,
			'action_name'			=> self::request_action,
			'action_value'		=> self::action_source_check,
			'id_name'					=> dbGlossaryLiterature::field_id,
			'id_value'				=> $id,
			'header'					=> $header,
			'intro'						=> $intro,
			'items'						=> $items,
			'btn_ok'					=> gl_btn_ok,
			'btn_abort'				=> gl_btn_abort,
			'abort_location'	=> $this->page_link,
			'add_buttons'			=> ''
		);
		return $parser->get($this->template_path.'backend.source.htt', $data);
	} // dlgAddSource
	
	private function sourceCheck() {
		global $tools;
		$result = '';
		$dbGlossaryLiterature = new dbGlossaryLiterature();
		$fields = $dbGlossaryLiterature->getFields();
  	foreach ($fields as $key => $value) {
  		switch ($key):
  		case dbGlossaryLiterature::field_id:
  			$fields[dbGlossaryLiterature::field_id] = $_REQUEST[dbGlossaryLiterature::field_id];
  			break;
  		// Type wird noch nicht verwendet, auf BUCH setzen
  		case dbGlossaryLiterature::field_type:
  			$fields[dbGlossaryLiterature::field_type] = dbGlossaryLiterature::type_book;
				break;
			// MUST fields
  		case dbGlossaryLiterature::field_identifer:
  		case dbGlossaryLiterature::field_author:
  		case dbGlossaryLiterature::field_title:
  		case dbGlossaryLiterature::field_group:
  			(isset($_REQUEST[$key])) ? $value = $_REQUEST[$key] : $value = '';
  			$fields[$key] = $value;
  			if (empty($value)) {
  				$result .= sprintf(gl_error_source_empty_field, $dbGlossaryLiterature->named_fields[$key]);
  			}
				break;
  		// Optional fields
  		case dbGlossaryLiterature::field_isbn:
  		case dbGlossaryLiterature::field_url:
  		case dbGlossaryLiterature::field_published_place:
  		case dbGlossaryLiterature::field_published_year:
			case dbGlossaryLiterature::field_subtitle:
  		case dbGlossaryLiterature::field_edition:
  			(isset($_REQUEST[$key])) ? $value = $_REQUEST[$key] : $value = '';
  			$fields[$key] = $value;
  			break;
  		case dbGlossaryLiterature::field_status:
  			(isset($_REQUEST[dbGlossaryLiterature::field_status])) ? $value = $_REQUEST[dbGlossaryLiterature::field_status] : $value = dbGlossaryLiterature::status_active;
  			$fields[dbGlossaryLiterature::field_status] = $value;
  			break;
  		endswitch;
  	}
  	if (empty($result)) {
  		// keine Fehler, Datensatz uebernehmen
  		$fields[dbGlossaryLiterature::field_update_by] = $tools->getDisplayName();
  		$fields[dbGlossaryLiterature::field_update_when] = date('Y-m-d H:i:s');
  		if ($fields[dbGlossaryLiterature::field_id] == -1) {
  			// neuer Datensatz
  			unset($fields[dbGlossaryLiterature::field_id]);
  			$id = -1;
  			if (!$dbGlossaryLiterature->sqlInsertRecord($fields, $id)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryLiterature->getError()));
  				return false; 
  			}
  			$_REQUEST[dbGlossaryLiterature::field_id] = $id;
  			$result = sprintf(gl_msg_source_add, $id);
  		}
  		else {
  			// Datensatz aktualisieren
  			$where = array();
  			$where[dbGlossaryLiterature::field_id] = $fields[dbGlossaryLiterature::field_id];
  			unset($fields[dbGlossaryLiterature::field_id]);
  			if (!$dbGlossaryLiterature->sqlUpdateRecord($fields, $where)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryLiterature->getError()));
  				return false;
  			}
  			$result = sprintf(gl_msg_source_update, $where[dbGlossaryLiterature::field_id]);
  		}
  		$this->setMessage($result);
  		return true;
  	}
  	else {
			$this->setMessage($result);
			return false;
  	}
	} // checkSource()
	
	/**
	 * Liste der Literaturquellen
	 * 
	 * @return STR Dialog
	 */
	public function dlgLiterature() {
		global $parser;
		// A-Z TAB's...
  	$abc_tab = '';
  	(isset($_REQUEST[self::request_abc])) ? $abc = $_REQUEST[self::request_abc] : $abc = 'a';
  	foreach ($this->tab_abc_array as $key => $value) {
  		($key== $abc) ? $selected = ' class="selected"' : $selected = ''; 
  		$abc_tab .= sprintf(	'<li%s><a href="%s">%s</a></li>', 
	  												$selected,
	  												sprintf('%s&%s=%s&%s=%s', $this->page_link, self::request_action, self::action_literature, self::request_abc, $key),
	  												$value
	  												);
  	}
  	$abc_tab = sprintf('<ul class="nav_tab">%s</ul>', $abc_tab);
  	switch ($abc):
  	case 'i':
  		// SELECT I,J
  		$search = sprintf("(%s LIKE 'i%%' OR %s LIKE 'j%%')",
  											dbGlossaryLiterature::field_author,
  											dbGlossaryLiterature::field_author);
  		break;
  	case 'x':
  		// SELECT X,Y,Z
  		$search = sprintf("(%s LIKE 'x%%' OR %s LIKE 'y%%' OR %s LIKE 'z%%')",
  											dbGlossaryLiterature::field_author,
  											dbGlossaryLiterature::field_author,
  											dbGlossaryLiterature::field_author);
  		break;
  	default:
  		// alle anderen Buchstaben
  		$search = sprintf("%s LIKE '%s%%'", dbGlossaryLiterature::field_author, $abc);
  		break;
  	endswitch;
  	$dbGlossaryLiterature = new dbGlossaryLiterature();
  	// Anzahl der Datensaetze ermitteln
  	$count = array();
  	$SQL = sprintf(	"SELECT COUNT(%s) AS 'COUNT' FROM %s WHERE %s!='%s'",
  									dbGlossaryLiterature::field_id,
  									$dbGlossaryLiterature->getTableName(),
  									dbGlossaryLiterature::field_status,
  									dbGlossaryLiterature::status_deleted);
  	if (!$dbGlossaryLiterature->sqlExec($SQL, $count)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryLiterature->getError()));
  		return false;
  	}
  	$count = $count[0]['COUNT'];
  	if ($count < 1) {
  		// es gibt noch keine Literaturquellen, Hilfeseite aufrufen
  		return $this->dlgHelp();
  	}
  									
  	$SQL = sprintf(	"SELECT * FROM %s WHERE %s AND %s!='%s' ORDER BY %s ASC",
  									$dbGlossaryLiterature->getTableName(),
  									$search,
  									dbGlossaryLiterature::field_status,
  									dbGlossaryLiterature::status_deleted,
  									dbGlossaryLiterature::field_author);
  	$literatur = array();
  	if (!$dbGlossaryLiterature->sqlExec($SQL, $literatur)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryLiterature->getError()));
  		return false;
  	}
  	
		$items = '';
  	if (sizeof($literatur) < 1) {
  		// Keine Eintraege bei diesem Buchstaben
  		$items = sprintf(gl_error_empty_abc_tab, $this->tab_abc_array[$abc]);
  	}
  	else {
	  	$header 	= '<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>';
	  	$row 			= '<tr class="%s"><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
	  	// Kopfzeile schreiben
	  	$items .= sprintf($header,
	  										gl_header_lit_id,
	  										gl_header_lit_author,
	  										gl_header_lit_edit,
	  										gl_header_lit_identifier,
	  										gl_header_lit_footnote);
  	}
  	$cfg = new dbGlossaryCfg();
  	$typeFooter = $cfg->getValue(dbGlossaryCfg::cfgTypeSource);
  	// Einzelne Zeilen schreiben
  	$flipFlop = true;
  	foreach ($literatur as $quelle) {
  		if ($flipFlop) {
  		  $flipFlop = false; $flip = 'flip';
  		}
  		else {
  		  $flipFlop = true; $flip = 'flop';
  		}
  		$id = sprintf('<a href="%s" title="%s">%05d</a>', 
  									sprintf('%s&%s=%s&%s=%s', 
  													$this->page_link, 
  													self::request_action, 
  													self::action_source, 
  													dbGlossaryLiterature::field_id, 
  													$quelle[dbGlossaryLiterature::field_id]),
  									gl_header_lit_edit,
  									$quelle[dbGlossaryLiterature::field_id]);
  		// Bearbeiten
  		$edit = sprintf('<a href="%s" title="%s"><img src="%s" /></a>',
  										sprintf('%s&%s=%s&%s=%s', 
  														$this->page_link, 
  														self::request_action, 
  														self::action_source, 
  														dbGlossaryLiterature::field_id, 
  														$quelle[dbGlossaryLiterature::field_id]),
  										gl_header_lit_edit,
  										$this->img_url.'edit.gif');
  		$footnote = '';
  		(!empty($quelle[dbGlossaryLiterature::field_subtitle])) ? $subtitle = sprintf('. %s', $quelle[dbGlossaryLiterature::field_subtitle]) : $subtitle = '';
  		(!empty($quelle[dbGlossaryLiterature::field_edition])) ? $edition = sprintf(' %s, ', $quelle[dbGlossaryLiterature::field_edition]) : $edition = '';
  		$search = array('{author}', '{title}', '{subtitle}', '{pub_place}', '{edition}', '{pub_year}', '{isbn}', '{url}');
  		$replace = array(	$quelle[dbGlossaryLiterature::field_author],
  											$quelle[dbGlossaryLiterature::field_title],
  											$subtitle,
  											$quelle[dbGlossaryLiterature::field_published_place],
  											$edition,
  											$quelle[dbGlossaryLiterature::field_published_year],
  											$quelle[dbGlossaryLiterature::field_isbn],
  											$quelle[dbGlossaryLiterature::field_url]	);
  		$footnote = str_ireplace($search, $replace, $typeFooter);
  		
  		$items .= sprintf($row,
  											$flip,
  											$id,
  											$quelle[dbGlossaryLiterature::field_author],
  											$edit,
  											$quelle[dbGlossaryLiterature::field_identifer],
  											$footnote
  											);
  	} // foreach 
  	// Import / Export
  	$import_file = sprintf('<input name="%s" type="file">', self::request_csv_import);
  	$data = array(
  		'form_export_name'		=> 'csv_export',
  		'form_action'					=> $this->page_link,
  		'action_name'					=> self::request_action,
  		'action_export_value'	=> self::action_csv_ex_literature,
  		'export_label'				=> gl_label_csv_export,
  		'export_group'				=> '',
  		'btn_export'					=> gl_btn_export,
  		// Import
  		'form_import_name'		=> 'csv_import',
  		'action_import_value'	=> self::action_csv_im_literature,
  		'import_label'				=> gl_label_csv_import,
  		'import_file'					=> $import_file,
  		'btn_import'					=> gl_btn_import
  	);
  	$csv = $parser->get($this->template_path.'backend.csv.htt', $data);
  	
  	// Mitteilungen anzeigen
		if ($this->isMessage()) {
			$intro = sprintf('<div class="message">%s</div>', $this->getMessage());
		}
		else {
			$intro = sprintf('<div class="intro">%s</div>', sprintf(gl_intro_literature, $count));
		}		
		$data = array(
			'abc'						=> $abc_tab,
  		'intro'					=> $intro,
  		'items'					=> $items,
  		'csv'						=> $csv
		);  	
		return $parser->get($this->template_path.'backend.literature.htt', $data);
	}
	
	
	
} // class toolGlossary

?>