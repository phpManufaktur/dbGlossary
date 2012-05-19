//:Inserts a formatted LITERATURE definition list from dbGlossary
//:[[literature_list?groups=one,two]] groups: select groups separated by comma 
if (file_exists(WB_PATH.'/modules/dbglossary/class.droplets.php')) {
  require_once(WB_PATH.'/modules/dbglossary/class.droplets.php');
  if (isset($groups)) {
    $grps = split(",", $groups);
  }
  else {
    $grps = array();
  }
  return show_literature_list($grps);
}
else {
  $result = 'Error: Module dbGlossary not found!';
  return $result;
}