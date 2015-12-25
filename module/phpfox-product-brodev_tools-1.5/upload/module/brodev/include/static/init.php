<?php
// init
define('BRODEV_MODULE', PHPFOX_DIR . 'module' . PHPFOX_DS . 'brodev' . PHPFOX_DS);

// functions
require BRODEV_MODULE . 'include' . PHPFOX_DS . 'static' . PHPFOX_DS . 'functions.php';

// classes
$aBrodevClasses = array(
	'tools',
	'tpl',
	'database',
	'bdvar',
);

foreach ($aBrodevClasses as $sClassName) {
	require BRODEV_MODULE . 'include' . PHPFOX_DS . 'library' . PHPFOX_DS . $sClassName . '.class.php';
}
?>