<?php
if (!class_exists('BrodevBettermobileInstaller')) { class BrodevBettermobileInstaller { var $sVersion; function __construct($sVersion) { $this->sVersion = $sVersion; } /**
         * import sample data
         * @return bool
         */ private function importSample() { $sCode = file_get_contents(PHPFOX_DIR_MODULE . 'bettermobile'. PHPFOX_DS . 'include' . PHPFOX_DS . 'static' . PHPFOX_DS . 'sample.txt'); $aImages = json_decode($sCode, true); foreach($aImages as $aImage) { $aVals['title'] = $aImage['title']; $aVals['image'] = $aImage['image']; $aVals['active'] = $aImage['active']; $aVals['server_id'] = 0; Phpfox::getLib('database')->insert(Phpfox::getT('brodev_bettermobile_background'), $aVals); } return true; } var $bNoInstall = false; function install() { if ($this->bNoInstall) { return false; } $oDb = Phpfox::getLib('phpfox.database'); $aSql = array( "DROP TABLE IF EXISTS `" . Phpfox::getT('brodev_bettermobile_background') . "`", "CREATE TABLE `" . Phpfox::getT('brodev_bettermobile_background') . "` (
                     `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                     `title` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                     `image` VARCHAR( 150 ) NOT NULL,
                     `server_id` VARCHAR(200) NOT NULL,
                      `active` BOOLEAN NOT NULL,
                      `time_stamp` INT NOT NULL
                )" ); foreach ($aSql as $sSql) { $oDb->query($sSql); } $this->importSample(); } function upgrade() { $this->bNoInstall = true; $sVersion = $this->sVersion; $oDb = Phpfox::getLib('phpfox.database'); $aSqls = array(); switch ($sVersion) { case '1.3': $aSqls[] = "CREATE TABLE IF NOT EXISTS`" . Phpfox::getT('brodev_bettermobile_background') . "` (
                         `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                         `title` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
                         `image` VARCHAR( 150 ) NOT NULL,
                          `active` BOOLEAN NOT NULL,
                          `time_stamp` INT NOT NULL
                        )"; break; case '4.8': $aSqls[] = "ALTER TABLE `". Phpfox::getT('brodev_bettermobile_background'). "` ADD  `server_id` VARCHAR( 200 ) NOT NULL"; break; case '4.9': if (Phpfox::getLib('setting')->isParam('bettermobile.itunes_id')) { $sItunesdId = Phpfox::getParam('bettermobile.itunes_id') == "" ? "0" : Phpfox::getParam('bettermobile.itunes_id'); $sAndroidId = Phpfox::getParam('bettermobile.android_app_id') == "" ? "0" : Phpfox::getParam('bettermobile.android_app_id'); $aVals = array(); if ($sItunesdId == "588592923") { $aVals['value']['itunes_id'] = ""; } if ($sAndroidId == "com.brodev.socialapp.android") { $aVals['value']['android_app_id'] = ""; } if (!empty($aVals)) { $aVals['value']['app_name'] = ""; Phpfox::getService('admincp.setting.process')->update($aVals); } } break; } foreach($aSqls as $sSql) { $oDb->query($sSql); } if ($sVersion == '1.3') { $this->importSample(); } } function uninstall() { $oDb = Phpfox::getLib('phpfox.database'); $aSql = array( 'DROP TABLE IF EXISTS `' . Phpfox::getT('brodev_bettermobile_background') . '`', ); foreach ($aSql as $sSql) { $oDb->query($sSql); } } } } $v8fb4f159 = Phpfox::getLib('phpfox.request'); if ($v8fb4f159->get('upgrade')) { $vc0bf0ed8 = 'upgrade'; } else if ($v8fb4f159->get('delete')) { $vc0bf0ed8 = 'uninstall'; } else if ($v8fb4f159->get('install')) { $vc0bf0ed8 = 'install'; } if ($vc0bf0ed8 != 'uninstall') { $v8c7dd922 = 'phpfox-product-brodev_better_mobile-4.9.zip'; $v2f8b4c76 = dirname(__FILE__); $v0a7c842c = dirname($v2f8b4c76) . '/license_key'; $v572d4e42 = '' . base64_decode('aHR0cDovL3d3dy5waHBmb3gucHJvL3BhZ2VzL2xpY2Vuc2UtaXNzdWU=') . ''; if (!file_exists($v0a7c842c)) { header('Location: ' . $v572d4e42); exit(); } $v71877975 = trim(file_get_contents($v0a7c842c)); if (empty($v71877975)) { header('Location: ' . $v572d4e42); exit(); } if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])) { header('Location: ' . $v572d4e42); exit(); } $vf6e57c9d = '' . base64_decode('aHR0cDovL3d3dy5waHBmb3gucHJvL2xpY2Vuc2VzL3ZlcmlmeS8=') . '' . $v71877975 . '/' . $_SERVER['HTTP_HOST'] . '/' . $v8c7dd922; $vd88fc6ed = curl_init(); curl_setopt($vd88fc6ed, CURLOPT_URL, $vf6e57c9d); curl_setopt($vd88fc6ed, CURLOPT_RETURNTRANSFER, 1); curl_setopt($vd88fc6ed, CURLOPT_TIMEOUT, 10); $vb4a88417 = curl_exec($vd88fc6ed); curl_close($vd88fc6ed); if (empty($vb4a88417)) { $vb4a88417 = file_get_contents($vf6e57c9d); } if (!empty($vb4a88417)) { $vb4a88417 = json_decode($vb4a88417, true); if (is_object($vb4a88417)) { $vb4a88417 = array( 'is_verified' => $vb4a88417->is_verified ); } if (!isset($vb4a88417['is_verified']) || !$vb4a88417['is_verified']) { header('Location: ' . $v572d4e42); exit(); } } } $oRequest = Phpfox::getLib('phpfox.request'); if ($oRequest->get('upgrade')) { $sType = 'upgrade'; } else if ($oRequest->get('delete')) { $sType = 'uninstall'; } else if ($oRequest->get('install')) { $sType = 'install'; } $oInstaller = new BrodevBettermobileInstaller($sVersion); $oInstaller->{$sType}();