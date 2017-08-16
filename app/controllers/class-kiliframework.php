<?php

#-----------------------------------------------------------------
# Defaults constants for the parent and child theme
#-----------------------------------------------------------------
include_once get_template_directory() . '/config/defaults.php';

//Autoload Helpers.
foreach (glob(get_template_directory() . '/app/helpers/*/*.php') as $module) {
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