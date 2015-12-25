<?php
class BDTpl {
	// get var from template class
	static function getVar($index) {
		$indexes = explode('.', $index);
		$var = Phpfox::getLib('phpfox.template')->getVar($indexes[0]);
		$return = $var;
		
		// if not exists
		if (empty($return)) {
			return false;
		}
		
		for ($i = 1; $i < count($indexes); $i++) {
			if (!isset($var[$indexes[$i]])) {
				return false;
			}
			$return = $var[$indexes[$i]];
		}
		return $return;
	}
}
?>