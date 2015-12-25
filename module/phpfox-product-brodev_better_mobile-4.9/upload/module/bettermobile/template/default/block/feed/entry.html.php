<div class="row_feed_loop js_parent_feed_entry {if isset($aFeed.feed_mini)} row_mini {else}{if isset($bChildFeed)} row1{else}{if isset($phpfox.iteration.iFeed)}{if is_int($phpfox.iteration.iFeed/2)}row1{else}row2{/if}{if $phpfox.iteration.iFeed == 1 && !PHPFOX_IS_AJAX} row_first{/if}{else}row1{/if}{/if}{/if} js_user_feed" id="js_item_feed_{$aFeed.feed_id}">

    <div class="feed_delete_link">
        <a href="#" onclick="$Core.newfeed.show('feed_id_{$aFeed.feed_id}'); return false;" id="feed_id_{$aFeed.feed_id}">
            <img class="v_middle image_normal" src="{param var='core.path'}module/bettermobile/static/image/cancel_new.png">
            <img width="20px" height="20px" class="v_middle image_retina" src="{param var='core.path'}module/bettermobile/static/image/cancel@2x.png">
        </a>
        <div class="feed_delete_drop">
            <ul>
                {if ((defined('PHPFOX_FEED_CAN_DELETE')) || (Phpfox::getUserParam('feed.can_delete_own_feed') && $aFeed.user_id == Phpfox::getUserId()) || Phpfox::getUserParam('feed.can_delete_other_feeds'))}
                <li><a href="#" onclick="$.ajaxCall('feed.delete', 'id={$aFeed.feed_id}{if isset($aFeedCallback.module)}&amp;module={$aFeedCallback.module}&amp;item={$aFeedCallback.item_id}{/if}', 'GET'); return false;">{phrase var='bettermobile.delete'}</a></li>
                {/if}
                {if Phpfox::isModule('report') && isset($aFeed.report_module) && isset($aFeed.force_report)}
                    <li><a href="#?call=report.add&amp;height=100&amp;width=400&amp;type={$aFeed.report_module}&amp;id={$aFeed.item_id}" class="inlinePopup activity_feed_report" title="{$aFeed.report_phrase}">{phrase var='report.report'}</a></li>
                {/if}
            </ul>
        </div>
    </div>
    {plugin call='feed.template_block_entry_1'}
    <div class="activity_feed_image">
        {img user=$aFeed  suffix='_50_square' max_width=50 max_height=50}
    </div><!-- // .activity_feed_image -->

    {template file='bettermobile.block.feed.content'}

    {plugin call='feed.template_block_entry_3'}
</div><!-- // #js_item_feed_{$aFeed.feed_id} -->