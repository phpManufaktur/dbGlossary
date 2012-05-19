<?php

/**
 * dbGlossary
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2009 - 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: DE.php 16 2011-07-19 16:04:28Z phpmanufaktur $
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

// Deutsche Modulbeschreibung
$module_description 	= 'dbGlossary erm&ouml;glicht die automatische Verschlagwortung von Abk&uuml;rzungen, Akronymen oder Begriffen mit Tooltipps oder durch Verlinkung.';
// name of the person(s) who translated and edited this language file
$module_translation_by = 'Ralf Hertsch (phpManufaktur)';

define('gl_btn_abort',										'Abbruch');
define('gl_btn_export',										'Exportieren');
define('gl_btn_import',										'Importieren');
define('gl_btn_ok',												'Übernehmen');

define('gl_desc_cfg_a_z_tabs',						'Aufteilung der A-Z Karteireiter für die Ausgabe von Glossaren und Literaturlisten');
define('gl_desc_cfg_a_z_tabs_item',				'Replacement für einzelne A-Z Karteireiter (Tabs)');
define('gl_desc_cfg_a_z_tabs_start_empty','Wenn Sie möchten, dass zunächst nur die A-Z Karteireiter angezeigt werden, jedoch noch kein Inhalt, setzen Sie 1 ansonsten 0');
define('gl_desc_cfg_a_z_tabs_use',				'A-Z Karteireiter (Tabs) ein- oder ausschalten, Tabs mit einem | trennen (0=AUS, 1=AN)');
define('gl_desc_cfg_developer_mode',			'Ermöglicht dem Programmierer das Hinzufügen von Konfigurationsparametern.');
define('gl_desc_cfg_entities_2_umlauts',	'Wandelt vor der Prüfung im Ausgabefilter maskierte Sonderzeichen in Fundstellen, z.B. <b>&amp;uml;</b> in <b>ä</b>, um.');
define('gl_desc_cfg_group_array',					'Frei definierte Gruppen zur Gliederung der Stichworte, Einträge jeweils mit Komma trennen. Der erste Eintrag ist gleichzeitig der Vorgabewert!');
define('gl_desc_cfg_active',							'dbGlossary Ausführen');
define('gl_desc_cfg_link_check',					'Prüfung von externen Links ein- oder ausschalten (0=AUS, 1=AN)');
define('gl_desc_cfg_link_extern',					'Icon für die Darstellung von externen Links (im \MEDIA Verzeichnis ablegen)');
define('gl_desc_cfg_link_intern',					'Icon für die Darstellung von internen Links (im \MEDIA Verzeichnis ablegen)');
define('gl_desc_cfg_lit_group_array',			'Frei definierbare Gruppen zur Gliederung der Literaturliste, die Einträge jeweils mit einem Komma trennen. Der erste Eintrag ist gleichzeitig der Vorgabewert.');
define('gl_desc_cfg_type_literature',			'Replacement fur Literaturangaben und freie Anmerkungen');
define('gl_desc_cfg_type_missing_spot',		'Replacement für fehlerhafte oder nicht vorhandene Stichworte.');
define('gl_desc_cfg_page_id',							'Ignoriere die Seiten mit den angegebenen ID\'s - ID\'s mit Komma trennen.');
define('gl_desc_cfg_show_missing_spots',	'Markiert fehlende Fundstellen im Frontend farblich');
define('gl_desc_cfg_type_abbr',						'Replacement für Stichworte vom Typ <i>Abkürzung</i>.');
define('gl_desc_cfg_type_acronym',				'Replacement für Stichworte vom Typ <i>Akronym</i>.');
define('gl_desc_cfg_type_footnote',				'Replacement für die Ausgabe von <i>Fußnoten</i> am Seitenende');
define('gl_desc_cfg_type_html',						'Replacement für Stichworte vom Typ <i>Stichwort, HTML</i> (darf auch HTML Code enthalten).');
define('gl_desc_cfg_type_source',					'Replacement für <i>Literaturquellen</i> die in Fußnoten ausgegeben werden.');
define('gl_desc_cfg_type_text',						'Replacement für Stichworte vom Typ <i>Stichwort, TEXT</i> (darf nur TEXT enthalten).');
define('gl_desc_cfg_type_link',						'Replacement für Stichworte vom Typ <i>Querverweis</i>.');

define('gl_error_addon_version',     		 	'<p>Fataler Fehler: <b>dbGlossary</b> benoetigt das Website Baker Addon <b>%s</b> ab der Version <b>%01.2f</b> - installiert ist die Version <b>%01.2f</b>.</p><p>Bitte aktualisieren Sie zunaechst dieses Addon.</p>');
define('gl_error_cfg_group_empty',				'<p>Die <i>Gruppen</i> müssen mindestens einen Eintrag enthalten!</p>');
define('gl_error_cfg_id',									'<p>Der Konfigurationsdatensatz mit der <b>ID %05d</b> konnte nicht ausgelesen werden!</p>');
define('gl_error_cfg_name',								'<p>Zu dem Bezeichner <b>%s</b> wurde kein Konfigurationsdatensatz gefunden!</p>');
define('gl_error_csv_move_error',					'<p>Bei der Übertragung der Datei <b>%s</b> ist ein Fehler aufgetreten.</p>');
define('gl_error_csv_no_file',						'<p>Es wurde keine Datei für die Datenübertragung ausgewählt.</p>');
define('gl_error_empty_abc_tab',					'<div style="text-align:center;padding:15px 0 15px 0;"><p>Die Untergruppe <b>%s</b> enthält keinen Eintrag!</p></div>');
define('gl_error_explain_empty',					'<p>Das Feld <i>Erläuterung</i> darf nicht leer sein oder Sie müssen eine <i>Verknüpfung</i> für das Stichwort definieren.</p>');
define('gl_error_filter_multiple_source',	'<p>Für den Suchbegriff \'<b>%s</b>\' wurden insgesamt <b>%d</b> Literaturquellen gefunden, es wird die erste gefundene <b>ID %d (%s)</b> verwendet.</p>');
define('gl_error_filter_no_source',				'<p>Der Suchbegriff \'<b>%s</b>\' führte nicht zu einem Treffer in den Literaturquellen, bitte prüfen Sie die Angabe.</p>');
define('gl_error_item_exists',						'<p>Das <i>Stichwort</i> <b>%s</b> kann nicht hinzugefügt werden, es existiert mit der <b>ID %05d</b> bereits ein entsprechender Eintrag!</p>');
define('gl_error_item_id',								'<p>Zu der <b>ID %05d</b> existiert kein Datensatz!</p>');
define('gl_error_item_length',						'<p>Die Länge eines <i>Stichwort</i> muss mindestens 2 Zeichen betragen!</p>');
define('gl_error_link_status',						'<p>Der Link <b>%s</b> ist ungültig.<br>Es wird folgender Status zurückgegeben: <b>%s</b>.</p>');
define('gl_error_list_link_invalid',			'<p>Der Verweis von <b>%s</b> auf die Definition <b>%s</b> ist ungültig.</p>');
define('gl_error_missing_addon',    		 	'<p>Fataler Fehler: <b>dbGlossary</b> benoetigt das Website Baker Addon <b>%s</b>, die Programmausfuehrung wurde gestoppt.</p>');
define('gl_error_missing_output_filter',	'<p>Fataler Fehler: <b>dbGlossary</b> kann den Ausgabefilter nicht finden, bitte wenden Sie sich an den Support!</p>');
define('gl_error_source_empty_field',			'<p>Das Feld <b>%s</b> darf nicht leer sein, bitte prüfen Sie Ihre Eingabe!</p>');
define('gl_error_type_target_invalid',		'<p>Der <i>dbGlossary: Verweis</i> <b>%s</b> kann nicht hinzugefügt werden, da das angegebene <i>Ziel</i> <b>%s</b> ebenfalls ein <i>dbGlossary: Verweis</i> ist. Bitte w�hlen Sie ein g�ltiges Ziel.</p>');
define('gl_error_type_target_missing',		'<p>Der <i>dbGlossary: Verweis</i> <b>%s</b> kann nicht hinzugefügt werden, da das angegebene <i>Ziel</i> <b>%s</b> nicht gefunden wurde.</p>');
define('gl_error_type_undefined',					'<p>Bitte definieren Sie den <i>Stichwort Typ</i>!</p>');
define('gl_error_type_unknown',						'<span style="font-weight:bold;color:#ff0000;" title="Der Stichwort-Typ mit der Kennung %d ist nicht ausreichend definiert!">{catchword}</span>');
define('gl_error_missing_spot',						'Das Stichwort wurde nicht gefunden oder der Stichwort-Typ ist nicht ausreichend definiert.');

define('gl_exec_auto',										'Automatisch');
define('gl_exec_manual',									'Manuell');

define('gl_header_cfg_description',				'Beschreibung');
define('gl_header_cfg_identifier',				'Bezeichner');
define('gl_header_cfg_label',							'Label');
define('gl_header_cfg_typ',								'Typ');
define('gl_header_cfg_value',							'Wert');
define('gl_header_cfg',										'Einstellungen');
define('gl_header_catchword_edit',				'Stichwort bearbeiten');
define('gl_header_catchword_new',					'Neues Stichwort erstellen');
define('gl_header_glossary',							'Glossar');
define('gl_header_glossary_edit',					'');
define('gl_header_glossary_group',				'Gruppe');
define('gl_header_glossary_id',						'ID');
define('gl_header_glossary_status',				'Status');
define('gl_header_glossary_stichwort',		'Stichwort');
define('gl_header_glossary_target',				'Ziel');
define('gl_header_glossary_typ',					'Typ');
define('gl_header_glossary_update_when',	'Geändert');
define('gl_header_lit_id',								'ID');
define('gl_header_lit_author',						'Autor');
define('gl_header_lit_edit',							'');
define('gl_header_lit_identifier',				'Bezeichner');
define('gl_header_lit_footnote',					'Fußnote');
define('gl_header_prompt_error',    		 	'[dbGlossary] Fehlermeldung');
define('gl_header_source_new',						'Neue Literaturquelle erstellen');
define('gl_header_source_edit',						'Literaturquelle bearbeiten');

define('gl_intro_cfg',										'<p>Bearbeiten Sie die Einstellungen für dbGlossary.</p>');
define('gl_intro_cfg_add_item',						'<p>Das Hinzufügen von Einträgen zur Konfiguration ist nur sinnvoll, wenn die angegebenen Werte mit dem Programm korrespondieren.</p>');
define('gl_intro_catchword_edit',					'<p>Mit diesem Dialog bearbeiten Sie einen bereits existierendes Stichwort für dbGlossary.</p>');
define('gl_intro_catchword_new',					'<p>Mit Hilfe dieses Dialog erstellen Sie einen neues Stichwort für dbGlossary.</p>');
define('gl_intro_glossary',								'<p>Das <b>Glossar</b> enthält z.Zt. <b>%d</b> Datensätze. Klicken Sie auf die <b>ID</b> um ein <i>Stichwort</i> zu verändern, wählen Sie den Reiter <b>Stichwort</b>, um ein neues <i>Stichwort</i> zu erstellen.</p>');
define('gl_intro_literature',							'<p>Die <b>Literaturliste</b> enthält z.Zt. <b>%d</b> Datensätze. Klicken Sie auf die <b>ID</b> um eine <i>Literaturquelle</i> zu verändern, wählen Sie den Reiter <b>Quelle</b>, um eine neue <i>Literaturquelle</i> zu erstellen.</p>');
define('gl_intro_source_new',							'<p>Mit Hilfe dieses Dialog fügen Sie eine neue Literaturquelle zur Literaturliste von dbGlossary hinzu</p>');
define('gl_intro_source_edit',						'<p>Mit diesem Dialog bearbeiten Sie eine bereits existierende Literaturquelle in dbGlossary.</p>');

define('gl_label_author_lastname',				'Autor: Nachname');
define('gl_label_author_firstname',				'Autor: Vorname');
define('gl_label_authors',								'Autor(en), Herausgeber');
define('gl_label_cfg_a_z_tabs',						'A-Z Tabs');
define('gl_label_cfg_a_z_tabs_item',			'Typ: A-Z Tab');
define('gl_label_cfg_a_z_tabs_start_empty','Nur Tabs anzeigen');
define('gl_label_cfg_a_z_tabs_use',				'A-Z Tabs verwenden');
define('gl_label_cfg_developer_mode',			'Programmierer Modus');
define('gl_label_cfg_entities_2_umlauts',	'Sonderzeichen demaskieren');
define('gl_label_cfg_group_array',				'Stichwort Gruppen');
define('gl_label_cfg_active',							'dbGlossary Ausführen');
define('gl_label_cfg_link_check',					'Links prüfen');
define('gl_label_cfg_link_extern',				'Icon: Externer Link');
define('gl_label_cfg_link_intern',				'Icon: Interner Link');
define('gl_label_cfg_lit_group_array',		'Literatur Gruppen');
define('gl_label_cfg_type_literature',		'Typ: Literatur, Anmerkung');
define('gl_label_cfg_type_missing_spot',	'Typ: Fehlerhafte Stichworte');
define('gl_label_cfg_page_id',						'Ignoriere Page ID\'s');
define('gl_label_cfg_show_missing_spots',	'Zeige fehlende Stichworte');
define('gl_label_cfg_type_abbr',					'Typ: Abkürzung (ABBR)');
define('gl_label_cfg_type_acronym',				'Typ: Akronym (ACRONYM)');
define('gl_label_cfg_type_footnote',			'Typ: Fußnote');
define('gl_label_cfg_type_html',					'Typ: Stichwort (HTML)');
define('gl_label_cfg_type_source',				'Typ: Quelle');
define('gl_label_cfg_type_link',					'Typ: Querverweis');
define('gl_label_cfg_type_text',					'Typ: Stichwort (TEXT)');
define('gl_label_csv_import',							'CSV Import');
define('gl_label_csv_export',							'CSV Export');
define('gl_label_explain',								'Erläuterung');
define('gl_label_group',									'Gruppe');   
define('gl_label_id',											'ID');
define('gl_label_identifier',							'Bezeichner');
define('gl_label_source_edition',					'Auflage');
define('gl_label_source_isbn',						'ISBN');
define('gl_label_source_monographie',			'Monographie');
define('gl_label_source_published_place', 'Erscheinungsort');
define('gl_label_source_published_year',	'Erscheinungsjahr');
define('gl_label_source_subtitle',				'Untertitel');
define('gl_label_source_title',						'Titel');
define('gl_label_source_type',						'Art der Publikation');
define('gl_label_source_url',							'URL, z.B. amazon.de');
define('gl_label_stichwort',							'Stichwort');
define('gl_label_type',										'Stichwort Typ');
define('gl_label_link',										'Verknüpfung');
define('gl_label_status',									'Status');
define('gl_label_target',									'Verweisziel');
define('gl_label_update_when',						'Aktualisiert am');
define('gl_label_update_by',							'Aktualisiert von');

define('gl_msg_cfg_add_exists',						'<p>Der Konfigurationsdatensatz mit dem Bezeichner <b>%s</b> existiert bereits und kann nicht noch einmal hinzugef�gt werden!</p>');
define('gl_msg_cfg_add_incomplete',				'<p>Der neu hinzuzufügende Konfigurationsdatensatz ist unvollständig! Bitte prüfen Sie Ihre Angaben!</p>');
define('gl_msg_cfg_add_success',					'<p>Der Konfigurationsdatensatz mit der <b>ID #%05d</b> und dem Bezeichner <b>%s</b> wurde hinzugef�gt.</p>');
define('gl_msg_cfg_csv_export',						'<p>Die Konfigurationsdaten wurden als <b>%s</b> im /MEDIA Verzeichnis gesichert.</p>');
define('gl_msg_cfg_id_updated',						'<p>Der Konfigurationsdatensatz mit der <b>ID #%05d</b> und dem Bezeichner <b>%s</b> wurde aktualisiert.</p>');
define('gl_msg_csv_export_liste_all',			'<p>Es wurden alle Gruppen als <b>%s</b> in das /MEDIA Verzeichnis exportiert.</p>');
define('gl_msg_csv_export_liste_grp',			'<p>Die Gruppe <b>%s</b> wurde als <b>%s</b> in das /MEDIA Verzeichnis exportiert.</p>');
define('gl_msg_csv_export_literature',		'<p>Die Literaturliste wurde als <b>%s</b> in das /MEDIA Verzeichnis exportiert.</p>');
define('gl_msg_csv_file_moved',						'<p>Die Datei <b>%s</b> wurde als <b>%s</b> im /MEDIA Verzeichnis gesichert.</p>');
define('gl_msg_csv_imp_glossary',					'<p>Es wurden die folgenden Stichworte importiert: <b>%s</b>.</p>');
define('gl_msg_csv_imp_gpsp',							'<p>Es wurden die folgenden Stichworte importiert:<br /><b>%s</b>.</p><p>Bei den folgenden Stichworten gab es Probleme, sie wurden auf "inaktiv" gesetzt:<br /><b>%s</b></p>');
define('gl_msg_csv_imp_no_glossary',			'<p>Es wurden keine Stichworte importiert.</p>');
define('gl_msg_csv_imp_literature',				'<p>Es wurden die folgenden Literaturquellen importiert: <b>%s</b>.</p>');
define('gl_msg_csv_imp_no_literature',		'<p>Es wurden keine Literaturquellen importiert.</p>');
define('gl_msg_invalid_email',						'<p>Die E-Mail Adresse <b>%s</b> ist nicht gültig, bitte prüfen Sie Ihre Eingabe.</p>');
define('gl_msg_item_add',									'<p>Der Datensatz mit der <b>ID %05d</b> wurde dem Glossar hinzugefügt.</p>');
define('gl_msg_item_update',							'<p>Der Datensatz mit der <b>ID %05d</b> im Glossar wurde aktualisiert.</p>');
define('gl_msg_list_empty',								'<p><em>- das Glossar  enthält z.Zt. noch keine Einträge -</em></p>');
define('gl_msg_list_tab_empty',						'<p><em>- für <b>%s</b> ist z.Zt. noch kein Eintrag hinterlegt -</em></p>');
define('gl_msg_output_filter_patched',		'<p><b>dbGlossary</b> hat den Ausgabefilter aktualisiert und ist einsatzbereit.</p>');
define('gl_msg_output_filter_not_patched','<p><b>dbGlossary</b> konnte den Ausgabefilter nicht automatische aktualisieren und ist deshalb nicht einsatzbereit.</p><p>Bitte suchen Sie in der Hilfe nach der Anleitung zum Anpassen des Ausgabefilters!</p>');
define('gl_msg_source_add',								'<p>Der Datensatz mit der <b>ID %05d</b> wurde der Literaturliste hinzugefügt.</p>');
define('gl_msg_source_update',						'<p>Der Datensatz mit der <b>ID %05d</b> in der Literaturliste wurde aktualisiert.</p>');

define('gl_status_active',								'Aktiv');
define('gl_status_deleted',								'Gelöscht');
define('gl_status_locked',								'Gesperrt');

define('gl_tab_config',										'Einstellungen');
define('gl_tab_help',											'?');
define('gl_tab_catchword',								'Stichwort');
define('gl_tab_glossary',									'Glossar');
define('gl_tab_literature',								'Literatur');
define('gl_tab_source',										'Quelle');

define('gl_target_blank',									'_blank');
define('gl_target_parent',								'_parent');
define('gl_target_self',									'_self');
define('gl_target_top',										'_top');

define('gl_template_error',          		 	'<div style="margin:15px;padding:15px;border:1px solid #cc0000;color: #cc0000; background-color:#ffffdd;"><h1>%s</h1>%s</div>');

define('gl_type_abbreviation',						'Abkürzung (ABBR)');
define('gl_type_acronym',									'Akronym (ACRONYM)');
define('gl_type_html',										'Stichwort (HTML)');
define('gl_type_book',										'Buch, Allgemein');
define('gl_type_db_glossary',							'dbGlossary: Verweis');
define('gl_type_link',										'Querverweis');
define('gl_type_text',										'Stichwort (TEXT)');
define('gl_type_undefined',								'- nicht definiert -');

define('gl_text_all_groups',							'- alle Gruppen -');
define('gl_text_and_others',							'u.a.');
define('gl_text_link_extern',							'Symbol für externen Link'); // Description for external Link Icon
define('gl_text_link_intern',							'Symbol für internen Link'); // Description for internal Link Icon
define('gl_text_no_id',										'- keine ID -');
define('gl_text_undefined',								'- nicht definiert -');

?>