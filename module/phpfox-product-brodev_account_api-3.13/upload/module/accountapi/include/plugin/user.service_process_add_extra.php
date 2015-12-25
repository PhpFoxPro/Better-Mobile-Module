<?php

	if (!Phpfox::getParam('user.profile_use_id') && !Phpfox::getParam('user.disable_username_on_sign_up') && !isset($aVals['user_name']))
	{
		$this->database()->update($this->_sTable, array('user_name' => 'profile-' . $iId), 'user_id = ' . $iId); 
	}
