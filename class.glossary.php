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

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php');

class dbGlossary extends dbConnectLE {

	const field_id							= 'gl_id';
	const field_item						= 'gl_item';
	const field_sort						= 'gl_sort';
	const field_explain					= 'gl_explain';
	const field_type						= 'gl_type';
	const field_link						= 'gl_link';
	const field_target					= 'gl_target';
	const field_group						= 'gl_group';
	const field_status					= 'gl_status';
	const field_update_when			= 'gl_update_when';
	const field_update_by				= 'gl_update_by';

	const type_undefined				= 0;
	const type_abbreviation			= 1;
	const type_acronym					= 2;
	const type_text							= 3;
	const type_link							= 4;
	const type_db_glossary			= 5;
	const type_html							= 6;

	public $type_array = array(
		self::type_undefined			=> gl_type_undefined,
		self::type_abbreviation		=> gl_type_abbreviation,
		self::type_acronym				=> gl_type_acronym,
		self::type_text						=> gl_type_text,
		self::type_html						=> gl_type_html,
		self::type_link						=> gl_type_link,
		self::type_db_glossary		=> gl_type_db_glossary
	);

	const note_remark						= 'remark';
	const note_source						= 'source';
	const note_footnote					= 'footnote';
	const note_footnotes				= 'footnotes';

	const target_self						= 0;
	const target_blank					= 1;
	const target_parent					= 2;
	const target_top						= 3;

	public $target_array = array(
		self::target_self					=> gl_target_self,
		self::target_blank				=> gl_target_blank,
		self::target_parent				=> gl_target_parent,
		self::target_top					=> gl_target_top
	);

	const group_default					= 'Default';

	const status_active					= 1;
	const status_locked					= 2;
	const status_deleted				= 0;

	public $status_array = array(
		self::status_active				=> gl_status_active,
		self::status_locked				=> gl_status_locked,
		self::status_deleted			=> gl_status_deleted
	);

	public $sort_search = array(
		'Ä','ä','Ö','ö','Ü','ü','ß'
	);
	public $sort_replace = array(
		'A','a','O','o','U','u','s'
	);

	private $create_tables 			= false;

	protected static $config_file = 'config.json';
	protected static $table_prefix = TABLE_PREFIX;

	public function __construct($create_tables=false) {
		$this->create_tables = $create_tables;
		// use another table prefix?
    if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/config.json')) {
      $config = json_decode(file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/config.json'), true);
      if (isset($config['table_prefix']))
        self::$table_prefix = $config['table_prefix'];
    }
    parent::__construct();
    $this->setTablePrefix(self::$table_prefix);
    $this->setTableName('mod_glossary');
		$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
		$this->addFieldDefinition(self::field_item, "VARCHAR(80) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_sort, "VARCHAR(80) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_explain, "TEXT NOT NULL DEFAULT ''", false, false, true);
		$this->addFieldDefinition(self::field_type, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::type_undefined."'");
		$this->addFieldDefinition(self::field_link, "VARCHAR(255) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_target, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::target_self."'");
		$this->addFieldDefinition(self::field_group, "VARCHAR(80) NOT NULL DEFAULT '".self::group_default."'");
		$this->addFieldDefinition(self::field_status, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::status_active."'");
		$this->addFieldDefinition(self::field_update_when, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
		$this->addFieldDefinition(self::field_update_by, "VARCHAR(80) NOT NULL DEFAULT 'SYSTEM'");
		$this->checkFieldDefinitions();
		if ($this->create_tables) {
			if (!$this->sqlTableExists()) {
				if (!$this->sqlCreateTable()) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
					return false;
				}
			}
		}
		// important: switch decoding off.
		$this->setDecodeSpecialChars(false);
	} // __construct()

} // class dbGlossary

class dbGlossaryLiterature extends dbConnectLE {

	const field_id							= 'lit_id';
	const field_identifer				= 'lit_identifer';
	const field_type						= 'lit_type';
	const field_author					= 'lit_author';
	const field_group						= 'lit_group';
	const field_title						= 'lit_title';
	const field_subtitle				= 'lit_subtitle';
	const field_published_place	= 'lit_published_place';
	const field_edition					= 'lit_edition';
	const field_published_year	= 'lit_published_year';
	const field_isbn						= 'lit_isbn';
	const field_url							= 'lit_url';
	const field_status					= 'lit_status';
	const field_update_when			= 'lit_update_when';
	const field_update_by				= 'lit_update_by';

