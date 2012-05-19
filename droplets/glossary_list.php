//:Inserts a formatted GLOSSARY definition list from dbGlossary
//:[[glossary_list?link_intern=1&link_extern=1,groups=one,two]] link_intern: 1 = create internal links to crossreferenced catchwords, 0 = hide internal links link_extern: 1 = create external links to referenced definitions, 0 = hide external links groups: select groups separated by comma 
if (file_exists(WB_PATH.'/modules/dbglossary/class.droplets.php')) {
  require_once(WB_PATH.'/modules/dbglossary/class.droplets.php');
  isset($link_intern) ? $intern = (bool) $link_intern : $intern = true;
  isset($link_extern) ? $extern = (bool) $link_extern : $extern = true;
  if (isset($groups)) {
    $grps = split(",", $groups);
  }
  else {
    $grps = array();
  }
  return show_glossary_list($intern, $extern, $grps);
}
else {
  $result = 'Error: Module dbGlossary not found!';
  return $result;
}