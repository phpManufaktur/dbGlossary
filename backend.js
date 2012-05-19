/**
  Module developed for the Open Source Content Management System Website Baker (http://websitebaker.org)
  Copyright (c) 2009, Ralf Hertsch
  Contact me: hertsch(at)berlin.de, http://phpManufaktur.de

  This module is free software. You can redistribute it and/or modify it
  under the terms of the GNU General Public License  - version 2 or later,
  as published by the Free Software Foundation: http://www.gnu.org/licenses/gpl.html.

  This module is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  
  $Id: backend.js 10 2011-01-30 06:02:46Z phpmanufaktur $
  
**/

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