	public $named_fields = array(
		self::field_id							=> gl_label_id,
		self::field_identifer				=> gl_label_identifier,
		self::field_type						=> gl_label_source_type,
		self::field_author					=> gl_label_authors,
		self::field_group						=> gl_label_group,
		self::field_title						=> gl_label_source_title,
		self::field_subtitle				=> gl_label_source_subtitle,
		self::field_published_place	=> gl_label_source_published_place,
		self::field_edition					=> gl_label_source_edition,
		self::field_published_year	=> gl_label_source_published_year,
		self::field_isbn						=> gl_label_source_isbn,
		self::field_url							=> gl_label_source_url,
		self::field_status					=> gl_label_status,
		self::field_update_when			=> gl_label_update_when,
		self::field_update_by				=> gl_label_update_by
	);

	const group_default					= 'Default';

	const type_undefined				= 0;
	const type_book							= 1;

	public $type_array = array(
		self::type_undefined			=> gl_type_undefined,
		self::type_book						=> gl_type_book
	);

	const status_active					= 1;
	const status_locked					= 2;
	const status_deleted				= 0;

	public $status_array = array(
		self::status_active				=> gl_status_active,
		self::status_locked				=> gl_status_locked,
		self::status_deleted			=> gl_status_deleted
	);

	private $create_tables 			= false;

	protected static $config_file = 'config.json';
	protected static $table_prefix = TABLE_PREFIX;

