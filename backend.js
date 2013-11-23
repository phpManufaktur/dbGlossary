/**
 * dbGlossary
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2009 - 2013
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

function glossaryChangeType(target_url, id_type, id_item, name_explain, id_link, id_target, id_group, id_status) {
	var target;
	target = target_url;
	// Type
	target += '&'+id_type+'='+document.getElementById(id_type).value;
	// Item
	target += '&'+id_item+'='+document.getElementById(id_item).value;
	// Explain
	var txt = document.getElementsByName(name_explain)[0];
	target += '&'+name_explain+'='+txt.value;
	// Link
	target += '&'+id_link+'='+document.getElementById(id_link).value;
	// Target
	target += '&'+id_target+'='+document.getElementById(id_target).value;
	// Group
	target += '&'+id_group+'='+document.getElementById(id_group).value;
	// Status
	target += '&'+id_status+'='+document.getElementById(id_status).value;	
	// Execute...
	window.location = encodeURI(target);
	return false;
}