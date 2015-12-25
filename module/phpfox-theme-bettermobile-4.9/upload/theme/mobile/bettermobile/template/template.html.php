<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author			Raymond Benc
 * @package 		Phpfox
 * @version 		$Id: template.html.php 1458 2010-01-29 19:28:49Z Raymond_Benc $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{if !PHPFOX_IS_AJAX_PAGE}
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN"
"http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="{$sLocaleDirection}" lang="{$sLocaleCode}">
<head>
    <title>{title}</title>
    {header}
</head>
{if (!Phpfox::isUser() && (Phpfox::getLib('module')->getFullControllerName() == 'mobile.index' || Phpfox::getLib('module')->getFullControllerName() == 'user.register' || Phpfox::getLib('module')->getFullControllerName() == 'mobilesignup.login' || Phpfox::getLib('module')->getFullControllerName() == 'user.login'))}
{if !Phpfox::getParam('bettermobile.set_background_is_image') || ($sImageBackground == '' && Phpfox::getParam('bettermobile.set_background_is_image')) }
<body class="login_page" style="background-color: #{param var='bettermobile.background_color'}">
{else}
<body class="login_page" style="background-image:url('{url link=$sImageBackground}');">
{/if}
{else}
<body class="{if Phpfox::getParam('bettermobile.italiano')}italiano{/if}">
{/if}

{plugin call='theme_template_body__start'}
{if Phpfox::getParam('core.site_is_offline') && Phpfox::getUserParam('core.can_view_site_offline')}
<div id="site_offline">
    {phrase var='core.the_site_is_currently_in_offline_mode'}
</div>
{/if}
<div style="overflow-x:visible; position: relative;  width: 100%;" {holder_name}>
<div id="showsidebar">
    {module name='bettermobile.sidebar'}
</div>
{if (!Phpfox::isUser() && Phpfox::getLib('module')->getFullControllerName() == 'mobile.index' && Phpfox::getParam('bettermobile.set_background_is_image') && $sImageBackground != '')}
<div id="mobile_holder" class="full_site" style="position: relative;">
{else}
<div id="mobile_holder" style="position: relative;">
{/if}

{if !Phpfox::isUser() && Phpfox::getLib('module')->getFullControllerName() == 'mobile.index'}
<div>
{else}
<div id="mobile_header" >
    {/if}
    {if Phpfox::getParam('core.site_is_offline') && !Phpfox::getUserParam('core.can_view_site_offline')}
    {else}
    {if Phpfox::getLib('module')->getFullControllerName() != 'user.register'}
    {if !Phpfox::isUser() && !Phpfox::getParam('bettermobile.show_main_menu')}
    {else}
                            <span id ="mobile_header_home1">
                            <a href="#" onclick="oShowSidebar.show(); return false;" id="mobile_header_home">Home</a>
                            {/if}
                        {else}
                        <span class="sign_up_button">
                        <a href="javascript: window.history.go(-1)" class="button_back_click" onclick="oStatus.hide();" ><div class="button_back"></div></a>

                        {/if}
                        </span>
                        {if Phpfox::isUser()}
                        <div id="notification_mobile" >
                            <div id="holder_notify">
                                {notification}
                                <div class="clear"></div>
                            </div>
                        </div>
                                <div class="clone"></div>
                        <div class="clear"></div>
                        {else}
                            {if (!Phpfox::isUser() && Phpfox::getLib('module')->getFullControllerName() != 'mobile.index')}
                                {if Phpfox::getLib('module')->getFullControllerName() != 'user.register'}
                                {param var='core.site_title'}
                                {else}
                                {phrase var='user.sign_up_for_ssitetitle' sSiteTitle=$sSiteTitle}
                                {/if}
                            {/if}
                        {/if}

                        {/if}

</div>

{if Phpfox::getParam('core.site_is_offline') && !Phpfox::getUserParam('core.can_view_site_offline')}
{else}
{if Phpfox::isUser()}


