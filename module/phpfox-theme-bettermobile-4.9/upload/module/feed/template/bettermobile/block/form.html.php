{literal}
<style type="text/css">
    .mobile_profile_header_menu {
        margin-top: 8px;
    }

    .js_hover_title img {
        margin-top: 5px;
    }

    li.menu_privacy_drop {
        margin: -7px 8px 185px 8px;
        width: 94%;
        height: 32px;
    }
</style>
{/literal}
<div class="activity_feed_form_share">
    {if !isset($bSkipShare)}
    <ul class="activity_feed_form_attach">
        {if !Phpfox::isMobile()}
        <li class="share">{phrase var='feed.share'}:</li>
        {/if}
        {if isset($aFeedCallback.module)}
        <li><a href="#" class="status_button_show" onclick="oStatus.show(); return false;" rel="global_attachment_status" class="">
            <div class="status_button"><i></i>{phrase var='feed.status'}</div>
            <div><span class="activity_feed_link_form_ajax">{$aFeedCallback.ajax_request}</span></div>
            <div class="drop"></div>
        </a></li>
        {elseif !isset($bFeedIsParentItem) && (!defined('PHPFOX_IS_USER_PROFILE') || (defined('PHPFOX_IS_USER_PROFILE')
        && isset($aUser.user_id) && $aUser.user_id == Phpfox::getUserId()))}
        <li><a href="#" class="status_button_show" onclick="oStatus.show(); return false;" rel="global_attachment_status" class="">
            <div class="status_button"><i></i>{phrase var='feed.status'}</div>
            <div><span class="activity_feed_link_form_ajax">user.updateStatus</span></div>
            <div class="drop"></div>
        </a></li>
        {else}
        <li><a href="#" class="status_button_show" onclick="oStatus.show(); return false;" rel="global_attachment_status">
            <div class="status_button"><i></i>{phrase var='feed.status'}</div>
            <div><span class="activity_feed_link_form_ajax">feed.addComment</span></div>
            <div class="drop"></div>
        </a></li>
        {/if}

        {literal}
        <style type="text/css">
            ul.activity_feed_form_attach li {
                width: 50%;
            }
        </style>
        {/literal}

        {foreach from=$aFeedStatusLinks item=aFeedStatusLink name=feedlinks}

        {if $aFeedStatusLink.module_id == 'photo'}
        {if Phpfox::getParam('bettermobile.enable_share_other')}
        {literal}
        <style type="text/css">
            ul.activity_feed_form_attach li {
                width: 33%;
            }

            ul.activity_feed_form_attach li.other_share_button {
                width: 34%;
            }
        </style>
        {/literal}
        {/if}
        <li>
            <a href="#" onclick="oFeedOthers.show1(); return false;"
               style="background-position:{if Phpfox::getService('profile')->timeline() && $phpfox.iteration.feedlinks >= 3}5px center{else}center left{/if};"
               rel="global_attachment_{$aFeedStatusLink.module_id}"{if $aFeedStatusLink.no_input}
            class="no_text_input"{/if}>
            <div class="{$aFeedStatusLink.module_id}_button"><i></i>{$aFeedStatusLink.title|convert}</div>
            <div>
                {if $aFeedStatusLink.is_frame}
                <span class="activity_feed_link_form">{url link=''$aFeedStatusLink.module_id'.frame'}</span>
                {else}
                <span
                    class="activity_feed_link_form_ajax">{$aFeedStatusLink.module_id}.{$aFeedStatusLink.ajax_request}</span>
                {/if}
                <span class="activity_feed_extra_info">{$aFeedStatusLink.description|convert}</span>
            </div>
            <div class="drop"></div>
            </a>
        </li>
        {/if}

        {/foreach}
        {if Phpfox::getParam('bettermobile.enable_share_other')}
        <li class="other_share_button">
            <a href="#" class="status_button_show" onclick="oOtherShare.show(); return false;">
                <div class="status_button">{phrase var='bettermobile.other'}<i></i></div>
            </a>
        </li>
        <div class="other_share">
            <ul>
                {template file='bettermobile.block.checkin'}
                {foreach from=$aFeedStatusLinks item=aFeedStatusLink name=feedlinks}
                {if $aFeedStatusLink.module_id != 'photo'}
                {if isset($aFeedCallback.module) && $aFeedStatusLink.no_profile}
                {else}
                {if ($aFeedStatusLink.no_profile && !isset($bFeedIsParentItem) && (!defined('PHPFOX_IS_USER_PROFILE') ||
                (defined('PHPFOX_IS_USER_PROFILE') && isset($aUser.user_id) && $aUser.user_id == Phpfox::getUserId())))
                || !$aFeedStatusLink.no_profile}
                <li>
                    <a href="#" onclick="oFeedOthers.show1(); return false;"
                       style="background-image:url('{img theme='feed/'$aFeedStatusLink.icon'' return_url=true}'); background-repeat:no-repeat; background-position:{if Phpfox::getService('profile')->timeline() && $phpfox.iteration.feedlinks >= 3}5px center{else}center left{/if};"
                       rel="global_attachment_{$aFeedStatusLink.module_id}"{if $aFeedStatusLink.no_input}
                    class="no_text_input"{/if}>
                    <div class="status_button">{$aFeedStatusLink.title|convert}</div>
                    {if $aFeedStatusLink.is_frame}
                    <span class="activity_feed_link_form">{url link=''$aFeedStatusLink.module_id'.frame'}</span>
                    {else}
                    <span class="activity_feed_link_form_ajax">{$aFeedStatusLink.module_id}.{$aFeedStatusLink.ajax_request}</span>
                    {/if}
                    </a>
                </li>
                {/if}
                {/if}
                {/if}
                {/foreach}
            </ul>
        </div>
        {/if}

    </ul>
    {/if}
    <div class="clear"></div>
