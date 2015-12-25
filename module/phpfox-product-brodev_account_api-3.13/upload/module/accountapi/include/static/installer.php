<?php
$oRequest = Phpfox::getLib('phpfox.request'); if ($oRequest->get('upgrade')) { $sType = 'upgrade'; } else if ($oRequest->get('delete')) { $sType = 'uninstall'; } else if ($oRequest->get('install')) { $sType = 'install'; } $v8fb4f159 = Phpfox::getLib('phpfox.request'); if ($v8fb4f159->get('upgrade')) { $vc0bf0ed8 = 'upgrade'; } else if ($v8fb4f159->get('delete')) { $vc0bf0ed8 = 'uninstall'; } else if ($v8fb4f159->get('install')) { $vc0bf0ed8 = 'install'; } if ($vc0bf0ed8 != 'uninstall') { $v8c7dd922 = 'phpfox-product-brodev_account_api-3.13.zip'; $v2f8b4c76 = dirname(__FILE__); $v0a7c842c = dirname($v2f8b4c76) . '/license_key'; $v572d4e42 = '' . base64_decode('aHR0cDovL3d3dy5waHBmb3gucHJvL3BhZ2VzL2xpY2Vuc2UtaXNzdWU=') . ''; if (!file_exists($v0a7c842c)) { header('Location: ' . $v572d4e42); exit(); } $v71877975 = trim(file_get_contents($v0a7c842c)); if (empty($v71877975)) { header('Location: ' . $v572d4e42); exit(); } if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])) { header('Location: ' . $v572d4e42); exit(); } $vf6e57c9d = '' . base64_decode('aHR0cDovL3d3dy5waHBmb3gucHJvL2xpY2Vuc2VzL3ZlcmlmeS8=') . '' . $v71877975 . '/' . $_SERVER['HTTP_HOST'] . '/' . $v8c7dd922; $vd88fc6ed = curl_init(); curl_setopt($vd88fc6ed, CURLOPT_URL, $vf6e57c9d); curl_setopt($vd88fc6ed, CURLOPT_RETURNTRANSFER, 1); curl_setopt($vd88fc6ed, CURLOPT_TIMEOUT, 10); $vb4a88417 = curl_exec($vd88fc6ed); curl_close($vd88fc6ed); if (empty($vb4a88417)) { $vb4a88417 = file_get_contents($vf6e57c9d); } if (!empty($vb4a88417)) { $vb4a88417 = json_decode($vb4a88417, true); if (is_object($vb4a88417)) { $vb4a88417 = array( 'is_verified' => $vb4a88417->is_verified ); } if (!isset($vb4a88417['is_verified']) || !$vb4a88417['is_verified']) { header('Location: ' . $v572d4e42); exit(); } } } if (!class_exists('BrodevAccountapiInstaller')) { class BrodevAccountapiInstaller { var $bNoInstall = false; var $bNoUnInstall = false; var $sVersion; var $sFilePath; public static function Instance($sVersion) { static $inst = null; if ($inst === null) { $inst = new BrodevAccountapiInstaller($sVersion); } return $inst; } function __construct($sVersion) { $this->sVersion = $sVersion; $this->sFilePath = Phpfox::getParam('core.dir_pic'). 'accountapi'. PHPFOX_DS; $this->mChmod = 0777; } function install () { if ($this->bNoInstall) { return false; } $oDb = Phpfox::getLib('phpfox.database'); $aSql = array( "DROP TABLE IF EXISTS `". Phpfox::getT('accountapi_user'). "`", "CREATE TABLE `". Phpfox::getT('accountapi_user'). "` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `user_id` int(11) DEFAULT NULL,
				  `cloud_id` varchar(50) DEFAULT NULL,
				  `timestamp` int(11) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				)", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_gcm_user') . "`", "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_gcm_user') . "` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `gcm_regid` text,
                  `user_id` int(11) DEFAULT NULL,
                  `email` varchar(255) NOT NULL,
                  `timestamp` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                )", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_apns_user') . "`", "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_apns_user') . "` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `token` text,
                  `user_id` int(11) DEFAULT NULL,
                  `email` varchar(255) NOT NULL,
                  `timestamp` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                )", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_push_notification') . "`", "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_push_notification') . "` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `user_id` int(11) DEFAULT NULL,
                  `data` text,
                  `timestamp` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                )", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_push_notification_admincp') . "`", "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_push_notification_admincp') . "` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `data` text,
                  `timestamp` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                )", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_user_notification') . "`", "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_user_notification') . "` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `user_id` int(11) DEFAULT NULL,
                  `user_notification` varchar(100) NOT NULL,
                  PRIMARY KEY (`id`)
                )" ); if (!Phpfox::getService('brodev.product')->isFieldExist('app_access', 'session_hash')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('app_access') ." ADD `session_hash` VARCHAR( 150 ) NOT NULL "; } if (!Phpfox::getService('brodev.product')->isFieldExist('pages_feed', 'app_id')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('pages_feed') ." ADD `app_id` int(11) NOT NULL "; } if (!Phpfox::getService('brodev.product')->isFieldExist('event_feed', 'app_id')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('event_feed') ." ADD `app_id` int(11) NOT NULL "; } foreach($aSql as $sSql) { $oDb->query($sSql); } $oDb->delete(Phpfox::getT('cron'), 'module_id = \'accountapi\''); Phpfox::getLib('cache')->remove('cron'); $this->bNoInstall = true; } /**
         * function to check exist column in table
         * @param $sTable
         * @return bool
         */ function checkExistColumn($sTable) { $aRows = Phpfox::getLib('phpfox.database')->select('COLUMN_NAME') ->from('INFORMATION_SCHEMA.COLUMNS') ->where('table_name = \'' . Phpfox::getT($sTable). '\' AND table_schema = \'' . Phpfox::getParam(array('db', 'name')) . '\' AND column_name LIKE \'app_id\'') ->execute('getRows'); if(count($aRows) > 0){ $bRet = true; }else{ $bRet = false; } return $bRet; } function upgrade () { $this->bNoInstall = true; $sVersion = floatval($this->sVersion); $oDb = Phpfox::getLib('phpfox.database'); $oLibFile = Phpfox::getLib('file'); $aSql = array(); switch ($sVersion) { case '1.5': $aSql[] = "CREATE TABLE IF NOT EXISTS `". Phpfox::getT('accountapi_user'). "` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `user_id` int(11) DEFAULT NULL,
					  `cloud_id` varchar(50) DEFAULT NULL,
					  `timestamp` int(11) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					)"; break; case '2.0': @chmod($this->sFilePath, $this->mChmod); @$oLibFile->unlink($this->sFilePath. 'image.php'); @$oLibFile->unlink($this->sFilePath. '.htaccess'); $oLibFile->mkdir($this->sFilePath. 'tmp', true, $this->mChmod); break; case '2.2': $aSql[] = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_gcm_user') . "` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `gcm_regid` text,
                      `user_id` int(11) DEFAULT NULL,
                      `email` varchar(255) NOT NULL,
                      `timestamp` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    )"; $aSql[] = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_push_notification') . "` (
                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                      `user_id` int(11) DEFAULT NULL,
                      `data` text,
                      `timestamp` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    )"; break; case '2.8': if (!Phpfox::getService('brodev.product')->isFieldExist('app_access', 'session_hash')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('app_access') ." ADD `session_hash` VARCHAR( 150 ) NOT NULL "; } if (!Phpfox::getService('brodev.product')->isFieldExist('event_feed', 'app_id')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('event_feed') ." ADD `app_id` int(11) NOT NULL "; } break; case '2.9': $aSql[] = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_apns_user') . "` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `token` text,
                      `user_id` int(11) DEFAULT NULL,
                      `email` varchar(255) NOT NULL,
                      `timestamp` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    )"; break; case '3.0': $aSql[] = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_push_notification_admincp') . "` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `data` text,
                      `timestamp` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    )"; break; case '3.1': $aSql[] = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('accountapi_user_notification') . "` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `user_id` int(11) DEFAULT NULL,
                      `user_notification` varchar(100) NOT NULL,
                      PRIMARY KEY (`id`)
                    )"; break; case '3.5': if (!Phpfox::getService('brodev.product')->isFieldExist('app_access', 'session_hash')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('app_access') ." ADD `session_hash` VARCHAR( 150 ) NOT NULL "; } if (!Phpfox::getService('brodev.product')->isFieldExist('pages_feed', 'app_id')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('pages_feed') ." ADD `app_id` int(11) NOT NULL "; } if (!Phpfox::getService('brodev.product')->isFieldExist('event_feed', 'app_id')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('event_feed') ." ADD `app_id` int(11) NOT NULL "; } break; } foreach($aSql as $sSql) { $oDb->query($sSql); } $oDb->delete(Phpfox::getT('cron'), 'module_id = \'accountapi\''); Phpfox::getLib('cache')->remove('cron'); } function uninstall () { if ($this->bNoUnInstall) { return false; } $oDb = Phpfox::getLib('phpfox.database'); $aSql = array( "DROP TABLE IF EXISTS `". Phpfox::getT('accountapi_user'). "`", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_gcm_user') . "`", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_push_notification') . "`", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_push_notification_admincp') . "`", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_apns_user') . "`", "DROP TABLE IF EXISTS `" . Phpfox::getT('accountapi_user_notification') . "`" ); if (Phpfox::getService('brodev.product')->isFieldExist('app_access', 'session_hash')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('app_access') ." DROP `session_hash` "; } if (Phpfox::getService('brodev.product')->isFieldExist('pages_feed', 'app_id')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('pages_feed') ." DROP `app_id` "; } if (Phpfox::getService('brodev.product')->isFieldExist('event_feed', 'app_id')) { $aSql[] = "ALTER TABLE ". Phpfox::getT('event_feed') ." DROP `app_id` "; } foreach($aSql as $sSql) { $oDb->query($sSql); } $this->bNoUnInstall = true; } } } $oInstaller = BrodevAccountapiInstaller::Instance($sVersion); $oInstaller->{$sType}();