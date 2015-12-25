<?php
// auto login
if (isset($_GET['loginToken']) && $sToken = $_GET['loginToken']) {
    $aUser = Phpfox::getLib('database')
        ->select('u.user_id, u.email, u.user_name, u.password')
        ->from(Phpfox::getT('app_access'), 'aa')
        ->leftJoin(Phpfox::getT('user'), 'u', 'aa.user_id = u.user_id')
        ->where('aa.token_key = "' . base64_decode($sToken) . '"')
        ->execute('getRow')
    ;

    // found the user
    if ($aUser) {

        //get facebook id
        $iFacebookId = Phpfox::getService('accountapi.facebook')->getFacebookId($aUser['user_id']);

        if (Phpfox::getParam('user.login_type') == 'email') {
            $aUser['login'] = $aUser['email'];
        }  else {
            $aUser['login'] = $aUser['user_name'];
        }

        if (!empty($iFacebookId)) {
            Phpfox::getLib('database')->update(Phpfox::getT('fbconnect'), array('is_unlinked' => 1), 'user_id = ' . (int) $aUser['user_id']);

            $oUserAuth = Phpfox::getService('user.auth');
            $aUserSession = $oUserAuth->getUserSession();
            $aUserSession['fb_is_unlinked'] = true;
            $aUserSession = $oUserAuth->setUser($aUserSession);

            list($bLogged, $aUser) = (Phpfox::getService('user.auth')->login($aUser['user_name'], null, false, 'user_name', true));
        } else {
            list($bLogged, $aUser) = (Phpfox::getService('user.auth')->login($aUser['login'], $aUser['password'], false, Phpfox::getParam('user.login_type'), true));
        }

        // need to reconstruct
        $aUser = Phpfox::getService('user')->getUser($aUser['user_id']);
        Phpfox::getService('user.auth')->setUser($aUser);
        Phpfox::getLib('session')->set('social_app', true);
    }

}

// hide header?
if (isset($_GET['hideHeader']) && $_GET['hideHeader']) {
    Phpfox::getLib('session')->set('social_app', true);
}