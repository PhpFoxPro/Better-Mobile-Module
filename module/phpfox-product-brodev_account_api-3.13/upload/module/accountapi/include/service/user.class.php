<?php

class Accountapi_Service_User extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('user');
    }

    /**
     * Add new user
     * @param $aVals
     */
    public function add($aVals, $iUserGroupId = null) {

        Phpfox::getLib('setting')->setParam('user.force_user_to_upload_on_sign_up', false);

        $iId = Phpfox::getService('user.process')->add($aVals, $iUserGroupId);

        return $iId;
    }

    /**
     * process add user info
     * @param $aRow
     */
    private function _process(&$aRow) {
        $aRow['photo_50px'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['user_server_id'],
                'suffix' => '_50',
                'return_url' => true
            )
        );

        $aRow['photo_50px_square'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['user_server_id'],
                'suffix' => '_50_square',
                'return_url' => true
            )
        );

        $aRow['photo_75px_square'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['user_server_id'],
                'suffix' => '_75_square',
                'return_url' => true
            )
        );

        $aRow['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['user_server_id'],
                'suffix' => '_75_square',
                'return_url' => true
            )
        );

        $aRow['photo_120px'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['user_server_id'],
                'suffix' => '_120',
                'return_url' => true
            )
        );

        $aRow['photo_120px_square'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['user_server_id'],
                'suffix' => '_120_square',
                'return_url' => true
            )
        );

        $aRow['photo_original'] = Phpfox::getLib('image.helper')->display(array(
                'user' => $aRow,
                'server_id' => $aRow['user_server_id'],
                'suffix' => '',
                'return_url' => true
            )
        );
        $aRow['gender_phrase'] = Phpfox::getService('user')->gender($aRow['gender']);
    }
    private function _getSalt($iTotal = 3)
    {
        $sSalt = '';
        for ($i = 0; $i < $iTotal; $i++)
        {
            $sSalt .= chr(rand(33, 91));
        }

        return $sSalt;
    }

    public function checkEmail($sEmail)
    {
        $iCnt = $this->database()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where("email = '" . $this->database()->escape($sEmail) . "'")
            ->execute('getField');

        if ($iCnt)
        {
            return Phpfox::getPhrase('accountapi.there_is_already_an_account_assigned_with_the_email_email', array('email' => strip_tags($sEmail)));
        }

        if (!Phpfox::getService('ban')->check('email', $sEmail))
        {
            return Phpfox::getPhrase('user.this_email_is_not_allowed_to_be_used');
        }

        return true;
    }

    public function getUserInfoExtra($iUserId) {

    }

    /**
     * get user list by filter by sFile and value oValue with json_encode with array
     * @param $oValue
     * @param $sField
     * @return array
     */
    public function getUserBy($iUserId, $oValue, $sField) {
        $aValues = json_decode($oValue, true);
        $iCount = 0;

        if (empty($aValues)) {
            return array($iCount, array());
        }
        $sWhere = "u.profile_page_id = 0 AND u.status_id = 0";
        $sWhere .= " AND u.$sField in ('". implode("', '", $aValues) ."')";

        $aUsers = $this->database()
            ->select(Phpfox::getUserField())
            ->from(Phpfox::getT('user'), 'u')
            ->where($sWhere)
            ->execute('getRows');
        if (!empty($aUsers)) {
            foreach($aUsers as $iKey => $aUser) {               
                if (!Phpfox::getService('friend')->isFriend($iUserId, $aUser['user_id']) && ($iUserId != $aUser['user_id'])) {
                    $this->_process($aUsers[$iKey]);
                } else {
                    unset($aUsers[$iKey]);
                }

            }
        }
        
        $iCount = count($aUsers);
        return array($iCount, $aUsers);
    }
	public function checkKey($sAppId, $aUser) {
        $oServiceAccountapiCore = Phpfox::getService('accountapi.core');
        $oServiceAccountapi = Phpfox::getService('accountapi');
		$oServiceAccountapi->setThrough(1);
		$aApp = $oServiceAccountapi->getAppById($sAppId, $aUser['user_id']);

        if (empty($aApp['is_installed'])) {
            $oServiceAccountapi->install($aApp['app_id'], $aUser['user_id'], array());
        }

        $sKey = $oServiceAccountapi->getKey($aApp['app_id'], $aUser);
        $aKey = json_decode(Phpfox::getService('api')->createToken($sKey), true);
        $this->database()->update(Phpfox::getT('log_session'), array('user_id' => $aUser['user_id']), 'session_hash = \'' .Phpfox::getLib('session')->get('session') .'\'');
        $aKey['user_id'] = $aUser['user_id'];

        Phpfox::getService('user.auth')->setUser($aUser);

        $aKey['phrases'] = $oServiceAccountapiCore->getPhrases($aUser['language_id']);

        if(Phpfox::getParam('accountapi.admob_publish_key') && Phpfox::getUserParam('accountapi.can_see_ads')) {
            $aKey['key_admob'] = Phpfox::getParam('accountapi.admob_publish_key');
        }

        if (Phpfox::isModule('messenger') && Phpfox::getParam('messenger.key')) {
            $aKey['chat_server_key'] = Phpfox::getParam('messenger.key');
            $aKey['chat_server_secret'] = Phpfox::getParam('messenger.secret');
            $aKey['chat_server_url'] = Phpfox::getParam('messenger.api_url');
            $aKey['apns_token'] = Phpfox::getService('accountapi.apns')->getAPNSToken($aUser['user_id']);
        }

        $aKey['enable_check_in'] = false;
        if (Phpfox::getParam('feed.enable_check_in')) {
            $aKey['enable_check_in'] = Phpfox::getParam('feed.enable_check_in');
        }

        $aKey['google_key'] = Phpfox::getParam('accountapi.gcm_api_key');

		return $aKey;
	}

    /**
     * @param $iUserId
     * @return array
     */
    public function getUserSetting($iUserId) {
        $aUser = Phpfox::getService('user')->get($iUserId, true);
        $aUser['can_change_email'] = Phpfox::getUserParam('user.can_change_email');
        if (!empty($aUser['birthday']))
        {
            $aUser = array_merge($aUser, Phpfox::getService('user')->getAgeArray($aUser['birthday']));
        }
        $aGateways = Phpfox::getService('api.gateway')->getActive();
        if (!empty($aGateways))
        {
            $aGatewayValues = Phpfox::getService('api.gateway')->getUserGateways($aUser['user_id']);

            foreach ($aGateways as $iKey => $aGateway)
            {
                foreach ($aGateway['custom'] as $iCustomKey => $aCustom)
                {
                    $aGateways[$iKey]['custom'][$iCustomKey]['field'] = $iCustomKey;
                    if (isset($aGatewayValues[$aGateway['gateway_id']]['gateway'][$iCustomKey]))
                    {
                        $aGateways[$iKey]['custom'][$iCustomKey]['user_value'] = $aGatewayValues[$aGateway['gateway_id']]['gateway'][$iCustomKey];

                    }
                }
            }
        }
        $bChangeUserName = false;
        if (Phpfox::getUserParam('user.can_change_own_user_name') && !Phpfox::getParam('user.profile_use_id')) {
            $iTotal = Phpfox::getUserParam('user.total_times_can_change_user_name');
            if ($aUser['total_user_change'] < $iTotal  || $iTotal == 0) {
                $bChangeUserName = true;
            }
        }
        $bChangeFullName = false;
        if (Phpfox::getUserParam('user.can_change_own_full_name')) {
            $iTotal = Phpfox::getUserParam('user.total_times_can_change_own_full_name');
            if ($aUser['total_full_name_change'] < $iTotal || $iTotal == 0) {
                $bChangeFullName = true;
            }
        }
        $aUser['aGateways'] = $aGateways;
        $aCurrenciesGet = Phpfox::getService('core.currency')->get();
        $aCurrencies[] = array(
            'symbol' => "",
            'name' => Phpfox::getPhrase('core.select'),
            'value' => "",
            'isChoose' => $aUser['default_currency'] == null ? true : false
        );
        $iCount = 1;
        if (!empty($aCurrenciesGet)) {
            foreach($aCurrenciesGet as $iKey => $aCurrency) {
                $aCurrencies[$iCount]['value'] = $iKey;
                $aCurrencies[$iCount]['name'] = Phpfox::getPhrase($aCurrency['name']);
                $aCurrencies[$iCount]['symbol'] = $aCurrency['symbol'];
                $aCurrencies[$iCount]['isChoose'] = $aUser['default_currency'] == $iKey ? true : false;
                $iCount++;
            }
        }
        $aLanguagesGet = Phpfox::getService('language')->get(array('l.user_select = 1'));
        $aLanguage = array();
        foreach($aLanguagesGet as $iKey => $aItem) {
            $aLanguage[] = array(
                'name' => $aItem['title'],
                'value' => $aItem['language_id']
            );
        }
        $aUser['Currencies'] = array_values($aCurrencies);
        $aUser['Languages'] = array_values($aLanguage);
        $aTimeZonesGet = Phpfox::getService('core')->getTimeZones();
        $aTimeZones = array();
        foreach($aTimeZonesGet as $sKey => $sTimeZone) {
            $aTimeZones[] = array(
                'name' => $sTimeZone,
                'value' => $sKey
            );
        }
        $aUser['Timezone'] = array_values($aTimeZones);
        $aUser['can_change_user_name'] = $bChangeUserName;
        $aUser['can_change_full_name'] = $bChangeFullName;

        return $aUser;
    }
}
