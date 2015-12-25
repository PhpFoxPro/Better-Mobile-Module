
{if !Phpfox::isUser() && Phpfox::getService('bettermobile')->isCheckIn()}
<script type="text/javascript">
    $Behavior.prepareInit = function()
    {l}
    $Core.Feed.sIPInfoDbKey = '{param var="core.ip_infodb_api_key"}';
    $Core.Feed.sGoogleKey = '{param var="core.google_api_key"}';

    {if isset($aVisitorLocation)}
        $Core.Feed.setVisitorLocation({$aVisitorLocation.latitude}, {$aVisitorLocation.longitude} );
    {else}

    {/if}

        $Core.Feed.googleReady();
        $Core.Feed.init();
        {r}
</script>
<script type="text/javascript" src="{param var='core.url_module'}bettermobile/static/jscript/places.js"></script>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key={param var="core.google_api_key"}&sensor=true&libraries=places"></script>
{/if}
{if Phpfox::isUser() && !PHPFOX_IS_AJAX && $sCustomViewType === null}
{if (Phpfox::getUserBy('profile_page_id') > 0 && defined('PHPFOX_IS_USER_PROFILE'))
|| (isset($aFeedCallback.disable_share) && $aFeedCallback.disable_share)
|| (defined('PHPFOX_IS_USER_PROFILE') && !Phpfox::getService('user.privacy')->hasAccess('' . $aUser.user_id . '', 'feed.share_on_wall'))
|| (defined('PHPFOX_IS_USER_PROFILE') && !Phpfox::getUserParam('profile.can_post_comment_on_profile') && $aUser.user_id != Phpfox::getUserId())
}

{else}
{if !Phpfox::getService('profile')->timeline()}
    {template file='bettermobile.block.feed.form'}
{/if}
{/if}
{/if}
{if !defined('PHPFOX_IS_USER_PROFILE') && !PHPFOX_IS_AJAX}

{/if}
{if Phpfox::getParam('feed.refresh_activity_feed') > 0 && Phpfox::getLib('module')->getFullControllerName() == 'core.index-member'}
<div id="activity_feed_updates_link_holder">
    <a href="#" id="activity_feed_updates_link_single" class="activity_feed_updates_link" onclick="return $Core.loadMoreFeeds();">{phrase var='feed.1_new_update'}</a>
    <a href="#" id="activity_feed_updates_link_plural" class="activity_feed_updates_link" onclick="return $Core.loadMoreFeeds();">{phrase var='feed.span_id_js_new_update_view_span_new_updates'}</a>
</div>
{/if}
{if Phpfox::isModule('captcha') && Phpfox::getUserParam('captcha.captcha_on_comment')}
{module name='captcha.form' sType='comment' captcha_popup=true}
{/if}
<div id="feed"><a name="feed"></a></div>
<div id="js_feed_content">
    {if $sCustomViewType !== null}
    <h2>{$sCustomViewType}</h2>
    {/if}
    <div id="js_new_feed_comment"></div>
    <div id="js_new_feed_update"></div>
    {if Phpfox::getService('profile')->timeline()}

    {else}
    {foreach from=$aFeeds name=iFeed item=aFeed}
    {if isset($aFeed.feed_mini) && !isset($bHasRecentShow)}
    {if $bHasRecentShow = true}{/if}
    <div class="activity_recent_holder">
        <div class="activity_recent_title">
            {phrase var='feed.recent_activity'}
        </div>
        {/if}
        {if !isset($aFeed.feed_mini) && isset($bHasRecentShow)}
    </div>
    {unset var=$bHasRecentShow}
    {/if}

    <div class="js_feed_view_more_entry_holder" >
        {template file='bettermobile.block.feed.entry'}
    </div>

    {if isset($aFeed.more_feed_rows) && is_array($aFeed.more_feed_rows) && count($aFeed.more_feed_rows)}
    {foreach from=$aFeed.more_feed_rows item=aFeed}
    {if $bChildFeed = true}{/if}
    <div class="js_feed_view_more_entry_holder" >
        {template file='bettermobile.block.feed.entry'}
    </div>
    {/foreach}
    {unset var=$bChildFeed}
    {/if}
    {/foreach}
    {/if}

    {if isset($bHasRecentShow)}
</div>
{/if}
{if $sCustomViewType === null}
{if defined('PHPFOX_IN_DESIGN_MODE')}

{else}
{if count($aFeeds) || (isset($bForceReloadOnPage) && $bForceReloadOnPage)}
<div id="feed_view_more">
    <div id="js_feed_pass_info" style="display:none;">page={$iFeedNextPage}{if defined('PHPFOX_IS_USER_PROFILE') && isset($aUser.user_id)}&profile_user_id={$aUser.user_id}{/if}{if isset($aFeedCallback.module)}&callback_module_id={$aFeedCallback.module}&callback_item_id={$aFeedCallback.item_id}{/if}</div>
    <div id="feed_view_more_loader">{img theme='ajax/add.gif'}</div>
    <a href="{if Phpfox::getLib('module')->getFullControllerName() == 'core.index-visitor'}{url link='core.index-visitor' page=$iFeedNextPage}{else}{url link='current' page=$iFeedNextPage}{/if}" onclick="$(this).hide(); $('#feed_view_more_loader').show(); $.ajaxCall('feed.viewMore', 'page={$iFeedNextPage}{if defined('PHPFOX_IS_USER_PROFILE') && isset($aUser.user_id)}&profile_user_id={$aUser.user_id}{/if}{if isset($aFeedCallback.module)}&callback_module_id={$aFeedCallback.module}&callback_item_id={$aFeedCallback.item_id}{/if}', 'GET'); return false;" class="global_view_more no_ajax_link">{phrase var='feed.view_more'}</a>
</div>
{else}
{if Phpfox::getService('profile')->timeline()}
{else}
<br />
<div class="message js_no_feed_to_show">{phrase var='feed.there_are_no_new_feeds_to_view_at_this_time'}</div>
{/if}
{/if}
{/if}
{/if}
{if !PHPFOX_IS_AJAX || (PHPFOX_IS_AJAX && count($aFeedVals))}
</div>
{/if}
{if Phpfox::getParam('feed.refresh_activity_feed') > 0 && Phpfox::getLib('module')->getFullControllerName() == 'core.index-member'}
<script type="text/javascript">
    $Core.reloadActivityFeed();
</script>
{/if} 
<div class="global_view_more" style="display: none;"></div>
