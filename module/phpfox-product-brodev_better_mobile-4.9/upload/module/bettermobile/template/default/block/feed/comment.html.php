{if isset($bIsViewingComments) && $bIsViewingComments}
<div id="comment-view"><a name="#comment-view"></a></div>
<div class="message js_feed_comment_border">
    {phrase var='comment.viewing_a_single_comment'} <a href="{$aFeed.feed_link}">{phrase
    var='comment.view_all_comments'}</a>
</div>
{/if}

{if isset($sFeedType)}
<div class="js_parent_feed_entry parent_item_feed">
    {/if}

    <div class="js_feed_comment_border">


        {plugin call='feed.template_block_comment_border'}
        {plugin call='core.template_block_comment_border_new'}
        {if (isset($bNewLink) && $bNewLink) || (isset($bDefault) && $bDefault)}
        {else}
        <div class="like_count" {if isset($bIsViewingComments) && $bIsViewingComments}style="display:none;"{/if}>
            {if Phpfox::isUser() && Phpfox::isModule('like') && isset($aFeed.like_type_id)}
            <div class="js_comment_like_holder" id="js_feed_like_holder_{$aFeed.feed_id}">
                <div id="js_like_body_{$aFeed.feed_id}" class="like_image">
                    {if isset($aFeed.feed_total_like)}
                    {template file='bettermobile.block.display'}
                    {/if}
                </div>
            </div>
            {/if}

            {if Phpfox::getParam('like.allow_dislike') &&Phpfox::isUser() && Phpfox::isModule('like') && isset($aFeed.like_type_id)}

                    {template file='bettermobile.block.displayactions'}

            {/if}

            {if $aFeed.type_id != 'friend'}
            {if isset($aFeed.total_comment)}
            <div class="comment_feed_link">
                <div class="image_normal ">
                    <a href="{$aFeed.feed_link}">
                        {img theme='feed/commet_icon.png'}
                        {$aFeed.total_comment}
                    </a>
                </div>
                <div class="image_retina">
                    <a href="{$aFeed.feed_link}">
                        {img theme='feed/commet_icon@2x.png'}
                        {$aFeed.total_comment}
                    </a>
                </div>
            </div>
            <div class="share_feed_link">
                <div class="image_normal ">
                    <a href="{$aFeed.feed_link}">
                        {img theme='feed/share_icon.png'}
                        {$aFeed.total_comment}
                    </a>
                </div>
                <div class="image_retina">
                    <a href="{$aFeed.feed_link}">
                        {img theme='feed/share_icon@2x.png'}
                        {$aFeed.total_comment}
                    </a>
                </div>
            </div>
            {/if}
            {/if}
        </div>
        {/if}
        {if !isset($aFeed.feed_mini)}
        <div class="comment_mini_link_like"
        {if isset($bDefault) && $bDefault}
        {else}
        {if (Phpfox::isModule('like') && isset($aFeed.like_type_id)) || (Phpfox::isModule('comment')
        && Phpfox::getUserParam('feed.can_post_comment_on_feed')
        && (isset($aFeed.comment_type_id) && $aFeed.can_post_comment)
        || (!isset($aFeed.comment_type_id) && isset($aFeed.total_comment)))}{else}style="display:none;"{/if}
        {/if}>
        {template file='bettermobile.block.feed.link'}

    </div>
    {/if}
    {if isset($bDefault)}
    {if $bDefault || isset($aFeed.isDefault)|| $sCustomViewType !== null}

    <div class="clear"></div>
    <div class="comment_mini_content_holder"
    {if isset($sFeedType) && $sFeedType == 'view' && $aFeed.can_post_comment}{else}{if isset($aFeed.likes) ||
    (isset($aFeed.total_comment) && $aFeed.total_comment > 0)}{else}{if ((isset($aFeed.comments) &&
    !count($aFeed.comments)) || !isset($aFeed.comments))} style="display:none;"{/if}{/if}{/if}>
    <div class="comment_mini_content_holder_icon"></div>
    <div class="comment_mini_content_border">
        <div class="js_comment_like_holder" id="js_feed_like_holder_{$aFeed.feed_id}">
            <div id="js_like_body_{$aFeed.feed_id}">
                {if isset($aFeed.likes) && is_array($aFeed.likes)}
                {template file='like.block.display'}
                {/if}
            </div>
        </div>
        <!-- // #js_feed_like_holder_{$aFeed.feed_id} -->

        {if Phpfox::isModule('comment') && Phpfox::getParam('feed.allow_comments_on_feeds')}
        <div id="js_feed_comment_post_{$aFeed.feed_id}" class="js_feed_comment_view_more_holder">
            {if isset($sFeedType) && $sFeedType == 'view'}

            {else}
            {if isset($aFeed.comment_type_id) && isset($aFeed.total_comment) && (isset($sFeedType) && $sFeedType ==
            'mini' ? $aFeed.total_comment > 0 : $aFeed.total_comment >
            Phpfox::getParam('comment.total_comments_in_activity_feed'))}
            <div class="comment_mini comment_mini_link_holder" id="js_feed_comment_view_more_link_{$aFeed.feed_id}">
                <div class="comment_mini_link_image">
                    {img theme='misc/comment.png' class='v_middle'}
                </div>
                <div class="comment_mini_link_loader" id="js_feed_comment_ajax_link_{$aFeed.feed_id}"
                     style="display:none;">{img theme='ajax/add.gif' class='v_middle'}
                </div>
                <div class="comment_mini_link">
                    <a href="#" class="comment_mini_link_block comment_mini_link_block_hidden" style="display:none;"
                       onclick="return false;">{phrase var='feed.loading'}</a>
                    <a href="#"{if
                    isset($sFeedType) && $sFeedType == 'mini'}{else}{if
                    Phpfox::getParam('comment.total_amount_of_comments_to_load') >
                    $aFeed.total_comment}onclick="$('#js_feed_comment_ajax_link_{$aFeed.feed_id}').show();
                    $(this).parent().find('.comment_mini_link_block_hidden').show(); $(this).hide();
                    $.ajaxCall('comment.viewMoreFeed', 'comment_type_id={$aFeed.comment_type_id}&amp;item_id={$aFeed.item_id}&amp;feed_id={$aFeed.feed_id}',
                    'GET'); return false;"{/if}{/if} class="comment_mini_link_block no_ajax_link">{phrase
                    var='comment.view_all_total_left_comments' total_left=$aFeed.total_comment}</a>
                </div>
            </div>
            <!-- // #js_feed_comment_view_more_link_{$aFeed.feed_id} -->
            {/if}
            {if isset($aFeed.total_comment) && !isset($aFeed.comment_type_id) && $aFeed.total_comment > 0}
            <div class="comment_mini comment_mini_link_holder" id="js_feed_comment_view_more_link_{$aFeed.feed_id}">
                <div class="comment_mini_link_image">
                    {img theme='misc/comment.png' class='v_middle'}
                </div>
                <div class="comment_mini_link">
                    <a href="{if isset($aFeed.feed_link_comment)}{$aFeed.feed_link_comment}{else}{$aFeed.feed_link}{/if}comment/"
                       class="comment_mini_link_block">{phrase var='comment.view_all_total_left_comments'
                        total_left=$aFeed.total_comment}</a>
                </div>
            </div>
            {/if}
            {/if}
            {if isset($aFeed.comments) && count($aFeed.comments)}
            {if isset($sFeedType) && $sFeedType == 'view' && $aFeed.total_comment >
            Phpfox::getParam('comment.comment_page_limit')}
            <div class="comment_mini" id="js_feed_comment_pager_{$aFeed.feed_id}">
                {pager}
            </div>
            {/if}
            <div id="js_feed_comment_view_more_{$aFeed.feed_id}">
                {parse_image width=200 height=200}
                {foreach from=$aFeed.comments name=comments item=aComment}
                {template file='comment.block.mini'}
                {/foreach}
                {parse_image clear=true}
            </div>
            <!-- // #js_feed_comment_view_more_{$aFeed.feed_id} -->
            {else}
            <div id="js_feed_comment_view_more_{$aFeed.feed_id}"></div>
            <!-- // #js_feed_comment_view_more_{$aFeed.feed_id} -->
            {/if}
        </div>
        <!-- // #js_feed_comment_post_{$aFeed.feed_id} -->
        {/if}

        {if isset($sFeedType) && $sFeedType == 'mini'}

        {else}
        {if Phpfox::isModule('comment')
        && isset($aFeed.comment_type_id)
        && Phpfox::getParam('feed.allow_comments_on_feeds')
        && Phpfox::isUser()
        && $aFeed.can_post_comment
        && Phpfox::getUserParam('feed.can_post_comment_on_feed')
        }
        <div class="js_feed_comment_form"
        {if isset($sFeedType) && $sFeedType == 'view'} id="js_feed_comment_form_{$aFeed.feed_id}"{/if}>
        <div class="js_comment_feed_textarea_browse"></div>
        <div class="{if isset($sFeedType) &&  $sFeedType == 'view'} feed_item_view{/if} comment_mini comment_mini_end">
            <form method="post" action="#" class="js_comment_feed_form">
                <div><input type="hidden" name="val[type]" value="{$aFeed.comment_type_id}"/></div>
                <div><input type="hidden" name="val[item_id]" value="{$aFeed.item_id}"/></div>
                <div><input type="hidden" name="val[parent_id]" value="0" class="js_feed_comment_parent_id"/></div>
                <div><input type="hidden" name="val[is_via_feed]" value="{$aFeed.feed_id}"/></div>
                {if Phpfox::isUser()}
                <div class="comment_mini_image"
                {if isset($sFeedType) && $sFeedType == 'view'} {else}style="display:none;"{/if}>
                {img user=$aGlobalUser suffix='_50_square' max_width='32' max_height='32'}
        </div>
        {/if}
        <div
            class="{if isset($sFeedType) &&  $sFeedType == 'view'}comment_mini_content {/if}comment_mini_textarea_holder">
            <div><input type="hidden" name="val[default_feed_value]" value="{phrase var='feed.write_a_comment'}"/></div>
            <div class="js_comment_feed_value">{phrase var='feed.write_a_comment'}</div>
            <textarea cols="60" rows="4" name="val[text]" class="js_comment_feed_textarea"
                      id="js_feed_comment_form_textarea_{$aFeed.feed_id}">{if isset($sFeedType) && $sFeedType == 'view'
                && Phpfox::getUserParam('comment.wysiwyg_on_comments') && Phpfox::getParam('core.wysiwyg') ==
                'tiny_mce'}{else}{phrase var='feed.write_a_comment'}{/if}</textarea>

            <div class="js_feed_comment_process_form">{phrase var='feed.adding_your_comment'}{img
                theme='ajax/add.gif'}
            </div>
        </div>
        <div class="feed_comment_buttons_wrap" style="display:block;">
            <div class="js_feed_add_comment_button t_right">
                <input type="submit" value="{phrase var='feed.comment'}" class="button"/>
            </div>
        </div>
        {if !PHPFOX_IS_AJAX && !Phpfox::isMobile() && isset($sFeedType) && $sFeedType == 'view' &&
        Phpfox::getUserParam('comment.wysiwyg_on_comments') && Phpfox::getParam('core.wysiwyg') == 'tiny_mce'}
        <div><input type="hidden" name="val[is_in_view]" value="1"/></div>
        <script type="text/javascript">
            $Behavior.commentPreLoadTinymce = function () {
                l
            }
            customTinyMCE_init('js_feed_comment_form_textarea_{$aFeed.feed_id}');
            $Behavior.commentPreLoadTinymce = function () {
                l
            }
            {
                r
            }
            {
                r
            }
        </script>
        {/if}
        </form>
    </div>
</div>
{/if}
{/if}

</div><!-- // .comment_mini_content_border -->
</div>
{/if}
{/if}


{if Phpfox::isModule('report') && isset($aFeed.report_module) && (isset($sFeedType) && $sFeedType != 'mini')}
<div class="report_this_item">
    <a href="#?call=report.add&amp;height=100&amp;width=400&amp;type={$aFeed.report_module}&amp;id={$aFeed.item_id}"
       class="item_bar_flag inlinePopup" title="{$aFeed.report_phrase}">{$aFeed.report_phrase}</a>
</div>
{if Phpfox::isModule('captcha') && Phpfox::getUserParam('captcha.captcha_on_comment')}
{module name='captcha.form' sType='comment' captcha_popup=true}
{/if}
{/if}
</div>
{if isset($sFeedType)}
</div>
{/if}