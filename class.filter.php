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

if (DEBUG) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}
else {
	ini_set('display_errors', 0);
	error_reporting(E_ERROR);
}

/**
 * Wird von /modules/output_filter/filter-routines.php
 * aufgerufen
 *
 * @param STR $content
 * @return STR
 */
function parseGlossary($content) {
	$filter = new filterGlossary($content);
	return $filter->exec();
}


class filterGlossary {

	private $content;
	private $header;
	private $body;
	private $error = '';
	private $template_abbr;
	private $template_acronym;
	private $template_text;
	private $template_html;
	private $template_link;
	private $template_path;
	private $template_source;
	private $template_footnote;
	private $isActive;
	private $img_url;
	private $ignorePageIDs;
	private $footnotes = array();
	private $show_missing_spots;
	private $template_missing_spot;
	private $template_literature;
	private $entities2umlauts;

	// Muster fuer die regulaere Suche nach Glossar- und Literatureintraegen
	private $pregSearchPatternL				= '\|\|';  	// im Text: ||Stichwort||
	private $pregSearchPatternR				= '\|\|';		//
	private $pregAddLitPatternL				= '\{';			// im Text: ||{Literatur}||
	private $pregAddLitPatternR				= '\}';
	private $pregSplitPattern					= '|';			// im Text: ||{Literatur|Seite}||
	private $pregSplitAddPattern			= ':';			// im Text: ||{remark:Anmerkung}||

