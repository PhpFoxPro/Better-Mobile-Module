<?php
// purpose decrease sql queries by save variable in global vars
class BDVar {
	
	// class instance
	private static $__instance;
	public $vars = array();
	
	// get instance of class
	public static function getInstance() {
		
		// init instance
		if (!isset(self::$__instance)){
			self::$__instance = new BDVar();
		}
		
		return self::$__instance;
	}
	
	// write var
	public static function write($var, $data) {
		$BDVar = BDVar::getInstance();
		$parts = explode('.', $var);
		
		$pointer =& $BDVar->vars;
		
		$i = 0;
		foreach ($parts as $part) {
			if ($i == count($parts) - 1) {
				break;
			}
			$pointer[$part] = array();
			$i++;
		}
		
		switch (count($parts)) {
			case 1:
				$BDVar->vars[$parts[0]] = $data;
				break;
			case 2:
				$BDVar->vars[$parts[0]][$parts[1]] = $data;
				break;
			case 3:
				$BDVar->vars[$parts[0]][$parts[1]][$parts[2]] = $data;
				break;
			case 4:
				$BDVar->vars[$parts[0]][$parts[1]][$parts[2]][$parts[3]] = $data;
				break;
		}
	}
	
	// read var
	public static function read($var) {
		// !isset() -> return false
		if (!BDVar::check($var)) {
			return false;
		}
		
		$BDVar = BDVar::getInstance();
		$parts = explode('.', $var);
		switch (count($parts)) {
			case 1:
				return $BDVar->vars[$parts[0]];
				break;
			case 2:
				return $BDVar->vars[$parts[0]][$parts[1]];
				break;
			case 3:
				return $BDVar->vars[$parts[0]][$parts[1]][$parts[2]];
				break;
			case 4:
				return $BDVar->vars[$parts[0]][$parts[1]][$parts[2]][$parts[3]];
				break;
		}
	}
	
	// check if var is existed
	public static function check($var) {
		$BDVar = BDVar::getInstance();
		$parts = explode('.', $var);
		switch (count($parts)) {
			case 1:
				return isset($BDVar->vars[$parts[0]]);
				break;
			case 2:
				return isset($BDVar->vars[$parts[0]][$parts[1]]);
				break;
			case 3:
				return isset($BDVar->vars[$parts[0]][$parts[1]][$parts[2]]);
				break;
			case 4:
				return isset($BDVar->vars[$parts[0]][$parts[1]][$parts[2]][$parts[3]]);
				break;
		}
	}
	
}