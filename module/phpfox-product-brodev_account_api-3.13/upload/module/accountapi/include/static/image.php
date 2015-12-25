<?php
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author			Raymond Benc
 * @package 		Phpfox
 * @version 		$Id: image.php 3608 2011-11-30 07:17:43Z Raymond_Benc $
 */

ob_start();

/**
 * Key to include phpFox
 *
 */
define('PHPFOX', true);

/**
 * Directory Seperator
 *
 */
define('PHPFOX_DS', DIRECTORY_SEPARATOR);

/**
 * phpFox Root Directory
 *
 */
define('PHPFOX_DIR', dirname(dirname(dirname(dirname(__FILE__)))) . PHPFOX_DS);

// Require phpFox Init
require(PHPFOX_DIR . 'include' . PHPFOX_DS . 'init.inc.php');

$sFile = PHPFOX_DIR . 'file' . PHPFOX_DS . 'pic' . PHPFOX_DS . 'accountapi' . PHPFOX_DS . $_GET['file'] . '.' . $_GET['ext'];
$sDesFile = $_GET['file']. '.'. $_GET['ext']; 

$_GET['file'] = base64_decode($_GET['file']);

// include library
include_once PHPFOX_DIR . 'module/accountapi/include/library/resize-class.php';
require_once(PHPFOX_DIR_LIB . 'amazons3/S3.php');
$oS3Object = new S3(Phpfox::getParam('core.amazon_access_key'), Phpfox::getParam('core.amazon_secret_key'));

$sImage = strtok($_GET['file'], '?');
$iWidth = (empty($_REQUEST['width']) ? 0 : $_REQUEST['width'] * 2);
$iHeight = (empty($_REQUEST['height']) ? 0 : $_REQUEST['height'] * 2);

if (empty($iHeight)) {
	$iHeight = $iWidth;
	$sOption = 'landscape';
} 
if (empty($iWidth))  {
	$iWidth = $iHeight;
	$sOption = 'portrait';
}
$sOption = (!empty($_REQUEST['width']) && !empty($_REQUEST['height'])) ? 'crop' : $sOption;

$sOutputDir = Phpfox::getParam('core.dir_pic') . 'accountapi'. PHPFOX_DS. $_REQUEST['width']. PHPFOX_DS. $_REQUEST['height']. PHPFOX_DS;
Phpfox::getLib('file')->mkdir($sOutputDir, true, '777');

// get resource and destination path, include download from amazon s3 if needed.
function getFilePath($sImage) {
	global $oS3Object, $sOutputDir, $sOption, $iWidth, $iHeight, $sExt, $sDestFile;
	$sLocalPath = substr($sImage, strpos($sImage, 'file/pic/'));
	$aFile = pathinfo($sImage);
	$sExt = $aFile['extension'];
	$sDestPath = $sOutputDir. $sDestFile;
	$sResourcePath = 'tmp'. PHPFOX_DS. md5($sImage). '.'. $sExt;
	if (strpos($sImage, 'amazon')) {
		if (!file_exists($sResourcePath)) {
			$oS3Object->getObject(Phpfox::getParam('core.amazon_bucket'), $sLocalPath, $sResourcePath);	
		}
		$sResourcePath = $sTmpImage;
	} elseif (is_numeric(strpos($sImage, Phpfox::getParam('core.path')))) {
		$sResourcePath = PHPFOX_DIR. $sLocalPath;
	} else {
		if (!file_exists($sResourcePath)) {
			grabImage($sImage, $sResourcePath);
		}
	}

	return array($sResourcePath, $sDestPath);	
}

// File name & location
$sExt = '';

list($sImage, $sOutputFile) = getFilePath($sImage);

$sOutputFile .= PHPFOX_DS. $sDesFile;

if (!file_exists($sImage)) {
	header('HTTP/1.0 404 Not Found');
	exit;
}

// Check file existance
if (!file_exists($sOutputFile)) {
	$oResize = new resize($sImage);
	$oResize -> resizeImage($iWidth, $iHeight, $sOption);
	$oResize -> saveImage($sOutputFile, 100);
}

// return image
ob_clean();
header('Content-Type: image/'. $sExt);
echo file_get_contents($sOutputFile);
ob_flush();
exit;

function grabImage($url, $saveto) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
	$raw = curl_exec($ch);
	curl_close($ch);
	if (file_exists($saveto)) {
		unlink($saveto);
	}
	$fp = fopen($saveto, 'x');
	fwrite($fp, $raw);
	fclose($fp);
}