{/if}
{/if}
<div id="holder">
    {if (!Phpfox::isUser() && Phpfox::getLib('module')->getFullControllerName() == 'mobile.index')}
    <div id="main_content_holder_login">
        {else}
        <div id="main_content_holder">
            {/if}

            {else}
            <div id="main_content_holder" style="background: none">
                {/if}



                <div id="content">
                    <div class="source" style="display:  none;">
                        {if (isset($aFilterMenus) && is_array($aFilterMenus) && count($aFilterMenus)) || (count($aBlocks1))}

                        <a href="#" class="mobile_main_sub_menu icon_pages" onclick="$('.sub_section_menu').toggle(); return false;">Menu</a>
                    </div>
                    <div>
                        <div class="sub_section_menu">
                            {if (isset($aFilterMenus) && is_array($aFilterMenus) && count($aFilterMenus))}
                            <ul>
                                {foreach from=$aFilterMenus name=filtermenu item=aFilterMenu}
                                {if !isset($aFilterMenu.name)}
                                <li class="menu_line">&nbsp;</li>
                                {else}
                                <li class="{if $aFilterMenu.active}active{/if}"><a href="{$aFilterMenu.link}">{$aFilterMenu.name}</a></li>
                                {/if}
                                {/foreach}
                            </ul>
                            {/if}
                            {if Phpfox::getLib('request')->get('req1') != "pages" && !defined('PHPFOX_IS_USER_PROFILE') }
                            {if Phpfox::getLib('module')->getModuleName() == "event"}
                            {else}
                            <ul class="block1-mobile">
                                {block location='1'}
                            </ul>
                            {/if}
                            {/if}
                        </div>
                        {else}
                        {if !Phpfox::isUser() && (Phpfox::getLib('module')->getFullControllerName() == 'mobile.index' || Phpfox::getLib('module')->getFullControllerName() == 'user.register')}
                        {else}
                        {if Phpfox::isModule('messenger') && !$bIsMessengerPage}
                        <a href="#" class="mobile_chat_friend_show" onclick="oShowSidebarMessenger.show(); return false;"></a>
                        {else}
                        {if Phpfox::isModule('search')}
                        <a href="#" class="mobile_search_button" onclick="oShowSidebar.show('sFocus=true'); return false;"></a>
                        {/if}
                        {/if}
                        {/if}
                        {/if}
                    </div>
                    {if !defined('PHPFOX_IS_USER_PROFILE')}
                    {breadcrumb}
                    {/if}
                    {if (!Phpfox::isUser() && Phpfox::getLib('module')->getFullControllerName() != 'photo.index')}
                    {search}
                    {/if}
                    <div id="mobile_content">

                        {error}
                        {if isset($aBreadCrumbTitle) && count($aBreadCrumbTitle)}
                        <div id="mobile_h1_main">
                            <h1><a href="{$aBreadCrumbTitle[1]}">{$aBreadCrumbTitle[0]|clean|split:20}</a></h1>
                        </div>
                        {/if}
                        {if (!Phpfox::isUser() && Phpfox::getLib('module')->getFullControllerName() == 'user.login')
                        || (!Phpfox::isUser() && Phpfox::getLib('module')->getFullControllerName() == 'mobile.index')
                        }
                        <div class="welcome">
                            {if !empty($sMobileLogo)}
                            <a href="{url link=''}" ><img src="{$sMobileLogo}" class="v_middle" /></a>
                            {else}
                            <div class="welcome_text" style="color: #{param var='bettermobile.site_name_color'}">
                                {param var='core.site_title'}
                            </div>
                            {/if}

                        </div>

                        {if (!Phpfox::isUser() && Phpfox::getLib('module')->getFullControllerName() == 'mobile.index')}
                        {module name='user.login-block'}
                        {else}
                        {block location='2'}
                        {content}
                        {block location='4'}
                        {/if}
                        {if (Phpfox::isModule('mobilesignup') && Phpfox::getLib('module')->getFullControllerName() == 'user.login')}
                        <a href="{url link='mobilesignup.login'}" class="login_mobile">{phrase var='mobilesignup.login_mobile'}</a>
                        {/if}
                        {if Phpfox::getLib('module')->getFullControllerName() == 'mobile.index'}
                        <a class="login-facebook" href="{url link='user.login'}">{phrase var='bettermobile.login_user'}</a>
                        <a class="signup" href="{url link='user.register'}">{phrase var='user.sign_up'}</a>
                        {if (Phpfox::isModule('facebook') && Phpfox::getParam('facebook.enable_facebook_connect')) || (Phpfox::isModule('janrain') && Phpfox::getParam('janrain.enable_janrain_login'))}
                        <div class="p_top_8 facebook" style="width: 140px; margin: 0 auto;">
                            {if Phpfox::isModule('facebook') && Phpfox::getParam('facebook.enable_facebook_connect')}
                            <div class="header_login_block">
                                <div id="fb-root"></div>
                                {literal}
                                <script>
                                    window.fbAsyncInit = function() {
                                        FB.init({
                                            appId      : '{/literal}{param var='facebook.facebook_app_id'}{literal}',
                                            status     : true,
                                            xfbml      : true,
                                            channelUrl : '{/literal}{url link='current'}{literal}'
                                    });
                                    };

                                    (function(d, s, id){
                                        var js, fjs = d.getElementsByTagName(s)[0];
                                        if (d.getElementById(id)) {return;}
                                        js = d.createElement(s); js.id = id;
                                        js.src = "//connect.facebook.net/en_US/all.js";
                                        fjs.parentNode.insertBefore(js, fjs);
                                    }(document, 'script', 'facebook-jssdk'));
                                </script>
                                {/literal}
                                <a href="#" onclick="FB.login();">{phrase var='bettermobile.login_facebook'}</a>
                            </div>
                            {/if}
                        </div>
                        {/if}
                        {/if}
                        {else}

                        {if defined('PHPFOX_IS_USER_PROFILE')}
                        {module name='profile.mobile'}
                        {breadcrumb}
                        {/if}
                        {block location='2'}
                        {content}
                        {block location='4'}

                        {/if}

                    </div>
                    {literal}
                    <script type="text/javascript">$Behavior.initLeftButton = function() {oBetterMobile.showButton();}</script>
                    {/literal}
                    {if defined('PHPFOX_IS_USER_PROFILE')}
                    {module name='bettermobile.profilefooter'}
                    {/if}
                </div>
                {if !PHPFOX_IS_AJAX_PAGE}
            </div>
        </div>

    </div>
    {if Phpfox::isModule('messenger') && !$bIsMessengerPage}
    {module name='bettermobile.messenger.friend'}
    {/if}
</div>
{footer}
</body>
</html>
{/if}