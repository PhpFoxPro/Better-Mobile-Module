{if isset($bDefault) && $bDefault}
{else}
{if Phpfox::getParam('bettermobile.italiano')}
{literal}
<style type="text/css">
    .js_feed_comment_border ul li, .comment_mini_action ul li, .activity_feed_content_status ul li {
        float: none;
    }
</style>
{/literal}
{if Phpfox::isUser() && Phpfox::isModule('like')}
{else}
{literal}
<style type="text/css">
    .js_feed_comment_border{
        display: none;
    }
</style>
{/literal}
{/if}
{/if}
{/if}




<ul>
    {if !Phpfox::getService('profile')->timeline()}
    {if !isset($aFeed.feed_mini)}

    {if isset($bDefault) && $bDefault}
    {if $aFeed.privacy > 0 && ($aFeed.user_id == Phpfox::getUserId() || Phpfox::getUserParam('core.can_view_private_items'))}
    <li><div class="js_hover_title">{img theme='layout/privacy_icon.png' alt=$aFeed.privacy}<span class="js_hover_info">{$aFeed.privacy|privacy_phrase}</span></div></li>
    <li><span>&middot;</span></li>
    {/if}
    {/if}
    {/if}
    {/if}

    {if Phpfox::isUser() && Phpfox::isModule('like') && isset($aFeed.like_type_id)}
        {if isset($aFeed.like_item_id)}
        {module name='like.link' like_type_id=$aFeed.like_type_id like_item_id=$aFeed.like_item_id like_is_liked=$aFeed.feed_is_liked}
        {else}
        {module name='like.link' like_type_id=$aFeed.like_type_id like_item_id=$aFeed.item_id like_is_liked=$aFeed.feed_is_liked}
        {/if}

        {if Phpfox::isUser()
        && Phpfox::isModule('comment')
        && Phpfox::getUserParam('feed.can_post_comment_on_feed')
        && (isset($aFeed.comment_type_id) && $aFeed.can_post_comment)
        || (!isset($aFeed.comment_type_id) && isset($aFeed.total_comment))
        }
            {if isset($bDefault) && $bDefault}

            {else}
                {if Phpfox::getParam('bettermobile.italiano')}
                {else}
                {/if}
            {/if}
        {/if}



    {/if}



    {if Phpfox::isUser()
    && Phpfox::isModule('comment')
    && Phpfox::getUserParam('feed.can_post_comment_on_feed')
    && (isset($aFeed.comment_type_id) && $aFeed.can_post_comment)
    || (!isset($aFeed.comment_type_id) && isset($aFeed.total_comment))
    }
    {if !empty($sCustomViewType)}
    <li class="add-comment">
        <a href="{$aFeed.feed_link}add-comment/" class="{if (isset($sFeedType) && $sFeedType == 'mini') || (!isset($aFeed.comment_type_id) && isset($aFeed.total_comment))}{else}js_feed_entry_add_comment no_ajax_link{/if}">{phrase var='feed.comment'}</a>
    </li>
    {else}
    <li class="add-comment">
        <a href="{$aFeed.feed_link}add-comment/" class="">{phrase var='feed.comment'}</a>
    </li>
    {/if}
    {/if}


    {if Phpfox::isUser()
    && Phpfox::isModule('share') && !isset($aFeed.no_share)}
    {if $aFeed.privacy == '0'}
    {module name='share.link' type='feed' display='menu' url=$aFeed.feed_link title=$aFeed.feed_title sharefeedid=$aFeed.item_id sharemodule=$aFeed.type_id}
    {else}
    {module name='share.link' type='feed' display='menu' url=$aFeed.feed_link title=$aFeed.feed_title}
    {/if}
    {/if}

    {plugin call='feed.template_block_entry_2'}
    {if isset($bDefault) && $bDefault}
    {if Phpfox::isMobile() && ((defined('PHPFOX_FEED_CAN_DELETE')) || (Phpfox::getUserParam('feed.can_delete_own_feed') && $aFeed.user_id == Phpfox::getUserId()) || Phpfox::getUserParam('feed.can_delete_other_feeds'))}
    {if Phpfox::isUser() && Phpfox::isModule('like') && isset($aFeed.like_type_id)}
    <li class="dot-stt"><span>&middot;</span></li>
    {/if}
    <li class="delete-stt"><a href="#" onclick="if (confirm(getPhrase('core.are_you_sure'))){l}$.ajaxCall('feed.delete', 'id={$aFeed.feed_id}{if isset($aFeedCallback.module)}&amp;module={$aFeedCallback.module}&amp;item={$aFeedCallback.item_id}{/if}', 'GET');{r} return false;">{phrase var='feed.delete'}</a></li>
    {/if}
    {/if}
</ul>
<div class="clear"></div>
{if PHPFOX_IS_AJAX && Phpfox::getLib('request')->get('theater') == 'true'}


{elseif isset($sFeedType) &&  $sFeedType == 'view'}
{literal}
<style type="text/css">
    .report_this_item{
        padding-top: 18px;
        height: 0px;
    }
    #js_feed_content {
        padding-bottom: 1px;
    }
    .mobile_profile_header_menu
    {
        margin-top: 8px;
    }
    .js_feed_view_more_entry_holder {
        padding-bottom: 24px;
    }
</style>
<script type="text/javascript">
    //change css .like_icon if has .report_this_item
    $Behavior.initLink= function() {
        if($("div").hasClass("report_this_item"))
        {
            $(".like_icon").css('bottom',33);
            $(".comment_icon").css('bottom',33);
        }
    };
</script>
{/literal}
<div class="feed_share_custom">
    {if Phpfox::isModule('share') && Phpfox::getParam('share.share_twitter_link')}
    <div class="feed_share_custom_block" style="float: left; width: 89px;"><a href="http://twitter.com/share" class="twitter-share-button" data-url="{$aFeed.feed_link}" data-count="horizontal" data-via="{param var='feed.twitter_share_via'}">{phrase var='feed.tweet'}</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></div>
    {/if}
    {if Phpfox::isModule('share') && Phpfox::getParam('share.share_google_plus_one')}
    <div class="feed_share_custom_block" style="float: left; width: 67px;">
        <g:plusone href="{$aFeed.feed_link}" size="medium"></g:plusone>
        {literal}
        <script type="text/javascript">
            (function() {
                var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                po.src = 'https://apis.google.com/js/plusone.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
            })();
        </script>
        {/literal}
    </div>
    {/if}
    {if Phpfox::isModule('share') && Phpfox::getParam('share.share_facebook_like')}
    <div class="feed_share_custom_block" style="float: left;width: 80px">
        <iframe src="http://www.facebook.com/plugins/like.php?app_id=156226084453194&amp;href={if !empty($aFeed.feed_link)}{$aFeed.feed_link}{else}{url link='current'}{/if}&amp;send=false&amp;layout=button_count&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;width=90&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:140px; height:21px;" allowTransparency="true"></iframe>
    </div>
    {/if}
    <div class="clear"></div>
</div>
{/if}