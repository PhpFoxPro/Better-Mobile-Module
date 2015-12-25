<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 11/16/12
 * Time: 2:12 PM
 * To change this template use File | Settings | File Templates.
 */

if (Phpfox::isModule('bettermobile')) {
    if (Phpfox::isMobile()) {
        define('PHPFOX_SKIP_IM', true);
    }
//if (Phpfox::getLib('setting')->isParam('profile_menus')) {

    if (Phpfox::getLib('setting')->isParam('profile_menus')) {
        $aProfileMenus = Phpfox::getParam('profile_menus');
        if (!empty($aProfileMenus)) {
            Phpfox::getLib('template')->buildSectionMenu('profile', $aProfileMenus);
        }
    }

    //}
    $sControllerName = Phpfox::getLib('module')->getFullControllerName();
    if(Phpfox::isMobile() || $sControllerName == 'forum.forum' ){
        //check version
        $sVersion = Phpfox::getVersion();
        $aVersion = preg_split('[\.]', $sVersion);
        if ($aVersion[0] >= 3 && $aVersion[1] >= 5) {
            $bNewVersion = true;
        }

        Phpfox::getLib('template')
            ->assign(array(
                'bNewVersion' => $bNewVersion,
                'iTotalActivityPoints' => (int) Phpfox::getUserBy('activity_points')
            )
        );
        // check language
        $aLang = Phpfox::getLib('locale')->getLang();
        if ($aLang['direction'] == 'rtl')
        {
            Phpfox::getLib('template')->setHeader(array('
                    <script type="text/javascript" language="javascript">
                    var bRtl = true;
                    </script>'
            ));
            Phpfox::getLib('template')->setHeader('cache', array(
                    'rtl.css' => 'module_bettermobile'
                )
            );
        } else {
            Phpfox::getLib('template')->setHeader(array('
                    <script type="text/javascript" language="javascript">
                    var bRtl = false;
                    </script>'
            ));
        }

        if (Phpfox::getParam('bettermobile.set_background_is_image')) {
            $aImage = Phpfox::getService('bettermobile.background')->getActive();
            if (!empty($aImage)) {
                $sImage = sprintf($aImage['image'], '');
                Phpfox::getLib('template')
                    ->assign(array(
                    'sImageBackground' => Phpfox::getLib('image.helper')->display(array(
                            'path' => 'bettermobile.image_url',
                            'server_id' => $aImage['server_id'],
                            'file' => $aImage['image'],
                            'return_url' => true
                        )
                    )
                ));
            } else {
                $sImage = '';
                Phpfox::getLib('template')
                    ->assign(array(
                    'sImageBackground' => ''
                ));
            }



        }
        $aLayout2 = array(
            'mobile.index',
            'profile.index',
            'event.view',
            'feed.index',
            'pages.view'
        );

        //if (!isset($this->_aVars['aFeed']['feed_view_comment']))
        //  $this->_aVars['bNewLink'] = true;

        if (in_array($sControllerName, $aLayout2)) {
            $bDefault = false;

            Phpfox::getLib('template')->assign(array(
                'bDefault' => $bDefault,
            ));
        }else{
            $bDefault = true;
            Phpfox::getLib('template')->assign(array(
                'bDefault' => $bDefault
            ));
        }
        if (Phpfox::isModule('accountapi') && Phpfox::getLib('session')->get('social_app')) {

        } else {
            $sItunesdId = Phpfox::getParam('bettermobile.itunes_id') == "" ? "0" : Phpfox::getParam('bettermobile.itunes_id');
            if ($sItunesdId != 0) {
                $sLinkTunes = Phpfox::getParam('bettermobile.link_itunes');
                if ($sLinkTunes != "") {
                    $sLinkTunes = ", app-argument=". $sLinkTunes;
                }
            }
            $sAndroidId = Phpfox::getParam('bettermobile.android_app_id') == "" ? "0" : Phpfox::getParam('bettermobile.android_app_id');
            if ($sItunesdId != "0" || $sAndroidId != "0") {
                Phpfox::getLib('template')->setHeader(array(
                    'smartbanner.js' => 'module_bettermobile',
                    'smartbanner.css' => 'module_bettermobile',
                    '<meta name="apple-itunes-app" content="app-id='. $sItunesdId . $sLinkTunes .'"/>',
                    '<meta name="google-play-app" content="app-id='. $sAndroidId .'">',
                    '<script type="text/javascript" language="javascript">
                    $Behavior.initSmartBanner = function() {
                    $.smartbanner({
                    title:\''. Phpfox::getParam('bettermobile.app_name') .'\'
                    });
                    }
                    </script>'
                ));
            }
        }

        if (Phpfox::isModule('messenger') && Phpfox::isUser()) {
            $aToken = Phpfox::getService('messenger.api')->getToken(Phpfox::getUserId());
            $bIsMessengerPage = true;
            if ($aToken['status']) {
                $bIsMessengerPage = ((Phpfox::getLib('module')->getFullControllerName()  == "messenger.index"));
                if (!$bIsMessengerPage) {
                    $oProcess = Phpfox::getService('messenger.process');

                    Phpfox::getLib('template')->setHeader(array(
                        '<script type="text/javascript">$Behavior.initMobileMessenger = function(){$Core.betterMobileMessengerHandle.setIntervalTime('. $oProcess->getChatCacheTime() * 40000 .');
                    $Core.betterMobileMessengerHandle.setLink(\''. Phpfox::getLib('url')->makeUrl('messenger') .'\');
                    $Core.betterMobileMessengerHandle.init();
                    oShowSidebarMessenger.setMessenger(true);}</script>',
                        'messenger.js' => 'module_bettermobile',
                        'messenger.css' => 'module_bettermobile'
                    ));

                }
            }


        } else {
            $bIsMessengerPage = false;
        }
        Phpfox::getLib('template')->assign(array(
            'bIsMessengerPage' => $bIsMessengerPage
        ));

        Phpfox::getLib('template')->setHeader(array(
            'sidebar.js' => 'module_bettermobile',
            'newsfeed.js' => 'module_bettermobile',
            'update.js' => 'module_notification',
            'custom.css' => 'module_bettermobile'
        ));
        Phpfox::getLib('template')->setHeader(array(
            '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />',
            '<link rel="apple-touch-icon" href="'. Phpfox::getParam('core.path') .'module/bettermobile/static/image/social-icon.png" />',
        ));


        $aCheck = Phpfox::getLib('request')->get('req1');
        $aCheck2 = Phpfox::getLib('request')->get('req2');
        if(!Phpfox::isUser() && Phpfox::getParam('bettermobile.must_login')){
//            if($aCheck2 == "login" && $aCheck == "user"){
//                if(count(Phpfox_Error::get())){
//                    Phpfox::getLib('url')->send('', null, implode('.', Phpfox_Error::get()));
//                }
//                Phpfox::getLib('url')->send('');
//            }
        }else{
            if($aCheck == ""){
                Phpfox::getLib('url')->send('feed');
            }
        }

        //check like count show
        $aStatus = Phpfox::getLib('request')->get('status-id');
        if($aStatus == ""){
            $bNewLink = false;
        } else {
            $bNewLink = true;
        }

        Phpfox::getLib('template')->assign(array(
            'bNewLink' => $bNewLink,
        ));


        //check ios
        if(Phpfox::getLib('request')->isIOS()){
            Phpfox::getLib('template')->setHeader(array('
                    <script type="text/javascript" language="javascript">
                    var iOs = true;
                    </script>'
            ));
        }else{
            Phpfox::getLib('template')->setHeader(array('
                    <script type="text/javascript" language="javascript">
                    var iOs = false;
                    </script>'
            ));
        }

        //if (!Phpfox::isUser()) {
        Phpfox::getLib('template')->assign(array(
            'sMobileLogo' => Phpfox::getService('bettermobile.template')->getStyleLogo()
        ));
        //}

    }
}