	public function __construct($content) {
		$this->content = $content;
		$end_head = stripos($this->content, '</head>')+strlen('</head>');
		$this->header = substr($this->content, 0, $end_head);
		$this->body		= substr($this->content, $end_head+1);
		$this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/' ;
		$this->img_url = WB_URL.'/modules/'.basename(dirname(__FILE__)).'/img/';
		$this->getGlossaryCfg();
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

  private function getGlossaryCfg() {
  	$config = new dbGlossaryCfg();
  	$this->template_abbr 					= $config->getValue(dbGlossaryCfg::cfgTypeAbbr);
  	$this->template_acronym 			= $config->getValue(dbGlossaryCfg::cfgTypeAcronym);
  	$this->template_text 					= $config->getValue(dbGlossaryCfg::cfgTypeText);
  	$this->template_link					= $config->getValue(dbGlossaryCfg::cfgTypeLink);
  	$this->ignorePageIDs					= $config->getValue(dbGlossaryCfg::cfgIgnorePageID);
  	$this->template_source 				= $config->getValue(dbGlossaryCfg::cfgTypeSource);
  	$this->isActive								= $config->getValue(dbGlossaryCfg::cfgActive);
  	$this->template_footnote			= $config->getValue(dbGlossaryCfg::cfgTypeFootnote);
  	$this->template_html					= $config->getValue(dbGlossaryCfg::cfgTypeHTML);
  	$this->show_missing_spots			= $config->getValue(dbGlossaryCfg::cfgShowMissingSpots);
  	$this->template_missing_spot 	= $config->getValue(dbGlossaryCfg::cfgTypeMissingSpot);
  	$this->template_literature		= $config->getValue(dbGlossaryCfg::cfgTypeLiterature);
  	$this->entities2umlauts				= $config->getValue(dbGlossaryCfg::cfgEntities2Umlauts);
  }

	public function exec() {
		if (!$this->isActive) {
			// dbGlossary ist inaktiv
			return $this->content;
		}
		$this->check();
		$this->content = $this->header.$this->body;
		// Fussnoten aufraeumen und sichern
		$dbGlossaryFootnotes = new dbGlossaryFootnotes();
		$where = array();
		$where[dbGlossaryFootnotes::field_page_id] = PAGE_ID;
		if (!$dbGlossaryFootnotes->sqlDeleteRecord($where)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryFootnotes->getError()));
		}
		foreach ($this->footnotes as $footnote) {
			unset($footnote[dbGlossaryFootnotes::field_id]);
			if (!$dbGlossaryFootnotes->sqlInsertRecord($footnote)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryFootnotes->getError()));
				break;
			}
		}
		if ($this->isError()) {
			// Fehlerbehandlung
			$error = strip_tags($this->getError());
			$prompt = sprintf('<div style="position:absolute;top:0px;left:0px;margin:5px;width:10px;height:10px;"><img src="%s" title="%s" alt="%s"></div></body>',
												$this->img_url.'icon-error.png',
												$error,
												$error);
			$this->content = str_ireplace('</body>', $prompt, $this->content);
		}
		return $this->content;
	} // exec();

	/**
	 * FILTER Routine ersetzt alle Stichworte und Literaturhinweise durch entsprechende Formatierungen...
	 *
	 * @todo Wenn der Treffer Tags enthaelt, wird ein Fehler ausgeloest - Protokoll oder E-Mail Benachrichtigung?
	 */
	public function check() {
		if (in_array(PAGE_ID, $this->ignorePageIDs)) {
			return false;
		}
		$dbGlossary = new dbGlossary();
		$dbGlossaryLiterature = new dbGlossaryLiterature();
		$matches = array();
		$general_pattern = sprintf('/%s(.*?)%s/', $this->pregSearchPatternL, $this->pregSearchPatternR);
		$general_literature = sprintf('/%s(.*?)%s/', $this->pregAddLitPatternL, $this->pregAddLitPatternR);
		preg_match_all($general_pattern, $this->body, $matches);
		foreach ($matches[1] as $match) {
			// Fundstelle
			$notes = array();
			$leading = '';
			if (preg_match($general_literature, $match, $notes) == 1) {
				// es handelt sich um eine Fussnote
				$leading = str_replace($notes, '', $match);
				$note_remark = '';
				$note_source = '';
				$note_action = dbGlossary::note_remark;
				$notes_array = explode($this->pregSplitPattern, $notes[1]);
				foreach ($notes_array as $note) {
					if (strpos($note, $this->pregSplitAddPattern) > 0) {
						// Schluesselwort
						list($note_key, $note_value) = explode($this->pregSplitAddPattern, $note, 2);
						$note_key = strtolower(trim($note_key));
						switch($note_key):
						case 'f':
						case 'fn':
						case dbGlossary::note_footnote:
						case 'r': // Shortcut "remark"
						case dbGlossary::note_remark:
							$note_remark = $note_value;
							break;
						case 'l':
						case 'lit':
						case 'q:':
						case 'quelle':
						case 's': // Shortcut "source"
						case dbGlossary::note_source:
							$note_source = $note_value;
							break;
						endswitch;
					}
					else {
						// kein Schluesselwort
						$note_key = strtolower(trim($note));
						switch ($note_key):
						case dbGlossary::note_footnotes:
							// Fussnoten ausgeben
							$note_action = dbGlossary::note_footnotes;
							break;
						default:
							// kein Schluesselwort, Annahme: es handelt sich um eine Anmerkung
							$note_remark = $note;
							break;
						endswitch;
					}
				} // foreach
				if ($note_action == dbGlossary::note_remark) {
					// Fussnote hinzufuegen
					if (!empty($note_source)) {
						// es ist eine Quelle angegeben
						$where = array();
						if (is_numeric($note_source)) {
							// Annahme: ID angegeben
							$where[dbGlossaryLiterature::field_id] = (int) $note_source;
						}
						else {
							// Annahme: Identifier angegeben
							$where[dbGlossaryLiterature::field_identifer] = $note_source;
						}
						$source = array();
						if (!$dbGlossaryLiterature->sqlSelectRecord($where, $source)) {
							$this->setError(sprintf('[%s - %s] - %s', __METHOD__, __LINE__, $dbGlossaryLiterature->getError()));
							return false;
						}
						if (count($source) > 1) {
							// mehr als eine Quelle gefunden
							$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
								sprintf(gl_error_filter_multiple_source,
												$note_source,
												sizeof($source),
												$source[0][dbGlossaryLiterature::field_id],
												$source[0][dbGlossaryLiterature::field_identifer])));
							// die erste Quelle verwenden
							$note_source = $source[0][dbGlossaryLiterature::field_id];
						}
						elseif (count($source) == 1) {
							// genau eine Quelle gefunden
							$note_source = $source[0][dbGlossaryLiterature::field_id];
						}
						else {
							// kein Treffer
							$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
								sprintf(gl_error_filter_no_source, $note_source)));
							$note_source = -1;
						}
					}
					else {
						// keine Quelle angegeben
						$note_source = -1;
					}
					if ((!empty($note_remark)) || ($note_source != -1)) {
						// nur Aktion, wenn Anmerkung nicht leer oder Quelle gueltig
						$footnote = array();
						$count = sizeof($this->footnotes)+1;
						$footnote[dbGlossaryFootnotes::field_note_id] = $count;
						$footnote[dbGlossaryFootnotes::field_page_id] = PAGE_ID;
						$footnote[dbGlossaryFootnotes::field_remark] = $note_remark;
						$footnote[dbGlossaryFootnotes::field_source] = $note_source;
						$footnote[dbGlossaryFootnotes::field_update_when] = date('Y-m-d H:i:s');
						// Fussnote in das Sammelarray uebernehmen
						$this->footnotes[] = $footnote;
						// Fussnote einsetzen
						$replace = $leading;
						$replace .= str_replace('{count}', $count, $this->template_literature);
						$this->body = str_replace(stripslashes($this->pregSearchPatternL).$match.stripslashes($this->pregSearchPatternR), $replace, $this->body);
					}
					else {
						// Fehler beim Ermitteln der Fussnote, Ausgabe bereinigen
						$this->body = str_replace(stripslashes($this->pregSearchPatternL).$match.stripslashes($this->pregSearchPatternR), $leading, $this->body);
					}
				}
				else {
					// Fussnoten ausgeben
					$footer = '';
					foreach ($this->footnotes as $footnote) {
						if ($footnote[dbGlossaryFootnotes::field_source] != -1) {
							// Literaturquelle zitieren
							$where = array();
							$where[dbGlossaryLiterature::field_id] = $footnote[dbGlossaryFootnotes::field_source];
							$quelle = array();
							if (!$dbGlossaryLiterature->sqlSelectRecord($where, $quelle)) {
								$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbGlossaryLiterature->getError()));
								return false;
							}
							$quelle = $quelle[0];
  						//($quelle[dbGlossaryLiterature::field_monographie] == 1) ? $and_others = '' : $and_others = ' '.gl_text_and_others;
  						(!empty($quelle[dbGlossaryLiterature::field_subtitle])) ? $subtitle = sprintf('. %s', $quelle[dbGlossaryLiterature::field_subtitle]) : $subtitle = '';
  						(!empty($quelle[dbGlossaryLiterature::field_edition])) ? $edition = sprintf(' %s, ', $quelle[dbGlossaryLiterature::field_edition]) : $edition = '';
  						$search  = array('{author}', '{title}', '{subtitle}', '{pub_place}', '{edition}', '{pub_year}', '{isbn}', '{url}');
  						$replace = array(	$quelle[dbGlossaryLiterature::field_author],
  															$quelle[dbGlossaryLiterature::field_title],
  															$subtitle,
  															$quelle[dbGlossaryLiterature::field_published_place],
  															$edition,
  															$quelle[dbGlossaryLiterature::field_published_year],
  															$quelle[dbGlossaryLiterature::field_isbn],
  															$quelle[dbGlossaryLiterature::field_url]);
  						$literatur = str_ireplace($search, $replace, $this->template_source);

  						(empty($footnote[dbGlossaryFootnotes::field_remark])) ? $remark = '' : $remark = sprintf(', %s', $footnote[dbGlossaryFootnotes::field_remark]);
  						$search  = array('{number}', '{footnote}');
  						$replace = array($footnote[dbGlossaryFootnotes::field_note_id], $literatur.$remark);
  						$footer .= str_ireplace($search, $replace, $this->template_footnote);
						}
						else {
							// nur Anmerkung ausgeben
							$search = array('{number}', '{footnote}');
							$replace = array($footnote[dbGlossaryFootnotes::field_note_id], $footnote[dbGlossaryFootnotes::field_remark]);
							$footer .= str_ireplace($search, $replace, $this->template_footnote);
						}
					}
					if (empty($footer)) {
						// keine Fussnoten ausgeben, Ausgabe bereinigen
						$this->body = str_replace(stripslashes($this->pregSearchPatternL).$match.stripslashes($this->pregSearchPatternR), '', $this->body);
					}
					else {
						// Fussnoten ausgeben
						$this->body = str_replace(stripslashes($this->pregSearchPatternL).$match.stripslashes($this->pregSearchPatternR), sprintf('<div class="fn_footer">%s</div>', $footer), $this->body);
					}
				}
			}
			else {
				// Abkuerzung, Stichwort....
				$where = array();
				$where[dbGlossary::field_item] = ($this->entities2umlauts) ? entities_to_umlauts($match) : $match;
				$where[dbGlossary::field_status] = dbGlossary::status_active;
				$catchword = array();
				if (!$dbGlossary->sqlSelectRecord($where, $catchword)) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
					return false;
				}
				if (sizeof($catchword) > 0) {
					// Treffer
					$process = true;
					$catchword = $catchword[0];
					if ($catchword[dbGlossary::field_type] == dbGlossary::type_db_glossary) {
						// dbGlossary: Verweis
						$where = array();
						$where[dbGlossary::field_item] = $catchword[dbGlossary::field_explain];
						$where[dbGlossary::field_status] = dbGlossary::status_active;
						$verweis = array();
						if (!$dbGlossary->sqlSelectRecord($where, $verweis)) {
							$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
							return false;
						}
						if (sizeof($verweis) > 0) {
							$catchword = $verweis[0];
						}
						else {
							$process = false;
						}
					}
					if ($process) {
						$search  = array('{explain}', '{catchword}', '{link}', '{target}', '{error}');
						$replace = array(	$catchword[dbGlossary::field_explain],
															$match,
															$catchword[dbGlossary::field_link],
															$dbGlossary->target_array[$catchword[dbGlossary::field_target]],
															gl_error_missing_spot);
						switch ($catchword[dbGlossary::field_type]):
						case dbGlossary::type_abbreviation:
							$replace = str_ireplace($search, $replace, $this->template_abbr);
							break;
						case dbGlossary::type_acronym:
							$replace = str_ireplace($search, $replace, $this->template_acronym);
							break;
						case dbGlossary::type_text:
							$replace = str_ireplace($search, $replace, $this->template_text);
							break;
						case dbGlossary::type_link:
							$replace = str_ireplace($search, $replace, $this->template_link);
							break;
						case dbGlossary::type_html:
							$replace[0] = htmlspecialchars($replace[0]);
							$replace = str_ireplace($search, $replace, $this->template_html);
							break;
						default:
							// Stichwort nicht gefunden
							if ($this->show_missing_spots) {
								$replace = str_ireplace($search, $replace, $this->template_missing_spot);
							}
							else {
								$replace = str_ireplace($search, $replace, '{catchword}');
							}
							break;
						endswitch;
						if (strpos($match, '</') == false)
							// PROBLEM: wenn die zu parsende Stelle Tags enthaelt </span> wird ein Fehler ausgeloest...
							$this->body = preg_replace('/'.$this->pregSearchPatternL.$match.$this->pregSearchPatternR.'/', $replace, $this->body);
					}
					elseif ($this->show_missing_spots) {
						$search = array('{catchword}', '{error}');
						$replace = array($match, gl_error_missing_spot);
						$replace = str_ireplace($search, $replace, $this->template_missing_spot);
						$this->body = str_replace(stripslashes($this->pregSearchPatternL).$match.stripslashes($this->pregSearchPatternR), $replace, $this->body);
					}
					else {
						$this->body = str_replace(stripslashes($this->pregSearchPatternL).$match.stripslashes($this->pregSearchPatternR), $match, $this->body);
					}
				}
				elseif ($this->show_missing_spots) {
					$search = array('{catchword}', '{error}');
					$replace = array($match, gl_error_missing_spot);
					$replace = str_ireplace($search, $replace, $this->template_missing_spot);
					$this->body = str_replace(stripslashes($this->pregSearchPatternL).$match.stripslashes($this->pregSearchPatternR), $replace, $this->body);
				}
				else {
					$this->body = str_replace(stripslashes($this->pregSearchPatternL).$match.stripslashes($this->pregSearchPatternR), $match, $this->body);
				}
			}
		}
	} // check()

} // class filterGlossary
?>