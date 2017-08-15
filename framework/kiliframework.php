<?php

#-----------------------------------------------------------------
# Defaults constants for the parent and child theme
#-----------------------------------------------------------------
include_once 'defaults.php';
//Autoload Helpers.
foreach (glob(__DIR__ . '/helpers/*/*.php') as $module) {
  if (!$modulepath = $module) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'kiliframework'), $module), E_USER_ERROR);
  }
  require_once $modulepath;
}
unset($module, $filepath);

/**
 * Kili Main Class
 */
class KiliFramework {

  public function __construct() {

  }

}