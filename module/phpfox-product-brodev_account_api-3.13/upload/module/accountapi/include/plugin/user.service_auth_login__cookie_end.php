<?php
$_COOKIE[Phpfox::getParam('core.session_prefix') .  $this->_sNameCookieUserId] = $aRow['user_id'];
$_COOKIE[Phpfox::getParam('core.session_prefix') . $this->_sNameCookieHash] = $sPasswordHash;