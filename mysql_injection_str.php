<?php
  $injection_str = array("/*","-- ","#","LOAD_FILE(");
  $value = str_ireplace($injection_str, "", $value);
?>
