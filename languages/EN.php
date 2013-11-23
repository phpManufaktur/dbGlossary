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

// English description of module
$module_description     = 'dbGlossary enables automatic creation of glossaries for abbreviations, acronyms or terms with tooltipps or by linking.';

if (!defined('gl_btn_abort')) {
    define('gl_btn_abort', 'Abort');
    define('gl_btn_export', 'Export');
    define('gl_btn_import', 'Import');
    define('gl_btn_ok', 'OK');

    define('gl_desc_cfg_a_z_tabs', 'Configuuration of tabsl A-Z to output glossaries or literature lists');
    define('gl_desc_cfg_a_z_tabs_item', 'Replacement for individual tabs');
    define('gl_desc_cfg_a_z_tabs_use', 'Turnin tabs ON/OFF. Separate tabs with pipe |  (0=OFF, 1=On)');
    define('gl_desc_cfg_developer_mode', 'Enables programmmers to add configuration parameters.');
    define('gl_desc_cfg_entities_2_umlauts', 'Converts special masked characters prior to validation in the output filter, e.g. <b>&amp;uml;</b> is converted to <b>ä</b>.');
    define('gl_desc_cfg_group_array', 'Freely defined groups to structure keywords. Entries are separated by comma. The frist entry is default.!');
    define('gl_desc_cfg_active', 'dbGlossary activation');
    define('gl_desc_cfg_link_check', 'Turning validation of external links ON/OFF (0=OFF, 1=ON)');
    define('gl_desc_cfg_link_extern', 'Icon ifür die Darstellung von externen Links (im \MEDIA Verzeichnis ablegen)');
    define('gl_desc_cfg_link_intern', 'Icon to represent internal links (store it in \MEDIA directory)');
    define('gl_desc_cfg_lit_group_array', 'Freely defined groups to structure literature list.  Entries are separated by comma. The frist entry is default.!');
    define('gl_desc_cfg_type_literature', 'Replacement for literature references and free annotations');
    define('gl_desc_cfg_type_missing_spot', 'Replacement for erroneous or missing keywords.');
    define('gl_desc_cfg_page_id', 'Ignore pages with listed ID\'s - ID\'s are to be separated by comma.');
    define('gl_desc_cfg_show_missing_spots', 'Marks missing reference in front end by color.');
    define('gl_desc_cfg_type_abbr', 'Replacement for keywords of the type <i>abbreviation</i>.');
    define('gl_desc_cfg_type_acronym', 'Replacement for keywords of the type <i>acronym</i>.');
    define('gl_desc_cfg_type_footnote', 'Replacement for the output of <i>footnotes</i> at the end of a page');
    define('gl_desc_cfg_type_html', 'Replacement for keywords of the type <i>keyword, HTML</i> (may contain HTML code).');
    define('gl_desc_cfg_type_source', 'Replacement for <i>literature sources</i> which are listed in footnotes.');
    define('gl_desc_cfg_type_text', 'Replacement for keywords of the type <i>keyword, TEXT</i> (must not contain anything else but TEXT).');
    define('gl_desc_cfg_type_link', 'Replacement for keywords of the type <i>cross reference</i>.');

    define('gl_error_addon_version', '<p>Critical error: <b>dbGlossary</b> requires the Website Baker addon <b>%s</b> version <b>%01.2f</b> or later - installed version: <b>%01.2f</b>.</p><p>Please update this addon first!.</p>');
    define('gl_error_cfg_group_empty', '<p>The <i>groups</i> have to contain at least one item!</p>');
    define('gl_error_cfg_id', '<p>The configuration record with the <b>ID %05d</b> could not be read!</p>');
    define('gl_error_cfg_name', '<p>For identifier <b>%s</b> no configuration record could be found!</p>');
    define('gl_error_csv_move_error', '<p>While transferring file <b>%s</b> an error occured.</p>');
    define('gl_error_csv_no_file', '<p>No file for data transfer selected!.</p>');
    define('gl_error_empty_abc_tab', '<div style="text-align:center;padding:15px 0 15px 0;"><p>Subgroup <b>%s</b> is empty!</p></div>');
    define('gl_error_explain_empty', '<p>Field <i>Explanation</i> must not be empty or you have to define a <i>link</i> for the keyword.</p>');
    define('gl_error_filter_multiple_source', '<p>For this search \'<b>%s</b>\' a total of <b>%d</b> literature sources have been found. The first one (<b>ID %d (%s)</b>) will be used.</p>');
    define('gl_error_filter_no_source', '<p>The search for \'<b>%s</b>\' provided no finding in the literature sources, please check again!</p>');
    define('gl_error_item_exists', '<p><i>Keyword</i> <b>%s</b> cannot be added. It already exists under <b>ID %05d</b>!</p>');
    define('gl_error_item_id', '<p>For <b>ID %05d</b> there exists no data record!</p>');
    define('gl_error_item_length', '<p>The minimum length of a <i>keyword</i> is two characters!</p>');
    define('gl_error_link_status', '<p>Link <b>%s</b> is invalid.<br> The following status is returned: <b>%s</b>.</p>');
    define('gl_error_list_link_invalid', '<p>Referencing from <b>%s</b> to the definition <b>%s</b> is invalid.</p>');
    define('gl_error_missing_addon', '<p>Critical error: <b>dbGlossary</b> requires Website Baker Addon <b>%s</b>. Execution of program was terminated.</p>');
    define('gl_error_missing_output_filter', '<p>Critical error: <b>dbGlossary</b> cannot find the output filter. Please contact your support!</p>');
    define('gl_error_source_empty_field', '<p>Field <b>%s</b> must not be empty. Please check your entry!</p>');
    define('gl_error_type_target_invalid', '<p><i>dbGlossary: reference</i> <b>%s</b> cannot be added, since the <i>target</i> <b>%s</b> is also a <i>dbGlossary: reference</i>. Please select a valid target!</p>');
    define('gl_error_type_target_missing', '<p> <i>dbGlossary: reference</i> <b>%s</b> cannot be added, since <i>target</i> <b>%s</b> could not be found.</p>');
    define('gl_error_type_undefined', '<p>Please define the <i>type of the keyword</i>!</p>');
    define('gl_error_type_unknown', '<span style="font-weight:bold;color:#ff0000;" title="The keyword type %d is not defined in sufficient detail!">{catchword}</span>');
    define('gl_error_missing_spot', 'Keyword could not be found or keyword type is not defined in sufficient detail.');

    define('gl_exec_auto', 'Automatically');
    define('gl_exec_manual', 'Manually');

    define('gl_header_cfg_description', 'Description');
    define('gl_header_cfg_identifier', 'Identifier');
    define('gl_header_cfg_label', 'Label');
    define('gl_header_cfg_typ', 'Type');
    define('gl_header_cfg_value', 'Value');
    define('gl_header_cfg', 'Settings');
    define('gl_header_catchword_edit', 'Edit keyword');
    define('gl_header_catchword_new', 'Create new keyword');
    define('gl_header_glossary', 'Glossary');
    define('gl_header_glossary_edit', '');
    define('gl_header_glossary_group', 'Group');
    define('gl_header_glossary_id', 'ID');
    define('gl_header_glossary_status', 'Status');
    define('gl_header_glossary_stichwort', 'Keyword');
    define('gl_header_glossary_target', 'Tyrget');
    define('gl_header_glossary_typ', 'Type');
    define('gl_header_glossary_update_when', 'Updated');
    define('gl_header_lit_id', 'ID');
    define('gl_header_lit_author', 'Author');
    define('gl_header_lit_edit', '');
    define('gl_header_lit_identifier', 'Identifier');
    define('gl_header_lit_footnote', 'Footnote');
    define('gl_header_prompt_error', '[dbGlossary] Error message');
    define('gl_header_source_new', 'Create new literature source');
    define('gl_header_source_edit', 'Edit literature source');

    define('gl_intro_cfg', '<p>Edit configuration of dbGlossary.</p>');
    define('gl_intro_cfg_add_item', '<p>Adding entries to the configuration makes only sense when the values entered correspod with the program.</p>');
    define('gl_intro_catchword_edit', '<p>With this dialogue you edit an existing keyword for dbGlossary.</p>');
    define('gl_intro_catchword_new', '<p>This dialogue lets you create a new keyword for dbGlossary.</p>');
    define('gl_intro_glossary', '<p>The <b>glossary</b> currently contains <b>%d</b> data records. Klick on <b>ID</b> to edit a <i>keyword</i>, select the tab <b>keyword</b>, to create a new <i>keyword</i>.</p>');
    define('gl_intro_literature', '<p>The <b>literature list</b> currently contains <b>%d</b> data records. Klick on <b>ID</b> to edit a <i>literature source</i>, select the tab <b>source</b>, to create a new <i>literature source</i>.</p>');
    define('gl_intro_source_new', '<p>THis dialogue enables you to ad a new literature source to the literature list of dbGlossary.</p>');
    define('gl_intro_source_edit', '<p>With this dialogue you edit an existing literature source for dbGlossary.</p>');

    define('gl_label_author_lastname', 'Author: Last Name');
    define('gl_label_author_firstname', 'Author: First Name');
    define('gl_label_authors', 'Author(s), Editor');
    define('gl_label_cfg_a_z_tabs', 'A-Z Tabs');
    define('gl_label_cfg_a_z_tabs_item', 'Type: A-Z Tab');
    define('gl_label_cfg_a_z_tabs_use', 'Use A-Z Tabs');
    define('gl_label_cfg_developer_mode', 'Programming mode');
    define('gl_label_cfg_entities_2_umlauts', 'Demask special characters');
    define('gl_label_cfg_group_array', 'Groups of Keyword');
    define('gl_label_cfg_active', 'Activate dbGlossary');
    define('gl_label_cfg_link_check', 'Validate links');
    define('gl_label_cfg_link_extern', 'Icon: External link');
    define('gl_label_cfg_link_intern', 'Icon: Internal link');
    define('gl_label_cfg_lit_group_array', 'Literature groups');
    define('gl_label_cfg_type_literature', 'Type: Literatur, remarks');
    define('gl_label_cfg_type_missing_spot', 'Type: Erroneous keywords');
    define('gl_label_cfg_page_id', 'Ignore page ID\'s');
    define('gl_label_cfg_show_missing_spots', 'Show missing keywords');
    define('gl_label_cfg_type_abbr', 'Type: Abbreviation (ABBR)');
    define('gl_label_cfg_type_acronym', 'Type: Acronym (ACRONYM)');
    define('gl_label_cfg_type_footnote', 'Type: Footnote');
    define('gl_label_cfg_type_html', 'Type: Keyword (HTML)');
    define('gl_label_cfg_type_source', 'Type: Source');
    define('gl_label_cfg_type_link', 'Type: Cross-reference');
    define('gl_label_cfg_type_text', 'Type: Keyword (TEXT)');
    define('gl_label_csv_import', 'CSV Import');
    define('gl_label_csv_export', 'CSV Export');
    define('gl_label_explain', 'Explanation');
    define('gl_label_group', 'Group');
    define('gl_label_id', 'ID');
    define('gl_label_identifier', 'Identifier');
    define('gl_label_source_edition', 'Edition');
    define('gl_label_source_isbn', 'ISBN');
    define('gl_label_source_monographie', 'Monograph');
    define('gl_label_source_published_place', 'Place of publication');
    define('gl_label_source_published_year', 'Year of Publication');
    define('gl_label_source_subtitle', 'Subtitle');
    define('gl_label_source_title', 'Title');
    define('gl_label_source_type', 'Type of publication');
    define('gl_label_source_url', 'URL, i.e. amazon.com');
    define('gl_label_stichwort', 'Keyword');
    define('gl_label_type', 'Type of keyword');
    define('gl_label_link', 'Link');
    define('gl_label_status', 'Status');
    define('gl_label_target', 'Target');
    define('gl_label_update_when', 'Updated on');
    define('gl_label_update_by', 'Updated by');

    define('gl_msg_cfg_add_exists', '<p>A configuration data record with the identifier <b>%s</b> already exists and therefor cannot be added.!</p>');
    define('gl_msg_cfg_add_incomplete', '<p>The new configuration data record to be added is incomplet! Please check your entries!</p>');
    define('gl_msg_cfg_add_success', '<p>The configuration data record with <b>ID #%05d</b> and identifier <b>%s</b> has beeen added.</p>');
    define('gl_msg_cfg_csv_export', '<p>The configuration data have been saved as <b>%s</b> in directory /MEDIA.</p>');
    define('gl_msg_cfg_id_updated', '<p>The configuration data record with <b>ID #%05d</b> and identifier <b>%s</b> whas been updated.</p>');
    define('gl_msg_csv_export_liste_all', '<p>Es wurden alle Gruppen als <b>%s</b> in das /MEDIA Verzeichnis exportiert.</p>');
    define('gl_msg_csv_export_liste_grp', '<p>The group <b>%s</b> has been exported as <b>%s</b> into the directory /MEDIA.</p>');
    define('gl_msg_csv_export_literature', '<p>The group <b>%s</b> has been exported as <b>%s</b> into the directory /MEDIA.</p>');
    define('gl_msg_csv_file_moved', '<p>The file <b>%s</b> has been saved as <b>%s</b> in the directory /MEDIA.</p>');
    define('gl_msg_csv_imp_glossary', '<p>The following keywords have been imported: <b>%s</b>.</p>');
    define('gl_msg_csv_imp_no_glossary', '<p>No keywords have been imported.</p>');
    define('gl_msg_csv_imp_literature', '<p>The following literature sources have been imported: <b>%s</b>.</p>');
    define('gl_msg_csv_imp_no_literature', '<p>No literature sources have been imported.</p>');
    define('gl_msg_invalid_email', '<p>E-mail-address <b>%s</b> is not valid! Please check your entry!</p>');
    define('gl_msg_item_add', '<p>The data record with <b>ID %05d</b> has been added to the glossary.</p>');
    define('gl_msg_item_update', '<p>The data record with <b>ID %05d</b> has been updated in the glossary.</p>');
    define('gl_msg_output_filter_patched', '<p><b>dbGlossary</b> has actualized the output filter and is ready for use.</p>');
    define('gl_msg_output_filter_not_patched', '<p><b>dbGlossary</b> could not automatically actualize the output filter and is NOT ready for use.</p><p>Check Help to find instructions on how to adapt the output filter manually!</p>');
    define('gl_msg_source_add', '<p>The data record with <b>ID %05d</b> has been added to the literature list.</p>');
    define('gl_msg_source_update', '<p>The data record with <b>ID %05d</b> has been updated in the literature list.</p>');

    define('gl_status_active', 'Active');
    define('gl_status_deleted', 'Deleted');
    define('gl_status_locked', 'Locked');

    define('gl_tab_config', 'Settings');
    define('gl_tab_help', '?');
    define('gl_tab_catchword', 'Keyword');
    define('gl_tab_glossary', 'Glossary');
    define('gl_tab_literature', 'Literature');
    define('gl_tab_source', 'Source');

    define('gl_target_blank', '_blank');
    define('gl_target_parent', '_parent');
    define('gl_target_self', '_self');
    define('gl_target_top', '_top');

    define('gl_template_error', '<div style="margin:15px;padding:15px;border:1px solid #cc0000;color: #cc0000; background-color:#ffffdd;"><h1>%s</h1>%s</div>');

    define('gl_type_abbreviation', 'Abbreviation (ABBR)');
    define('gl_type_acronym', 'Acronym (ACRONYM)');
    define('gl_type_html', 'Keyword (HTML)');
    define('gl_type_book', 'Book, generell');
    define('gl_type_db_glossary', 'dbGlossary: Reference');
    define('gl_type_link', 'Cross-reference');
    define('gl_type_text', 'Keword (TEXT)');
    define('gl_type_undefined', '- undefined -');

    define('gl_text_all_groups', '- all groups -');
    define('gl_text_and_others', 'a.o.');
    define('gl_text_link_extern', ''); // Description for external Link Icon
    define('gl_text_link_intern', ''); // Description for internal Link Icon
    define('gl_text_no_id', '- no ID -');
    define('gl_text_undefined', '- undefined -');
}
