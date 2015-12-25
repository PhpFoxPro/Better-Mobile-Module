{literal}
<style type="text/css" xmlns="http://www.w3.org/1999/html">
    .activity_feed_pages_post_as_page {
        left: 8px;
        position: absolute;
        top: -7px;
    }

    #mobile_h1_main {
        display: none;
    }
    #mobile_profile_header {
        min-height: 100px;
        padding-bottom: 45px;
    }

    #mobile_profile_photo {
        min-height: 90px;
    }
</style>
{/literal}

{module name='bettermobile.cover'}
<div id="mobile_profile_header" class="pages_profile">
    <div id="mobile_profile_photo">
        <div id="mobile_profile_photo_image" {if Phpfox::getParam('bettermobile.image_radius')} class="image_radius" {/if}>
            {if $aPage.is_app}
            {img server_id=0 path='app.url_image' file=$aPage.aApp.image_path suffix='_120_square' max_width='175'
            max_height='175' title=$aPage.aApp.app_title}
            {else}
                {if $bNewVersion}
                {img thickbox=true server_id=$aPage.image_server_id title=$aPage.title path='core.url_user' file=$aPage.image_path suffix='_75' max_width='75' max_height='75'}
                {else}
            {img thickbox=true server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.image_path suffix='_120' max_width='75' max_height='75'}
                {/if}

            {/if}
        </div>
        <div class="mobile_profile_name">
            <div class="mobile_profile_name_content">
                <a href="{$aPage.link}">{$aPage.title|clean|split:30|shorten:50:'...'}</a>
            </div>

        </div>
        <div class="clear"></div>
        <div class="mobile_profile_info">
            {$aPage.category_name|convert}{if $aPage.page_type == '1'}<span> &middot; </span>{if $aPage.total_like >
            1}{phrase var='pages.total_members' total=$aPage.total_like|number_format}{elseif $aPage.total_like ==
            1}{phrase var='pages.1_member'}{else}{phrase var='pages.no_members'}{/if}{/if}
        </div>
        <div class="clear"></div>
    </div>
    <div id="mobile_profile_photo_name">
        <ul>
            {if !Phpfox::getUserBy('profile_page_id') && Phpfox::isUser()}
                {if $aPage.reg_method == '2' && !isset($aPage.is_invited) && $aPage.page_type == '1'}
                {else}
                    {if isset($aPage.is_reg) && $aPage.is_reg}
                    {else}
                        {literal}
                        <style type="text/css">


                            .activity_feed_form_button_position_button {
                                top: -38px !important;
                            }

                            #mobile_h1_main {
                                display: none;
                            }
                        </style>
                        {/literal}
                        <li id="pages_like"
                        {if $aPage.is_liked} style="display:none;"{/if}>
                        <a href="#" id=""
                           onclick="$(this).parent().hide(); $('#js_add_pages_unlike').show(); {if $aPage.page_type == '1' && $aPage.reg_method == '1'} $.ajaxCall('pages.signup', 'page_id={$aPage.page_id}'); {else}$.ajaxCall('like.add', 'type_id=pages&amp;item_id={$aPage.page_id}');{/if} return false;">
                            {if $aPage.page_type == '1'}
                            {phrase var='pages.join'}
                            {else}
                            {phrase var='pages.like'}
                            {/if}
                        </a>
                        </li>
                    {/if}
                {/if}
            {else}
            {literal}
            <style type="text/css">

                #mobile_profile_photo {
                    min-height: 147px;
                }


            </style>
            {/literal}
            {/if}
            {if !Phpfox::getUserBy('profile_page_id')}


            <li id="pages_like"
            {if !$aPage.is_liked} style="display:none;"{/if}><a href="#"
                                                                onclick="$(this).parent().hide(); $('#pages_like_join_position').show(); $.ajaxCall('like.delete', 'type_id=pages&amp;item_id={$aPage.page_id}'); return false;">{if
            $aPage.page_type == '1'}{phrase var='bettermobile.unjoin'}{else}{phrase var='pages.unlike'}{/if}</a></li>
            {/if}

            {module name='bettermobile.pages.like'}


        </ul>
    </div>
    <div class="clear"></div>
</div>

{if $aPage.view_id == '1'}
<div class="message js_moderation_off" id="js_approve_message">
    {phrase var='pages.this_page_is_pending_an_admins_approval_before_it_can_be_displayed_publicly'}
</div>
{/if}
{if !Phpfox::isMobile() && (Phpfox::getUserParam('pages.can_moderate_pages') || $aPage.is_admin)}
<div class="item_bar">
    <div class="item_bar_action_holder">
        {if $aPage.view_id == '1' && Phpfox::getUserParam('pages.can_moderate_pages')}
        <a href="#" class="item_bar_approve item_bar_approve_image" onclick="return false;" style="display:none;"
           id="js_item_bar_approve_image">
            {img theme='ajax/add.gif'}
        </a>
        <a href="#" class="item_bar_approve"
           onclick="$(this).hide(); $('#js_item_bar_approve_image').show(); $.ajaxCall('pages.approve', 'page_id={$aPage.page_id}'); return false;">
            {phrase var='pages.approve'}
        </a>
        {/if}
        <a href="#" class="item_bar_action" onclick=" return false;">
				<span>
					{phrase var='pages.actions'}
				</span>
        </a>
        <ul>
            {template file='pages.block.link'}
        </ul>
    </div>
</div>
{/if}

{if $bCanViewPage}
{if isset($aWidget)}
<div class="item_view_content">
    {$aWidget.text|parse}
</div>
{elseif $sCurrentModule == 'info' && !$iViewCommentId}
<div class="item_view_content">
    {$aPage.text|parse}
</div>
{elseif $sCurrentModule == 'pending'}
{if count($aPendingUsers)}
{foreach from=$aPendingUsers name=pendingusers item=aPendingUser}
<div id="js_pages_user_entry_{$aPendingUser.signup_id}"
     class="row1{if $phpfox.iteration.pendingusers == 1} row_first{/if}">
    <div class="go_left" style="width:50px;">
        {img user=$aPendingUser suffix='_50_square' max_width='50' max_height='50'}
        <a href="#{$aPendingUser.signup_id}" class="moderate_link" rel="pages">{phrase var='pages.moderate'}</a>
    </div>
    <div style="margin-left:55px">
        <span class="row_title_link">{$aPendingUser|user|shorten:50:'...'}</span>
    </div>
    <div class="clear"></div>
</div>
{/foreach}
{moderation}
{else}
{/if}
{else}
{if $bHasPermToViewPageFeed}
    {if !$bNewVersion}
        {module name='feed.display'}
    {/if}
{else}
{phrase var='pages.unable_to_view_this_section_due_to_privacy_settings'}
{/if}
{/if}
{else}
<div class="message">
    {if isset($aPage.is_invited) && $aPage.is_invited}
    {phrase var='pages.you_have_been_invited_to_join_this_community'}
    {else}
    {phrase var='pages.due_to_privacy_settings_this_page_is_not_visible'}
    {if $aPage.page_type == '1' && $aPage.reg_method == '2'}
    {phrase var='pages.this_page_is_also_invite_only'}
    {/if}
    {/if}
</div>
{/if}
{module name='bettermobile.pages.photo'}