	/**
	 * Constructor for dbGlossary
	 *
	 * @param boolean $create_tables
	 */
	public function __construct($create_tables=false) {
		$this->create_tables = $create_tables;
		// use another table prefix?
    if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/config.json')) {
      $config = json_decode(file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/config.json'), true);
      if (isset($config['table_prefix']))
        self::$table_prefix = $config['table_prefix'];
    }
    parent::__construct();
    $this->setTablePrefix(self::$table_prefix);
    $this->setTableName('mod_glossary_literature');
		$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
		$this->addFieldDefinition(self::field_identifer, "VARCHAR(80) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_type, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::type_book."'");
		$this->addFieldDefinition(self::field_author, "VARCHAR(255) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_group, "VARCHAR(255) NOT NULL DEFAULT '".self::group_default."'");
		$this->addFieldDefinition(self::field_title, "VARCHAR(255) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_subtitle, "VARCHAR(255) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_published_place, "VARCHAR(255) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_edition, "VARCHAR(255) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_published_year, "VARCHAR(50) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_isbn, "VARCHAR(80) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_url, "VARCHAR(255) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_status, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::status_active."'");
		$this->addFieldDefinition(self::field_update_when, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
		$this->addFieldDefinition(self::field_update_by, "VARCHAR(80) NOT NULL DEFAULT 'SYSTEM'");
		$this->checkFieldDefinitions();
		if ($this->create_tables) {
			if (!$this->sqlTableExists()) {
				if (!$this->sqlCreateTable()) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
					return false;
				}
			}
		}
		// important: switch decoding OFF.
		$this->setDecodeSpecialChars(false);
	} // __construct()

} // class dbGlossaryLiterature

class dbGlossaryFootnotes extends dbConnectLE {

	const field_id						= 'fn_id';
	const field_page_id				= 'fn_page_id';
	const field_note_id				= 'fn_note_id';
	const field_remark				= 'fn_remark';
	const field_source				= 'fn_source';
	const field_update_when		= 'fn_update_when';

	private $create_tables 			= false;

	protected static $config_file = 'config.json';
	protected static $table_prefix = TABLE_PREFIX;

	public function __construct($create_tables=false) {
		$this->create_tables = $create_tables;
		// use another table prefix?
    if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/config.json')) {
      $config = json_decode(file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/config.json'), true);
      if (isset($config['table_prefix']))
        self::$table_prefix = $config['table_prefix'];
    }
    parent::__construct();
    $this->setTablePrefix(self::$table_prefix);
    $this->setTableName('mod_glossary_footnotes');
		$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
		$this->addFieldDefinition(self::field_page_id, "INT(11) NOT NULL DEFAULT '-1'");
		$this->addFieldDefinition(self::field_note_id, "INT(11) NOT NULL DEFAULT '-1'");
		$this->addFieldDefinition(self::field_remark, "TEXT NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_source, "INT(11) NOT NULL DEFAULT '-1'");
		$this->addFieldDefinition(self::field_update_when, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
		$this->checkFieldDefinitions();
		if ($this->create_tables) {
			if (!$this->sqlTableExists()) {
				if (!$this->sqlCreateTable()) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
					return false;
				}
			}
		}
		// important: switch decoding OFF.
		$this->setDecodeSpecialChars(false);
	} // __construct()

} // class dbGlossaryFootnotes

class dbGlossaryCfg extends dbConnectLE {

	const field_id						= 'cfg_id';
	const field_name					= 'cfg_name';
	const field_type					= 'cfg_type';
	const field_value					= 'cfg_value';
	const field_label					= 'cfg_label';
	const field_description		= 'cfg_desc';
	const field_status				= 'cfg_status';
	const field_update_by			= 'cfg_update_by';
	const field_update_when		= 'cfg_update_when';

	const status_active				= 1;
	const status_deleted			= 0;

	const type_undefined			= 0;
	const type_array					= 7;
  const type_boolean				= 1;
  const type_email					= 2;
  const type_float					= 3;
  const type_integer				= 4;
  const type_path						= 5;
  const type_string					= 6;
  const type_url						= 8;

  public $type_array = array(
  	self::type_undefined		=> '-UNDEFINED-',
  	self::type_array				=> 'ARRAY',
  	self::type_boolean			=> 'BOOLEAN',
  	self::type_email				=> 'E-MAIL',
  	self::type_float				=> 'FLOAT',
  	self::type_integer			=> 'INTEGER',
  	self::type_path					=> 'PATH',
  	self::type_string				=> 'STRING',
  	self::type_url					=> 'URL'
  );

  private $createTables 		= false;
  private $message					= '';

  const cfgDeveloperMode		= 'cfgDeveloperMode';
  const cfgTypeAbbr					= 'cfgTypeAbbr';
  const cfgTypeAcronym			= 'cfgTypeAcronym';
  const cfgTypeText					= 'cfgTypeText';
  const cfgTypeLink					= 'cfgTypeLink';
  const cfgIgnorePageID			= 'cfgIgnorePageID';
  const cfgGroupArray				= 'cfgGroupArray';
  const cfgIconLinkIntern		= 'cfgIconLinkIntern';
  const cfgIconLinkExtern		= 'cfgIconLinkExtern';
  const cfgActive						= 'cfgActive';
  const cfgTypeSource				= 'cfgTypeSource';
  const cfgTypeFootnote			= 'cfgTypeFootnote';
  const cfgTypeLiterature		= 'cfgTypeLiterature';
  const cfgLinkCheck				= 'cfgLinkCheck';
  const cfgTypeHTML					= 'cfgTypeHTML';
  const cfgShowMissingSpots	= 'cfgShowMissingSpots';
  const cfgTypeMissingSpot	= 'cfgTypeMissingSpot';
  const cfgAZTabs						= 'cfgAZTabs';
  const cfgAZTabsUse				= 'cfgAZTabsUse';
  const cfgAZTabsItem				= 'cfgAZTabsItem';
  const cfgAZTabsStartEmpty = 'cfgAZTabsStartEmpty';
  const cfgLitGroupArray		= 'cfgLitGroupArray';
  const cfgEntities2Umlauts	= 'cfgEntities2Umlauts';

  protected $config_array = array(
  	//array('gl_label_cfg_developer_mode', self::cfgDeveloperMode, self::type_boolean, 0, 'gl_desc_cfg_developer_mode'),
  	array('gl_label_cfg_active', self::cfgActive, self::type_boolean, 1, 'gl_desc_cfg_active'),
  	array('gl_label_cfg_type_abbr', self::cfgTypeAbbr, self::type_string, '<abbr title="{explain}">{catchword}</abbr>', 'gl_desc_cfg_type_abbr'),
  	array('gl_label_cfg_type_acronym', self::cfgTypeAcronym, self::type_string, '<acronym title="{explain}">{catchword}</acronym>', 'gl_desc_cfg_type_acronym'),
  	array('gl_label_cfg_type_text', self::cfgTypeText, self::type_string, '<span class="catchword_text" title="{explain}">{catchword}</span>', 'gl_desc_cfg_type_text'),
  	array('gl_label_cfg_type_html', self::cfgTypeHTML, self::type_string, '<a class="catchword_html" href="#">{catchword}<span>{explain}</span></a>', 'gl_desc_cfg_type_html'),
  	array('gl_label_cfg_type_link', self::cfgTypeLink, self::type_string, '<a href="{link}" target="{target}" title="{explain}">{catchword}</a>', 'gl_desc_cfg_type_link'),
  	array('gl_label_cfg_page_id',	self::cfgIgnorePageID, self::type_array, '', 'gl_desc_cfg_page_id'),
  	array('gl_label_cfg_group_array', self::cfgGroupArray, self::type_array, dbGlossary::group_default, 'gl_desc_cfg_group_array'),
  	array('gl_label_cfg_link_intern', self::cfgIconLinkIntern, self::type_string, 'icon-link-intern.gif', 'gl_desc_cfg_link_intern'),
  	array('gl_label_cfg_link_extern', self::cfgIconLinkExtern, self::type_string, 'icon-link-extern.gif', 'gl_desc_cfg_link_extern'),
  	array('gl_label_cfg_type_source', self::cfgTypeSource, self::type_string, '<b>{author}</b>: <i>{title}</i>{subtitle}, {pub_place}, {edition}{pub_year}', 'gl_desc_cfg_type_source'),
  	array('gl_label_cfg_type_footnote', self::cfgTypeFootnote, self::type_string, '<div class="fn_item"><span class="fn_number"><a name="fn_{number}"><sup>{number}</sup></a></span><span class="fn_footnote">{footnote}</span></div>', 'gl_desc_cfg_type_footnote'),
  	array('gl_label_cfg_link_check', self::cfgLinkCheck, self::type_boolean, 1, 'gl_desc_cfg_link_check'),
  	array('gl_label_cfg_show_missing_spots', self::cfgShowMissingSpots, self::type_boolean, 1, 'gl_desc_cfg_show_missing_spots'),
  	array('gl_label_cfg_type_missing_spot', self::cfgTypeMissingSpot, self::type_string, '<span class="catchword_error" title="{error}">{catchword}</span>', 'gl_desc_cfg_type_missing_spot'),
  	array('gl_label_cfg_type_literature', self::cfgTypeLiterature, self::type_string, '<sup class="fn_sup"><a href="#fn_{count}">{count}</a></sup>', 'gl_desc_cfg_type_literature'),
  	array('gl_label_cfg_a_z_tabs', self::cfgAZTabs, self::type_string, 'A|B|C|D|E|F|G|H|I,J|K|L|M|N|O,P,Q|R|S|T|U|V|W|X,Y,Z', 'gl_desc_cfg_a_z_tabs'),
  	array('gl_label_cfg_a_z_tabs_use', self::cfgAZTabsUse, self::type_boolean, 1, 'gl_desc_cfg_a_z_tabs_use'),
  	array('gl_label_cfg_a_z_tabs_start_empty', self::cfgAZTabsStartEmpty, self::type_boolean, 0, 'gl_desc_cfg_a_z_tabs_start_empty'),
  	array('gl_label_cfg_a_z_tabs_item', self::cfgAZTabsItem, self::type_string, '<span class="gl_tab">[{tab}]</span>', 'gl_desc_cfg_a_z_tabs_item'),
  	array('gl_label_cfg_lit_group_array', self::cfgLitGroupArray, self::type_array, dbGlossary::group_default, 'gl_desc_cfg_lit_group_array'),
  	array('gl_label_cfg_entities_2_umlauts', self::cfgEntities2Umlauts, self::type_boolean, 1, 'gl_desc_cfg_entities_2_umlauts')
  );

  protected static $config_file = 'config.json';
  protected static $table_prefix = TABLE_PREFIX;

  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	// use another table prefix?
    if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/config.json')) {
      $config = json_decode(file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/config.json'), true);
      if (isset($config['table_prefix']))
        self::$table_prefix = $config['table_prefix'];
    }
    parent::__construct();
    $this->setTablePrefix(self::$table_prefix);
    $this->setTableName('mod_glossary_cfg');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_name, "VARCHAR(32) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_type, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::type_undefined."'");
  	$this->addFieldDefinition(self::field_value, "VARCHAR(255) NOT NULL DEFAULT ''", false, false, true);
  	$this->addFieldDefinition(self::field_label, "VARCHAR(64) NOT NULL DEFAULT 'ed_str_undefined'");
  	$this->addFieldDefinition(self::field_description, "VARCHAR(255) NOT NULL DEFAULT 'ed_str_undefined'");
  	$this->addFieldDefinition(self::field_status, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_update_by, "VARCHAR(32) NOT NULL DEFAULT 'SYSTEM'");
  	$this->addFieldDefinition(self::field_update_when, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
  	$this->setIndexFields(array(self::field_name));
  	//$this->setAllowedHTMLtags('<a><abbr><acronym><span><div><br><p><sup>');
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	// Default Werte garantieren
  	if ($this->sqlTableExists()) {
  		$this->checkConfig();
  	}
  	// important: switch decoding OFF.
		$this->setDecodeSpecialChars(false);
  } // __construct()

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
   * Aktualisiert den Wert $new_value des Datensatz $name
   *
   * @param $new_value STR - Wert, der uebernommen werden soll
   * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
   *
   * @return BOOL Ergebnis
   *
   */
  public function setValueByName($new_value, $name) {
  	$where = array();
  	$where[self::field_name] = $name;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(cfg_error_cfg_id, $name)));
  		return false;
  	}
  	return $this->setValue($new_value, $config[0][self::field_id]);
  } // setValueByName()

  /**
   * Fuegt den Wert $new_value in die dbShortLinkConfig ein
   *
   * @param $new_value STR - Wert, der uebernommen werden soll
   * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
   *
   * @return BOOL Ergebnis
   */
  public function setValue($new_value, $id) {
  	$tools = new rhTools();
  	$value = '';
  	$where = array();
  	$where[self::field_id] = $id;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(gl_error_cfg_id, $id)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
  	case self::type_array:
  		// Funktion geht davon aus, dass $value als STR uebergeben wird!!!
  		$worker = explode(",", $new_value);
  		$data = array();
  		foreach ($worker as $item) {
  			$data[] = trim($item);
  		};
  		$value = implode(",", $data);
  		break;
  	case self::type_boolean:
  		$value = (bool) $new_value;
  		$value = (int) $value;
  		break;
  	case self::type_email:
  		if ($tools->validateEMail($new_value)) {
  			$value = trim($new_value);
  		}
  		else {
  			$this->setMessage(sprintf(gl_msg_invalid_email, $new_value));
  			return false;
  		}
  		break;
  	case self::type_float:
  		$value = $tools->str2float($new_value);
  		break;
  	case self::type_integer:
  		$value = $tools->str2int($new_value);
  		break;
  	case self::type_url:
  	case self::type_path:
  		$value = $tools->addSlash(trim($new_value));
  		break;
  	case self::type_string:
  		$value = (string) trim($new_value);
  		// Hochkommas demaskieren
  		$value = str_replace('&quot;', '"', $value);
  		break;
  	endswitch;
  	unset($config[self::field_id]);
  	$config[self::field_value] = (string) $value;
  	$config[self::field_update_by] = $tools->getDisplayName();
  	$config[self::field_update_when] = date('Y-m-d H:i:s');
  	if (!$this->sqlUpdateRecord($config, $where)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	return true;
  } // setValue()

  /**
   * Gibt den angeforderten Wert zurueck
   *
   * @param $name - Bezeichner
   *
   * @return WERT entsprechend des TYP
   */
  public function getValue($name) {
  	$result = '';
  	$where = array();
  	$where[self::field_name] = $name;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(gl_error_cfg_name, $name)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
  	case self::type_array:
  		$result = explode(",", $config[self::field_value]);
  		break;
  	case self::type_boolean:
  		$result = (bool) $config[self::field_value];
  		break;
  	case self::type_email:
  	case self::type_path:
  	case self::type_string:
  	case self::type_url:
  		$result = (string) $config[self::field_value];
  		break;
  	case self::type_float:
  		$result = (float) $config[self::field_value];
  		break;
  	case self::type_integer:
  		$result = (integer) $config[self::field_value];
  		break;
  	default:
  		$result = $config[self::field_value];
  		break;
  	endswitch;
  	return $result;
  } // getValue()

  public function checkConfig() {
  	foreach ($this->config_array as $item) {
  		$where = array();
  		$where[self::field_name] = $item[1];
  		$check = array();
  		if (!$this->sqlSelectRecord($where, $check)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			return false;
  		}
  		if (sizeof($check) < 1) {
  			// Eintrag existiert nicht
  			$data = array();
  			$data[self::field_label] = $item[0];
  			$data[self::field_name] = $item[1];
  			$data[self::field_type] = $item[2];
  			$data[self::field_value] = $item[3];
  			$data[self::field_description] = $item[4];
  			$data[self::field_update_when] = date('Y-m-d H:i:s');
  			$data[self::field_update_by] = 'SYSTEM';
  			if (!$this->sqlInsertRecord($data)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  				return false;
  			}
  		}
  	}
  	return true;
  }

} // class dbGlossaryCfg

/**
 * check if output_filter is patched
 *
 * @param STR $filename
 * @return BOOL
 */
function isPatched($filename) {
	if (file_exists($filename)) {
		$lines = file($filename);
		foreach ($lines as $line) {
			if (strpos($line , "parseGlossary" ) > 0)
				return true;
		}
		return false;
	}
	return false;
}

/**
 * unpatch output_filter
 *
 * @return BOOL
 */
function unPatch() {
	$original = WB_PATH .'/modules/output_filter/filter-routines.php';
	$tmp 			= WB_PATH .'/modules/output_filter/filter-routines.backup.php';
	$backup 	= WB_PATH .'/modules/output_filter/original-glossary-filter-routines.php';
	if (!file_exists($backup) )
		return false;  // No backup, can't do anything
	if (file_exists($tmp))
		unlink($tmp);
	if (rename($original, $tmp)) {
		if (rename($backup, $original)) {
			unlink($tmp);
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

/**
 * insert patch into output_filter
 *
 * @param STR $filename
 * @return BOOL
 */
function doPatch($filename) {
	$returnvalue = false;
	$tempfile = WB_PATH .'/modules/output_filter/new_filter.php';
	$backup = WB_PATH .'/modules/output_filter/original-glossary-filter-routines.php';

	$addline = "\n\n\t\t// exec dbGlossary filtering ";
	$addline .= "\n\t\tif(file_exists(WB_PATH .'/modules/dbglossary/class.filter.php')) { ";
	$addline .= "\n\t\t\trequire_once (WB_PATH .'/modules/dbglossary/class.filter.php'); ";
	$addline .= "\n\t\t\t".'$content = parseGlossary($content); ';
	$addline .= "\n\t\t}\n\n ";
	if(file_exists($filename)) {
		$lines = file ($filename);
		$handle = @fopen ($tempfile, 'w');
		if ($handle !== false) {
			foreach ($lines as $line) {
				if (@fwrite ($handle, $line) == true) {
					if (strpos($line, 'function filter_frontend_output($content)' ) > 0) {
						$returnvalue = true;
						fwrite($handle, $addline);
					}
				}
				else {
					@fclose($handle);
					return false;
				}
			}
			fclose ($handle);
			if (rename($filename, $backup)) {
				if (rename($tempfile, $filename)) {
					return $returnvalue;
				}
				else {
					return false;
				}
			}
		}
	}
	return false;
}

/**
 * insert patch into output_filter
 *
 * @param STR $filename
 * @return BOOL
 */
function doPatchWB283($filename) {
  $returnvalue = false;
  $tempfile = WB_PATH .'/modules/output_filter/new_filter.php';
  $backup = WB_PATH .'/modules/output_filter/original-glossary-filter-routines.php';

  $addline = "\n\n\t\t// exec dbGlossary filtering ";
  $addline .= "\n\t\tif(file_exists(WB_PATH .'/modules/dbglossary/class.filter.php')) { ";
  $addline .= "\n\t\t\trequire_once (WB_PATH .'/modules/dbglossary/class.filter.php'); ";
  $addline .= "\n\t\t\t".'$content = parseGlossary($content); ';
  $addline .= "\n\t\t}\n\n ";
  if(file_exists($filename)) {
    $lines = file ($filename);
    $handle = @fopen ($tempfile, 'w');
    if ($handle !== false) {
      foreach ($lines as $line) {
        if (@fwrite ($handle, $line) == true) {
          if (strpos($line, "define('OUTPUT_FILTER_DOT_REPLACEMENT'" ) > 0) {
            $returnvalue = true;
            fwrite($handle, $addline);
          }
        }
        else {
          @fclose($handle);
          return false;
        }
      }
      fclose ($handle);
      if (rename($filename, $backup)) {
        if (rename($tempfile, $filename)) {
          return $returnvalue;
        }
        else {
          return false;
        }
      }
    }
  }
  return false;
}


?>