</div>
<div class="activity_feed_form_share_process">{img theme='ajax/add.gif' class='v_middle'}</div>
<div class="activity_feed_form" style="display: none;">
    <form method="post" action="#" id="js_activity_feed_form" enctype="multipart/form-data">
        <div id="js_custom_privacy_input_holder"></div>
        {if isset($aFeedCallback.module)}
        <div><input type="hidden" name="val[callback_item_id]" value="{$aFeedCallback.item_id}"/></div>
        <div><input type="hidden" name="val[callback_module]" value="{$aFeedCallback.module}"/></div>
        <div><input type="hidden" name="val[parent_user_id]" value="{$aFeedCallback.item_id}"/></div>
        {/if}
        {if isset($bFeedIsParentItem)}
        <div><input type="hidden" name="val[parent_table_change]" value="{$sFeedIsParentItemModule}"/></div>
        {/if}
        {if defined('PHPFOX_IS_USER_PROFILE') && isset($aUser.user_id) && $aUser.user_id != Phpfox::getUserId()}
        <div><input type="hidden" name="val[parent_user_id]" value="{$aUser.user_id}"/></div>
        {/if}
        <div class="activity_feed_form_holder">
            <div id="activity_feed_upload_error" style="display:none;">
                <div class="error_message" id="activity_feed_upload_error_message"></div>
            </div>

            <div class="global_attachment_holder_section" id="global_attachment_status" style="display:none;">
                <div>
                    <textarea {if isset($aPage)} id="pageFeedTextarea" {else} {if isset($aEvent)} id="eventFeedTextarea"
                    {else} {if isset($bOwnProfile) && $bOwnProfile == false}id="profileFeedTextarea" {/if}{/if}{/if}
                    cols="60" rows="8" name="val[user_status]" placeholder="{if isset($aFeedCallback.module) ||
                    defined('PHPFOX_IS_USER_PROFILE')}{phrase var='feed.write_something'}{else}{phrase
                    var='feed.what_s_on_your_mind'}{/if}">
                    {$aFeedCallback.module}
                    </textarea>
                </div>

            </div>
            {if Phpfox::getService('bettermobile')->isCheckIn()}
                {if Phpfox::getParam('feed.enable_check_in') && (Phpfox::getParam('core.ip_infodb_api_key') != '' || Phpfox::getParam('core.google_api_key') != '')}
                <div id="js_add_location">
                    <div><input type="hidden" id="val_location_latlng" name="val[location][latlng]"></div>
                    <div><input type="hidden" id="val_location_name" name="val[location][name]"></div>
                    <div id="js_loction_show">
                        <span class="value"></span>
                        <a href="#" onclick="$Core.Feed.cancelCheckIn(); return false;">
                            <img class="v_middle image_normal" src="{param var='core.path'}module/bettermobile/static/image/cancel.png">
                            <img width="20px" class="v_middle image_retina" src="{param var='core.path'}module/bettermobile/static/image/cancel@2x.png">
                        </a>
                    </div>
                    <div class="location_title">{phrase var='bettermobile.choose_location'}</div>
                    <div id="js_add_location_suggestions" style="overflow-y: auto;"></div>
                    <div id="js_feed_check_in_map"></div>
                </div>
                {/if}
            {/if}

            {foreach from=$aFeedStatusLinks item=aFeedStatusLink}
            {if !empty($aFeedStatusLink.module_block)}
            {module name=$aFeedStatusLink.module_block}
            {/if}
            {/foreach}
            {if Phpfox::isModule('egift')}
            {module name='egift.display'}
            {/if}
        </div>
        <div class="activity_feed_form_button">
            <div class="activity_feed_form_button_status_info">
                <textarea id="activity_feed_textarea_status_info" cols="60" rows="8" name="val[status_info]"></textarea>
            </div>

            <div class="activity_feed_form_button_position">

                {if defined('PHPFOX_IS_PAGES_VIEW') && $aPage.is_admin && $aPage.page_id !=
                Phpfox::getUserBy('profile_page_id')}
                <div class="activity_feed_pages_post_as_page">
                    {phrase var='feed.post_as'}:
                    <select name="custom_pages_post_as_page">
                        <option value="{$aPage.page_id}">{$aPage.full_name|clean|shorten:20:'...'}</option>
                        <option value="0">{$sGlobalUserFullName|shorten:20:'...'}</option>
                    </select>
                </div>
                {else}

                {/if}
                {if Phpfox::isModule('share')}
                <div class="feed_share_on_holder timeline_date_holder">
                    {if Phpfox::getParam('share.share_on_facebook')}
                    <div class="feed_share_on_item"><a href="#"
                                                       onclick="$(this).toggleClass('active'); $.ajaxCall('share.connect', 'connect-id=facebook', 'GET'); return false;">{img
                        theme='layout/facebook.png' class='v_middle'} {phrase var='feed.facebook'}</a></div>
                    {/if}
                    {if Phpfox::getParam('share.share_on_twitter')}
                    <div class="feed_share_on_item"><a href="#"
                                                       onclick="$(this).toggleClass('active'); $.ajaxCall('share.connect', 'connect-id=twitter', 'GET'); return false;">{img
                        theme='layout/twitter.png' class='v_middle'} {phrase var='feed.twitter'}</a></div>
                    {/if}
                    <div class="clear"></div>
                    <div><input type="hidden" name="val[connection][facebook]" value="0"
                                id="js_share_connection_facebook" class="js_share_connection"/></div>
                    <div><input type="hidden" name="val[connection][twitter]" value="0" id="js_share_connection_twitter"
                                class="js_share_connection"/></div>
                </div>
                {/if}


                {if isset($aFeedCallback.module)}
                {else}
                {if !isset($bFeedIsParentItem) && (!defined('PHPFOX_IS_USER_PROFILE') ||
                (defined('PHPFOX_IS_USER_PROFILE') && isset($aUser.user_id) && $aUser.user_id == Phpfox::getUserId()))}
                {module name='privacy.form' privacy_name='privacy' privacy_type='mini'}
                {/if}
                {/if}
                <div class="clear"></div>
            </div>
        </div>
        <div class="activity_feed_form_button_position_button">
            <input type="submit" value="{phrase var='feed.share'}" onclick="oStatus.hide();" id="activity_feed_submit"
                   class="button_status"/>
        </div>
        <a href="#" class="button_cancel_click" onclick="oStatus.hide(); return false;">
            <div class="button_cancel">Cancel</div>
        </a>
        <a href="#" class="button_back_click" onclick="oStatus.hide();{if Phpfox::getService('bettermobile')->isCheckIn()}$Core.Feed.cancelCheckIn();{/if} return false;">
            <div class="button_back"></div>
        </a>

    </form>
    <div class="activity_feed_form_iframe"></div>
</div>