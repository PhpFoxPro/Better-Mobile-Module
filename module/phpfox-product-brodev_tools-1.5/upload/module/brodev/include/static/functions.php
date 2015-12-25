<?php
// debug var_dump like
if (!function_exists('vd')) {
	function vd($var = false, $showHtml = false, $showFrom = true) {
		if ($showFrom) {
			$calledFrom = debug_backtrace();
			echo '<strong>' . substr($calledFrom[0]['file'], 1) . '</strong>';
			echo ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
		}
		echo "\n<pre class=\"brodev-debug\">\n";
	
		$var = print_r($var, true);
		if ($showHtml) {
			$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
		}
		echo $var . "\n</pre>\n";
	}
	
	// debug var_dump like then die
	function vdd($var = false, $showHtml = false, $showFrom = true) {
		if ($showFrom) {
			$calledFrom = debug_backtrace();
			echo '<strong>' . substr($calledFrom[0]['file'], 1) . '</strong>';
			echo ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
		}
		echo "\n<pre class=\"brodev-debug\">\n";
	
		$var = print_r($var, true);
		if ($showHtml) {
			$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
		}
		echo $var . "\n</pre>\n";
		die;
	}
}
?>